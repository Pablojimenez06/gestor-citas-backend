<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('citas', function (Blueprint $table) {
        $table->id();
        $table->foreignId('cliente_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('profesional_id')->constrained()->onDelete('cascade');
        $table->foreignId('servicio_id')->constrained()->onDelete('cascade');
        $table->date('fecha');
        $table->time('hora');
        $table->string('estado')->default('pendiente'); // pendiente, confirmada, cancelada
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
