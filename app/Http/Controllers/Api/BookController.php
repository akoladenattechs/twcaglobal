<?php

namespace App\Http\Controllers\Api;

use App\Helpers\HtmlHelper;
use App\Http\Controllers\Controller;
use App\Models\Book;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::where('status', 'available')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($book) {
                return [
                    'id' => $book->id,
                    'title' => HtmlHelper::sanitize($book->title),
                    'author' => HtmlHelper::sanitize($book->author),
                    'description' => HtmlHelper::sanitize($book->description),
                    'price' => $book->price,
                    'purchase_link' => $book->purchase_link,
                    'download_link' => $book->download_link,
                    'status' => $book->status,
                ];
            });

        return response()->json(['books' => $books]);
    }

    public function show(string $slug)
    {
        $book = Book::where('slug', $slug)->where('status', 'available')->firstOrFail();

        $book->title = HtmlHelper::sanitize($book->title);
        $book->author = HtmlHelper::sanitize($book->author);
        $book->description = HtmlHelper::sanitize($book->description);

        return response()->json(['book' => $book]);
    }
}
