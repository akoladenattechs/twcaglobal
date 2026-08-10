<?php

namespace App\Helpers;

/**
 * Lightweight HTML sanitizer for admin-entered rich content.
 *
 * Strips dangerous tags and attributes (event handlers, javascript: links)
 * while preserving safe formatting HTML tags.
 *
 * This is NOT a full HTML Purifier replacement — for stricter sanitisation,
 * consider installing mews/purifier or a similar HTMLPurifier wrapper.
 */
class HtmlHelper
{
    /**
     * Allowed HTML tags for admin-entered rich content.
     */
    private const ALLOWED_TAGS = [
        'p', 'br', 'b', 'strong', 'i', 'em', 'u', 's', 'mark',
        'a', 'ul', 'ol', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'blockquote', 'pre', 'code', 'span', 'div',
        'img', 'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td',
        'hr', 'sub', 'sup', 'small', 'del', 'ins',
    ];

    /**
     * Sanitize HTML content — strips dangerous tags, event handlers,
     * javascript: URLs, and base64 data URIs.
     */
    public static function sanitize(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        // Step 1: Strip event handler attributes (onclick, onload, onerror, etc.)
        $html = preg_replace('/\s+on\w+\s*=\s*"[^"]*"/i', '', $html);
        $html = preg_replace("/\s+on\w+\s*=\s*'[^']*'/i", '', $html);
        $html = preg_replace('/\s+on\w+\s*=\s*[^\s>]+/i', '', $html);

        // Step 2: Strip javascript: and data: URIs in href/src attributes
        $html = preg_replace('/href\s*=\s*"javascript:[^"]*"/i', 'href="#"', $html);
        $html = preg_replace("/href\s*=\s*'javascript:[^']*'/i", "href='#'", $html);
        $html = preg_replace('/src\s*=\s*"data:[^"]*"/i', 'src=""', $html);
        $html = preg_replace("/src\s*=\s*'data:[^']*'/i", "src=''", $html);

        // Step 3: Strip all tags except the allowed list
        $allowed = '<'.implode('><', self::ALLOWED_TAGS).'>';
        $html = strip_tags($html, $allowed);

        return $html;
    }

    /**
     * Build an asset URL that works for BOTH local public files and full
     * external URLs (e.g. Cloudflare R2).
     *
     * - Empty path → returns ''
     * - Full http(s) URL → returned unchanged
     * - Local path → asset() with a cache-busting ?v=mtime suffix,
     *   but ONLY when the file actually exists on disk (avoids the
     *   filemtime() E_WARNING → ErrorException → HTTP 500 crash on
     *   fresh installs or DB restores where the file is absent).
     */
    public static function assetUrl(?string $path): string
    {
        if (empty($path)) {
            return '';
        }

        // Already an absolute URL (R2 or other CDN) — use as-is.
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $url = asset($path);

        // Only append the mtime cache-buster when the local file exists.
        $fullPath = public_path($path);
        if (is_file($fullPath)) {
            $url .= '?v='.filemtime($fullPath);
        }

        return $url;
    }
}
