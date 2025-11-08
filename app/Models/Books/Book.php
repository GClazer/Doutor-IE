<?php

namespace App\Models\Books;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'mysql';

    protected $table = 'books';

    protected $fillable = [
        'usuario_publicador_id',
        'titulo',
    ];

    public function usuario_publicador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_publicador_id');
    }

    public function indices(): HasMany
    {
        return $this->hasMany(BookIndex::class, 'livro_id')->whereNull('indice_pai_id');
    }
}
