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
        if (!Schema::hasTable('telemetry_error_logs')) {
            Schema::create('telemetry_error_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->integer('status_code')->default(500)->index();
                $table->text('message');
                $table->string('file', 255)->nullable();
                $table->integer('line')->nullable();
                $table->string('url', 500)->nullable();
                $table->string('method', 10)->nullable();
                $table->string('ip', 45)->nullable();
                $table->longText('trace')->nullable();
                $table->timestamp('created_at')->nullable()->index();
                $table->timestamp('updated_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telemetry_error_logs');
    }
};
