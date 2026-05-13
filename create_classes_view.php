<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::statement("ALTER TABLE school_classes MODIFY academic_year VARCHAR(255) NULL DEFAULT '2025-2026'");
    DB::statement("CREATE OR REPLACE VIEW classes AS SELECT id, class_name, section AS class_arm, grade_level AS class_level, class_teacher_id AS form_teacher_link, max_capacity, created_at, updated_at FROM school_classes");
    echo "Successfully created view and altered table.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
