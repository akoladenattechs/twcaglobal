@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Site Settings</h1>
</div>
<div class="container-fluid">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" role="tab">
                <i class="fas fa-globe mr-1"></i> General
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="contact-tab" data-toggle="tab" href="#contact" role="tab">
                <i class="fas fa-address-card mr-1"></i> Contact Info
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="social-tab" data-toggle="tab" href="#social" role="tab">
                <i class="fas fa-share-alt mr-1"></i> Social Media
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="appearance-tab" data-toggle="tab" href="#appearance" role="tab">
                <i class="fas fa-palette mr-1"></i> Appearance
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="typography-tab" data-toggle="tab" href="#typography" role="tab">
                <i class="fas fa-font mr-1"></i> Typography
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="currency-tab" data-toggle="tab" href="#currency" role="tab">
                <i class="fas fa-coins mr-1"></i> Currency
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="seo-tab" data-toggle="tab" href="#seo" role="tab">
                <i class="fas fa-chart-line mr-1"></i> SEO
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="mail-tab" data-toggle="tab" href="#mail" role="tab">
                <i class="fas fa-envelope mr-1"></i> Mail
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="gateways-tab" data-toggle="tab" href="#gateways" role="tab">
                <i class="fas fa-credit-card mr-1"></i> Payment Gateways
            </a>
        </li>
    </ul>

    <form method="post" action="{{ route('admin.site-settings') }}" enctype="multipart/form-data">
        @csrf
        <div class="tab-content mt-3" id="settingsTabContent">

            {{-- General --}}
            <div class="tab-pane fade show active" id="general" role="tabpanel">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-globe mr-2"></i> General Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Site Title</label>
                            <input type="text" class="form-control" name="general[site_title]" value="{{ $settings['general']['site_title'] ?? '' }}" placeholder="Enter your site name">
                        </div>
                        <div class="form-group">
                            <label>Site Description</label>
                            <textarea class="form-control" name="general[site_description]" rows="3" placeholder="Enter a brief description of your site">{{ $settings['general']['site_description'] ?? '' }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Footer Text</label>
                            <textarea class="form-control" name="general[footer_text]" rows="3" placeholder="e.g. Copyright © 2026 Your Church. All rights reserved.">{{ $settings['general']['footer_text'] ?? '' }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Footer Tagline</label>
                            <textarea class="form-control" name="general[footer_tagline]" rows="2" placeholder="Enter a footer tagline or scripture">{{ $settings['general']['footer_tagline'] ?? '' }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Footer Credit</label>
                            <textarea class="form-control" name="general[footer_credit]" rows="2">{{ $settings['general']['footer_credit'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-body">
                        <h5 class="font-weight-bold mb-3 text-primary">Content Labels</h5>
                        <p class="text-muted small mb-3">Customize section headings and labels across the frontend. Leave blank to hide certain elements.</p>

                        <h6 class="font-weight-bold mt-4 mb-2 section-label">Homepage</h6>
                        <div class="form-group">
                            <label>Sermons Section Heading</label>
                            <input type="text" class="form-control" name="general[home_sermons_heading]" value="{{ $settings['general']['home_sermons_heading'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Sermons Section Subheading</label>
                            <input type="text" class="form-control" name="general[home_sermons_subheading]" value="{{ $settings['general']['home_sermons_subheading'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Sermons Button Text</label>
                            <input type="text" class="form-control" name="general[home_sermons_button]" value="{{ $settings['general']['home_sermons_button'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Events Section Heading</label>
                            <input type="text" class="form-control" name="general[home_events_heading]" value="{{ $settings['general']['home_events_heading'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Quotes Section Heading</label>
                            <input type="text" class="form-control" name="general[home_quotes_heading]" value="{{ $settings['general']['home_quotes_heading'] ?? '' }}">
                        </div>

                        <h6 class="font-weight-bold mt-4 mb-2 section-label">Footer</h6>
                        <div class="form-group">
                            <label>Contact Info Heading</label>
                            <input type="text" class="form-control" name="general[footer_contact_heading]" value="{{ $settings['general']['footer_contact_heading'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Social Section Heading</label>
                            <input type="text" class="form-control" name="general[footer_social_heading]" value="{{ $settings['general']['footer_social_heading'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Social Section Description</label>
                            <textarea class="form-control" name="general[footer_social_desc]" rows="2">{{ $settings['general']['footer_social_desc'] ?? '' }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Apps Section Heading</label>
                            <input type="text" class="form-control" name="general[footer_apps_heading]" value="{{ $settings['general']['footer_apps_heading'] ?? '' }}">
                        </div>

                        <h6 class="font-weight-bold mt-4 mb-2 section-label">Radio</h6>
                        <div class="form-group">
                            <label>Radio Section Heading</label>
                            <input type="text" class="form-control" name="general[radio_heading]" value="{{ $settings['general']['radio_heading'] ?? '' }}">
                        </div>

                        <h6 class="font-weight-bold mt-4 mb-2 section-label">About Page</h6>
                        <div class="form-group">
                            <label>Centers Subheading</label>
                            <input type="text" class="form-control" name="general[about_centers_subheading]" value="{{ $settings['general']['about_centers_subheading'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Centers Heading</label>
                            <input type="text" class="form-control" name="general[about_centers_heading]" value="{{ $settings['general']['about_centers_heading'] ?? '' }}">
                        </div>
                    </div>
                </div>

                {{-- Maintenance Mode Card --}}
                <div class="card shadow mb-4 border-left-warning">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-warning"><i class="fas fa-tools mr-2"></i> Maintenance Mode</h6>
                        <div class="custom-control custom-switch">
                            <input type="hidden" name="general[maintenance_mode]" value="0">
                            <input type="checkbox" class="custom-control-input" id="maintenance_mode" name="general[maintenance_mode]" value="1" {{ (!empty($settings['general']['maintenance_mode']) && in_array($settings['general']['maintenance_mode'], ['1', 'true', 'on', true], true)) ? 'checked' : '' }}>
                            <label class="custom-control-label font-weight-bold text-dark" for="maintenance_mode">Enable Maintenance Mode</label>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning mb-3">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <strong>Notice:</strong> When Maintenance Mode is active, all public website pages will display the maintenance screen. The admin panel (`/admin`) remains accessible so you can manage your site and toggle Maintenance Mode off anytime.
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Maintenance Page Title</label>
                            <input type="text" class="form-control" name="general[maintenance_title]" value="{{ $settings['general']['maintenance_title'] ?? 'We\'ll Be Back Soon!' }}" placeholder="e.g. We'll Be Back Soon!">
                        </div>
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Maintenance Page Message</label>
                            <textarea class="form-control" name="general[maintenance_message]" rows="3" placeholder="Enter the message displayed to visitors during maintenance">{{ $settings['general']['maintenance_message'] ?? 'Our site is currently undergoing scheduled maintenance to serve you better. Please check back shortly.' }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contact Info --}}
            <div class="tab-pane fade" id="contact" role="tabpanel">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-address-card mr-2"></i> Contact Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Address</label>
                            <input type="text" class="form-control" name="contact[address]" value="{{ $settings['contact']['address'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" class="form-control" name="contact[phone]" value="{{ $settings['contact']['phone'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" class="form-control" name="contact[email]" value="{{ $settings['contact']['email'] ?? '' }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Social Media --}}
            <div class="tab-pane fade" id="social" role="tabpanel">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-share-alt mr-2"></i> Social Media Links</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Facebook</label>
                            <input type="text" class="form-control" name="social[facebook]" value="{{ $settings['social']['facebook'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Twitter / X</label>
                            <input type="text" class="form-control" name="social[twitter]" value="{{ $settings['social']['twitter'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Telegram</label>
                            <input type="text" class="form-control" name="social[telegram]" value="{{ $settings['social']['telegram'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Instagram</label>
                            <input type="text" class="form-control" name="social[instagram]" value="{{ $settings['social']['instagram'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>YouTube</label>
                            <input type="text" class="form-control" name="social[youtube]" value="{{ $settings['social']['youtube'] ?? '' }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Appearance --}}
            <div class="tab-pane fade" id="appearance" role="tabpanel">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-palette mr-2"></i> Appearance Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Primary Color</label>
                            <input type="color" class="form-control" name="appearance[primary_color]" value="{{ $settings['appearance']['primary_color'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Secondary Color</label>
                            <input type="color" class="form-control" name="appearance[secondary_color]" value="{{ $settings['appearance']['secondary_color'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Logo</label>
                            <div class="mb-2">
                                @php $logoPath = $settings['appearance']['logo'] ?? ''; @endphp
                                <img src="{{ \App\Helpers\HtmlHelper::assetUrl($logoPath) }}" alt="Current Logo" class="img-preview-bordered settings-logo-preview">
                            </div>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" name="appearance[logo_file]" id="logoFile" accept="image/*">
                                <label class="custom-file-label" for="logoFile">Choose new logo...</label>
                            </div>
                            <input type="hidden" name="appearance[logo]" value="{{ $logoPath }}">
                            <small class="form-text text-muted">Leave empty to keep current logo. Recommended size: 200x60px</small>
                        </div>
                        <div class="form-group">
                            <label>Favicon</label>
                            <div class="mb-2">
                                @php $faviconPath = $settings['appearance']['favicon'] ?? ''; @endphp
                                <img src="{{ \App\Helpers\HtmlHelper::assetUrl($faviconPath) }}" alt="Current Favicon" class="img-preview-bordered settings-favicon-preview">
                            </div>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" name="appearance[favicon_file]" id="faviconFile" accept="image/*">
                                <label class="custom-file-label" for="faviconFile">Choose new favicon...</label>
                            </div>
                            <input type="hidden" name="appearance[favicon]" value="{{ $faviconPath }}">
                            <small class="form-text text-muted">Leave empty to keep current favicon. Recommended size: 32x32px</small>
                        </div>
                        <div class="form-group">
                            <label>Devotional Card Header Logo</label>
                            <div class="mb-2">
                                @php $devoLogoPath = $settings['appearance']['devotional_logo'] ?? ''; @endphp
                                @if(!empty($devoLogoPath))
                                    <img src="{{ \App\Helpers\HtmlHelper::assetUrl($devoLogoPath) }}" alt="Devotional Card Logo" class="img-preview-bordered settings-logo-preview devotional-logo-preview">
                                @endif
                            </div>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" name="appearance[devotional_logo_file]" id="devoLogoFile" accept="image/*">
                                <label class="custom-file-label" for="devoLogoFile">Choose devotional header logo...</label>
                            </div>
                            <input type="hidden" name="appearance[devotional_logo]" value="{{ $devoLogoPath }}">
                            <small class="form-text text-muted">Dedicated emblem/logo for shareable Devotional Card images. Recommended size: PNG with transparent background.</small>
                        </div>
                        <div class="form-group">
                            <label>Font Family</label>
                            <select class="form-control" name="appearance[font_family]">
                                <option value="Poppins, Arial, sans-serif" {{ (isset($settings['appearance']['font_family']) && $settings['appearance']['font_family'] == 'Poppins, Arial, sans-serif') ? 'selected' : '' }}>Poppins</option>
                                <option value="Roboto, Arial, sans-serif" {{ (isset($settings['appearance']['font_family']) && $settings['appearance']['font_family'] == 'Roboto, Arial, sans-serif') ? 'selected' : '' }}>Roboto</option>
                                <option value="Montserrat, Arial, sans-serif" {{ (isset($settings['appearance']['font_family']) && $settings['appearance']['font_family'] == 'Montserrat, Arial, sans-serif') ? 'selected' : '' }}>Montserrat</option>
                                <option value="Open Sans, Arial, sans-serif" {{ (isset($settings['appearance']['font_family']) && $settings['appearance']['font_family'] == 'Open Sans, Arial, sans-serif') ? 'selected' : '' }}>Open Sans</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Button Style</label>
                            <select class="form-control" name="appearance[button_style]">
                                <option value="rounded" {{ (isset($settings['appearance']['button_style']) && $settings['appearance']['button_style'] == 'rounded') ? 'selected' : '' }}>Rounded</option>
                                <option value="square" {{ (isset($settings['appearance']['button_style']) && $settings['appearance']['button_style'] == 'square') ? 'selected' : '' }}>Square</option>
                                <option value="pill" {{ (isset($settings['appearance']['button_style']) && $settings['appearance']['button_style'] == 'pill') ? 'selected' : '' }}>Pill</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Header Style</label>
                            <select class="form-control" name="appearance[header_style]">
                                <option value="default" {{ (isset($settings['appearance']['header_style']) && $settings['appearance']['header_style'] == 'default') ? 'selected' : '' }}>Default</option>
                                <option value="transparent" {{ (isset($settings['appearance']['header_style']) && $settings['appearance']['header_style'] == 'transparent') ? 'selected' : '' }}>Transparent</option>
                                <option value="sticky" {{ (isset($settings['appearance']['header_style']) && $settings['appearance']['header_style'] == 'sticky') ? 'selected' : '' }}>Sticky</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Typography --}}
            <div class="tab-pane fade" id="typography" role="tabpanel">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-font mr-2"></i> Typography Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Heading Font</label>
                            <select class="form-control" name="typography[heading_font]">
                                <option value="Poppins" {{ (isset($settings['typography']['heading_font']) && $settings['typography']['heading_font'] == 'Poppins') ? 'selected' : '' }}>Poppins</option>
                                <option value="Roboto" {{ (isset($settings['typography']['heading_font']) && $settings['typography']['heading_font'] == 'Roboto') ? 'selected' : '' }}>Roboto</option>
                                <option value="Montserrat" {{ (isset($settings['typography']['heading_font']) && $settings['typography']['heading_font'] == 'Montserrat') ? 'selected' : '' }}>Montserrat</option>
                                <option value="Open Sans" {{ (isset($settings['typography']['heading_font']) && $settings['typography']['heading_font'] == 'Open Sans') ? 'selected' : '' }}>Open Sans</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Body Font</label>
                            <select class="form-control" name="typography[body_font]">
                                <option value="Poppins" {{ (isset($settings['typography']['body_font']) && $settings['typography']['body_font'] == 'Poppins') ? 'selected' : '' }}>Poppins</option>
                                <option value="Roboto" {{ (isset($settings['typography']['body_font']) && $settings['typography']['body_font'] == 'Roboto') ? 'selected' : '' }}>Roboto</option>
                                <option value="Montserrat" {{ (isset($settings['typography']['body_font']) && $settings['typography']['body_font'] == 'Montserrat') ? 'selected' : '' }}>Montserrat</option>
                                <option value="Open Sans" {{ (isset($settings['typography']['body_font']) && $settings['typography']['body_font'] == 'Open Sans') ? 'selected' : '' }}>Open Sans</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Base Font Size</label>
                            <select class="form-control" name="typography[base_font_size]">
                                <option value="14px" {{ (isset($settings['typography']['base_font_size']) && $settings['typography']['base_font_size'] == '14px') ? 'selected' : '' }}>14px</option>
                                <option value="16px" {{ (isset($settings['typography']['base_font_size']) && $settings['typography']['base_font_size'] == '16px') ? 'selected' : '' }}>16px</option>
                                <option value="18px" {{ (isset($settings['typography']['base_font_size']) && $settings['typography']['base_font_size'] == '18px') ? 'selected' : '' }}>18px</option>
                                <option value="20px" {{ (isset($settings['typography']['base_font_size']) && $settings['typography']['base_font_size'] == '20px') ? 'selected' : '' }}>20px</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Line Height</label>
                            <select class="form-control" name="typography[line_height]">
                                <option value="1.4" {{ (isset($settings['typography']['line_height']) && $settings['typography']['line_height'] == '1.4') ? 'selected' : '' }}>1.4</option>
                                <option value="1.5" {{ (isset($settings['typography']['line_height']) && $settings['typography']['line_height'] == '1.5') ? 'selected' : '' }}>1.5</option>
                                <option value="1.6" {{ (isset($settings['typography']['line_height']) && $settings['typography']['line_height'] == '1.6') ? 'selected' : '' }}>1.6</option>
                                <option value="1.8" {{ (isset($settings['typography']['line_height']) && $settings['typography']['line_height'] == '1.8') ? 'selected' : '' }}>1.8</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Currency --}}
            <div class="tab-pane fade" id="currency" role="tabpanel">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-coins mr-2"></i> Currency Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-1"></i>
                            Formatting details (symbol, position, separators) are auto-derived from the selected currency.
                        </div>

                        <div class="row">
                            {{-- Currency Select --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fas fa-money-bill-wave mr-1"></i> Default Currency</label>
                                    <select class="form-control select2-currency w-100" id="default_currency" name="currency[default_currency]">
                                        @php
                                            $currencies = [
                                                'AED' => ['name' => 'UAE Dirham', 'locale' => 'ar-AE'],
                                                'ARS' => ['name' => 'Argentine Peso', 'locale' => 'es-AR'],
                                                'AUD' => ['name' => 'Australian Dollar', 'locale' => 'en-AU'],
                                                'BDT' => ['name' => 'Bangladeshi Taka', 'locale' => 'bn-BD'],
                                                'BGN' => ['name' => 'Bulgarian Lev', 'locale' => 'bg-BG'],
                                                'BRL' => ['name' => 'Brazilian Real', 'locale' => 'pt-BR'],
                                                'CAD' => ['name' => 'Canadian Dollar', 'locale' => 'en-CA'],
                                                'CHF' => ['name' => 'Swiss Franc', 'locale' => 'de-CH'],
                                                'CLP' => ['name' => 'Chilean Peso', 'locale' => 'es-CL'],
                                                'CNY' => ['name' => 'Chinese Yuan', 'locale' => 'zh-CN'],
                                                'COP' => ['name' => 'Colombian Peso', 'locale' => 'es-CO'],
                                                'CZK' => ['name' => 'Czech Koruna', 'locale' => 'cs-CZ'],
                                                'DKK' => ['name' => 'Danish Krone', 'locale' => 'da-DK'],
                                                'EGP' => ['name' => 'Egyptian Pound', 'locale' => 'ar-EG'],
                                                'EUR' => ['name' => 'Euro', 'locale' => 'de-DE'],
                                                'GBP' => ['name' => 'British Pound', 'locale' => 'en-GB'],
                                                'GHS' => ['name' => 'Ghanaian Cedi', 'locale' => 'en-GH'],
                                                'HKD' => ['name' => 'Hong Kong Dollar', 'locale' => 'zh-HK'],
                                                'HUF' => ['name' => 'Hungarian Forint', 'locale' => 'hu-HU'],
                                                'IDR' => ['name' => 'Indonesian Rupiah', 'locale' => 'id-ID'],
                                                'ILS' => ['name' => 'Israeli Shekel', 'locale' => 'he-IL'],
                                                'INR' => ['name' => 'Indian Rupee', 'locale' => 'en-IN'],
                                                'ISK' => ['name' => 'Icelandic Króna', 'locale' => 'is-IS'],
                                                'JPY' => ['name' => 'Japanese Yen', 'locale' => 'ja-JP'],
                                                'KES' => ['name' => 'Kenyan Shilling', 'locale' => 'en-KE'],
                                                'KRW' => ['name' => 'South Korean Won', 'locale' => 'ko-KR'],
                                                'LKR' => ['name' => 'Sri Lankan Rupee', 'locale' => 'en-LK'],
                                                'MAD' => ['name' => 'Moroccan Dirham', 'locale' => 'ar-MA'],
                                                'MXN' => ['name' => 'Mexican Peso', 'locale' => 'es-MX'],
                                                'MYR' => ['name' => 'Malaysian Ringgit', 'locale' => 'ms-MY'],
                                                'NGN' => ['name' => 'Nigerian Naira', 'locale' => 'en-NG'],
                                                'NOK' => ['name' => 'Norwegian Krone', 'locale' => 'nb-NO'],
                                                'NZD' => ['name' => 'New Zealand Dollar', 'locale' => 'en-NZ'],
                                                'PEN' => ['name' => 'Peruvian Sol', 'locale' => 'es-PE'],
                                                'PHP' => ['name' => 'Philippine Peso', 'locale' => 'en-PH'],
                                                'PKR' => ['name' => 'Pakistani Rupee', 'locale' => 'en-PK'],
                                                'PLN' => ['name' => 'Polish Zloty', 'locale' => 'pl-PL'],
                                                'RON' => ['name' => 'Romanian Leu', 'locale' => 'ro-RO'],
                                                'RUB' => ['name' => 'Russian Ruble', 'locale' => 'ru-RU'],
                                                'SAR' => ['name' => 'Saudi Riyal', 'locale' => 'ar-SA'],
                                                'SEK' => ['name' => 'Swedish Krona', 'locale' => 'sv-SE'],
                                                'SGD' => ['name' => 'Singapore Dollar', 'locale' => 'en-SG'],
                                                'THB' => ['name' => 'Thai Baht', 'locale' => 'th-TH'],
                                                'TRY' => ['name' => 'Turkish Lira', 'locale' => 'tr-TR'],
                                                'TWD' => ['name' => 'Taiwan Dollar', 'locale' => 'zh-TW'],
                                                'TZS' => ['name' => 'Tanzanian Shilling', 'locale' => 'en-TZ'],
                                                'UGX' => ['name' => 'Ugandan Shilling', 'locale' => 'en-UG'],
                                                'USD' => ['name' => 'US Dollar', 'locale' => 'en-US'],
                                                'VND' => ['name' => 'Vietnamese Dong', 'locale' => 'vi-VN'],
                                                'XAF' => ['name' => 'CFA Franc (Central)', 'locale' => 'fr-CM'],
                                                'XOF' => ['name' => 'CFA Franc (West)', 'locale' => 'fr-SN'],
                                                'ZAR' => ['name' => 'South African Rand', 'locale' => 'en-ZA'],
                                            ];
                                            $selectedCurrency = $settings['currency']['default_currency'] ?? 'USD';
                                        @endphp
                                        @foreach($currencies as $code => $info)
                                            <option value="{{ $code }}" data-locale="{{ $info['locale'] }}" {{ $selectedCurrency == $code ? 'selected' : '' }}>
                                                {{ $code }} – {{ $info['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Live Preview --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fas fa-eye mr-1"></i> Preview</label>
                                    <div class="card bg-light">
                                        <div class="card-body text-center py-4">
                                            <h3 class="mb-0" id="currencyPreview">$1,234.56</h3>
                                            <small class="text-muted">Sample amount (1,234.56)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            {{-- Exchange Rate --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fas fa-exchange-alt mr-1"></i> Exchange Rate (to USD)</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">1 USD =</span>
                                        </div>
                                        <input type="number" class="form-control" id="exchange_rate" name="currency[exchange_rate]" value="{{ $settings['currency']['exchange_rate'] ?? '1.0000' }}" step="0.0001" min="0.0001" placeholder="1.0000">
                                        <div class="input-group-append">
                                            <span class="input-group-text currency-rate-unit" id="rateUnit">USD</span>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-sync-alt mr-1"></i>
                                        <a href="javascript:void(0)" id="fetchRateBtn" onclick="fetchLiveRate()">
                                            Fetch live rate
                                        </a>
                                        <span id="rateStatus" class="ml-2"></span>
                                    </small>
                                </div>

                                {{-- Last updated --}}
                                <div class="form-group">
                                    <label><i class="fas fa-calendar-check mr-1"></i> Rate Last Updated</label>
                                    <input type="text" class="form-control" name="currency[rate_updated_at]" value="{{ $settings['currency']['rate_updated_at'] ?? '' }}" placeholder="Not yet fetched" readonly>
                                    <small class="form-text text-muted">Timestamp of the last successful live rate fetch.</small>
                                </div>
                            </div>

                            {{-- Auto-derived formatting info (read-only) --}}
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="font-weight-bold mb-3"><i class="fas fa-sliders-h mr-1"></i> Formatting Details</h6>
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="mb-2">
                                                    <small class="text-muted d-block">Symbol</small>
                                                    <strong id="fmtSymbol">$</strong>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="mb-2">
                                                    <small class="text-muted d-block">Position</small>
                                                    <strong id="fmtPosition">Before amount</strong>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="mb-2">
                                                    <small class="text-muted d-block">Decimal Places</small>
                                                    <strong id="fmtDecimals">2</strong>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="mb-2">
                                                    <small class="text-muted d-block">Decimal Separator</small>
                                                    <strong id="fmtDecimalSep">.</strong>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="mb-2">
                                                    <small class="text-muted d-block">Thousands Separator</small>
                                                    <strong id="fmtGroupSep">,</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Hidden fields for backward compatibility --}}
                                <input type="hidden" name="currency[currency_symbol]" id="currency_symbol" value="{{ $settings['currency']['currency_symbol'] ?? '$' }}">
                                <input type="hidden" name="currency[currency_position]" id="currency_position" value="{{ $settings['currency']['currency_position'] ?? 'before' }}">
                                <input type="hidden" name="currency[decimal_places]" id="decimal_places" value="{{ $settings['currency']['decimal_places'] ?? '2' }}">
                                <input type="hidden" name="currency[decimal_separator]" id="decimal_separator" value="{{ $settings['currency']['decimal_separator'] ?? '.' }}">
                                <input type="hidden" name="currency[thousands_separator]" id="thousands_separator" value="{{ $settings['currency']['thousands_separator'] ?? ',' }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SEO --}}
            <div class="tab-pane fade" id="seo" role="tabpanel">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-line mr-2"></i> Search Engine Optimization (SEO) &amp; Social Sharing</h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle mr-1"></i> These settings control how search engines like Google index your site, and how preview cards appear when links are shared on WhatsApp, Facebook, Twitter, and LinkedIn.
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Site Title / Title Suffix</label>
                            <input type="text" class="form-control" name="seo[site_title]" value="{{ $settings['seo']['site_title'] ?? ($settings['general']['site_title'] ?? '') }}" placeholder="The Wordfare Christian Assembly | Official Website">
                            <small class="form-text text-muted">Appears in Google search titles and browser tab headers.</small>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Meta Description</label>
                            <textarea class="form-control" name="seo[meta_description]" rows="3" placeholder="Welcome to The Wordfare Christian Assembly — reaching lives with the gospel, teaching the word of God, and transforming communities.">{{ $settings['seo']['meta_description'] ?? ($settings['general']['meta_description'] ?? '') }}</textarea>
                            <small class="form-text text-muted">A short, keyword-rich summary shown under your title in Google search results (recommended length: 140–160 characters).</small>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Meta Keywords</label>
                            <textarea class="form-control" name="seo[meta_keywords]" rows="2" placeholder="church, Christian ministry, sermon, devotional, faith, Bible study">{{ $settings['seo']['meta_keywords'] ?? ($settings['general']['meta_keywords'] ?? '') }}</textarea>
                            <small class="form-text text-muted">Comma-separated list of core topics and keywords for search indexers.</small>
                        </div>

                        <hr>

                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fas fa-share-alt mr-1"></i> Default Social Share Image (Open Graph Image)</label>
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" name="seo[og_image]" value="{{ $settings['seo']['og_image'] ?? '' }}" placeholder="https://example.com/images/og-banner.jpg">
                            </div>
                            <input type="file" class="form-control-file" name="seo[og_image_file]" accept="image/*">
                            <small class="form-text text-muted">This image is displayed when your website link is shared on WhatsApp, Facebook, Twitter, and LinkedIn (Recommended aspect ratio: 1200x630px).</small>
                            @if(!empty($settings['seo']['og_image']))
                                <div class="mt-2">
                                    <img src="{{ \App\Helpers\HtmlHelper::assetUrl($settings['seo']['og_image']) }}" alt="OG Preview" style="max-height: 90px;" class="rounded border">
                                </div>
                            @endif
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold"><i class="fab fa-google mr-1"></i> Google Search Console Verification Meta Tag</label>
                                <input type="text" class="form-control" name="seo[google_site_verification]" value="{{ $settings['seo']['google_site_verification'] ?? '' }}" placeholder="e.g. google-site-verification-code or full meta content">
                                <small class="form-text text-muted">Paste your Google Search Console verification code to claim site ownership.</small>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold"><i class="fab fa-windows mr-1"></i> Bing Webmaster Verification Code</label>
                                <input type="text" class="form-control" name="seo[bing_site_verification]" value="{{ $settings['seo']['bing_site_verification'] ?? '' }}" placeholder="e.g. Bing webmaster verification code">
                                <small class="form-text text-muted">Verification code for Bing &amp; Yahoo Webmaster Tools.</small>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold"><i class="fas fa-chart-bar mr-1"></i> Google Analytics Tracking ID</label>
                            <input type="text" class="form-control col-md-6" name="seo[google_analytics_id]" value="{{ $settings['seo']['google_analytics_id'] ?? ($settings['advanced']['google_analytics_id'] ?? '') }}" placeholder="G-XXXXXXXXXX or UA-XXXXXXXXX-X">
                            <small class="form-text text-muted">GA4 or Universal Analytics ID. Automatically injects Google Analytics tracking snippet.</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Mail --}}
            <div class="tab-pane fade" id="mail" role="tabpanel">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-envelope mr-2"></i> Mail Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Mail Transport</label>
                            <select class="form-control" name="mail[mail_transport]" id="mail_transport" required>
                                <option value="smtp" {{ (isset($settings['mail']['mail_transport']) && $settings['mail']['mail_transport'] == 'smtp') ? 'selected' : '' }}>SMTP</option>
                                <option value="resend" {{ (isset($settings['mail']['mail_transport']) && $settings['mail']['mail_transport'] == 'resend') ? 'selected' : '' }}>Resend API</option>
                            </select>
                            <small class="form-text text-muted">
                                <strong>SMTP</strong> — Use standard SMTP credentials (Gmail, SendGrid, Mailgun, etc.).
                                <strong>Resend API</strong> — Use the <a href="https://resend.com" target="_blank">Resend</a> API key for sending emails.
                            </small>
                        </div>

                        <hr>

                        {{-- SMTP Fields --}}
                        <div id="smtp_fields">
                            <h6 class="font-weight-bold text-secondary mb-3"><i class="fas fa-cog"></i> SMTP Configuration</h6>

                            {{-- Security notice --}}
                            <div class="alert alert-info">
                                <i class="fas fa-shield-alt mr-1"></i>
                                <strong>Credentials are set via <code>.env</code></strong> —
                                SMTP password and API keys are managed through the server environment for security.
                                Set <code>MAIL_PASSWORD</code> in your <code>.env</code> file.
                                <a href="https://laravel.com/docs/configuration#environment-configuration" target="_blank" class="alert-link">Learn more</a>.
                            </div>

                            <div class="form-group">
                                <label>SMTP Host</label>
                                <input type="text" class="form-control" name="mail[smtp_host]" value="{{ $settings['mail']['smtp_host'] ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label>SMTP Port</label>
                                <input type="number" class="form-control" name="mail[smtp_port]" value="{{ $settings['mail']['smtp_port'] ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label>SMTP Username</label>
                                <input type="text" class="form-control" name="mail[smtp_username]" value="{{ $settings['mail']['smtp_username'] ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label>SMTP Encryption</label>
                                <select class="form-control" name="mail[smtp_encryption]">
                                    <option value="tls" {{ (isset($settings['mail']['smtp_encryption']) && $settings['mail']['smtp_encryption'] == 'tls') ? 'selected' : '' }}>TLS</option>
                                    <option value="ssl" {{ (isset($settings['mail']['smtp_encryption']) && $settings['mail']['smtp_encryption'] == 'ssl') ? 'selected' : '' }}>SSL</option>
                                    <option value="none" {{ (isset($settings['mail']['smtp_encryption']) && $settings['mail']['smtp_encryption'] == 'none') ? 'selected' : '' }}>None</option>
                                </select>
                            </div>
                        </div>

                        {{-- Resend Fields --}}
                        <div id="resend_fields" class="d-none">
                            <h6 class="font-weight-bold text-secondary mb-3"><i class="fas fa-key"></i> Resend API Configuration</h6>

                            {{-- Security notice --}}
                            <div class="alert alert-info">
                                <i class="fas fa-shield-alt mr-1"></i>
                                <strong>API key is set via <code>.env</code></strong> —
                                Set <code>RESEND_API_KEY</code> in your <code>.env</code> file.
                                Get your key at <a href="https://resend.com/api-keys" target="_blank" class="alert-link">resend.com/api-keys</a>.
                            </div>
                        </div>

                        <hr>

                        {{-- Common Fields --}}
                        <h6 class="font-weight-bold text-secondary mb-3"><i class="fas fa-user"></i> Sender Details</h6>
                        <div class="form-group">
                            <label>From Email</label>
                            <input type="email" class="form-control" name="mail[from_email]" value="{{ $settings['mail']['from_email'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>From Name</label>
                            <input type="text" class="form-control" name="mail[from_name]" value="{{ $settings['mail']['from_name'] ?? '' }}">
                        </div>
                    </div>
                </div>

                {{-- Test Email --}}
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-paper-plane"></i> Test Email Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Send Test Email To</label>
                            <input type="email" class="form-control" name="test_email" placeholder="email@example.com">
                        </div>
                        <button type="submit" name="send_test_email" value="1" class="btn btn-secondary">Send Test Email</button>
                        <small class="form-text text-muted mt-2">Your mail settings will be saved before the test is sent.</small>
                    </div>
                </div>
            </div>

            {{-- Payment Gateways --}}
            <div class="tab-pane fade" id="gateways" role="tabpanel">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-credit-card mr-2"></i> Online Payment Gateway Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-1"></i> Select the active payment gateway and currency for the <strong>/partnership-giving</strong> page. Gateway API credentials are managed securely via your server's <code>.env</code> file.
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Active Payment Gateway</label>
                            <select class="form-control" name="general[active_payment_gateway]">
                                <option value="paystack" {{ ($settings['general']['active_payment_gateway'] ?? 'paystack') === 'paystack' ? 'selected' : '' }}>Paystack (Recommended for Nigeria/West Africa)</option>
                                <option value="flutterwave" {{ ($settings['general']['active_payment_gateway'] ?? '') === 'flutterwave' ? 'selected' : '' }}>Flutterwave (Africa & Global)</option>
                                <option value="stripe" {{ ($settings['general']['active_payment_gateway'] ?? '') === 'stripe' ? 'selected' : '' }}>Stripe (US, UK, EU, Global)</option>
                                <option value="none" {{ ($settings['general']['active_payment_gateway'] ?? '') === 'none' ? 'selected' : '' }}>None (Bank Transfer & Manual Only)</option>
                            </select>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Gateway Currency Code</label>
                            <input type="text" class="form-control col-md-4" name="general[currency_code]" value="{{ $settings['general']['currency_code'] ?? 'NGN' }}" placeholder="NGN, USD, GHS, etc.">
                            <small class="form-text text-muted">ISO 3-letter currency code used during checkout.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Save Settings</button>
    </form>
</div>
@endsection

@section('scripts')
<script>
// ════════════════════════════════════════════════════════════════
//  Comprehensive currency locale mappings for Intl API
// ════════════════════════════════════════════════════════════════
const currencyLocaleMap = {
    'AED': 'ar-AE', 'ARS': 'es-AR', 'AUD': 'en-AU', 'BDT': 'bn-BD', 'BGN': 'bg-BG',
    'BRL': 'pt-BR', 'CAD': 'en-CA', 'CHF': 'de-CH', 'CLP': 'es-CL', 'CNY': 'zh-CN',
    'COP': 'es-CO', 'CZK': 'cs-CZ', 'DKK': 'da-DK', 'EGP': 'ar-EG', 'EUR': 'de-DE',
    'GBP': 'en-GB', 'GHS': 'en-GH', 'HKD': 'zh-HK', 'HUF': 'hu-HU', 'IDR': 'id-ID',
    'ILS': 'he-IL', 'INR': 'en-IN', 'ISK': 'is-IS', 'JPY': 'ja-JP', 'KES': 'en-KE',
    'KRW': 'ko-KR', 'LKR': 'en-LK', 'MAD': 'ar-MA', 'MXN': 'es-MX', 'MYR': 'ms-MY',
    'NGN': 'en-NG', 'NOK': 'nb-NO', 'NZD': 'en-NZ', 'PEN': 'es-PE', 'PHP': 'en-PH',
    'PKR': 'en-PK', 'PLN': 'pl-PL', 'RON': 'ro-RO', 'RUB': 'ru-RU', 'SAR': 'ar-SA',
    'SEK': 'sv-SE', 'SGD': 'en-SG', 'THB': 'th-TH', 'TRY': 'tr-TR', 'TWD': 'zh-TW',
    'TZS': 'en-TZ', 'UGX': 'en-UG', 'USD': 'en-US', 'VND': 'vi-VN', 'XAF': 'fr-CM',
    'XOF': 'fr-SN', 'ZAR': 'en-ZA'
};

// ════════════════════════════════════════════════════════════════
//  Derive formatting info from Intl.NumberFormat
// ════════════════════════════════════════════════════════════════
function getCurrencyFormatting(code) {
    const locale = currencyLocaleMap[code] || 'en-US';
    try {
        const sample = 1234.56;
        const fmt = new Intl.NumberFormat(locale, { style: 'currency', currency: code });
        const parts = fmt.formatToParts(sample);

        let symbol = code;
        let position = 'before';
        let intlDecimal = '.';
        let intlGroup = ',';
        let decimals = 2;

        parts.forEach(function(p) {
            if (p.type === 'currency') {
                symbol = p.value;
                position = (parts.indexOf(p) < parts.findIndex(function(x) { return x.type === 'integer'; })) ? 'before' : 'after';
            }
            if (p.type === 'group') intlGroup = p.value;
            if (p.type === 'decimal') intlDecimal = p.value;
            if (p.type === 'fraction') decimals = p.value.length;
        });

        // Handle zero-decimal currencies
        if (['JPY', 'KRW', 'VND', 'CLP', 'ISK'].indexOf(code) !== -1) decimals = 0;

        return { symbol: symbol, position: position, decimalSep: intlDecimal, groupSep: intlGroup, decimalPlaces: decimals };
    } catch(e) {
        return { symbol: '$', position: 'before', decimalSep: '.', groupSep: ',', decimalPlaces: 2 };
    }
}

// ════════════════════════════════════════════════════════════════
//  Format a sample amount for preview
// ════════════════════════════════════════════════════════════════
function formatCurrencyPreview(code, amount) {
    const locale = currencyLocaleMap[code] || 'en-US';
    try {
        return new Intl.NumberFormat(locale, { style: 'currency', currency: code }).format(amount);
    } catch(e) {
        return code + ' ' + amount;
    }
}

// ════════════════════════════════════════════════════════════════
//  Update all UI elements when currency changes
// ════════════════════════════════════════════════════════════════
function updateCurrencyInfo() {
    const select = document.getElementById('default_currency');
    if (!select) return;

    const code = select.value;
    const fmt = getCurrencyFormatting(code);

    // Update live preview
    const preview = document.getElementById('currencyPreview');
    if (preview) {
        preview.textContent = formatCurrencyPreview(code, 1234.56);
    }

    // Update formatting details card
    setText('fmtSymbol', fmt.symbol);
    setText('fmtPosition', fmt.position === 'before' ? 'Before amount' : 'After amount');
    setText('fmtDecimals', String(fmt.decimalPlaces));
    setText('fmtDecimalSep', fmt.decimalSep);
    setText('fmtGroupSep', fmt.groupSep);

    // Update hidden fields (backward compatibility)
    setValue('currency_symbol', fmt.symbol);
    setValue('currency_position', fmt.position);
    setValue('decimal_places', String(fmt.decimalPlaces));
    setValue('decimal_separator', fmt.decimalSep);
    setValue('thousands_separator', fmt.groupSep);

    // Update exchange rate unit label
    const rateUnit = document.getElementById('rateUnit');
    if (rateUnit) rateUnit.textContent = code;
}

function setText(id, val) {
    var el = document.getElementById(id);
    if (el) el.textContent = val;
}

function setValue(id, val) {
    var el = document.getElementById(id);
    if (el) el.value = val;
}

// ════════════════════════════════════════════════════════════════
//  Fetch live exchange rate from free API
// ════════════════════════════════════════════════════════════════
function fetchLiveRate() {
    const select = document.getElementById('default_currency');
    const code = select ? select.value : 'USD';
    const rateInput = document.getElementById('exchange_rate');
    const btn = document.getElementById('fetchRateBtn');
    const status = document.getElementById('rateStatus');

    if (!rateInput) return;

    btn.textContent = 'Fetching...';
    btn.style.pointerEvents = 'none';
    status.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';

    fetch('https://open.er-api.com/v6/latest/USD')
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data && data.rates && data.rates[code]) {
                var rate = data.rates[code];
                rateInput.value = rate.toFixed(4);

                // Trigger change event
                var evt = new Event('change', { bubbles: true });
                rateInput.dispatchEvent(evt);

                // Update last-updated timestamp
                var updatedInput = document.querySelector('input[name="currency[rate_updated_at]"]');
                if (updatedInput) {
                    var now = new Date();
                    var pad = function(n) { return String(n).padStart(2, '0'); };
                    updatedInput.value = now.getFullYear() + '-' + pad(now.getMonth()+1) + '-' + pad(now.getDate()) +
                        ' ' + pad(now.getHours()) + ':' + pad(now.getMinutes());
                }

                status.innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> Updated: 1 USD = ' + rate.toFixed(4) + ' ' + code + '</span>';

                // Show notification
                showNotification('Exchange rate updated successfully!', 'success');
            } else {
                status.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-circle"></i> Rate not available for ' + code + '</span>';
                showNotification('Rate not available for ' + code, 'danger');
            }
        })
        .catch(function(err) {
            console.error('Exchange rate fetch failed:', err);
            status.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-circle"></i> Failed to fetch. Try again later.</span>';
            showNotification('Failed to fetch live rate. Check your connection.', 'danger');
        })
        .finally(function() {
            btn.textContent = 'Fetch live rate';
            btn.style.pointerEvents = 'auto';
        });
}

// ════════════════════════════════════════════════════════════════
//  Toast notification helper
// ════════════════════════════════════════════════════════════════
function showNotification(message, type) {
    type = type || 'info';
    var container = document.querySelector('.tab-content');
    if (!container) return;

    var alert = document.createElement('div');
    alert.className = 'alert alert-' + type + ' alert-dismissible fade show mt-2';
    alert.innerHTML = message + '<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>';

    // Remove existing notification
    var existing = document.getElementById('currency-notification');
    if (existing) existing.remove();

    alert.id = 'currency-notification';
    var card = document.getElementById('currency');
    if (card) {
        var cardBody = card.querySelector('.card-body');
        if (cardBody) {
            cardBody.insertBefore(alert, cardBody.firstChild);
        }
    }

    setTimeout(function() {
        if (alert.parentNode) alert.remove();
    }, 6000);
}

// ════════════════════════════════════════════════════════════════
//  Initialize Select2 and event listeners
// ════════════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', function () {
    // Init Select2 on currency dropdown
    var currencySelect = $('#default_currency');
    if (currencySelect.length && $.fn.select2) {
        currencySelect.select2({
            theme: 'classic',
            width: 'resolve',
            placeholder: 'Search currency...',
            allowClear: false
        });
        currencySelect.on('change', function() {
            updateCurrencyInfo();
        });
    }

    // Initial update
    updateCurrencyInfo();

    // File input labels
    document.querySelectorAll('.custom-file-input').forEach(function(input) {
        input.addEventListener('change', function(e) {
            var fileName = e.target.files[0]?.name || 'Choose file...';
            var label = this.nextElementSibling;
            if (label) label.textContent = fileName;
        });
    });

    // ═══ Mail Transport Toggle ═══
    function toggleMailFields() {
        var transport = document.getElementById('mail_transport');
        if (!transport) return;
        var smtp = document.getElementById('smtp_fields');
        var resend = document.getElementById('resend_fields');
        if (transport.value === 'resend') {
            smtp.classList.add('d-none');
            resend.classList.remove('d-none');
        } else {
            resend.classList.add('d-none');
            smtp.classList.remove('d-none');
        }
    }
    toggleMailFields();
    var mailTransport = document.getElementById('mail_transport');
    if (mailTransport) mailTransport.addEventListener('change', toggleMailFields);


});

</script>
@endsection
