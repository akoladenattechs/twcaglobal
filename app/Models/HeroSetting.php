<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $title Global hero title (replaces per-slider titles)
 * @property string|null $badge_text Text shown in the glass badge (e.g. "Worship With Us")
 * @property string|null $prefix_text Text before the title (e.g. "Welcome to")
 * @property string|null $suffix_text Text after the title (e.g. "Ministries")
 * @property string|null $description Global hero description (replaces per-slider descriptions)
 * @property bool $show_badge
 * @property bool $show_description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HeroSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HeroSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HeroSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HeroSetting whereBadgeText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HeroSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HeroSetting whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HeroSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HeroSetting wherePrefixText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HeroSetting wherePrimaryButtonLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HeroSetting wherePrimaryButtonText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HeroSetting whereSecondaryButtonLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HeroSetting whereSecondaryButtonText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HeroSetting whereShowBadge($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HeroSetting whereShowButtons($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HeroSetting whereShowDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HeroSetting whereSuffixText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HeroSetting whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HeroSetting whereUpdatedAt($value)
 *
 * @mixin Model
 */
class HeroSetting extends Model
{
    protected $table = 'hero_settings';

    protected $fillable = [
        'title',
        'badge_text',
        'prefix_text',
        'suffix_text',
        'description',
        'show_badge',
        'show_description',
        'show_button',
        'button_text',
        'button_link',
    ];

    protected $casts = [
        'show_badge' => 'boolean',
        'show_description' => 'boolean',

    ];

    /**
     * Get the hero settings (singleton - always the first row).
     */
    public static function getSettings(): self
    {
        try {
            $settings = self::first();
            if (! $settings) {
                $settings = new self([
                    'prefix_text' => 'Welcome to',
                    'suffix_text' => 'Ministries',
                    'show_badge' => true,
                    'show_description' => true,
                ]);
                $settings->save();
            }
            return $settings;
        } catch (\Throwable $e) {
            return new self([
                'prefix_text' => 'Welcome to',
                'suffix_text' => 'Ministries',
                'show_badge' => true,
                'show_description' => true,
            ]);
        }
    }
}
