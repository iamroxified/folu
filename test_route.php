<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::create('/admin/students.php', 'GET')
);
echo "Status: " . $response->getStatusCode() . "\n";
if ($response->getStatusCode() != 200) {
    if (method_exists($response, 'exception') && $response->exception) {
        echo $response->exception->getMessage() . "\n";
        echo $response->exception->getFile() . ":" . $response->exception->getLine() . "\n";
    }
}
$kernel->terminate($request, $response);
