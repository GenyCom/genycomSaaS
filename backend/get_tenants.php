<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
foreach(\App\Models\Tenant::all() as $t) {
    echo $t->id . " | " . $t->nom . " | " . $t->database_name . "\n";
}
