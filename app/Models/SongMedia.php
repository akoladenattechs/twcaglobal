<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $song_id
 * @property int $media_id
 * @property string|null $created_at
 * @property-read Media $media
 * @property-read Song $song
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SongMedia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SongMedia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SongMedia query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SongMedia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SongMedia whereMediaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SongMedia whereSongId($value)
 *
 * @mixin Model
 */
class SongMedia extends Model
{
    protected $table = 'song_media';

    public $timestamps = false;

    protected $fillable = ['song_id', 'media_id', 'track_order'];

    public function song()
    {
        return $this->belongsTo(Song::class, 'song_id');
    }

    public function media()
    {
        return $this->belongsTo(Media::class, 'media_id');
    }
}
