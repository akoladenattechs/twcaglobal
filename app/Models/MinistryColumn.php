<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $column_type
 * @property string|null $icon_class
 * @property string|null $title
 * @property string|null $subtitle
 * @property string|null $description
 * @property string|null $quote_author
 * @property int $display_order
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder<static>|MinistryColumn ministries()
 * @method static Builder<static>|MinistryColumn newModelQuery()
 * @method static Builder<static>|MinistryColumn newQuery()
 * @method static Builder<static>|MinistryColumn published()
 * @method static Builder<static>|MinistryColumn query()
 * @method static Builder<static>|MinistryColumn quotes()
 * @method static Builder<static>|MinistryColumn whereColumnType($value)
 * @method static Builder<static>|MinistryColumn whereCreatedAt($value)
 * @method static Builder<static>|MinistryColumn whereDescription($value)
 * @method static Builder<static>|MinistryColumn whereDisplayOrder($value)
 * @method static Builder<static>|MinistryColumn whereIconClass($value)
 * @method static Builder<static>|MinistryColumn whereId($value)
 * @method static Builder<static>|MinistryColumn whereQuoteAuthor($value)
 * @method static Builder<static>|MinistryColumn whereStatus($value)
 * @method static Builder<static>|MinistryColumn whereSubtitle($value)
 * @method static Builder<static>|MinistryColumn whereTitle($value)
 * @method static Builder<static>|MinistryColumn whereUpdatedAt($value)
 *
 * @mixin Model
 */
class MinistryColumn extends Model
{
    protected $table = 'ministry_columns';

    protected $fillable = [
        'column_type',
        'icon_class',
        'title',
        'subtitle',
        'description',
        'quote_author',
        'display_order',
        'status',
    ];

    public function scopeMinistries(Builder $query): Builder
    {
        return $query->where('column_type', 'ministry')->orderBy('display_order');
    }

    public function scopeQuotes(Builder $query): Builder
    {
        return $query->where('column_type', 'quote')->orderBy('display_order');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }
}
