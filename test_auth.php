<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$user = \App\Models\User::first();
auth()->login($user);

$request = Illuminate\Http\Request::create('/admin/students.php', 'GET');
// simulate session
$request->setLaravelSession($app['session']->driver());
$app['auth']->setRequest($request);

$response = $kernel->handle($request);

echo "Status: " . $response->getStatusCode() . "\n";
if ($response->isRedirect()) {
    echo "Redirect: " . $response->headers->get('Location') . "\n";
} else {
    echo substr($response->getContent(), 0, 150) . "\n";
}
$kernel->terminate($request, $response);
