<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $location
 * @property int|null $display_order
 * @property string|null $status
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property-read Collection<int, MenuItem> $menuItems
 * @property-read int|null $menu_items_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereUpdatedAt($value)
 *
 * @mixin Model
 */
class Menu extends Model
{
    protected $table = 'menus';

    public $timestamps = true;

    protected $fillable = ['name', 'location', 'display_order', 'status'];

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class, 'menu_id');
    }
}
