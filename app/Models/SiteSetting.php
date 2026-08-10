<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $setting_key
 * @property string|null $setting_value
 * @property string|null $setting_group
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteSetting whereSettingGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteSetting whereSettingKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteSetting whereSettingValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteSetting whereUpdatedAt($value)
 *
 * @mixin Model
 */
class SiteSetting extends Model
{
    protected $fillable = [
        'setting_key',
        'setting_value',
        'setting_group',
    ];

    // Helper method to get all site settings as an array
    public static function getAllSettings(): array
    {
        return self::all()->pluck('setting_value', 'setting_key')->toArray();
    }

    // Helper method to get settings by group
    public static function getSettingsByGroup(string $group): array
    {
        return self::where('setting_group', $group)->pluck('setting_value', 'setting_key')->toArray();
    }
}
