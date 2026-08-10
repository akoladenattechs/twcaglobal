<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $sermon_id
 * @property int $media_id
 * @property string|null $created_at
 * @property-read Media|null $media
 * @property-read Sermon|null $sermon
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SermonMedia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SermonMedia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SermonMedia query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SermonMedia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SermonMedia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SermonMedia whereMediaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SermonMedia whereSermonId($value)
 *
 * @mixin Model
 */
class SermonMedia extends Model
{
    protected $table = 'sermon_media';

    public $timestamps = false;

    protected $fillable = ['sermon_id', 'media_id', 'track_order'];

    public function sermon()
    {
        return $this->belongsTo(Sermon::class, 'sermon_id');
    }

    public function media()
    {
        return $this->belongsTo(Media::class, 'media_id');
    }
}
