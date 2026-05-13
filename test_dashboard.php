<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

require_once __DIR__.'/db/config.php';
require_once __DIR__.'/db/functions.php';

try {
    $overview = get_admin_dashboard_overview(1, 1);
    print_r($overview);
    echo "\nSuccess!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
