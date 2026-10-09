<?php

namespace Tests\Feature;

use App\Models\Rule;
use App\Services\RuleSearchService;
use Database\Seeders\RuleDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RuleSearchFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test demo home page renders successfully.
     */
    public function test_demo_page_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('ប្រព័ន្ធស្វែងរកវិធានគតិយុត្តិ');
        $response->assertSee('បញ្ចូលវិធានថ្មី');
    }

    /**
     * Test database seeder seeds Cambodian engineering rules with vectors.
     */
    public function test_rule_demo_seeder_populates_data(): void
    {
        $this->seed(RuleDemoSeeder::class);

        $this->assertGreaterThanOrEqual(8, Rule::count());

        $firstRule = Rule::first();
        $this->assertNotEmpty($firstRule->document_title);
        $this->assertNotEmpty($firstRule->content_chunk);
        $this->assertIsArray($firstRule->embedding);
        $this->assertCount(768, $firstRule->embedding);
    }

    /**
     * Test user can entry a new rule via web form.
     */
    public function test_user_can_entry_new_rule_via_web_form(): void
    {
        $payload = [
            'document_title' => 'អនុក្រឹត្យ ស្ដីពីការបន្តការអភិវឌ្ឍវិជ្ជាជីវៈវិស្វកម្ម',
            'document_type' => 'អនុក្រឹត្យ',
            'article_no' => 'មាត្រា ៣២',
            'content_chunk' => 'វិស្វករអាជីពត្រូវចូលរួមវគ្គបណ្តុះបណ្តាលបន្តវិជ្ជាជីវៈ (CPD) ឱ្យបានយ៉ាងតិច ៣០ ក្រេឌីត ក្នុងរយៈពេល ៣ឆ្នាំ។',
        ];

        $response = $this->post('/rules', $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('rules', [
            'document_title' => 'អនុក្រឹត្យ ស្ដីពីការបន្តការអភិវឌ្ឍវិជ្ជាជីវៈវិស្វកម្ម',
            'article_no' => 'មាត្រា ៣២',
        ]);

        $created = Rule::where('article_no', 'មាត្រា ៣២')->first();
        $this->assertNotNull($created);
        $this->assertIsArray($created->embedding);
        $this->assertCount(768, $created->embedding);
    }

    /**
     * Test user can entry a new rule via JSON API.
     */
    public function test_user_can_entry_new_rule_via_json_api(): void
    {
        $payload = [
            'document_title' => 'ប្រកាស ស្ដីពីការគ្រប់គ្រងការិយាល័យវិស្វកម្ម',
            'document_type' => 'ប្រកាស',
            'article_no' => 'ប្រការ ៧',
            'content_chunk' => 'ការិយាល័យប្រឹក្សាយោបល់វិស្វកម្មត្រូវមានវិស្វករអាជីពយ៉ាងតិច ១រូប ជាអ្នកទទួលខុសត្រូវបច្ចេកទេស។',
        ];

        $response = $this->postJson('/rules', $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonPath('rule.vector_dimensions', 768);
    }

    /**
     * Test hybrid search via HTTP request.
     */
    public function test_search_endpoint_returns_ranked_results(): void
    {
        $this->seed(RuleDemoSeeder::class);

        $response = $this->get('/?query=លក្ខខណ្ឌចុះបញ្ជីវិស្វករអាជីព');

        $response->assertStatus(200);
        $response->assertSee('លទ្ធផលស្វែងរកសម្រាប់');
        $response->assertSee('មាត្រា ៦');
        $response->assertSee('RRF:');
    }

    /**
     * Test JSON API response with RRF score and ranking metadata.
     */
    public function test_search_json_api_response(): void
    {
        $this->seed(RuleDemoSeeder::class);

        $response = $this->getJson('/?query=ថ្លៃបង់ប្រាក់ភាគទានប្រចាំឆ្នាំ');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'query',
            'engine',
            'count',
            'execution_time_ms',
            'results' => [
                '*' => [
                    'id',
                    'document_title',
                    'document_type',
                    'article_no',
                    'snippet',
                    'rrf_score',
                    'keyword_rank',
                    'vector_rank',
                    'match_type',
                ]
            ]
        ]);

        $data = $response->json();
        $this->assertGreaterThan(0, $data['count']);
        $this->assertGreaterThan(0.0, $data['results'][0]['rrf_score']);
    }

    /**
     * Test direct RuleSearchService execution.
     */
    public function test_rule_search_service_direct_execution(): void
    {
        $this->seed(RuleDemoSeeder::class);

        $service = app(RuleSearchService::class);
        $results = $service->search('ទណ្ឌកម្មខាងវិន័យ', limit: 3);

        $this->assertNotEmpty($results);
        $top = $results->first();
        $this->assertNotNull($top->rrf_score);
        $this->assertContains($top->match_type, ['hybrid', 'vector', 'keyword']);
    }
}
