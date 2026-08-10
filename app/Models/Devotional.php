<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $title
 * @property string|null $slug
 * @property string $content
 * @property string|null $scripture_reference
 * @property string|null $scripture_text
 * @property string|null $prayer
 * @property string|null $reflection_questions
 * @property string|null $author
 * @property string $devotional_date
 * @property int|null $image_id
 * @property string|null $status
 * @property int|null $featured
 * @property int|null $views_count
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property-read Media|null $media
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Devotional newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Devotional newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Devotional query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Devotional whereAuthor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Devotional whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Devotional whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Devotional whereDevotionalDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Devotional whereFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Devotional whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Devotional whereImageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Devotional wherePrayer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Devotional whereReflectionQuestions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Devotional whereScriptureReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Devotional whereScriptureText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Devotional whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Devotional whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Devotional whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Devotional whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Devotional whereViewsCount($value)
 *
 * @mixin Model
 */
class Devotional extends Model
{
    protected $table = 'devotionals';

    public $timestamps = false;

    protected $fillable = ['title', 'slug', 'content', 'scripture_reference', 'scripture_text', 'prayer', 'reflection_questions', 'devotional_date', 'status', 'views_count'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = static::generateUniqueSlug($model->title);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('title') && ! $model->isDirty('slug')) {
                $model->slug = static::generateUniqueSlug($model->title, $model->id);
            }
        });
    }

    public static function generateUniqueSlug(string $title, $excludeId = null)
    {
        $slug = Str::slug($title);
        $original = $slug;
        $counter = 1;

        while (true) {
            $query = static::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            if (! $query->exists()) {
                break;
            }
            $slug = $original.'-'.$counter++;
        }

        return $slug;
    }

    public function media()
    {
        return $this->belongsTo(Media::class, 'image_id');
    }
}
