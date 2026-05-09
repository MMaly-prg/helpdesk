<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->text('content');

            // Czas poświęcony na tę czynność (minuty)
            $table->unsignedInteger('time_minutes')->default(0);

            // Status ticketu po tej notatce (jeśli zmieniony)
            $table->string('status_changed_to')->nullable();

            // Notatka systemowa (np. auto z API, zmiana statusu, SLA breach)
            $table->boolean('is_system_note')->default(false);

            // Widoczność dla klienta (portal klienta – przyszłość)
            $table->boolean('is_public')->default(false);

            $table->timestamps();

            $table->index(['ticket_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_notes');
    }
};
