<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;

// Simulate Auth
$user = User::whereHas('tenants', function($q) { $q->where('tenant_id', 1); })->first();

if (!$user) {
    echo "No user found for Tenant 1.\n";
    exit;
}

echo "Simulating /me for User: {$user->email}\n";

$controller = new AuthController();
// We need to use reflection or just call the private method if we can, 
// but formatUser is private. Let's use a workaround or just copy the logic.

$reflection = new ReflectionClass(AuthController::class);
$method = $reflection->getMethod('formatUser');
$method->setAccessible(true);

$result = $method->invoke($controller, $user);

echo "Result:\n";
print_r($result);
