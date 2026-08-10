<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $content
 * @property string|null $author
 * @property string|null $title
 * @property string|null $status
 * @property int|null $display_order
 * @property string|null $created_at
 * @property int|null $image_id
 * @property-read Media|null $media
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereAuthor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereImageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereTitle($value)
 *
 * @mixin Model
 */
class Quote extends Model
{
    protected $table = 'quotes';

    public $timestamps = false;

    protected $fillable = ['content', 'author', 'title', 'status', 'display_order', 'image_id'];

    public function media()
    {
        return $this->belongsTo(Media::class, 'image_id');
    }
}
