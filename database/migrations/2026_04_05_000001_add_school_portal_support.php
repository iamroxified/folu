<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('attendance')) {
            Schema::table('attendance', function (Blueprint $table): void {
                if (!Schema::hasColumn('attendance', 'subject_link')) {
                    $table->unsignedInteger('subject_link')->nullable()->after('class_link');
                }

                if (!Schema::hasColumn('attendance', 'academic_session_link')) {
                    $table->unsignedInteger('academic_session_link')->nullable()->after('subject_link');
                }

                if (!Schema::hasColumn('attendance', 'marked_by_user_link')) {
                    $table->unsignedInteger('marked_by_user_link')->nullable()->after('academic_session_link');
                }
            });
        }

        if (!Schema::hasTable('teacher_class_assignments')) {
            Schema::create('teacher_class_assignments', function (Blueprint $table): void {
                $table->increments('id');
                $table->unsignedInteger('teacher_link');
                $table->unsignedInteger('class_link');
                $table->unsignedInteger('academic_session_link');
                $table->string('assignment_role', 30)->default('class_teacher');
                $table->timestamp('created_at')->useCurrent();

                $table->unique(
                    ['teacher_link', 'class_link', 'academic_session_link', 'assignment_role'],
                    'teacher_class_assignments_unique'
                );
            });
        }

        if (!Schema::hasTable('announcements')) {
            Schema::create('announcements', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('title');
                $table->text('body');
                $table->string('audience', 30)->default('all');
                $table->unsignedInteger('academic_session_link')->nullable();
                $table->unsignedInteger('created_by_user_link')->nullable();
                $table->boolean('is_published')->default(true);
                $table->timestamp('published_at')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('announcements')) {
            Schema::dropIfExists('announcements');
        }

        if (Schema::hasTable('teacher_class_assignments')) {
            Schema::dropIfExists('teacher_class_assignments');
        }

        if (Schema::hasTable('attendance')) {
            Schema::table('attendance', function (Blueprint $table): void {
                foreach (['marked_by_user_link', 'academic_session_link', 'subject_link'] as $column) {
                    if (Schema::hasColumn('attendance', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
