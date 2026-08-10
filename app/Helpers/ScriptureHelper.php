<?php

namespace App\Helpers;

class ScriptureHelper
{
    /**
     * Comprehensive list of Bible books (full names and common abbreviations).
     * Ordered so longer/multi-word names come first to match correctly in regex.
     *
     * @var string[]
     */
    protected static $books = [
        // Old Testament
        'Genesis', 'Gen',
        'Exodus', 'Exod', 'Ex',
        'Leviticus', 'Lev',
        'Numbers', 'Num',
        'Deuteronomy', 'Deut',
        'Joshua', 'Josh',
        'Judges', 'Judg',
        'Ruth',
        '1 Samuel', '1 Sam', '1Sam',
        '2 Samuel', '2 Sam', '2Sam',
        '1 Kings', '1 Kgs', '1Kgs',
        '2 Kings', '2 Kgs', '2Kgs',
        '1 Chronicles', '1 Chron', '1Chron', '1 Chr', '1Chr',
        '2 Chronicles', '2 Chron', '2Chron', '2 Chr', '2Chr',
        'Ezra',
        'Nehemiah', 'Neh',
        'Esther', 'Esth',
        'Job',
        'Psalms', 'Psalm', 'Ps',
        'Proverbs', 'Prov', 'Pro',
        'Ecclesiastes', 'Eccles', 'Ecc',
        'Song of Solomon', 'Song of Songs', 'Song',
        'Isaiah', 'Isa',
        'Jeremiah', 'Jer',
        'Lamentations', 'Lam',
        'Ezekiel', 'Ezek',
        'Daniel', 'Dan',
        'Hosea', 'Hos',
        'Joel',
        'Amos',
        'Obadiah', 'Obad',
        'Jonah',
        'Micah',
        'Nahum',
        'Habakkuk', 'Hab',
        'Zephaniah', 'Zeph',
        'Haggai', 'Hag',
        'Zechariah', 'Zech',
        'Malachi', 'Mal',
        // New Testament
        'Matthew', 'Matt',
        'Mark',
        'Luke',
        'John',
        'Acts',
        'Romans', 'Rom',
        '1 Corinthians', '1 Cor', '1Cor', '1 Corinthians',
        '2 Corinthians', '2 Cor', '2Cor', '2 Corinthians',
        'Galatians', 'Gal',
        'Ephesians', 'Eph',
        'Philippians', 'Phil',
        'Colossians', 'Col',
        '1 Thessalonians', '1 Thess', '1Thess', '1 Thes', '1Thes',
        '2 Thessalonians', '2 Thess', '2Thess', '2 Thes', '2Thes',
        '1 Timothy', '1 Tim', '1Tim',
        '2 Timothy', '2 Tim', '2Tim',
        'Titus',
        'Philemon', 'Philem',
        'Hebrews', 'Heb',
        'James',
        '1 Peter', '1 Pet', '1Pet',
        '2 Peter', '2 Pet', '2Pet',
        '1 John', '1 John', '1Jn',
        '2 John', '2 John', '2Jn',
        '3 John', '3 John', '3Jn',
        'Jude',
        'Revelation', 'Rev',
    ];

    /**
     * Linkify scripture references in the given text.
     * Wraps matched references with <a class="bible-ref" data-ref="..."> tags.
     */
    public static function linkify(?string $text): ?string
    {
        if (empty($text)) {
            return $text;
        }

        // Escape books for regex and sort by length descending (longest first)
        $escaped = array_map(function ($book) {
            return preg_quote($book, '/');
        }, self::$books);

        // Remove duplicates
        $escaped = array_unique($escaped);

        // Sort by length descending so "Song of Solomon" matches before "Song"
        usort($escaped, function ($a, $b) {
            return strlen($b) - strlen($a);
        });

        $booksPattern = implode('|', $escaped);

        // Pattern breakdown:
        // (?:Book Name) - the book name
        // (?:\s+\d+(?::\d+(?:[–\-,\s]\d+)*)?)? - optional chapter:verse (e.g. 1, 1:1, 1:1-2, 1:1,4-5)
        $pattern = '/\b((?:[123]\s)?(?:'.$booksPattern.')'.'(?:\s+\d+(?::\d+(?:[–\-,\s\/]\d+)*)?)?'.')\b/i';

        return preg_replace_callback($pattern, function ($matches) {
            $fullRef = trim($matches[0]);

            // Skip if the matched text is just a number with no real context
            // This catches cases where something like "1 John" is matched alone as "1"
            if (strlen($fullRef) < 2) {
                return $matches[0];
            }

            // Skip if what follows the book name is just a year-like number (4 digits)
            // e.g. "Psalm 2024" should not be linked
            if (preg_match('/\b(?:[123]\s)?(?:'.implode('|', array_map(function ($b) {
                return preg_quote($b, '/');
            }, self::$books)).')\s+\d{4}\b/i', $fullRef)) {
                return $matches[0];
            }

            return '<a href="#" class="bible-ref" data-ref="'.htmlspecialchars($fullRef, ENT_QUOTES, 'UTF-8').'">'.htmlspecialchars($fullRef, ENT_QUOTES, 'UTF-8').'</a>';
        }, $text);
    }
}
