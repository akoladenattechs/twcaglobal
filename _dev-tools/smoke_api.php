<?php
// Dev smoke test: check API events slug vs id behavior + a few endpoint statuses
$base = 'http://localhost/bli-laravel';

function hit(string $url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $r = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$code, $r];
}

[$c1, $b1] = hit($base . '/api/events/any-slug-here');
echo "events/any-slug-here -> HTTP $c1\n";
if ($c1 == 200) {
    $j = json_decode($b1, true);
    echo "  body: " . substr($b1, 0, 200) . "\n";
} else {
    echo "  body: " . substr($b1, 0, 200) . "\n";
}

[$c2, $b2] = hit($base . '/api/events/1');
echo "events/1 -> HTTP $c2\n";
echo "  body: " . substr($b2, 0, 150) . "\n";

// Devotional search with very long q (check for error / slow query)
[$c3, $b3] = hit($base . '/api/devotionals?action=search&q=' . str_repeat('a', 5000));
echo "devotionals search q=5000 -> HTTP $c3\n";
echo "  body: " . substr($b3, 0, 150) . "\n";

// Radio current
[$c4, $b4] = hit($base . '/api/radio');
echo "radio -> HTTP $c4\n";
echo "  body: " . substr($b4, 0, 150) . "\n";
