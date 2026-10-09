<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRuleRequest;
use App\Models\Rule;
use App\Services\EmbeddingService;
use App\Services\RuleSearchService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RuleSearchController extends Controller
{
    protected RuleSearchService $searchService;
    protected EmbeddingService $embeddingService;

    public function __construct(RuleSearchService $searchService, EmbeddingService $embeddingService)
    {
        $this->searchService = $searchService;
        $this->embeddingService = $embeddingService;
    }

    /**
     * Display the Rule Vector Search Engine interactive demo (Hybrid Mode Only).
     *
     * @param Request $request
     * @return View|JsonResponse
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = (string) $request->input('query', '');

        $results = collect();
        $executionTimeMs = 0.0;

        $sortBy = (string) $request->input('sort_by', 'vector_rank');

        if (trim($query) !== '') {
            $startTime = microtime(true);
            
            // Active search function
            $results = $this->searchService->search($query, limit: 10, sortBy: $sortBy);

            $executionTimeMs = round((microtime(true) - $startTime) * 1000, 2);
        }

        $totalRules = Rule::count();
        $recentRules = Rule::latest('id')->take(8)->get();
        $activeProvider = $this->embeddingService->getActiveProvider();
        $dimensions = $this->embeddingService->getDimensions();


        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'query' => $query,
                'engine' => 'hybrid_rrf',
                'count' => $results->count(),
                'execution_time_ms' => $executionTimeMs,
                'results' => $results,
            ]);
        }

        return view('demo', [
            'query' => $query,
            'results' => $results,
            'executionTimeMs' => $executionTimeMs,
            'totalRules' => $totalRules,
            'recentRules' => $recentRules,
            'activeProvider' => $activeProvider,
            'dimensions' => $dimensions,
        ]);
    }

    /**
     * Store a newly created legal rule entry and generate its Gemini vector embedding.
     *
     * @param StoreRuleRequest $request
     * @return RedirectResponse|JsonResponse
     */
    public function store(StoreRuleRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();

        // 1. Build text representation for semantic embedding
        $contextText = trim("{$validated['document_title']} {$validated['article_no']} {$validated['content_chunk']}");

        // 2. Generate Google Gemini embedding vector (768 dimensions)
        $vector = $this->embeddingService->generate($contextText);

        // 3. Persist into MySQL rules table
        $rule = Rule::create([
            'document_title' => $validated['document_title'],
            'document_type' => $validated['document_type'],
            'article_no' => $validated['article_no'] ?: null,
            'content_chunk' => $validated['content_chunk'],
            'embedding' => $vector,
            'indexed_at' => Carbon::now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'វិធានត្រូវបានបញ្ចូល និងបង្កើត Gemini Vector ដោយជោគជ័យ!',
                'rule' => [
                    'id' => $rule->id,
                    'document_title' => $rule->document_title,
                    'document_type' => $rule->document_type,
                    'article_no' => $rule->article_no,
                    'content_chunk' => $rule->content_chunk,
                    'vector_dimensions' => count($vector),
                ]
            ], 201);
        }

        return redirect()->route('rules.index', ['query' => $rule->article_no ?: $rule->document_title])
            ->with('success', "វិធាន «{$rule->document_title}» [{$rule->article_no}] ត្រូវបានបញ្ចូល និងបង្កើត Gemini Vector (" . count($vector) . " dimensions) រួចរាល់ដោយជោគជ័យ!")
            ->with('new_rule_id', $rule->id);
    }
}
