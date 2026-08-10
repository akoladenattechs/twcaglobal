<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $title
 * @property string|null $slug
 * @property string|null $description
 * @property string|null $preacher
 * @property string|null $sermon_date
 * @property int|null $series_id
 * @property int $media_id
 * @property int|null $image_id
 * @property int|null $track_number
 * @property string|null $status
 * @property int|null $featured
 * @property string|null $created_at
 * @property-read Collection<int, Media> $audioMedia
 * @property-read int|null $audio_media_count
 * @property-read Media|null $media
 * @property-read SermonSeries|null $series
 * @property-read Collection<int, SermonMedia> $sermonMedia
 * @property-read int|null $sermon_media_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sermon newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sermon newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sermon query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sermon whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sermon whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sermon whereFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sermon whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sermon whereImageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sermon whereMediaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sermon wherePreacher($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sermon whereSeriesId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sermon whereSermonDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sermon whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sermon whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sermon whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sermon whereTrackNumber($value)
 *
 * @mixin Model
 */
class Sermon extends Model
{
    protected $table = 'sermons';

    public $timestamps = false;

    protected $fillable = ['title', 'slug', 'description', 'preacher', 'sermon_date', 'series_id', 'media_id', 'image_id', 'track_number', 'status', 'featured'];

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

    public function series()
    {
        return $this->belongsTo(SermonSeries::class, 'series_id');
    }

    public function sermonMedia()
    {
        return $this->hasMany(SermonMedia::class, 'sermon_id');
    }

    public function audioMedia()
    {
        return $this->belongsToMany(Media::class, 'sermon_media', 'sermon_id', 'media_id');
    }

    public function media()
    {
        return $this->belongsTo(Media::class, 'image_id');
    }
}
