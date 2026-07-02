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
        Schema::create('tenant_operations', function (Blueprint $table) {
            $table->id();

            $table->string('tenant_id')->index();

            // provision, suspend, resume, backup...
            $table->string('operation')->index();

            // queued, running, success, failed, cancelled
            $table->string('status')->index();

            // bootstrap, copy-files, start, cleanup...
            $table->string('current_step')->nullable();

            $table->longText('log')->nullable();

            $table->longText('error')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_operations');
    }
};
