<?php

namespace App\Http\Controllers;

use App\Models\Devotional;
use App\Models\SiteSetting;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Geometry\Point;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\FontInterface;
use Intervention\Image\Interfaces\FontProcessorInterface;
use Intervention\Image\Typography\FontFactory;
use Intervention\Image\Typography\TextBlock;

class DevotionalImageController extends Controller
{
    // ── Canvas: Fixed A4 Portrait (840 × 1188 ≈ 1:√2) ────────────────
    protected int $width = 840;

    protected int $height = 1188;

    // ── Layout ────────────────────────────────────────────────────────
    protected int $padding = 44;

    protected int $colGap = 26;

    protected int $leftColW = 474;

    protected int $rightColW = 252; // 840 - 44*2 - 26 - 474 = 252

    // ── Brand Colors ──────────────────────────────────────────────────
    protected string $green = '#1e8e5a';

    protected string $dark = '#1a1a1a';

    protected string $muted = '#555555';

    protected string $gray = '#6b7280';

    // =================================================================
    // ENTRY POINT
    // =================================================================

    public function generate(string $slug)
    {
        $devotional = Devotional::where('slug', $slug)->firstOrFail();

        $driver = new Driver;
        $manager = new ImageManager($driver);
        $fp = $driver->fontProcessor();

        $regular = $this->resolveFont('regular');
        $bold = $this->resolveFont('bold');
        $script = $this->resolveFont('script');

        // ── Site Settings ──────────────────────────────────────────
        $siteTitle = strtoupper(config('app.name', 'Site Title'));
        $devotionalLogoPath = null;
        try {
            $gen = SiteSetting::getSettingsByGroup('general');
            if (! empty($gen['site_title'])) {
                $siteTitle = strtoupper($gen['site_title']);
            }
            $app = SiteSetting::getSettingsByGroup('appearance');
            if (! empty($app['primary_color'])) {
                $this->green = $app['primary_color'];
            }
            if (! empty($app['secondary_color'])) {
                $this->dark = $app['secondary_color'];
            }
            // Fetch dedicated devotional logo image path
            $devoSetting = $app['devotional_logo'] ?? $app['logo'] ?? '';
            if (! empty($devoSetting)) {
                // Full URL (e.g. Cloudflare R2) — download to a temp file so GD can decode it
                if (preg_match('#^https?://#i', $devoSetting)) {
                    $tempLogo = @file_get_contents($devoSetting);
                    if ($tempLogo !== false) {
                        $tmpPath = tempnam(sys_get_temp_dir(), 'devo_logo_');
                        if ($tmpPath !== false && @file_put_contents($tmpPath, $tempLogo) !== false) {
                            $devotionalLogoPath = $tmpPath;
                        }
                    }
                } else {
                    $checkPaths = [
                        public_path($devoSetting),
                        public_path(ltrim($devoSetting, '/')),
                        base_path('public/'.ltrim($devoSetting, '/')),
                    ];
                    foreach ($checkPaths as $cp) {
                        if (file_exists($cp)) {
                            $devotionalLogoPath = $cp;
                            break;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
        }

        // Parse site title into abbreviation + full name
        // e.g. "TWCA | The Wordfare Christian Assembly" -> "TWCA" + "THE WORDFARE CHRISTIAN ASSEMBLY"
        $siteParts = preg_split('/\s*[|\-]\s*/', $siteTitle, 2);
        $siteAbbrev = strtoupper(trim($siteParts[0] ?? ''));
        $siteFullName = strtoupper(trim($siteParts[1] ?? $siteTitle));

        // ── Vertical zones ─────────────────────────────────────────
        $topM = 32;
        $headerH = 130;
        $footerH = 80;
        $bodyTop = $topM + $headerH;       // y where body columns begin
        $bodyH = $this->height - $bodyTop - $footerH - 10;

        $lx = $this->padding;                                         // left column x
        $rx = $this->padding + $this->leftColW + $this->colGap;       // right column x

        // ── Canvas ────────────────────────────────────────────────
        $img = $manager->createImage($this->width, $this->height);
        $img->fill('#ffffff');

        // =================================================================
        // 1. HEADER
        // =================================================================

        // Top-left: Dedicated Devotional Header Logo (if uploaded) or Dual-color Site Heading
        if ($devotionalLogoPath) {
            try {
                $devoLogoImg = $manager->decodePath($devotionalLogoPath);
                $devoLogoImg->scale(height: 75);
                $img->insert($devoLogoImg, $lx, $topM);
            } catch (\Throwable $e) {
                $this->renderTextLogoHeader($img, $siteFullName, $lx, $topM, $bold);
            }
        } else {
            $this->renderTextLogoHeader($img, $siteFullName, $lx, $topM, $bold);
        }

        // Top-right: Script cursive title
        $this->drawText($img, 'The Wordfare Devotional', $this->width - $lx, $topM, $script, 42, $this->green, 'right');

        // Top-right: Date beneath title
        $dateStr = date('l, jS F Y', strtotime($devotional->devotional_date));
        $this->drawText($img, $dateStr, $this->width - $lx, $topM + 72, $regular, 15, $this->muted, 'right');

        // Separator line below header
        $sepY = $bodyTop - 4;
        $img->drawLine(function ($d) use ($lx, $sepY) {
            $d->from($lx, $sepY);
            $d->to($this->width - $lx, $sepY);
            $d->color('#dddddd');
            $d->width(1);
        });

        // =================================================================
        // 2. LEFT COLUMN — Body paragraphs
        // =================================================================

        $fontSize = 15.5;
        $lhRatio = 1.6;

        // Build a reusable body FontInterface for measurements
        $bodyFont = $this->makeFont($regular, $fontSize, $this->dark, $lhRatio, $this->leftColW);
        $lineH = (int) ceil($fp->boxSize('Ag', $bodyFont)->height() * $lhRatio);

        $paragraphs = $this->extractParagraphs($devotional->content ?? '');
        $y = $bodyTop + 18;

        foreach ($paragraphs as $para) {
            $segments = $this->parseScriptureRefs($para);
            $hasHL = collect($segments)->contains('hl', true);

            if ($hasHL) {
                // Word-by-word renderer — supports inline green highlighting
                $used = $this->renderHighlightedPara(
                    $img, $fp, $segments,
                    $lx, $y,
                    $regular, $bold,
                    $fontSize, $lhRatio,
                    $this->leftColW, $lineH
                );
            } else {
                // Full-justified word-by-word renderer
                $used = $this->renderJustifiedBlock(
                    $img, $fp, $para,
                    $lx, $y,
                    $regular, $fontSize, $this->dark, $lhRatio,
                    $this->leftColW
                );
            }

            $y += $used + 24;
        }

        // =================================================================
        // 3. RIGHT COLUMN — Sidebar
        // =================================================================

        $this->renderSidebar($img, $devotional, $fp, $regular, $bold, $rx, $bodyTop + 18);

        // =================================================================
        // 4. FOOTER BANNER
        // =================================================================

        $footerY = $this->height - $footerH;
        $img->drawRectangle(function ($r) use ($footerY, $footerH) {
            $r->size($this->width, $footerH);
            $r->at(0, $footerY);
            $r->background($this->green);
        });

        // Footer text: use reflection_questions (Further Studies) if available, else generic download line
        // ALL lines use identical font file ($regular), size (12.5px), and style (#ffffff)
        if (! empty($devotional->reflection_questions)) {
            $furtherStudies = trim(strip_tags($devotional->reflection_questions));
            if (mb_strlen($furtherStudies) > 160) {
                $furtherStudies = mb_substr($furtherStudies, 0, 157).'...';
            }
            $footerLines = $this->splitIntoTwoLines($furtherStudies, 80);
            $this->renderJustifiedBlock(
                $img, $fp, $footerLines[0],
                $lx, $footerY + 16,
                $regular, 12.5, '#ffffff', 1.25,
                $this->width - ($lx * 2)
            );
            if (! empty($footerLines[1])) {
                $this->renderJustifiedBlock(
                    $img, $fp, $footerLines[1],
                    $lx, $footerY + 44,
                    $regular, 12.5, '#ffffff', 1.25,
                    $this->width - ($lx * 2)
                );
            }
        } else {
            // Fallback: generic download line
            $appUrl = config('app.url') ?? url('/');
            $cleanUrl = preg_replace('#^https?://#', '', rtrim($appUrl, '/'));
            $this->drawText($img, 'You Can Download The Teaching "'.$devotional->title.'"', $lx, $footerY + 16, $regular, 12.5, '#ffffff');
            $this->drawText($img, 'from our website '.$cleanUrl.' for more understanding', $lx, $footerY + 44, $regular, 12.5, '#ffffff');
        }

        return response($img->encode(new PngEncoder)->toString(), 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="devotional-'.date('Y-m-d', strtotime($devotional->devotional_date)).'.png"',
            // Allow browsers/CDNs to cache the generated image (content only changes on edit).
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    // =================================================================
    // SIDEBAR RENDERING
    // =================================================================

    protected function renderSidebar(
        Image $img,
        Devotional $devotional,
        FontProcessorInterface $fp,
        string $regular,
        string $bold,
        int $rx,
        int $startY
    ): void {
        $y = $startY;

        // ── Topic Label ─────────────────────────────────────────────
        $this->drawText($img, 'Topic:', $rx, $y, $regular, 14, $this->gray);
        $y += 26;

        // ── Topic Title: Bold, Uppercase, Large, Green ──────────────
        $topicText = strtoupper($devotional->title);
        $topicFont = $this->makeFont($bold, 22, $this->green, 1.25, $this->rightColW);
        $topicBlock = $fp->textBlock($topicText, $topicFont, new Point(0, 0));
        $topicH = $this->blockHeight($topicBlock, $topicFont, $fp);

        $img->text($topicText, $rx, $y, function ($f) use ($bold) {
            $f->file($bold);
            $f->size(22);
            $f->color($this->green);
            $f->align('left', 'top');
            $f->lineHeight(1.25);
            $f->wrap($this->rightColW);
        });
        $y += $topicH + 32;

        // ── Reference Text Box ───────────────────────────────────────
        if (! empty($devotional->scripture_reference)) {
            $pad = 14;
            $innerW = $this->rightColW - ($pad * 2);

            // Measure the content heights first
            $refLabelFont = $this->makeFont($bold, 13, $this->green, 1.3);
            $refLabelH = 18;

            $refRefFont = $this->makeFont($bold, 13, $this->dark, 1.3, $innerW);
            $refRefBlock = $fp->textBlock($devotional->scripture_reference, $refRefFont, new Point(0, 0));
            $refRefH = $this->blockHeight($refRefBlock, $refRefFont, $fp);

            $verseH = 0;
            $verseText = '';
            if (! empty($devotional->scripture_text)) {
                $verseText = trim(strip_tags($devotional->scripture_text));
                $verseFont = $this->makeFont($regular, 12.5, '#374151', 1.5, $innerW);
                $verseBlock = $fp->textBlock($verseText, $verseFont, new Point(0, 0));
                $verseH = $this->blockHeight($verseBlock, $verseFont, $fp);
            }

            $boxH = $pad + $refLabelH + 4 + $refRefH + ($verseH > 0 ? 10 + $verseH : 0) + $pad;

            // Gray background box
            $img->drawRectangle(function ($r) use ($rx, $y, $boxH) {
                $r->size($this->rightColW, $boxH);
                $r->at($rx, $y);
                $r->background('#f4f4f4');
            });

            // "Reference Text" green label
            $img->text('Reference Text', $rx + $pad, $y + $pad, function ($f) use ($bold) {
                $f->file($bold);
                $f->size(13);
                $f->color($this->green);
                $f->align('left', 'top');
            });

            // Scripture reference (e.g. "1 Chron 11:10")
            $img->text($devotional->scripture_reference, $rx + $pad, $y + $pad + $refLabelH + 4, function ($f) use ($bold, $innerW) {
                $f->file($bold);
                $f->size(13);
                $f->color($this->dark);
                $f->align('left', 'top');
                $f->wrap($innerW);
                $f->lineHeight(1.3);
            });

            // Verse text body
            if ($verseH > 0) {
                $verseOffsetY = $y + $pad + $refLabelH + 4 + $refRefH + 10;
                $this->renderJustifiedBlock(
                    $img, $fp, $verseText,
                    $rx + $pad, $verseOffsetY,
                    $regular, 12.5, '#374151', 1.5,
                    $innerW
                );
            }

            $y += $boxH + 30;
        }

        // ── Confession ───────────────────────────────────────────────
        if (! empty($devotional->prayer)) {
            $this->drawText($img, 'Confession', $rx, $y, $bold, 17, $this->green);
            $y += 28;

            $confText = trim(strip_tags($devotional->prayer));
            $this->renderJustifiedBlock(
                $img, $fp, $confText,
                $rx, $y,
                $regular, 13, '#374151', 1.55,
                $this->rightColW
            );
        }
    }

    // =================================================================
    // INLINE HIGHLIGHTED PARAGRAPH RENDERER
    // (word-by-word, supports mixed green/dark text mid-line)
    // =================================================================

    protected function renderHighlightedPara(
        Image $img,
        FontProcessorInterface $fp,
        array $segments,
        int $x,
        int $y,
        string $regular,
        string $bold,
        float $fontSize,
        float $lhRatio,
        int $colWidth,
        int $lineH
    ): int {
        // Flatten segments into word tokens (word width + space width measured per style)
        $tokens = [];
        foreach ($segments as $seg) {
            $words = preg_split('/\s+/', trim($seg['text']), -1, PREG_SPLIT_NO_EMPTY);
            foreach ($words as $word) {
                $fontFile = $seg['hl'] ? $bold : $regular;
                $color = $seg['hl'] ? $this->green : $this->dark;
                $fnt = $this->makeFont($fontFile, $fontSize, $color, $lhRatio);
                $tokens[] = [
                    'word' => $word,
                    'fontFile' => $fontFile,
                    'size' => $fontSize,
                    'color' => $color,
                    'lh' => $lhRatio,
                    'w' => $fp->boxSize($word, $fnt)->width(),
                    'sw' => $fp->boxSize(' ', $fnt)->width(),
                ];
            }
        }

        if (empty($tokens)) {
            return 0;
        }

        $lines = $this->wrapToLines($tokens, $colWidth);

        // Render each line word by word (full justification except the last line)
        $curY = $y;
        foreach ($lines as $i => $lineTokens) {
            $isLast = ($i === count($lines) - 1);
            $this->renderLineJustified($img, $lineTokens, $x, $curY, $colWidth, ! $isLast);
            $curY += $lineH;
        }

        return count($lines) * $lineH;
    }

    // =================================================================
    // JUSTIFIED TEXT RENDERER
    // =================================================================

    /** Wrap pre-measured word tokens into lines that fit the column width. */
    protected function wrapToLines(array $tokens, int $colWidth): array
    {
        $lines = [];
        $curLine = [];
        $curW = 0;

        foreach ($tokens as $tok) {
            $wordW = $tok['w'];

            if ($curW + $wordW > $colWidth && ! empty($curLine)) {
                $lines[] = $curLine;
                $curLine = [];
                $curW = 0;
            }

            $curLine[] = $tok;
            $curW += $wordW + $tok['sw'];
        }
        if (! empty($curLine)) {
            $lines[] = $curLine;
        }

        return $lines;
    }

    /** Draw one line of tokens; when $justify is true, gaps grow to fill the column width. */
    protected function renderLineJustified(Image $img, array $lineTokens, int $x, int $y, int $colWidth, bool $justify): void
    {
        $n = count($lineTokens);
        if ($n === 0) {
            return;
        }

        // Natural width of the rendered line = sum of word widths + the
        // space widths between words (last word has no trailing gap drawn).
        $wordsW = 0;
        $spacesW = 0;
        foreach ($lineTokens as $i => $tok) {
            $wordsW += $tok['w'];
            if ($i < $n - 1) {
                $spacesW += $tok['sw'];
            }
        }

        // Distribute ONLY the leftover width so the line ends exactly at
        // colWidth (never overflowing into the next column).
        $perGap = 0.0;
        if ($justify && $n > 1 && ($wordsW + $spacesW) < $colWidth) {
            $perGap = ($colWidth - $wordsW - $spacesW) / ($n - 1);
        }

        $curX = $x;
        foreach ($lineTokens as $tok) {
            $img->text($tok['word'], (int) round($curX), $y, function ($f) use ($tok) {
                $f->file($tok['fontFile']);
                $f->size($tok['size']);
                $f->color($tok['color']);
                $f->align('left', 'top');
                $f->lineHeight($tok['lh']);
            });
            $curX += $tok['w'] + $tok['sw'] + $perGap;
        }
    }

    /** Render a plain-text block fully justified (all lines except the last). Returns height used. */
    protected function renderJustifiedBlock(
        Image $img,
        FontProcessorInterface $fp,
        string $text,
        int $x,
        int $y,
        string $fontFile,
        float $fontSize,
        string $color,
        float $lhRatio,
        int $colWidth
    ): int {
        $text = trim($text);
        if ($text === '') {
            return 0;
        }

        $font = $this->makeFont($fontFile, $fontSize, $color, $lhRatio, $colWidth);
        $spaceW = $fp->boxSize(' ', $font)->width();

        $tokens = [];
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($words as $word) {
            $tokens[] = [
                'word' => $word,
                'fontFile' => $fontFile,
                'size' => $fontSize,
                'color' => $color,
                'lh' => $lhRatio,
                'w' => $fp->boxSize($word, $font)->width(),
                'sw' => $spaceW,
            ];
        }

        $lines = $this->wrapToLines($tokens, $colWidth);

        $lineH = (int) ceil($fp->boxSize('Ag', $font)->height() * $lhRatio);

        $curY = $y;
        foreach ($lines as $i => $lineTokens) {
            $isLast = ($i === count($lines) - 1);
            $this->renderLineJustified($img, $lineTokens, $x, $curY, $colWidth, ! $isLast);
            $curY += $lineH;
        }

        return count($lines) * $lineH;
    }

    // =================================================================
    // SCRIPTURE REFERENCE DETECTOR
    // =================================================================

    /**
     * Split a paragraph into segments: normal text vs. scripture references.
     * References are rendered in green.
     * Matches: (John 1:42), (2 Samuel 23), (Luke 5:10-11 KJV), 2 Sam 23, etc.
     */
    protected function parseScriptureRefs(string $text): array
    {
        $pattern = '/(\((?:[1-3]\s?)?[A-Za-z]+(?:\s[A-Za-z]+)?\.?\s+\d+(?::\d+(?:[-–]\d+)?)?\s?(?:[A-Z]+)?\))/u';

        $parts = preg_split($pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        $segs = [];

        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }
            $segs[] = [
                'text' => $part,
                'hl' => (bool) preg_match($pattern, $part),
            ];
        }

        return $segs;
    }

    // =================================================================
    // HTML → PARAGRAPHS
    // =================================================================

    protected function extractParagraphs(string $html): array
    {
        if (empty($html)) {
            return [];
        }

        $html = preg_replace('/<br\s*\/?>/i', "\n", $html);
        $html = preg_replace('/<\/p>/i', "\n\n", $html);

        $chunks = preg_split('/\n{2,}/', $html);

        return array_values(array_filter(
            array_map(fn ($c) => trim(strip_tags($c)), $chunks)
        ));
    }

    // =================================================================
    // HELPERS
    // =================================================================

    /** Quick single-call text draw */
    protected function drawText(Image $img, string $text, int $x, int $y, string $fontFile, float $size, string $color, string $align = 'left'): void
    {
        $img->text($text, $x, $y, function ($f) use ($fontFile, $size, $color, $align) {
            $f->file($fontFile);
            $f->size($size);
            $f->color($color);
            $f->align($align, 'top');
        });
    }

    /** Fallback dual-color site text header */
    protected function renderTextLogoHeader(Image $img, string $siteFullName, int $lx, int $topM, string $bold): void
    {
        $fullParts = explode(' ', $siteFullName);
        if (count($fullParts) >= 3) {
            $half = (int) ceil(count($fullParts) / 2);
            $line1Text = implode(' ', array_slice($fullParts, 0, $half));
            $line2Text = implode(' ', array_slice($fullParts, $half));
        } else {
            $line1Text = $siteFullName;
            $line2Text = '';
        }

        $this->drawText($img, $line1Text, $lx, $topM + 8, $bold, 14, $this->green);
        if (! empty($line2Text)) {
            $this->drawText($img, $line2Text, $lx, $topM + 32, $bold, 13, $this->dark);
        }
    }

    /** Build a FontInterface for measurement purposes */
    protected function makeFont(string $file, float $size, string $color, float $lh, ?int $wrap = null): FontInterface
    {
        return (new FontFactory(function ($f) use ($file, $size, $color, $lh, $wrap) {
            $f->file($file);
            $f->size($size);
            $f->color($color);
            $f->align('left', 'top');
            $f->lineHeight($lh);
            if ($wrap !== null) {
                $f->wrap($wrap);
            }
        }))->font();
    }

    /** Calculate pixel height of a wrapped TextBlock */
    protected function blockHeight(TextBlock $block, FontInterface $font, FontProcessorInterface $fp): int
    {
        $lines = count($block);
        if ($lines === 0) {
            return 0;
        }
        $lineH = $fp->boxSize('Ag', $font)->height() * $font->lineHeight();

        return (int) ceil($lines * $lineH);
    }

    /** Split long text into up to two lines at word boundaries */
    protected function splitIntoTwoLines(string $text, int $maxCharsPerLine = 80): array
    {
        if (mb_strlen($text) <= $maxCharsPerLine) {
            return [$text, ''];
        }

        $wrapped = wordwrap($text, $maxCharsPerLine, "\n");
        $lines = explode("\n", $wrapped, 2);

        return [
            $lines[0] ?? '',
            $lines[1] ?? '',
        ];
    }

    // =================================================================
    // FONT RESOLUTION
    // =================================================================

    protected function resolveFont(string $variant = 'regular'): string
    {
        if ($variant === 'script') {
            return $this->resolveScriptFont();
        }

        $map = [
            'windows' => [
                'regular' => [
                    'C:\\Windows\\Fonts\\arial.ttf',
                    'C:\\Windows\\Fonts\\segoeui.ttf',
                    'C:\\Windows\\Fonts\\calibri.ttf',
                ],
                'bold' => [
                    'C:\\Windows\\Fonts\\arialbd.ttf',
                    'C:\\Windows\\Fonts\\segoeuib.ttf',
                    'C:\\Windows\\Fonts\\calibrib.ttf',
                ],
            ],
            'linux' => [
                'regular' => [
                    '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
                    '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
                    '/usr/share/fonts/truetype/freefont/FreeSans.ttf',
                ],
                'bold' => [
                    '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
                    '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
                    '/usr/share/fonts/truetype/freefont/FreeSansBold.ttf',
                ],
            ],
        ];

        $os = PHP_OS_FAMILY === 'Windows' ? 'windows' : 'linux';
        $list = $map[$os][$variant] ?? [];

        foreach ($list as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        // Cross-fallback
        $fallback = ($variant === 'bold') ? 'regular' : 'bold';
        foreach (($map[$os][$fallback] ?? []) as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        throw new \RuntimeException("No usable font found for variant: $variant");
    }

    protected function resolveScriptFont(): string
    {
        // 1. Brother Signature (HPTypework)
        $brotherPaths = [
            storage_path('app/fonts/BrotherSignature.ttf'),
            storage_path('app/fonts/BrotherSignature.otf'),
            storage_path('app/fonts/Brother_Signature.ttf'),
            storage_path('app/fonts/Brother_Signature.otf'),
            storage_path('app/fonts/Brother-Signature.ttf'),
            storage_path('app/fonts/Brother-Signature.otf'),
            public_path('fonts/BrotherSignature.ttf'),
            public_path('fonts/BrotherSignature.otf'),
            public_path('fonts/Brother_Signature.ttf'),
            public_path('fonts/Brother_Signature.otf'),
            public_path('fonts/Brother-Signature.ttf'),
            public_path('fonts/Brother-Signature.otf'),
        ];

        foreach ($brotherPaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        // 2. Pre-downloaded Dancing Script Bold (fallback)
        $stored = storage_path('app/fonts/DancingScript-Bold.ttf');
        if (file_exists($stored)) {
            return $stored;
        }

        // 3. Windows built-in script fonts
        $winScripts = [
            'C:\\Windows\\Fonts\\segoesc.ttf',   // Segoe Script
            'C:\\Windows\\Fonts\\Gabriola.ttf',
        ];
        foreach ($winScripts as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        // 4. Try to auto-download Dancing Script Bold from Google Fonts
        try {
            $dir = storage_path('app/fonts');
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $url = 'https://fonts.gstatic.com/s/dancingscript/v29/If2cXTr6YS-zF4S-kcSWSVi_sxjsohD9F50Ruu7B1i0HTQ.ttf';
            $ttf = @file_get_contents($url);
            if ($ttf && strlen($ttf) > 10_000) {
                file_put_contents($stored, $ttf);

                return $stored;
            }
        } catch (\Throwable $e) {
        }

        // 5. Fall back to bold sans-serif
        return $this->resolveFont('bold');
    }
}
