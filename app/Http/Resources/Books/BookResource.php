<?php

namespace App\Http\Resources\Books;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'titulo' => $this->titulo,
            'usuario_publicador' => $this->usuario_publicador,
            'indices' => BookIndexResource::collection($this->indices),
        ];
    }
}
