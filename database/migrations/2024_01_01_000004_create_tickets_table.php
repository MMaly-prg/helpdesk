<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // Content
            $table->string('title');
            $table->text('description');

            // Enums
            $table->enum('status', [
                'open',
                'in_progress',
                'waiting_for_client',
                'resolved',
                'closed',
            ])->default('open');

            $table->enum('priority', [
                'critical',
                'high',
                'normal',
                'low',
            ])->default('normal');

            $table->enum('category', [
                'network',
                'server',
                'software',
                'hardware',
                'email',
                'backup',
                'security',
                'other',
            ])->default('other');

            // Source: web panel | rest_api | email
            $table->enum('source', ['web', 'api', 'email'])->default('web');
            $table->string('source_identifier')->nullable(); // np. zabbix-host, email nadawcy

            // Time tracking (sumowane z ticket_notes)
            $table->unsignedInteger('total_time_minutes')->default(0);

            // SLA
            $table->timestamp('sla_deadline_at')->nullable();
            $table->boolean('sla_breached')->default(false);
            $table->timestamp('sla_breached_at')->nullable();

            // Timestamps
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->string('contact_name')->nullable();   // zgłaszający (gdy z API/email)
            $table->string('contact_email')->nullable();

            $table->text('resolution_summary')->nullable();
            $table->json('tags')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indeksy
            $table->index(['status', 'priority']);
            $table->index(['company_id', 'status']);
            $table->index('sla_deadline_at');
            $table->index('assigned_to');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
