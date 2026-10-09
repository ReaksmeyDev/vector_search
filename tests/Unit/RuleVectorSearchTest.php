<?php

namespace Tests\Unit;

use App\Models\Rule;
use App\Services\EmbeddingService;
use Tests\TestCase;

class RuleVectorSearchTest extends TestCase
{
    /**
     * Test Cosine Similarity on identical vectors.
     */
    public function test_cosine_similarity_identical_vectors(): void
    {
        $vecA = [1.0, 2.0, 3.0, 4.0];
        $vecB = [1.0, 2.0, 3.0, 4.0];

        $similarity = Rule::cosineSimilarity($vecA, $vecB);
        $this->assertEqualsWithDelta(1.0, $similarity, 0.0001);
    }

    /**
     * Test Cosine Similarity on orthogonal vectors.
     */
    public function test_cosine_similarity_orthogonal_vectors(): void
    {
        $vecA = [1.0, 0.0];
        $vecB = [0.0, 1.0];

        $similarity = Rule::cosineSimilarity($vecA, $vecB);
        $this->assertEqualsWithDelta(0.0, $similarity, 0.0001);
    }

    /**
     * Test Cosine Similarity on opposite vectors.
     */
    public function test_cosine_similarity_opposite_vectors(): void
    {
        $vecA = [1.0, 2.0, 3.0];
        $vecB = [-1.0, -2.0, -3.0];

        $similarity = Rule::cosineSimilarity($vecA, $vecB);
        $this->assertEqualsWithDelta(-1.0, $similarity, 0.0001);
    }

    /**
     * Test Cosine Similarity on empty or zero vectors.
     */
    public function test_cosine_similarity_edge_cases(): void
    {
        $this->assertEquals(0.0, Rule::cosineSimilarity([], []));
        $this->assertEquals(0.0, Rule::cosineSimilarity([0.0, 0.0], [1.0, 2.0]));
    }

    /**
     * Test EmbeddingService generates normalized 768-dim vector for Gemini.
     */
    public function test_embedding_service_generates_normalized_768_vector(): void
    {
        $service = new EmbeddingService();
        $text = 'លក្ខខណ្ឌចុះបញ្ជីវិស្វករអាជីព នៃគណៈវិស្វករកម្ពុជា';

        $vector = $service->generate($text);

        $this->assertIsArray($vector);
        $this->assertCount(768, $vector);

        // Verify Euclidean L2 norm is ~ 1.0
        $sumSq = 0.0;
        foreach ($vector as $val) {
            $sumSq += $val * $val;
        }
        $this->assertEqualsWithDelta(1.0, sqrt($sumSq), 0.005);
    }

    /**
     * Test that similar texts have higher cosine similarity than unrelated texts.
     */
    public function test_semantic_similarity_relative_scoring(): void
    {
        $service = new EmbeddingService();

        $query = 'លក្ខខណ្ឌចុះបញ្ជីវិស្វករ';
        $similarText = 'ការចុះឈ្មោះក្នុងបញ្ជីគណៈវិស្វករកម្ពុជា ជាវិស្វករដំបូង';
        $unrelatedText = 'ការសាងសង់ស្ពានឆ្លងកាត់ទន្លេមេគង្គនៅឆ្នាំ២០២៦';

        $qVec = $service->generate($query);
        $simVec = $service->generate($similarText);
        $unrelatedVec = $service->generate($unrelatedText);

        $simA = Rule::cosineSimilarity($qVec, $simVec);
        $simB = Rule::cosineSimilarity($qVec, $unrelatedVec);

        $this->assertGreaterThan($simB, $simA);
    }
}
