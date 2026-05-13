<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/admin/students.php', 'GET');
$response = $kernel->handle($request);

echo "Status: " . $response->getStatusCode() . "\n";
foreach ($response->headers->all() as $name => $values) {
    echo $name . ": " . implode(", ", $values) . "\n";
}
echo "\nContent:\n" . substr($response->getContent(), 0, 500) . "\n";
$kernel->terminate($request, $response);
