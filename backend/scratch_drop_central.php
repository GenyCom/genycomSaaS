<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;

Schema::connection('central')->dropIfExists('notifications');
echo "Table notifications supprimée de la base centrale.\n";
