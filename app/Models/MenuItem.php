<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $menu_id
 * @property int|null $parent_id
 * @property string $title
 * @property string $url
 * @property string|null $target
 * @property int|null $order_number
 * @property string|null $status
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $is_cta
 * @property-read Collection<int, MenuItem> $children
 * @property-read int|null $children_count
 * @property-read Menu|null $menu
 * @property-read MenuItem|null $parent
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereIsCta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereMenuId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereOrderNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereUrl($value)
 *
 * @mixin Model
 */
class MenuItem extends Model
{
    protected $table = 'menu_items';

    public $timestamps = false;

    protected $fillable = ['menu_id', 'parent_id', 'title', 'url', 'target', 'order_number', 'status', 'is_cta'];

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }
}
