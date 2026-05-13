<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    if (Schema::hasColumn('academic_terms', 'is_current')) {
        DB::statement('ALTER TABLE academic_terms CHANGE is_current is_active TINYINT(1) NOT NULL DEFAULT 0');
        echo "Renamed is_current to is_active in academic_terms.\n";
    } else {
        echo "academic_terms already uses is_active.\n";
    }

    $files = [
        'resources/views/admin/students/add.blade.php',
        'resources/views/admin/sessions/index.blade.php',
        'db/school_portal_helpers.php'
    ];

    foreach($files as $file) {
        $path = __DIR__ . '/' . $file;
        if(file_exists($path)) {
            $content = file_get_contents($path);
            if (strpos($content, 'is_current') !== false) {
                $content = str_replace('is_current', 'is_active', $content);
                file_put_contents($path, $content);
                echo "Updated: $file\n";
            }
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
