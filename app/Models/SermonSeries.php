<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property int|null $image_id
 * @property string|null $status
 * @property string|null $created_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SermonSeries newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SermonSeries newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SermonSeries query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SermonSeries whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SermonSeries whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SermonSeries whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SermonSeries whereImageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SermonSeries whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SermonSeries whereTitle($value)
 *
 * @mixin Model
 */
class SermonSeries extends Model
{
    protected $table = 'sermon_series';

    public $timestamps = false;

    protected $fillable = ['title', 'description', 'image_id', 'status'];
}
