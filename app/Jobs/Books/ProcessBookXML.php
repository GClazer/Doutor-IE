<?php

namespace App\Jobs\Books;

use App\Models\Books\Book;
use App\Models\Books\BookIndex;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use SimpleXMLElement;

class ProcessBookXML implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly Book $book) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $book = $this->book->load('indices');
        $this->loadSubindicesRecursively($book->indices);

        $xmlRoot = new SimpleXMLElement('<indice/>');
        $this->appendIndices($xmlRoot, $book->indices);

        $xml = $this->formatXml($xmlRoot);

        Storage::disk('public')->put(
            "books/book_{$book->id}.xml",
            $xml
        );
    }

    /**
     * @param  Collection<int, BookIndex>  $indices
     */
    private function loadSubindicesRecursively(Collection $indices): void
    {
        $indices->each(function (BookIndex $index) {
            $index->load('subindices');

            if ($index->subindices->isNotEmpty()) {
                $this->loadSubindicesRecursively($index->subindices);
            }
        });
    }

    /**
     * @param  Collection<int, BookIndex>  $indices
     */
    private function appendIndices(SimpleXMLElement $node, Collection $indices): void
    {
        foreach ($indices as $index) {
            $item = $node->addChild('item');
            $item->addAttribute('pagina', (string) $index->pagina);
            $item->addAttribute('titulo', $index->titulo);

            if ($index->subindices->isNotEmpty()) {
                $this->appendIndices($item, $index->subindices);
            }
        }
    }

    private function formatXml(SimpleXMLElement $xml): string
    {
        $dom = dom_import_simplexml($xml)->ownerDocument;
        $dom->formatOutput = true;

        return $dom->saveXML();
    }
}
