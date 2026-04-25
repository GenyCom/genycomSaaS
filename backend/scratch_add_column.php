<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

Schema::table('depenses', function(Blueprint $table) {
    if (!Schema::hasColumn('depenses', 'derniere_notification_at')) {
        $table->timestamp('derniere_notification_at')->nullable();
    }
});
echo "Colonne derniere_notification_at ajoutée avec succès.\n";
