<?php

namespace App\Models\Books;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookIndex extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'mysql';

    protected $table = 'book_indexes';

    protected $fillable = [
        'livro_id',
        'indice_pai_id',
        'titulo',
        'pagina',
    ];

    public function livro(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'livro_id');
    }

    public function indice_pai(): BelongsTo
    {
        return $this->belongsTo(self::class, 'indice_pai_id');
    }

    public function subindices(): HasMany
    {
        return $this->hasMany(self::class, 'indice_pai_id');
    }
}
