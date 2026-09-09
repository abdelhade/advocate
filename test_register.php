<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\Central\RegisterController;

$subdomain = 'trialtest-' . rand(100, 999);

$req = Request::create('/register', 'POST', [
    'name' => 'أ. فهد المنياوي',
    'office_name' => 'مكتب الفهد الذهبي',
    'subdomain' => $subdomain,
    'email' => $subdomain . '@test.com',
    'phone' => '01012345678',
    'password' => 'password123',
    'password_confirmation' => 'password123',
]);

$controller = new RegisterController();
$response = $controller->register($req);

echo "RESPONSE: " . $response->getContent() . "\n";
