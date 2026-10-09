<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    use HasFactory;

    protected $table = 'rules';

    protected $fillable = [
        'document_title',
        'document_type',
        'article_no',
        'content_chunk',
        'embedding',
        'indexed_at',
    ];

    protected $casts = [
        'embedding' => 'array',
        'indexed_at' => 'datetime',
    ];

    /**
     * Compute cosine similarity between two float vectors in pure PHP.
     * Returns a float between -1.0 and 1.0 (typically 0.0 to 1.0 for normalized text embeddings).
     *
     * @param array<int, float> $vecA
     * @param array<int, float> $vecB
     * @return float
     */
    public static function cosineSimilarity(array $vecA, array $vecB): float
    {
        $count = min(count($vecA), count($vecB));
        if ($count === 0) {
            return 0.0;
        }

        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        for ($i = 0; $i < $count; $i++) {
            $a = (float) $vecA[$i];
            $b = (float) $vecB[$i];

            $dotProduct += $a * $b;
            $normA += $a * $a;
            $normB += $b * $b;
        }

        $magnitude = sqrt($normA) * sqrt($normB);

        if ($magnitude <= 0.0) {
            return 0.0;
        }

        return (float) ($dotProduct / $magnitude);
    }

    /**
     * Calculate cosine similarity between this model instance and an input vector.
     *
     * @param array<int, float> $queryVector
     * @return float
     */
    public function getSimilarityTo(array $queryVector): float
    {
        if (empty($this->embedding) || !is_array($this->embedding)) {
            return 0.0;
        }

        return self::cosineSimilarity($this->embedding, $queryVector);
    }

    /**
     * Get a clean text snippet/excerpt.
     *
     * @param int $length
     * @return string
     */
    public function getExcerpt(int $length = 240): string
    {
        $text = trim($this->content_chunk);
        if (mb_strlen($text, 'UTF-8') <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length, 'UTF-8') . '...';
    }
}
