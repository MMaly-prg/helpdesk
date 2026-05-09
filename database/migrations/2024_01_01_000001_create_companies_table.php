<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nip', 13)->unique()->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->string('address')->nullable();

            // SLA per priority (hours)
            $table->unsignedSmallInteger('sla_critical_hours')->default(2);
            $table->unsignedSmallInteger('sla_high_hours')->default(4);
            $table->unsignedSmallInteger('sla_normal_hours')->default(8);
            $table->unsignedSmallInteger('sla_low_hours')->default(24);

            // Billing
            $table->decimal('hourly_rate', 8, 2)->default(0);
            $table->string('currency', 3)->default('PLN');

            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
