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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')
                ->constrained('clients')
                ->restrictOnDelete();

            $table->string('client_name');
            $table->json('services');

            $table->date('report_date')->nullable()->index();

            $table->string('period_type');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->string('period_label')->nullable();

            $table->string('status')->default('todo')->index();
            $table->boolean('viewed')->default(false);

            $table->json('document_links')->nullable();
            $table->text('notes')->nullable();

            $table->boolean('is_copy')->default(false);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
