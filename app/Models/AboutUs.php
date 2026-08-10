<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string|null $subtitle
 * @property string|null $content
 * @property string|null $icon_class
 * @property string $section_type mission, vision, values, quote, custom
 * @property string|null $quote_author
 * @property string|null $image
 * @property int $display_order
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutUs newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutUs newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutUs query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutUs whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutUs whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutUs whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutUs whereIconClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutUs whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutUs whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutUs whereQuoteAuthor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutUs whereSectionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutUs whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutUs whereSubtitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutUs whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutUs whereUpdatedAt($value)
 *
 * @mixin Model
 */
class AboutUs extends Model
{
    protected $table = 'about_us';

    protected $fillable = [
        'title',
        'subtitle',
        'content',
        'section_type',
        'quote_author',
        'display_order',
        'status',
    ];
}
