<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\IdEncoder;

$originalId = 12345;
$encoded = IdEncoder::encode($originalId);
$decoded = IdEncoder::decode($encoded);

echo "Original ID: " . $originalId . "\n";
echo "Encoded UUID: " . $encoded . "\n";
echo "Decoded ID: " . $decoded . "\n";

if ($originalId === $decoded) {
    echo "SUCCESS: Encode/Decode works perfectly!\n";
} else {
    echo "FAILURE: Decode mismatch!\n";
}
