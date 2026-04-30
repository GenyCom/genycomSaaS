<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach(\App\Models\Tenant::all() as $t) {
    echo "ID: {$t->id}, Nom: {$t->nom}, DB: {$t->database_name}\n";
}
