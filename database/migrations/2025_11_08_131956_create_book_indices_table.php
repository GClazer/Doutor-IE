<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('book_indexes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('livro_id')->constrained('books');
            $table->foreignId('indice_pai_id')->nullable()->constrained('book_indexes');
            $table->string('titulo');
            $table->integer('pagina');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_indices');
    }
};
