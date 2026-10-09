<?php

namespace App\Services;

use App\Models\Rule;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RuleSearchService
{
    public const RRF_K = 60;

    protected EmbeddingService $embeddingService;

    public function __construct(EmbeddingService $embeddingService)
    {
        $this->embeddingService = $embeddingService;
    }

    /**
     * Perform Hybrid Search on regulatory rules combining MySQL Full-Text Search
     * and Google Gemini Vector Search with Reciprocal Rank Fusion (RRF k=60).
     *
     * @param string $query
     * @param int $limit
     * @param string $sortBy ('vector_rank' or 'rrf_score')
     * @return Collection
     */
    public function search(string $query, int $limit = 5, string $sortBy = 'vector_rank'): Collection
    {
        $trimmedQuery = trim($query);
        if ($trimmedQuery === '') {
            return collect();
        }

        $candidateLimit = max($limit * 4, 20);

        return $this->searchHybridRRF($trimmedQuery, $limit, $candidateLimit, $sortBy);
    }

    /**
     * Hybrid Search combining MySQL Full-Text Search and Vector Search with Reciprocal Rank Fusion (RRF).
     *
     * @param string $query
     * @param int $limit
     * @param int $candidateLimit
     * @param string $sortBy ('vector_rank' or 'rrf_score')
     * @return Collection
     */
    public function searchHybridRRF(string $query, int $limit = 5, int $candidateLimit = 20, string $sortBy = 'vector_rank'): Collection
    {
        // 1. Retrieve Keyword Candidates (MySQL FTS / Lexical match)
        $keywordCandidates = $this->retrieveKeywordCandidates($query, $candidateLimit);

        // 2. Retrieve Vector Candidates (Google Gemini Embeddings Cosine Similarity)
        $vectorCandidates = $this->retrieveVectorCandidates($query, $candidateLimit);

        // Map keyword ranks (rule_id => ['rank' => int, 'score' => float, 'rule' => Rule])
        $keywordRankMap = [];
        foreach ($keywordCandidates as $index => $item) {
            $keywordRankMap[$item['rule']->id] = [
                'rank' => $index + 1, // 1-based rank
                'score' => $item['score'] ?? null,
                'rule' => $item['rule'],
            ];
        }

        // Map vector ranks (rule_id => ['rank' => int, 'similarity' => float, 'rule' => Rule])
        $vectorRankMap = [];
        foreach ($vectorCandidates as $index => $item) {
            $vectorRankMap[$item['rule']->id] = [
                'rank' => $index + 1, // 1-based rank
                'similarity' => $item['similarity'],
                'rule' => $item['rule'],
            ];
        }

        // 3. Collect unique rule IDs across both retrieval streams
        $allRuleIds = array_unique(array_merge(
            array_keys($keywordRankMap),
            array_keys($vectorRankMap)
        ));

        // 4. Compute Reciprocal Rank Fusion (RRF) Score:
        // RRF(d) = sum( 1 / (k + rank_m(d)) ) for each stream m (k = 60)
        $fusedResults = [];
        $k = self::RRF_K;

        foreach ($allRuleIds as $id) {
            $kwInfo = $keywordRankMap[$id] ?? null;
            $vecInfo = $vectorRankMap[$id] ?? null;

            $rule = $kwInfo['rule'] ?? $vecInfo['rule'];

            $kwRank = $kwInfo['rank'] ?? null;
            $vecRank = $vecInfo['rank'] ?? null;

            $rrfScore = 0.0;
            if ($kwRank !== null) {
                $rrfScore += 1.0 / ($k + $kwRank);
            }
            if ($vecRank !== null) {
                $rrfScore += 1.0 / ($k + $vecRank);
            }

            // Determine Match Type
            if ($kwRank !== null && $vecRank !== null) {
                $matchType = 'hybrid';
                $matchTypeLabel = 'Hybrid Match (FTS + Vector)';
            } elseif ($vecRank !== null) {
                $matchType = 'vector';
                $matchTypeLabel = 'Semantic Vector Match';
            } else {
                $matchType = 'keyword';
                $matchTypeLabel = 'Exact Keyword Match';
            }

            $fusedResults[] = (object) [
                'id' => $rule->id,
                'rule' => $rule,
                'document_title' => $rule->document_title,
                'document_type' => $rule->document_type,
                'article_no' => $rule->article_no,
                'content_chunk' => $rule->content_chunk,
                'snippet' => $rule->getExcerpt(220),
                'rrf_score' => round($rrfScore, 5),
                'keyword_rank' => $kwRank,
                'vector_rank' => $vecRank,
                'cosine_similarity' => $vecInfo ? round($vecInfo['similarity'], 4) : null,
                'fts_score' => $kwInfo ? ($kwInfo['score'] !== null ? round((float) $kwInfo['score'], 4) : null) : null,
                'match_type' => $matchType,
                'match_type_label' => $matchTypeLabel,
            ];
        }

        // Sort results: prioritize vector rank (#1, #2, #3... first), with RRF score as tie-breaker
        usort($fusedResults, function ($a, $b) use ($sortBy) {
            if ($sortBy === 'vector_rank') {
                $rankA = $a->vector_rank ?? PHP_INT_MAX;
                $rankB = $b->vector_rank ?? PHP_INT_MAX;

                if ($rankA !== $rankB) {
                    return $rankA <=> $rankB;
                }
            }

            return $b->rrf_score <=> $a->rrf_score;
        });

        return collect(array_slice($fusedResults, 0, $limit));
    }

    /**
     * Retrieve candidate rules via MySQL Full-Text Search with resilient fallback.
     *
     * @param string $query
     * @param int $limit
     * @return array<int, array{rule: Rule, score: float|null}>
     */
    protected function retrieveKeywordCandidates(string $query, int $limit = 20): array
    {
        $results = [];
        $driver = DB::getDriverName();

        // 1. Try MySQL Full-Text Search MATCH(...) AGAINST(...)
        if ($driver === 'mysql') {
            try {
                $rawResults = Rule::selectRaw('rules.*, MATCH(document_title, article_no, content_chunk) AGAINST(? IN NATURAL LANGUAGE MODE) AS fts_score', [$query])
                    ->whereRaw('MATCH(document_title, article_no, content_chunk) AGAINST(? IN NATURAL LANGUAGE MODE)', [$query])
                    ->orderByDesc('fts_score')
                    ->limit($limit)
                    ->get();

                if ($rawResults->isNotEmpty()) {
                    foreach ($rawResults as $rule) {
                        $results[] = [
                            'rule' => $rule,
                            'score' => (float) $rule->fts_score,
                        ];
                    }
                    return $results;
                }
            } catch (Exception $e) {
                Log::warning('MySQL Full-Text Search query failed, falling back to lexical matching: ' . $e->getMessage());
            }
        }

        // 2. Resilient fallback for Khmer Unicode text and non-MySQL environments
        $tokens = array_filter(
            preg_split('/[\s\p{P}]+/u', $query, -1, PREG_SPLIT_NO_EMPTY) ?: [$query],
            fn($t) => mb_strlen($t, 'UTF-8') >= 2
        );

        $likeQuery = Rule::query()
            ->where(function ($q) use ($query, $tokens) {
                $q->where('content_chunk', 'like', "%{$query}%")
                    ->orWhere('document_title', 'like', "%{$query}%")
                    ->orWhere('article_no', 'like', "%{$query}%");

                foreach ($tokens as $token) {
                    $q->orWhere('content_chunk', 'like', "%{$token}%")
                      ->orWhere('document_title', 'like', "%{$token}%");
                }
            })
            ->limit($limit)
            ->get();

        foreach ($likeQuery as $rule) {
            $content = $rule->document_title . ' ' . $rule->article_no . ' ' . $rule->content_chunk;
            $count = mb_substr_count($content, $query, 'UTF-8');
            foreach ($tokens as $token) {
                $count += mb_substr_count($content, $token, 'UTF-8') * 0.5;
            }

            $results[] = [
                'rule' => $rule,
                'score' => (float) max(1.0, $count),
            ];
        }

        // Sort by computed relevance score
        usort($results, fn($a, $b) => ($b['score'] ?? 0) <=> ($a['score'] ?? 0));

        return $results;
    }

    /**
     * Retrieve candidate rules via Semantic Vector Cosine Similarity.
     *
     * @param string $query
     * @param int $limit
     * @return array<int, array{rule: Rule, similarity: float}>
     */
    protected function retrieveVectorCandidates(string $query, int $limit = 20): array
    {
        // 1. Generate query embedding vector using Gemini
        $queryVector = $this->embeddingService->generate($query);

        // dd(json_encode($queryVector), count($queryVector));

        // 2. Retrieve all rules with valid embeddings
        $rules = Rule::whereNotNull('embedding')->get();

        if ($rules->isEmpty()) {
            return [];
        }

        $candidates = [];

        // 3. Compute cosine similarity for each rule
        foreach ($rules as $rule) {
            if (!is_array($rule->embedding) || empty($rule->embedding)) {
                continue;
            }

            $similarity = Rule::cosineSimilarity($queryVector, $rule->embedding);

            $candidates[] = [
                'rule' => $rule,
                'similarity' => $similarity,
            ];
        }

        // Sort descending by similarity
        usort($candidates, fn($a, $b) => $b['similarity'] <=> $a['similarity']);

        return array_slice($candidates, 0, $limit);
    }
}
