<?php

namespace App\Http\Controllers\Api;

use App\Helpers\HtmlHelper;
use App\Http\Controllers\Controller;
use App\Models\SiteSetting;

class SettingController extends Controller
{
    /**
     * Setting groups that are safe for public display.
     * Sensitive groups like 'mail' (SMTP credentials, API keys)
     * and 'advanced' (internal config) are excluded.
     */
    private array $publicGroups = [
        'general',
        'contact',
        'social',
        'appearance',
        'layout',
        'typography',
        'currency',
    ];

    /**
     * Setting keys whose values may contain rich HTML.
     */
    private array $htmlKeys = [
        'footer_text',
        'about_text',
        'address',
        'announcement',
        'welcome_text',
        'welcome_message',
    ];

    public function index()
    {
        $settings = SiteSetting::whereIn('setting_group', $this->publicGroups)
            ->get()
            ->groupBy('setting_group')
            ->map(function ($items) {
                return $items->pluck('setting_value', 'setting_key');
            })
            ->map(function ($group) {
                return $group->map(function ($value, $key) {
                    if (in_array($key, $this->htmlKeys, true)) {
                        return HtmlHelper::sanitize($value);
                    }

                    return $value;
                });
            });

        return response()->json(['success' => true, 'data' => $settings]);
    }

    public function get(string $key)
    {
        // Only allow fetching settings from public groups
        $setting = SiteSetting::where('setting_key', $key)
            ->whereIn('setting_group', $this->publicGroups)
            ->first();

        if (! $setting) {
            return response()->json(['success' => false, 'message' => 'Setting not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $setting]);
    }
}
