<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $title
 * @property string|null $slug
 * @property int|null $image_id
 * @property string|null $status
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $featured
 * @property-read Media|null $media
 * @property-read Collection<int, SongMedia> $songMedia
 * @property-read int|null $song_media_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Song newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Song newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Song query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Song whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Song whereFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Song whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Song whereImageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Song whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Song whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Song whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Song whereUpdatedAt($value)
 *
 * @mixin Model
 */
class Song extends Model
{
    protected $table = 'songs';

    public $timestamps = false;

    protected $fillable = ['title', 'slug', 'image_id', 'status', 'featured'];

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

    public function songMedia()
    {
        return $this->hasMany(SongMedia::class, 'song_id');
    }

    public function media()
    {
        return $this->belongsTo(Media::class, 'image_id');
    }
}
