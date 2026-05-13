<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
auth()->login($user);

$request = Illuminate\Http\Request::create('/admin/add_students.php', 'GET');
$httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $httpKernel->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
if ($response->getStatusCode() != 200) {
    if (method_exists($response, 'exception') && $response->exception) {
        echo $response->exception->getMessage() . "\n";
    }
}
$httpKernel->terminate($request, $response);
