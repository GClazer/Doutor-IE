<?php

namespace App\Http\Controllers\Books;

use App\Http\Controllers\Controller;
use App\Http\Requests\Books\BookRequest;
use App\Models\Books\Book;
use App\Services\Books\BooksService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BooksController extends Controller
{
    public function __construct(private BooksService $booksService) {}

    public function index(Request $request): JsonResponse
    {
        $books = $this->booksService->get(
            $request->query('titulo'),
            $request->query('titulo_do_indice')
        );

        return response()->json($books);
    }

    public function store(BookRequest $request): JsonResponse
    {
        $book = $this->booksService->createBook(
            $request->validated('titulo'),
            $request->user()->id,
            $request->validated('indices', [])
        );

        return response()->json(['book_id' => $book->id], 201);
    }

    public function importarIndicesXml(Book $book): JsonResponse
    {
        $this->booksService->processBookXML($book);

        return response()->json(['message' => 'Índices importados com sucesso'], 200);
    }
}
