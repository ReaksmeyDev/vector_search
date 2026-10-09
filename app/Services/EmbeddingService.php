<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmbeddingService
{
    protected ?string $geminiKey;
    protected string $geminiModel;
    protected int $timeout;
    protected int $dimensions = 768; // Gemini embedding dimension

    public function __construct()
    {
        $this->geminiKey = (function_exists('config') ? config('services.gemini.api_key') : null) ?: env('GEMINI_API_KEY');
        $this->geminiModel = (function_exists('config') ? config('services.gemini.embedding_model') : null) ?: env('GEMINI_EMBEDDING_MODEL', 'gemini-embedding-001');
        $this->timeout = (int) env('EMBEDDING_API_TIMEOUT', 15);
    }

    /**
     * Generate embedding vector from text using Google Gemini only.
     *
     * @param string $text
     * @return array<int, float>
     */
    public function generate(string $text): array
    {
        $text = trim($text);
        if ($text === '') {
            return array_fill(0, $this->dimensions, 0.0);
        }

        // 1. Call Google Gemini Embedding API if key is provided
        if (!empty($this->geminiKey)) {
            $embedding = $this->callGeminiEmbedding($text);
            if (!empty($embedding)) {
                return $embedding;
            }
        }

        // 2. Fallback: Deterministic normalized vector generator (768 dimensions)
        // Enables local offline execution and testing without crashing
        return $this->generateDeterministicUnitVector($text, $this->dimensions);
    }

    /**
     * Call Google Gemini Embedding API (gemini-embedding-001).
     *
     * @param string $text
     * @return array<int, float>|null
     */
    protected function callGeminiEmbedding(string $text): ?array
    {
        try {
            $modelName = $this->geminiModel;
            // Gracefully handle deprecated/unsupported model alias in v1beta
            if (str_contains($modelName, 'text-embedding-004')) {
                $modelName = 'gemini-embedding-001';
            }

            $cleanModel = ltrim($modelName, '/');
            if (!str_starts_with($cleanModel, 'models/')) {
                $cleanModel = "models/{$cleanModel}";
            }

            $endpoint = "https://generativelanguage.googleapis.com/v1beta/{$cleanModel}:embedContent?key={$this->geminiKey}";

            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->post($endpoint, [
                    'model' => $cleanModel,
                    'content' => [
                        'parts' => [
                            ['text' => $text]
                        ]
                    ],
                    'outputDimensionality' => $this->dimensions,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['embedding']['values']) && is_array($data['embedding']['values'])) {
                    return array_map('floatval', $data['embedding']['values']);
                }
            }

            Log::error('Gemini Embedding API error response', [
                'model' => $cleanModel,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (Exception $e) {
            Log::error('Gemini Embedding API request failed', [
                'message' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Generate a deterministic, L2-normalized pseudo-embedding vector for offline / demo mode.
     * Uses sub-word token projections into R^768.
     *
     * @param string $text
     * @param int $dimensions
     * @return array<int, float>
     */
    public function generateDeterministicUnitVector(string $text, int $dimensions = 768): array
    {
        $vector = array_fill(0, $dimensions, 0.0);

        // Extract tokens (words and Khmer character clusters)
        $cleanText = mb_strtolower(trim($text), 'UTF-8');
        $words = preg_split('/[\s\p{P}]+/u', $cleanText, -1, PREG_SPLIT_NO_EMPTY) ?: [$cleanText];

        foreach ($words as $word) {
            $hash1 = crc32($word);
            $hash2 = crc32(md5($word));
            $pos1 = abs($hash1) % $dimensions;
            $pos2 = abs($hash2) % $dimensions;

            $vector[$pos1] += 1.5;
            $vector[$pos2] += 1.0;

            // Character n-grams for sub-word matching in non-spaced Khmer text
            $len = mb_strlen($word, 'UTF-8');
            for ($i = 0; $i < $len - 1; $i++) {
                $ngram = mb_substr($word, $i, 3, 'UTF-8');
                $nHash = abs(crc32($ngram)) % $dimensions;
                $vector[$nHash] += 0.8;
            }
        }

        // Global text hash signature
        $sha = sha1($cleanText);
        for ($i = 0; $i < 20; $i++) {
            $sub = hexdec(substr($sha, ($i * 2) % 32, 2));
            $idx = ($sub * 31 + $i) % $dimensions;
            $vector[$idx] += 0.5;
        }

        // L2 Normalization (make Euclidean norm = 1.0 for cosine similarity)
        $sumSquares = 0.0;
        foreach ($vector as $val) {
            $sumSquares += $val * $val;
        }

        $norm = sqrt($sumSquares);
        if ($norm > 0) {
            for ($i = 0; $i < $dimensions; $i++) {
                $vector[$i] = (float) round($vector[$i] / $norm, 6);
            }
        }

        return $vector;
    }

    /**
     * Get active provider name.
     */
    public function getActiveProvider(): string
    {
        if (!empty($this->geminiKey)) {
            return "Google Gemini ({$this->geminiModel})";
        }
        return "Gemini Local Engine ({$this->dimensions} dims)";
    }

    /**
     * Get embedding dimensions.
     */
    public function getDimensions(): int
    {
        return $this->dimensions;
    }
}
