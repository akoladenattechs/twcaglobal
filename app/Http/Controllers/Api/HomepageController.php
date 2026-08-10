<?php

namespace App\Http\Controllers\Api;

use App\Helpers\HtmlHelper;
use App\Http\Controllers\Controller;
use App\Models\HomepageService;
use App\Models\HomepageSlider;
use App\Models\Quote;

class HomepageController extends Controller
{
    public function index()
    {
        $sliders = HomepageSlider::where('status', 'published')
            ->orderBy('display_order')
            ->get()
            ->map(function ($slider) {
                $slider->title = HtmlHelper::sanitize($slider->title);
                $slider->subtitle = HtmlHelper::sanitize($slider->subtitle);
                $slider->description = HtmlHelper::sanitize($slider->description);

                return $slider;
            });

        $services = HomepageService::where('status', 'published')
            ->orderBy('display_order')
            ->get()
            ->map(function ($service) {
                $service->title = HtmlHelper::sanitize($service->title);
                $service->description = HtmlHelper::sanitize($service->description);

                return $service;
            });

        $quotes = Quote::where('status', 'published')
            ->orderBy('display_order')
            ->get()
            ->map(function ($quote) {
                $quote->content = HtmlHelper::sanitize($quote->content);
                $quote->author = HtmlHelper::sanitize($quote->author);

                return $quote;
            });

        return response()->json([
            'success' => true,
            'data' => [
                'sliders' => $sliders,
                'services' => $services,
                'quotes' => $quotes,
            ],
        ]);
    }
}
