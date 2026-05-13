<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::statement("CREATE OR REPLACE VIEW teachers AS SELECT id, staff_number AS teacher_id, first_name, last_name, email, phone, status, id AS user_link, created_at, updated_at FROM staff");
    echo "Successfully created teachers view.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
