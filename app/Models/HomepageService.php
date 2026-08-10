<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string|null $icon
 * @property string|null $link
 * @property int|null $display_order
 * @property string|null $status
 * @property string|null $created_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomepageService newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomepageService newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomepageService query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomepageService whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomepageService whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomepageService whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomepageService whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomepageService whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomepageService whereLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomepageService whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomepageService whereTitle($value)
 *
 * @mixin Model
 */
class HomepageService extends Model
{
    protected $table = 'homepage_services';

    public $timestamps = false;

    protected $fillable = ['title', 'description', 'icon', 'link', 'display_order', 'status'];
}
