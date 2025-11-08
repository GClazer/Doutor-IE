<?php

namespace App\Services\Books;

use App\Http\Resources\Books\BookResource;
use App\Jobs\Books\ProcessBookXML;
use App\Models\Books\Book;
use App\Models\Books\BookIndex;
use Illuminate\Support\Facades\DB;

class BooksService
{
    public function get(?string $titulo = null, ?string $titulo_do_indice = null): array
    {
        $books = Book::query()
            ->select(['id', 'titulo', 'usuario_publicador_id'])
            ->with([
                'usuario_publicador:id,name',
                'indices' => fn ($query) => $query
                    ->when($titulo_do_indice,
                        fn ($q) => $q
                            ->where('titulo', 'like', "%{$titulo_do_indice}%")
                            ->orWhereHas('subindices', fn ($q) => $q->where('titulo', 'like', "%{$titulo_do_indice}%"))
                    ),
            ])
            ->when($titulo, fn ($query) => $query->where('titulo', 'like', "%{$titulo}%"))
            ->get();

        return BookResource::collection($books)->resolve();
    }

    public function createBook(string $titulo, int $usuario_publicador_id, array $indices = []): Book
    {
        return DB::transaction(function () use ($titulo, $usuario_publicador_id, $indices) {
            $book = Book::create([
                'titulo' => $titulo,
                'usuario_publicador_id' => $usuario_publicador_id,
            ]);

            foreach ($indices as $index) {
                $this->createBookIndex($book, $index['titulo'], $index['pagina'], $index['subindices'] ?? []);
            }

            return $book->load('indices.subindices');
        });
    }

    private function createBookIndex(Book $book, string $titulo, int $pagina, array $subindices = []): void
    {
        $index = $book->indices()->create([
            'titulo' => $titulo,
            'pagina' => $pagina,
        ]);

        foreach ($subindices as $subindex) {
            $this->createBookIndexSubindex($index, $subindex['titulo'], $subindex['pagina'], $subindex['subindices'] ?? []);
        }
    }

    private function createBookIndexSubindex(BookIndex $index, string $titulo, int $pagina, array $subindices = []): void
    {
        $index = $index->subindices()->create([
            'livro_id' => $index->livro_id,
            'titulo' => $titulo,
            'pagina' => $pagina,
        ]);

        foreach ($subindices as $subindex) {
            $this->createBookIndexSubindex($index, $subindex['titulo'], $subindex['pagina'], $subindex['subindices'] ?? []);
        }
    }

    public function processBookXML(Book $book): void
    {
        ProcessBookXML::dispatch($book);
    }
}
