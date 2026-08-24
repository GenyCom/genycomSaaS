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
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'last_seen_at')) {
                    $table->timestamp('last_seen_at')->nullable()->index()->after('last_login_ip');
                }
                if (!Schema::hasColumn('users', 'last_seen_ip')) {
                    $table->string('last_seen_ip', 45)->nullable()->after('last_seen_at');
                }
            });
        }

        if (Schema::hasTable('tenants')) {
            Schema::table('tenants', function (Blueprint $table) {
                if (!Schema::hasColumn('tenants', 'plan')) {
                    $table->string('plan', 50)->default('Business')->after('statut');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'last_seen_at')) {
                    $table->dropColumn('last_seen_at');
                }
                if (Schema::hasColumn('users', 'last_seen_ip')) {
                    $table->dropColumn('last_seen_ip');
                }
            });
        }

        if (Schema::hasTable('tenants')) {
            Schema::table('tenants', function (Blueprint $table) {
                if (Schema::hasColumn('tenants', 'plan')) {
                    $table->dropColumn('plan');
                }
            });
        }
    }
};
