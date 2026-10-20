<?php

function schema_has_table(string $table, bool $refresh = false): bool
{
    static $tables = null;

    if ($tables === null || $refresh) {
        global $pdo;
        $tables = [];

        if (isset($pdo) && $pdo instanceof PDO) {
            try {
                foreach ($pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $tableName) {
                    $tables[strtolower((string) $tableName)] = true;
                }
            } catch (\Throwable $e) {}
        }
    }

    return isset($tables[strtolower($table)]);
}

function schema_table_columns(string $table, bool $refresh = false): array
{
    static $cache = [];
    $cacheKey = strtolower($table);

    if (!isset($cache[$cacheKey]) || $refresh) {
        if (!schema_has_table($table)) {
            $cache[$cacheKey] = [];
        } else {
            try {
                $cache[$cacheKey] = QueryDB("SHOW COLUMNS FROM `{$table}`")->fetchAll(PDO::FETCH_COLUMN);
            } catch (\Throwable $e) {
                $cache[$cacheKey] = [];
            }
        }
    }

    return $cache[$cacheKey];
}

function schema_has_column(string $table, string $column): bool
{
    return in_array($column, schema_table_columns($table), true);
}

function get_csa_class_column(): string
{
    return schema_has_column('class_subject_assignments', 'school_class_id')
        ? 'school_class_id'
        : (schema_has_column('class_subject_assignments', 'class_id') ? 'class_id' : 'class_link');
}

function get_csa_subject_column(): string
{
    return schema_has_column('class_subject_assignments', 'subject_id')
        ? 'subject_id'
        : 'subject_link';
}

function ensure_school_portal_schema(): void
{
    static $ensured = false;
    if ($ensured) {
        return;
    }
    $ensured = true;

    global $pdo;
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        if (class_exists('\Illuminate\Support\Facades\DB')) {
            try {
                $pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
            } catch (\Throwable $t) {}
        }
        if (!isset($pdo) || !($pdo instanceof PDO)) {
            $pdo = $GLOBALS['pdo'] ?? null;
        }
    }

    if (!isset($pdo) || !($pdo instanceof PDO)) {
        return;
    }

    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `student_subjects` (
                `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                `student_id` bigint unsigned NOT NULL,
                `subject_id` bigint unsigned NOT NULL,
                `session_id` bigint unsigned DEFAULT NULL,
                `term_id` bigint unsigned DEFAULT NULL,
                `status` enum('enrolled','dropped') NOT NULL DEFAULT 'enrolled',
                `dropped_at` timestamp NULL DEFAULT NULL,
                `reason` varchar(255) DEFAULT NULL,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `student_subject_session_unique` (`student_id`, `subject_id`, `session_id`),
                KEY `idx_student_subjects_student` (`student_id`),
                KEY `idx_student_subjects_subject` (`subject_id`),
                KEY `idx_student_subjects_session` (`session_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        schema_has_table('student_subjects', true);
    } catch (\Throwable $t) {}

    try {
        if (schema_has_table('students') && !schema_has_column('students', 'department')) {
            $pdo->exec("ALTER TABLE `students` ADD COLUMN `department` VARCHAR(50) NOT NULL DEFAULT 'general' AFTER `student_type`");
            schema_table_columns('students', true);
        }
    } catch (\Throwable $t) {}

    try {
        if (schema_has_table('subjects') && !schema_has_column('subjects', 'class_level')) {
            $pdo->exec("ALTER TABLE `subjects` ADD COLUMN `class_level` VARCHAR(50) NOT NULL DEFAULT 'ALL' AFTER `subject_name`");
            schema_table_columns('subjects', true);
        }
    } catch (\Throwable $t) {}

    try {
        if (schema_has_table('subjects') && !schema_has_column('subjects', 'department')) {
            $pdo->exec("ALTER TABLE `subjects` ADD COLUMN `department` VARCHAR(255) NULL DEFAULT NULL AFTER `description`");
            schema_table_columns('subjects', true);
        }
    } catch (\Throwable $t) {}

    try {
        if (schema_has_table('class_subject_assignments') && !schema_has_column('class_subject_assignments', 'academic_session_link')) {
            $pdo->exec("ALTER TABLE `class_subject_assignments` ADD COLUMN `academic_session_link` bigint unsigned NULL DEFAULT 1 AFTER `subject_id`");
            schema_table_columns('class_subject_assignments', true);
        }
    } catch (\Throwable $t) {}
}

function normalize_term_code($term): ?string
{
    return match (strtolower(trim((string) $term))) {
        '1', 'first', 'first term', 'first_term' => '1',
        '2', 'second', 'second term', 'second_term' => '2',
        '3', 'third', 'third term', 'third_term' => '3',
        default => null,
    };
}

function session_term_label($term): string
{
    $normalized = normalize_term_code($term);

    if ($normalized !== null) {
        return match ($normalized) {
            '1' => 'First Term',
            '2' => 'Second Term',
            '3' => 'Third Term',
        };
    }

    if (is_numeric($term) && (int) $term > 3 && schema_has_table('academic_terms')) {
        $termName = QueryDB(
            'SELECT term_name FROM academic_terms WHERE id = ? LIMIT 1',
            [(int) $term]
        )->fetchColumn();

        if ($termName) {
            return (string) $termName;
        }
    }

    $term = trim((string) $term);

    if ($term === '' || strtolower($term) === 'annual') {
        return 'Full Session';
    }

    return ucwords(str_replace('_', ' ', $term));
}

function session_label(?array $session): string
{
    return trim((string) ($session['session_name'] ?? 'Unknown Session'));
}

function term_label(?array $term): string
{
    if (!$term) {
        return 'No active term';
    }

    return trim((string) ($term['term_name'] ?? session_term_label($term['term_code'] ?? '')));
}

function session_term_context_label(?array $session, ?array $term = null): string
{
    $sessionName = session_label($session);

    if (!$term) {
        return $sessionName;
    }

    return trim($sessionName . ' | ' . term_label($term));
}

function current_session_label(): string
{
    $session = get_current_academic_session();

    if (!$session) {
        return 'No active session';
    }

    return session_label($session);
}

function current_term_label(): string
{
    return term_label(get_current_academic_term());
}

function get_current_academic_session(): ?array
{
    if (!schema_has_table('academic_sessions')) {
        return null;
    }

    $session = QueryDB(
        "SELECT * FROM academic_sessions WHERE is_active = 1 ORDER BY start_date DESC, id DESC LIMIT 1"
    )->fetch(PDO::FETCH_ASSOC);

    if ($session) {
        return $session;
    }

    return QueryDB(
        "SELECT * FROM academic_sessions ORDER BY start_date DESC, id DESC LIMIT 1"
    )->fetch(PDO::FETCH_ASSOC) ?: null;
}

function get_current_academic_session_id(): ?int
{
    $session = get_current_academic_session();

    return $session ? (int) $session['id'] : null;
}

function get_terms_for_session(?int $sessionId = null): array
{
    if (!schema_has_table('academic_terms')) {
        return [];
    }

    $sessionId ??= get_current_academic_session_id() ?? 0;

    return QueryDB(
        'SELECT * FROM academic_terms WHERE (? = 0 OR academic_session_link = ?) ORDER BY academic_session_link DESC, term_code ASC, id ASC',
        [$sessionId, $sessionId]
    )->fetchAll();
}

function get_current_academic_term(?int $sessionId = null): ?array
{
    if (!schema_has_table('academic_terms')) {
        return null;
    }

    $sessionId ??= get_current_academic_session_id();

    if ($sessionId) {
        $term = QueryDB(
            'SELECT * FROM academic_terms WHERE academic_session_link = ? AND is_active = 1 ORDER BY term_code ASC, id DESC LIMIT 1',
            [$sessionId]
        )->fetch(PDO::FETCH_ASSOC);

        if ($term) {
            return $term;
        }

        return QueryDB(
            'SELECT * FROM academic_terms WHERE academic_session_link = ? ORDER BY term_code ASC, id DESC LIMIT 1',
            [$sessionId]
        )->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    return QueryDB(
        'SELECT * FROM academic_terms WHERE is_active = 1 ORDER BY academic_session_link DESC, term_code ASC, id DESC LIMIT 1'
    )->fetch(PDO::FETCH_ASSOC) ?: null;
}

function get_current_academic_term_id(?int $sessionId = null): ?int
{
    $term = get_current_academic_term($sessionId);

    return $term ? (int) $term['id'] : null;
}

function set_current_academic_session(int $sessionId): bool
{
    if (!schema_has_table('academic_sessions')) {
        return false;
    }

    if (class_exists('\App\Services\AcademicSessionTransitionService')) {
        \App\Services\AcademicSessionTransitionService::transitionToSessionAndTerm($sessionId, null, true);
        return true;
    }

    global $pdo;
    $pdo->beginTransaction();

    try {
        QueryDB("UPDATE academic_sessions SET is_active = 0");
        QueryDB("UPDATE academic_sessions SET is_active = 1 WHERE id = ?", [$sessionId]);
        $pdo->commit();

        return true;
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function set_current_academic_term(int $termId): bool
{
    if (!schema_has_table('academic_terms') && !schema_has_table('terms')) {
        return false;
    }

    if (class_exists('\App\Services\AcademicSessionTransitionService')) {
        $sessId = get_current_academic_session_id() ?? 1;
        \App\Services\AcademicSessionTransitionService::transitionToSessionAndTerm($sessId, $termId, true);
        return true;
    }

    global $pdo;
    $pdo->beginTransaction();

    try {
        QueryDB('UPDATE academic_terms SET is_active = 0');
        QueryDB('UPDATE academic_terms SET is_active = 1 WHERE id = ?', [$termId]);
        $pdo->commit();

        return true;
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function nigerian_grade_from_score($score, bool $simplified = false): string
{
    $score = (float) $score;

    $grade = match (true) {
        $score >= 70 => 'A1',
        $score >= 60 => 'B2',
        $score >= 50 => 'B3',
        $score >= 45 => 'C4',
        $score >= 40 => 'C5',
        $score >= 35 => 'C6',
        $score >= 30 => 'D7',
        $score >= 25 => 'E8',
        default => 'F9',
    };

    if (!$simplified) {
        return $grade;
    }

    return match ($grade) {
        'A1' => 'A',
        'B2', 'B3' => 'B',
        'C4', 'C5', 'C6' => 'C',
        'D7' => 'D',
        'E8' => 'E',
        default => 'F',
    };
}

function generate_teacher_identifier(): string
{
    global $pdo;

    $count = 0;
    if (schema_has_table('teachers')) {
        $count = (int) QueryDB('SELECT COUNT(*) FROM teachers')->fetchColumn();
    }

    $hasEmployeeId = schema_has_column('teachers', 'employee_id');

    $num = $count + 1;
    do {
        $identifier = 'FIS/STF/' . str_pad((string) $num, 3, '0', STR_PAD_LEFT);
        if ($hasEmployeeId) {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM teachers WHERE teacher_id = ? OR employee_id = ?');
            $stmt->execute([$identifier, $identifier]);
        } else {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM teachers WHERE teacher_id = ?');
            $stmt->execute([$identifier]);
        }
        $exists = (int) $stmt->fetchColumn() > 0;
        if ($exists) {
            $num++;
        }
    } while ($exists);

    return $identifier;
}

function generate_username_slug(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '.', $value) ?: 'user';

    return trim($value, '.');
}

function generate_unique_username(string $base): string
{
    global $pdo;

    $base = generate_username_slug($base);
    $candidate = $base;
    $counter = 1;

    while (true) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
        $stmt->execute([$candidate]);

        if ((int) $stmt->fetchColumn() === 0) {
            return $candidate;
        }

        $counter++;
        $candidate = $base . $counter;
    }
}

function schema_table_column_details(string $table, bool $fresh = false): array
{
    static $cache = [];
    $cacheKey = strtolower($table);

    if ($fresh || !isset($cache[$cacheKey])) {
        $cache[$cacheKey] = [];

        if (!schema_has_table($table)) {
            return $cache[$cacheKey];
        }

        foreach (QueryDB("SHOW FULL COLUMNS FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC) as $column) {
            $cache[$cacheKey][(string) $column['Field']] = $column;
        }
    }

    return $cache[$cacheKey];
}

function schema_column_is_generated(string $table, string $column): bool
{
    $columns = schema_table_column_details($table);
    $extra = (string) ($columns[$column]['Extra'] ?? '');

    return stripos($extra, 'generated') !== false;
}

function schema_enum_values(string $table, string $column, bool $fresh = false): array
{
    $columns = schema_table_column_details($table, $fresh);
    $type = (string) ($columns[$column]['Type'] ?? '');

    if (!preg_match("/^enum\\((.*)\\)$/i", $type, $matches)) {
        return [];
    }

    $rawValues = str_getcsv($matches[1], ',', "'", "\\");

    return array_values(array_filter(array_map('trim', $rawValues), static fn ($value) => $value !== ''));
}

function schema_normalize_enum_identifier(string $value): string
{
    $val = trim(strtolower($value));
    $val = preg_replace('/[^a-z0-9_]+/i', '_', $val);
    $val = trim($val, '_');
    return $val;
}

function schema_add_enum_value(string $table, string $column, string $value): bool
{
    $val = schema_normalize_enum_identifier($value);
    if ($val === '') {
        throw new InvalidArgumentException("Enum value cannot be empty.");
    }

    $currentValues = schema_enum_values($table, $column, true);
    if (in_array($val, $currentValues, true)) {
        return true; // Already exists
    }

    $newValues = array_merge($currentValues, [$val]);
    $quoted = implode(', ', array_map(static fn ($v) => "'" . addslashes($v) . "'", $newValues));

    QueryDB("ALTER TABLE `{$table}` MODIFY COLUMN `{$column}` ENUM({$quoted}) NOT NULL");
    schema_table_column_details($table, true);

    return true;
}

function schema_rename_enum_value(string $table, string $column, string $oldValue, string $newValue): bool
{
    $oldVal = schema_normalize_enum_identifier($oldValue);
    $newVal = schema_normalize_enum_identifier($newValue);

    if ($oldVal === '' || $newVal === '') {
        throw new InvalidArgumentException("Enum value cannot be empty.");
    }

    if ($oldVal === $newVal) {
        return true;
    }

    $currentValues = schema_enum_values($table, $column, true);
    if (!in_array($oldVal, $currentValues, true)) {
        throw new RuntimeException("Original enum value '{$oldVal}' does not exist on `{$table}`.`{$column}`.");
    }

    // 1. Expand enum to include new value if not already present
    $expanded = array_values(array_unique(array_merge($currentValues, [$newVal])));
    $quotedExpanded = implode(', ', array_map(static fn ($v) => "'" . addslashes($v) . "'", $expanded));
    QueryDB("ALTER TABLE `{$table}` MODIFY COLUMN `{$column}` ENUM({$quotedExpanded}) NOT NULL");

    // 2. Migrate existing records from old value to new value
    QueryDB("UPDATE `{$table}` SET `{$column}` = ? WHERE `{$column}` = ?", [$newVal, $oldVal]);

    // 3. Remove old value from enum
    $finalValues = array_values(array_filter($expanded, static fn ($v) => $v !== $oldVal));
    if (empty($finalValues)) {
        throw new RuntimeException("Cannot leave enum with zero options.");
    }
    $quotedFinal = implode(', ', array_map(static fn ($v) => "'" . addslashes($v) . "'", $finalValues));
    QueryDB("ALTER TABLE `{$table}` MODIFY COLUMN `{$column}` ENUM({$quotedFinal}) NOT NULL");

    schema_table_column_details($table, true);

    return true;
}

function schema_delete_enum_value(string $table, string $column, string $value): bool
{
    $val = schema_normalize_enum_identifier($value);
    $currentValues = schema_enum_values($table, $column, true);

    if (!in_array($val, $currentValues, true)) {
        return true;
    }

    // Verify no records are using this value
    $usageCount = (int) QueryDB("SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` = ?", [$val])->fetchColumn();
    if ($usageCount > 0) {
        throw new RuntimeException("Cannot delete enum value '{$val}' because it is currently used by {$usageCount} record(s).");
    }

    $finalValues = array_values(array_filter($currentValues, static fn ($v) => $v !== $val));
    if (empty($finalValues)) {
        throw new RuntimeException("Cannot delete all values from enum.");
    }

    $quotedFinal = implode(', ', array_map(static fn ($v) => "'" . addslashes($v) . "'", $finalValues));
    QueryDB("ALTER TABLE `{$table}` MODIFY COLUMN `{$column}` ENUM({$quotedFinal}) NOT NULL");

    schema_table_column_details($table, true);

    return true;
}


function get_role_id_by_name(string $roleName): ?int
{
    static $cache = [];

    $normalized = strtolower(trim($roleName));
    if ($normalized === '') {
        return null;
    }

    if (array_key_exists($normalized, $cache)) {
        return $cache[$normalized];
    }

    if (!schema_has_table('roles') || !schema_has_column('roles', 'role_name')) {
        $cache[$normalized] = null;
        return null;
    }

    $roleId = QueryDB(
        'SELECT id FROM roles WHERE LOWER(role_name) = ? LIMIT 1',
        [$normalized]
    )->fetchColumn();

    $cache[$normalized] = $roleId ? (int) $roleId : null;

    return $cache[$normalized];
}

function get_role_name_by_id(?int $roleId): ?string
{
    static $cache = [];

    $roleId = (int) $roleId;
    if ($roleId <= 0) {
        return null;
    }

    if (array_key_exists($roleId, $cache)) {
        return $cache[$roleId];
    }

    if (!schema_has_table('roles') || !schema_has_column('roles', 'role_name')) {
        $cache[$roleId] = null;
        return null;
    }

    $roleName = QueryDB(
        'SELECT role_name FROM roles WHERE id = ? LIMIT 1',
        [$roleId]
    )->fetchColumn();

    $cache[$roleId] = $roleName ? strtolower((string) $roleName) : null;

    return $cache[$roleId];
}

function get_user_role_name(array $user): string
{
    $role = strtolower(trim((string) ($user['role'] ?? '')));
    if ($role !== '') {
        return $role;
    }

    $roleId = isset($user['role_id']) ? (int) $user['role_id'] : 0;

    return $roleId > 0 ? (get_role_name_by_id($roleId) ?? '') : '';
}

function normalize_user_status(string $status): string
{
    $status = strtolower(trim($status));
    if ($status === '') {
        $status = 'active';
    }

    $columns = schema_table_column_details('users');
    $statusType = strtolower((string) ($columns['status']['Type'] ?? ''));

    if ($statusType !== '' && str_contains($statusType, "'dismissed'")) {
        return $status === 'active' ? 'active' : 'dismissed';
    }

    if ($statusType !== '' && str_contains($statusType, "'inactive'")) {
        return $status === 'active' ? 'active' : 'inactive';
    }

    return $status;
}

function create_portal_user(array $userData): int
{
    global $pdo;

    $userColumns = schema_table_column_details('users');
    if ($userColumns === []) {
        throw new RuntimeException('Users table is not available.');
    }

    $username = trim((string) ($userData['username'] ?? ''));
    $name = trim((string) ($userData['name'] ?? ($userData['display_name'] ?? '')));
    $email = trim((string) ($userData['email'] ?? ''));
    $password = (string) ($userData['password'] ?? '');
    $status = normalize_user_status((string) ($userData['status'] ?? 'active'));
    $roleName = trim((string) ($userData['role_name'] ?? ($userData['role'] ?? '')));
    $roleId = isset($userData['role_id']) && (int) $userData['role_id'] > 0 ? (int) $userData['role_id'] : null;

    if ($name === '') {
        $name = trim(
            (string) ($userData['first_name'] ?? '') . ' ' . (string) ($userData['last_name'] ?? '')
        );
    }

    if ($name === '') {
        $name = $username !== '' ? $username : $email;
    }

    if ($email === '' || $password === '') {
        throw new InvalidArgumentException('User email and password are required.');
    }

    if ($roleId === null && $roleName !== '' && isset($userColumns['role_id'])) {
        $roleId = get_role_id_by_name($roleName);
    }

    $insertData = [];

    if (isset($userColumns['username'])) {
        $insertData['username'] = $username !== '' ? $username : null;
    }

    if (isset($userColumns['name'])) {
        $insertData['name'] = $name;
    }

    if (isset($userColumns['email'])) {
        $insertData['email'] = $email;
    }

    if (isset($userColumns['password'])) {
        $insertData['password'] = $password;
    }

    if (isset($userColumns['role_id']) && $roleId !== null) {
        $insertData['role_id'] = $roleId;
    }

    if (isset($userColumns['role']) && $roleName !== '') {
        $insertData['role'] = strtolower($roleName);
    }

    if (isset($userColumns['status'])) {
        $insertData['status'] = $status;
    }

    $timestamp = date('Y-m-d H:i:s');

    if (isset($userColumns['created_at'])) {
        $insertData['created_at'] = $timestamp;
    }

    if (isset($userColumns['updated_at'])) {
        $insertData['updated_at'] = $timestamp;
    }

    if ($insertData === []) {
        throw new RuntimeException('No supported user columns were found for insertion.');
    }

    $columnList = implode(', ', array_keys($insertData));
    $placeholders = implode(', ', array_fill(0, count($insertData), '?'));

    $stmt = $pdo->prepare("INSERT INTO users ({$columnList}) VALUES ({$placeholders})");
    $stmt->execute(array_values($insertData));

    return (int) $pdo->lastInsertId();
}

function portal_authenticate_user(string $username, string $password, array $allowedRoles): array
{
    global $pdo;

    $username = trim($username);
    $allowedRoles = array_values(array_filter(array_map('strtolower', $allowedRoles)));

    try {
        $user = QueryDB(
            'SELECT * FROM users WHERE username = ? LIMIT 1',
            [$username]
        )->fetch(PDO::FETCH_ASSOC);

        if (
            !$user &&
            in_array('student', $allowedRoles, true) &&
            schema_has_table('students') &&
            schema_has_column('students', 'user_link')
        ) {
            $user = QueryDB(
                'SELECT u.* FROM students s JOIN users u ON s.user_link = u.id WHERE s.admission_no = ? LIMIT 1',
                [$username]
            )->fetch(PDO::FETCH_ASSOC);
        }

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User account not found.',
            ];
        }

        if (($user['status'] ?? 'inactive') !== 'active') {
            return [
                'success' => false,
                'message' => 'Your account is not active right now. Please contact the school administrator.',
            ];
        }

        if (!password_verify($password, (string) ($user['password'] ?? ''))) {
            return [
                'success' => false,
                'message' => 'Invalid password. Please try again.',
            ];
        }

        $user['role'] = get_user_role_name($user);

        if (!in_array($user['role'], $allowedRoles, true)) {
            return [
                'success' => false,
                'message' => 'Your account does not have permission to use this portal.',
            ];
        }

        return [
            'success' => true,
            'user' => $user,
        ];
    } catch (Throwable $e) {
        return [
            'success' => false,
            'message' => 'A system error occurred while signing you in.',
        ];
    }
}

function get_teacher_profile_by_user_id(int $userId): ?array
{
    return QueryDB(
        'SELECT t.*, u.username, u.email AS user_email, u.status AS user_status
         FROM teachers t
         JOIN users u ON t.user_link = u.id
         WHERE t.user_link = ?
         LIMIT 1',
        [$userId]
    )->fetch(PDO::FETCH_ASSOC) ?: null;
}

function get_student_profile_by_user_id(int $userId): ?array
{
    $studentUserJoin = schema_has_column('students', 'user_link')
        ? '(s.user_link = u.id OR s.admission_no = u.username OR (s.email IS NOT NULL AND s.email != "" AND s.email = u.email))'
        : 's.admission_no = u.username';

    return QueryDB(
        'SELECT s.*, u.username, u.email AS user_email, u.status AS user_status,
                COALESCE(c.class_name, sc.class_name) AS class_name,
                COALESCE(c.class_arm, sc.section) AS class_arm,
                COALESCE(c.class_level, sc.grade_level) AS class_level,
                ac.session_name,
                COALESCE(t.term_name, at.term_name) AS session_term,
                COALESCE(t.term_name, at.term_name) AS term_name,
                COALESCE(CAST(t.term_number AS CHAR), at.term_code) AS term_code
         FROM students s
         JOIN users u ON ' . $studentUserJoin . '
         LEFT JOIN classes c ON s.class_link = c.id
         LEFT JOIN school_classes sc ON s.current_class_id = sc.id
         LEFT JOIN academic_sessions ac ON COALESCE(s.current_session_id, s.academic_session_link) = ac.id
         LEFT JOIN terms t ON COALESCE(s.current_term_id, s.term_link) = t.id
         LEFT JOIN academic_terms at ON s.term_link = at.id
         WHERE u.id = ?
         LIMIT 1',
        [$userId]
    )->fetch(PDO::FETCH_ASSOC) ?: null;
}

function get_student_profile_by_id(int $studentId): ?array
{
    return QueryDB(
        'SELECT s.*, u.username, u.email AS user_email, u.status AS user_status,
                COALESCE(c.class_name, sc.class_name) AS class_name,
                COALESCE(c.class_arm, sc.section) AS class_arm,
                COALESCE(c.class_level, sc.grade_level) AS class_level,
                ac.session_name,
                COALESCE(t.term_name, at.term_name) AS session_term,
                COALESCE(t.term_name, at.term_name) AS term_name,
                COALESCE(CAST(t.term_number AS CHAR), at.term_code) AS term_code
         FROM students s
         LEFT JOIN users u ON (s.user_link = u.id OR s.admission_no = u.username)
         LEFT JOIN classes c ON s.class_link = c.id
         LEFT JOIN school_classes sc ON s.current_class_id = sc.id
         LEFT JOIN academic_sessions ac ON COALESCE(s.current_session_id, s.academic_session_link) = ac.id
         LEFT JOIN terms t ON COALESCE(s.current_term_id, s.term_link) = t.id
         LEFT JOIN academic_terms at ON s.term_link = at.id
         WHERE s.id = ?
         LIMIT 1',
        [$studentId]
    )->fetch(PDO::FETCH_ASSOC) ?: null;
}

function get_teacher_default_session_id(int $teacherId): ?int
{
    $currentSessionId = get_current_academic_session_id();

    if ($currentSessionId) {
        $hasCurrentData = false;

        if (schema_has_table('teacher_class_assignments')) {
            $hasCurrentData = (int) QueryDB(
                'SELECT COUNT(*) FROM teacher_class_assignments WHERE teacher_link = ? AND academic_session_link = ?',
                [$teacherId, $currentSessionId]
            )->fetchColumn() > 0;
        }

        if (!$hasCurrentData && schema_has_table('teacher_subjects')) {
            $hasCurrentData = (int) QueryDB(
                'SELECT COUNT(*) FROM teacher_subjects WHERE teacher_link = ? AND academic_session_link = ?',
                [$teacherId, $currentSessionId]
            )->fetchColumn() > 0;
        }

        if (!$hasCurrentData && schema_has_column('classes', 'form_teacher_link')) {
            $hasCurrentData = (int) QueryDB(
                'SELECT COUNT(*) FROM classes WHERE form_teacher_link = ?',
                [$teacherId]
            )->fetchColumn() > 0;
        }

        if ($hasCurrentData) {
            return $currentSessionId;
        }
    }

    $candidates = [];

    if (schema_has_table('teacher_class_assignments')) {
        $sessionId = QueryDB(
            'SELECT academic_session_link FROM teacher_class_assignments WHERE teacher_link = ? ORDER BY academic_session_link DESC LIMIT 1',
            [$teacherId]
        )->fetchColumn();

        if ($sessionId) {
            $candidates[] = (int) $sessionId;
        }
    }

    if (schema_has_table('teacher_subjects')) {
        $sessionId = QueryDB(
            'SELECT academic_session_link FROM teacher_subjects WHERE teacher_link = ? ORDER BY academic_session_link DESC LIMIT 1',
            [$teacherId]
        )->fetchColumn();

        if ($sessionId) {
            $candidates[] = (int) $sessionId;
        }
    }

    rsort($candidates);

    return $candidates[0] ?? $currentSessionId;
}

function get_student_default_session_id(int $studentId, ?int $studentSessionId = null): ?int
{
    $studentSessionId = $studentSessionId ?: null;
    $currentSessionId = get_current_academic_session_id();

    if ($studentSessionId !== null && $studentSessionId > 0) {
        return $studentSessionId;
    }

    if ($currentSessionId) {
        return $currentSessionId;
    }

    $candidates = [];

    if (schema_has_table('student_fees') && schema_has_table('fee_structures')) {
        $sessionId = QueryDB(
            'SELECT fs.session_id
             FROM student_fees sf
             JOIN fee_structures fs ON sf.fee_structure_id = fs.id
             WHERE sf.student_id = ? AND fs.session_id IS NOT NULL
             ORDER BY fs.session_id DESC, sf.id DESC
             LIMIT 1',
            [$studentId]
        )->fetchColumn();

        if ($sessionId) {
            $candidates[] = (int) $sessionId;
        }
    }

    if (schema_has_table('payments') && schema_has_table('student_fees') && schema_has_table('fee_structures')) {
        $sessionId = QueryDB(
            "SELECT fs.session_id
             FROM payments p
             JOIN student_fees sf ON p.payable_type = 'student_fee' AND p.payable_id = sf.id
             JOIN fee_structures fs ON sf.fee_structure_id = fs.id
             WHERE sf.student_id = ? AND fs.session_id IS NOT NULL
             ORDER BY COALESCE(p.payment_date, p.created_at) DESC, p.id DESC
             LIMIT 1",
            [$studentId]
        )->fetchColumn();

        if ($sessionId) {
            $candidates[] = (int) $sessionId;
        }
    }

    if (schema_has_table('student_attendance') && schema_has_column('student_attendance', 'session_id')) {
        $sessionId = QueryDB(
            'SELECT session_id FROM student_attendance WHERE student_id = ? AND session_id IS NOT NULL ORDER BY session_id DESC, id DESC LIMIT 1',
            [$studentId]
        )->fetchColumn();

        if ($sessionId) {
            $candidates[] = (int) $sessionId;
        }
    }

    rsort($candidates);

    return $candidates[0] ?? null;
}

function save_primary_parent_contact(int $studentId, array $parentData): ?int
{
    global $pdo;

    if (!schema_has_table('parents') || !schema_has_table('student_parents')) {
        return null;
    }

    $firstName = validate((string) ($parentData['first_name'] ?? ''));
    $lastName = validate((string) ($parentData['last_name'] ?? ''));
    $email = validate((string) ($parentData['email'] ?? ''));
    $phone = validate((string) ($parentData['phone'] ?? ''));
    $relationship = validate((string) ($parentData['relationship'] ?? 'guardian'));

    if ($firstName === '' && $lastName === '' && $email === '' && $phone === '') {
        return null;
    }

    $displayName = trim($firstName . ' ' . $lastName);
    if ($displayName === '') {
        $displayName = 'Parent Contact';
    }

    $existing = QueryDB(
        "SELECT p.*, sp.id AS student_parent_id
         FROM student_parents sp
         JOIN parents p ON sp.parent_link = p.id
         WHERE sp.student_link = ? AND sp.is_primary_contact = 1
         LIMIT 1",
        [$studentId]
    )->fetch(PDO::FETCH_ASSOC);

    $pdo->beginTransaction();

    try {
        if ($existing) {
            QueryDB(
                'UPDATE parents SET first_name = ?, last_name = ?, email = ?, phone = ?, relationship_to_student = ?, updated_at = NOW() WHERE id = ?',
                [$firstName, $lastName, $email ?: null, $phone, $relationship, $existing['id']]
            );

            $pdo->commit();

            return (int) $existing['id'];
        }

        $usernameBase = $displayName !== 'Parent Contact' ? $displayName : ($phone !== '' ? 'parent.' . $phone : 'parent');
        $username = generate_unique_username($usernameBase);
        $userEmail = $email !== '' ? $email : ($username . '@parent.fimocol.local');
        $password = password_hash('password', PASSWORD_DEFAULT);

        $userId = create_portal_user([
            'username' => $username,
            'name' => $displayName,
            'email' => $userEmail,
            'password' => $password,
            'role_name' => 'parent',
            'status' => 'active',
        ]);

        QueryDB(
            'INSERT INTO parents (user_link, parent_id, first_name, last_name, email, phone, relationship_to_student, status, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
            [
                $userId,
                'PAR' . date('Y') . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
                $firstName !== '' ? $firstName : 'Parent',
                $lastName !== '' ? $lastName : 'Contact',
                $email !== '' ? $email : null,
                $phone !== '' ? $phone : 'N/A',
                $relationship,
                'active',
            ]
        );

        $parentId = (int) $pdo->lastInsertId();

        QueryDB(
            'INSERT INTO student_parents (student_link, parent_link, relationship, is_primary_contact, created_at) VALUES (?, ?, ?, 1, NOW())',
            [$studentId, $parentId, $relationship]
        );

        $pdo->commit();

        return $parentId;
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function get_primary_parent_contact(int $studentId): ?array
{
    if (!schema_has_table('parents') || !schema_has_table('student_parents')) {
        return null;
    }

    return QueryDB(
        "SELECT p.*, sp.relationship, sp.is_primary_contact
         FROM student_parents sp
         JOIN parents p ON sp.parent_link = p.id
         WHERE sp.student_link = ?
         ORDER BY sp.is_primary_contact DESC, sp.id ASC
         LIMIT 1",
        [$studentId]
    )->fetch(PDO::FETCH_ASSOC) ?: null;
}

function get_teacher_subject_assignments(int $teacherId, ?int $sessionId = null): array
{
    if (!schema_has_table('teacher_subjects')) {
        return [];
    }

    $sessionId ??= get_current_academic_session_id() ?? 0;

    return QueryDB(
        "SELECT ts.*, s.subject_name, s.subject_code, c.class_name, c.class_arm, c.class_level
         FROM teacher_subjects ts
         JOIN subjects s ON ts.subject_link = s.id
         LEFT JOIN classes c ON ts.class_link = c.id
         WHERE ts.teacher_link = ? AND (? = 0 OR ts.academic_session_link = ?)
         ORDER BY c.class_level, c.class_name, s.subject_name",
        [$teacherId, $sessionId, $sessionId]
    )->fetchAll();
}

function get_teacher_class_assignments(int $teacherId, ?int $sessionId = null): array
{
    $assignments = [];
    $seen = [];
    $sessionId ??= get_current_academic_session_id() ?? 0;

    if (schema_has_column('classes', 'form_teacher_link')) {
        foreach (
            QueryDB(
                "SELECT c.*, 'class_teacher' AS assignment_role
                 FROM classes c
                 WHERE c.form_teacher_link = ?
                 ORDER BY c.class_level, c.class_name, c.class_arm",
                [$teacherId]
            )->fetchAll() as $assignment
        ) {
            $key = implode(':', [
                (int) ($assignment['class_link'] ?? $assignment['id'] ?? 0),
                (int) $sessionId,
                (string) ($assignment['assignment_role'] ?? 'class_teacher'),
            ]);

            $seen[$key] = true;
            $assignments[] = $assignment;
        }
    }

    if (schema_has_table('teacher_class_assignments')) {
        foreach (
            QueryDB(
                "SELECT tca.*, c.class_name, c.class_arm, c.class_level
                 FROM teacher_class_assignments tca
                 JOIN classes c ON tca.class_link = c.id
                 WHERE tca.teacher_link = ? AND (? = 0 OR tca.academic_session_link = ?)
                 ORDER BY c.class_level, c.class_name, c.class_arm",
                [$teacherId, $sessionId, $sessionId]
            )->fetchAll() as $assignment
        ) {
            $key = implode(':', [
                (int) ($assignment['class_link'] ?? 0),
                (int) ($assignment['academic_session_link'] ?? $sessionId),
                (string) ($assignment['assignment_role'] ?? 'class_teacher'),
            ]);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $assignments[] = $assignment;
        }
    }

    usort($assignments, static function (array $left, array $right): int {
        return strcmp(
            strtolower(trim(($left['class_level'] ?? '') . ' ' . ($left['class_name'] ?? '') . ' ' . ($left['class_arm'] ?? '') . ' ' . ($left['assignment_role'] ?? ''))),
            strtolower(trim(($right['class_level'] ?? '') . ' ' . ($right['class_name'] ?? '') . ' ' . ($right['class_arm'] ?? '') . ' ' . ($right['assignment_role'] ?? '')))
        );
    });

    return $assignments;
}

function assign_teacher_subject(int $teacherId, int $subjectId, int $classId, int $sessionId): bool
{
    if (!schema_has_table('teacher_subjects')) {
        return false;
    }

    $existing = QueryDB(
        'SELECT COUNT(*) FROM teacher_subjects WHERE teacher_link = ? AND subject_link = ? AND class_link = ? AND academic_session_link = ?',
        [$teacherId, $subjectId, $classId, $sessionId]
    )->fetchColumn();

    if ((int) $existing > 0) {
        return false;
    }

    QueryDB(
        'INSERT INTO teacher_subjects (teacher_link, subject_link, class_link, academic_session_link, created_at) VALUES (?, ?, ?, ?, NOW())',
        [$teacherId, $subjectId, $classId, $sessionId]
    );

    return true;
}

function assign_teacher_class(int $teacherId, int $classId, int $sessionId, string $assignmentRole = 'class_teacher'): bool
{
    global $pdo;

    $assignmentRole = trim($assignmentRole) !== '' ? trim($assignmentRole) : 'class_teacher';
    $saved = false;

    $pdo->beginTransaction();

    try {
        if ($assignmentRole === 'class_teacher' && schema_has_column('classes', 'form_teacher_link')) {
            QueryDB(
                'UPDATE classes SET form_teacher_link = ? WHERE id = ?',
                [$teacherId, $classId]
            );
            $saved = true;

            if (schema_has_table('teacher_class_assignments')) {
                QueryDB(
                    'DELETE FROM teacher_class_assignments WHERE class_link = ? AND academic_session_link = ? AND assignment_role = ?',
                    [$classId, $sessionId, 'class_teacher']
                );
            }
        }

        if (schema_has_table('teacher_class_assignments')) {
            $existing = QueryDB(
                'SELECT COUNT(*) FROM teacher_class_assignments WHERE teacher_link = ? AND class_link = ? AND academic_session_link = ? AND assignment_role = ?',
                [$teacherId, $classId, $sessionId, $assignmentRole]
            )->fetchColumn();

            if ((int) $existing === 0) {
                QueryDB(
                    'INSERT INTO teacher_class_assignments (teacher_link, class_link, academic_session_link, assignment_role, created_at) VALUES (?, ?, ?, ?, NOW())',
                    [$teacherId, $classId, $sessionId, $assignmentRole]
                );
                $saved = true;
            }
        }

        $pdo->commit();

        return $saved;
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function remove_teacher_subject_assignment(int $teacherId, int $assignmentId): bool
{
    if (!schema_has_table('teacher_subjects')) {
        return false;
    }

    return QueryDB(
        'DELETE FROM teacher_subjects WHERE id = ? AND teacher_link = ?',
        [$assignmentId, $teacherId]
    )->rowCount() > 0;
}

function remove_teacher_class_assignment(int $teacherId, int $classId, int $sessionId, string $assignmentRole = 'class_teacher'): bool
{
    global $pdo;

    $removed = false;
    $assignmentRole = trim($assignmentRole) !== '' ? trim($assignmentRole) : 'class_teacher';

    $pdo->beginTransaction();

    try {
        if (schema_has_table('teacher_class_assignments')) {
            $removed = QueryDB(
                'DELETE FROM teacher_class_assignments WHERE teacher_link = ? AND class_link = ? AND academic_session_link = ? AND assignment_role = ?',
                [$teacherId, $classId, $sessionId, $assignmentRole]
            )->rowCount() > 0 || $removed;
        }

        if ($assignmentRole === 'class_teacher' && schema_has_column('classes', 'form_teacher_link')) {
            $removed = QueryDB(
                'UPDATE classes SET form_teacher_link = NULL WHERE id = ? AND form_teacher_link = ?',
                [$classId, $teacherId]
            )->rowCount() > 0 || $removed;
        }

        $pdo->commit();

        return $removed;
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function get_teacher_accessible_classes(int $teacherId, ?int $sessionId = null): array
{
    $classes = [];
    $seen = [];

    foreach (get_teacher_class_assignments($teacherId, $sessionId) as $assignment) {
        $classId = (int) ($assignment['class_link'] ?? $assignment['id'] ?? 0);

        if ($classId < 1 || isset($seen[$classId])) {
            continue;
        }

        $seen[$classId] = true;
        $classes[] = [
            'id' => $classId,
            'class_name' => $assignment['class_name'] ?? '',
            'class_arm' => $assignment['class_arm'] ?? '',
            'class_level' => $assignment['class_level'] ?? '',
            'assignment_role' => $assignment['assignment_role'] ?? 'class_teacher',
            'academic_session_link' => $assignment['academic_session_link'] ?? $sessionId,
        ];
    }

    foreach (get_teacher_subject_assignments($teacherId, $sessionId) as $assignment) {
        $classId = (int) ($assignment['class_link'] ?? 0);

        if ($classId < 1 || isset($seen[$classId])) {
            continue;
        }

        $seen[$classId] = true;
        $classes[] = [
            'id' => $classId,
            'class_name' => $assignment['class_name'] ?? '',
            'class_arm' => $assignment['class_arm'] ?? '',
            'class_level' => $assignment['class_level'] ?? '',
            'assignment_role' => 'subject_teacher',
            'academic_session_link' => $assignment['academic_session_link'] ?? $sessionId,
        ];
    }

    usort($classes, static function (array $left, array $right): int {
        return strcmp(
            strtolower(trim(($left['class_level'] ?? '') . ' ' . ($left['class_name'] ?? '') . ' ' . ($left['class_arm'] ?? ''))),
            strtolower(trim(($right['class_level'] ?? '') . ' ' . ($right['class_name'] ?? '') . ' ' . ($right['class_arm'] ?? '')))
        );
    });

    return $classes;
}

function get_teacher_accessible_subjects(int $teacherId, ?int $classId = null, ?int $sessionId = null): array
{
    if (!schema_has_table('teacher_subjects')) {
        return [];
    }

    $sessionId ??= get_current_academic_session_id() ?? 0;

    return QueryDB(
        "SELECT ts.*, s.subject_name, s.subject_code, c.class_name, c.class_arm, c.class_level
         FROM teacher_subjects ts
         JOIN subjects s ON ts.subject_link = s.id
         LEFT JOIN classes c ON ts.class_link = c.id
         WHERE ts.teacher_link = ?
           AND (? = 0 OR ts.class_link = ?)
           AND (? = 0 OR ts.academic_session_link = ?)
         ORDER BY c.class_level, c.class_name, s.subject_name",
        [$teacherId, (int) ($classId ?? 0), (int) ($classId ?? 0), $sessionId, $sessionId]
    )->fetchAll();
}

function teacher_has_class_access(int $teacherId, int $classId, ?int $sessionId = null): bool
{
    foreach (get_teacher_accessible_classes($teacherId, $sessionId) as $class) {
        if ((int) ($class['id'] ?? 0) === $classId) {
            return true;
        }
    }

    return false;
}

function teacher_has_subject_access(int $teacherId, int $classId, int $subjectId, ?int $sessionId = null): bool
{
    foreach (get_teacher_accessible_subjects($teacherId, $classId, $sessionId) as $assignment) {
        if ((int) ($assignment['subject_link'] ?? 0) === $subjectId) {
            return true;
        }
    }

    return false;
}

function get_students_by_class_and_session(int $classId, ?int $sessionId = null): array
{
    $sessionId ??= get_current_academic_session_id() ?? 0;

    return QueryDB(
        "SELECT s.* FROM students s
         WHERE s.class_link = ? AND (? = 0 OR s.academic_session_link = ?)
         ORDER BY s.last_name, s.first_name",
        [$classId, $sessionId, $sessionId]
    )->fetchAll();
}

function get_class_subject_assignments(?int $classId = null, ?int $sessionId = null): array
{
    ensure_school_portal_schema();

    if (!schema_has_table('class_subject_assignments')) {
        return [];
    }

    $sessionId ??= get_current_academic_session_id() ?? 0;
    $classId ??= 0;

    $classCol = get_csa_class_column();
    $subjCol = get_csa_subject_column();
    $hasSessionCol = schema_has_column('class_subject_assignments', 'academic_session_link');

    $sql = "SELECT csa.*, 
                c.class_name, c.class_arm, c.class_level, 
                s.subject_name, s.subject_code, s.subject_type, s.department,
                CASE WHEN s.subject_type = 'core' THEN 1 ELSE 0 END AS is_core
         FROM class_subject_assignments csa
         JOIN classes c ON csa.{$classCol} = c.id
         JOIN subjects s ON csa.{$subjCol} = s.id
         WHERE (? = 0 OR csa.{$classCol} = ?)";
    $params = [$classId, $classId];

    if ($hasSessionCol) {
        $sql .= " AND (? = 0 OR csa.academic_session_link = ?)";
        $params[] = $sessionId;
        $params[] = $sessionId;
    }

    $sql .= " ORDER BY c.class_level, c.class_name, c.class_arm, s.subject_name";

    return QueryDB($sql, $params)->fetchAll();
}

function is_pre_nursery_applicable_level(string $level): bool
{
    $clean = strtoupper(trim($level));
    if ($clean === '') {
        return false;
    }

    return (
        str_starts_with($clean, 'CRE') ||
        str_starts_with($clean, 'CRC') ||
        str_contains($clean, 'CRECHE') ||
        str_starts_with($clean, 'PNUR') ||
        str_starts_with($clean, 'PRE_NUR') ||
        str_starts_with($clean, 'PRE-NUR') ||
        str_contains($clean, 'PRE-NURSERY') ||
        str_contains($clean, 'PRE_NURSERY') ||
        str_starts_with($clean, 'NUR') ||
        str_contains($clean, 'NURSERY')
    );
}

function build_subject_class_level_sql_clause(string $classLevel, string $className = '', string $tableAlias = 's'): array
{
    $cls = trim($classLevel);
    $clsUpper = strtoupper($cls);

    $conditions = [
        "{$tableAlias}.class_level = 'ALL'",
        "{$tableAlias}.class_level = ?",
    ];
    $params = [$cls];

    if (str_starts_with($clsUpper, 'SSS') || str_contains($clsUpper, 'SENIOR')) {
        $conditions[] = "{$tableAlias}.class_level = 'SSS'";
    }
    if (str_starts_with($clsUpper, 'JSS') || str_contains($clsUpper, 'JUNIOR')) {
        $conditions[] = "{$tableAlias}.class_level = 'JSS'";
    }
    if (str_starts_with($clsUpper, 'PRI') || str_contains($clsUpper, 'PRIMARY')) {
        $conditions[] = "{$tableAlias}.class_level = 'PRI'";
    }
    if (is_pre_nursery_applicable_level($cls) || is_pre_nursery_applicable_level($className)) {
        $conditions[] = "{$tableAlias}.class_level IN ('Pre_Nursery', 'PRE_NURSERY', 'PNUR', 'NUR', 'EARLY_YEARS')";
    }

    $sql = '(' . implode(' OR ', $conditions) . ')';
    return [$sql, $params];
}

function subject_matches_class_level(string $subjectLevel, string $classLevel): bool
{
    $sub = strtoupper(trim($subjectLevel));
    $cls = strtoupper(trim($classLevel));
    if ($sub === 'ALL' || $sub === '' || $cls === '' || $sub === $cls) {
        return true;
    }
    if ($sub === 'SSS' && (str_starts_with($cls, 'SSS') || str_contains($cls, 'SENIOR'))) {
        return true;
    }
    if ($sub === 'JSS' && (str_starts_with($cls, 'JSS') || str_contains($cls, 'JUNIOR'))) {
        return true;
    }
    if (($sub === 'PRI' || $sub === 'PRIMARY') && (str_starts_with($cls, 'PRI') || str_contains($cls, 'PRIMARY'))) {
        return true;
    }
    if (in_array($sub, ['PRE_NURSERY', 'PRE-NURSERY', 'PNUR', 'NUR', 'EARLY_YEARS'], true)) {
        return is_pre_nursery_applicable_level($cls);
    }
    return false;
}

function subject_matches_student_department(?string $subjectDept, ?string $studentDept): bool
{
    $sub = strtolower(trim((string) $subjectDept));
    $stu = strtolower(trim((string) $studentDept));
    if ($stu === '') {
        $stu = 'general';
    }
    if ($sub === '' || $sub === 'all' || $sub === 'general' || $sub === 'core') {
        return true;
    }
    if ($stu === 'general') {
        return false;
    }
    return $sub === $stu;
}

function assign_subject_to_class(int $classId, int $subjectId, int $sessionId): bool
{
    ensure_school_portal_schema();

    if (!schema_has_table('class_subject_assignments')) {
        return false;
    }

    $class = QueryDB('SELECT * FROM classes WHERE id = ? LIMIT 1', [$classId])
        ->fetch(PDO::FETCH_ASSOC);
    $subject = QueryDB('SELECT * FROM subjects WHERE id = ? LIMIT 1', [$subjectId])->fetch(PDO::FETCH_ASSOC);

    if (!$class || !$subject) {
        return false;
    }

    $subjectLevel = (string) ($subject['class_level'] ?? 'ALL');
    $classLevel = (string) ($class['class_level'] ?? '');

    if (!subject_matches_class_level($subjectLevel, $classLevel)) {
        return false;
    }

    $classCol = get_csa_class_column();
    $subjCol = get_csa_subject_column();
    $hasSessionCol = schema_has_column('class_subject_assignments', 'academic_session_link');

    $checkSql = "SELECT COUNT(*) FROM class_subject_assignments WHERE {$classCol} = ? AND {$subjCol} = ?";
    $checkParams = [$classId, $subjectId];
    if ($hasSessionCol && $sessionId > 0) {
        $checkSql .= " AND academic_session_link = ?";
        $checkParams[] = $sessionId;
    }

    $existing = QueryDB($checkSql, $checkParams)->fetchColumn();

    if ((int) $existing > 0) {
        return false;
    }

    $sessionName = QueryDB('SELECT session_name FROM academic_sessions WHERE id = ? LIMIT 1', [$sessionId])->fetchColumn() ?: date('Y');

    $cols = [$classCol, $subjCol];
    $vals = [$classId, $subjectId];
    $placeholders = ['?', '?'];

    if (schema_has_column('class_subject_assignments', 'academic_year')) {
        $cols[] = 'academic_year';
        $vals[] = $sessionName;
        $placeholders[] = '?';
    }
    if ($hasSessionCol) {
        $cols[] = 'academic_session_link';
        $vals[] = $sessionId;
        $placeholders[] = '?';
    }
    if (schema_has_column('class_subject_assignments', 'status')) {
        $cols[] = 'status';
        $vals[] = 'active';
        $placeholders[] = '?';
    }
    if (schema_has_column('class_subject_assignments', 'created_at')) {
        $cols[] = 'created_at';
        $placeholders[] = 'NOW()';
    }
    if (schema_has_column('class_subject_assignments', 'updated_at')) {
        $cols[] = 'updated_at';
        $placeholders[] = 'NOW()';
    }

    $insertSql = "INSERT INTO class_subject_assignments (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $placeholders) . ")";
    QueryDB($insertSql, $vals);

    return true;
}

function remove_class_subject_assignment(int $assignmentId): bool
{
    if (!schema_has_table('class_subject_assignments')) {
        return false;
    }

    return QueryDB('DELETE FROM class_subject_assignments WHERE id = ?', [$assignmentId])->rowCount() > 0;
}

function get_student_allocated_subjects(int $studentId, ?int $sessionId = null): array
{
    ensure_school_portal_schema();

    $student = QueryDB("SELECT * FROM students WHERE id = ? LIMIT 1", [$studentId])->fetch(PDO::FETCH_ASSOC);
    if (!$student) {
        return [];
    }

    $classId = (int) ($student['current_class_id'] ?? $student['class_link'] ?? 0);
    $studentDept = strtolower(trim((string) ($student['department'] ?? 'general')));
    if ($studentDept === '') {
        $studentDept = 'general';
    }
    $sessionId = ($sessionId !== null && $sessionId > 0)
        ? $sessionId
        : (int) ($student['current_session_id'] ?? $student['academic_session_link'] ?? get_current_academic_session_id() ?? 0);

    // Class details
    $class = $classId > 0
        ? QueryDB("SELECT * FROM classes WHERE id = ? LIMIT 1", [$classId])->fetch(PDO::FETCH_ASSOC)
        : null;
    $classLevel = (string) ($class['class_level'] ?? '');

    // 1. Get subjects explicitly assigned to this class
    $classAssignedSubjects = [];
    if ($classId > 0 && schema_has_table('class_subject_assignments')) {
        $classCol = get_csa_class_column();
        $subjCol = get_csa_subject_column();
        $hasSessionCol = schema_has_column('class_subject_assignments', 'academic_session_link');

        $query = "SELECT s.*, 1 AS is_class_assignment
                  FROM class_subject_assignments csa
                  JOIN subjects s ON csa.{$subjCol} = s.id
                  WHERE csa.{$classCol} = ?";
        $params = [$classId];

        if ($hasSessionCol) {
            $query .= " AND (? = 0 OR csa.academic_session_link = ? OR csa.academic_session_link IS NULL)";
            $params[] = $sessionId;
            $params[] = $sessionId;
        }

        $query .= " ORDER BY s.subject_name";

        $classAssignedSubjects = QueryDB($query, $params)->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. Fetch subjects matching class level
    $levelSubjects = [];
    if ($classLevel !== '') {
        if (schema_has_column('subjects', 'class_level')) {
            [$levelClause, $levelParams] = build_subject_class_level_sql_clause($classLevel, (string) ($class['class_name'] ?? ''));
            $levelSubjects = QueryDB(
                "SELECT s.*, 0 AS is_class_assignment
                 FROM subjects s
                 WHERE (s.status IS NULL OR s.status = 'active')
                   AND {$levelClause}
                 ORDER BY s.subject_name",
                $levelParams
            )->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $allSubjects = QueryDB(
                "SELECT s.*, 0 AS is_class_assignment
                 FROM subjects s
                 WHERE (s.status IS NULL OR s.status = 'active')
                 ORDER BY s.subject_name"
            )->fetchAll(PDO::FETCH_ASSOC);
            $levelSubjects = array_filter($allSubjects, fn($sub) => subject_matches_class_level($sub['class_level'] ?? 'ALL', $classLevel));
        }
    }

    // Combine subjects by ID
    $combinedSubjects = [];
    foreach ($classAssignedSubjects as $sub) {
        $combinedSubjects[(int) $sub['id']] = $sub;
    }
    foreach ($levelSubjects as $sub) {
        if (!isset($combinedSubjects[(int) $sub['id']])) {
            $combinedSubjects[(int) $sub['id']] = $sub;
        }
    }

    // 3. Fetch explicit student_subjects records (for enrollments and drops)
    $studentSubjectRecords = [];
    if (schema_has_table('student_subjects')) {
        $records = QueryDB(
            "SELECT ss.*, s.subject_name, s.subject_code, s.department, s.subject_type
             FROM student_subjects ss
             JOIN subjects s ON ss.subject_id = s.id
             WHERE ss.student_id = ?
               AND (? = 0 OR ss.session_id = ? OR ss.session_id IS NULL)",
            [$studentId, $sessionId, $sessionId]
        )->fetchAll(PDO::FETCH_ASSOC);

        foreach ($records as $rec) {
            $subId = (int) $rec['subject_id'];
            $studentSubjectRecords[$subId] = $rec;
            if (!isset($combinedSubjects[$subId])) {
                $combinedSubjects[$subId] = $rec;
                $combinedSubjects[$subId]['id'] = $subId;
            }
        }
    }

    $result = [];
    foreach ($combinedSubjects as $subId => $sub) {
        $subjDept = strtolower(trim((string) ($sub['department'] ?? 'general')));
        if ($subjDept === '' || $subjDept === 'all') {
            $subjDept = 'general';
        }

        $matchesStream = subject_matches_student_department($subjDept, $studentDept);
        $hasRecord = isset($studentSubjectRecords[$subId]);
        $record = $hasRecord ? $studentSubjectRecords[$subId] : null;

        $isExplicitEnrolled = $hasRecord && ($record['status'] ?? '') === 'enrolled';
        $isDropped = $hasRecord && ($record['status'] ?? '') === 'dropped';

        // An item is allocated to the student if:
        // - It matches their stream and hasn't been dropped, OR
        // - It has been explicitly dropped (we still return it so the UI shows it as dropped with re-enroll option), OR
        // - It was explicitly enrolled
        if (!$matchesStream && !$isExplicitEnrolled && !$isDropped) {
            continue;
        }

        $status = $isDropped ? 'dropped' : 'enrolled';

        $result[] = [
            'id' => (int) $subId,
            'subject_id' => (int) $subId,
            'subject_name' => (string) ($sub['subject_name'] ?? ''),
            'subject_code' => (string) ($sub['subject_code'] ?? ''),
            'class_level' => (string) ($sub['class_level'] ?? 'ALL'),
            'department' => $subjDept,
            'subject_type' => (string) ($sub['subject_type'] ?? 'core'),
            'matches_stream' => $matchesStream,
            'status' => $status,
            'is_dropped' => $isDropped,
            'dropped_at' => $isDropped ? ($record['dropped_at'] ?? null) : null,
            'reason' => $isDropped ? ($record['reason'] ?? null) : null,
            'is_class_assignment' => !empty($sub['is_class_assignment']),
        ];
    }

    usort($result, function($a, $b) {
        if ($a['is_dropped'] !== $b['is_dropped']) {
            return $a['is_dropped'] ? 1 : -1;
        }
        return strcasecmp($a['subject_name'], $b['subject_name']);
    });

    return $result;
}

function drop_student_subject(int $studentId, int $subjectId, ?int $sessionId = null, ?string $reason = null): bool
{
    ensure_school_portal_schema();

    if (!schema_has_table('student_subjects')) {
        return false;
    }

    $sessionId ??= get_current_academic_session_id() ?? 0;
    
    $existing = QueryDB(
        "SELECT id FROM student_subjects WHERE student_id = ? AND subject_id = ? AND (session_id = ? OR session_id IS NULL) LIMIT 1",
        [$studentId, $subjectId, $sessionId]
    )->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        QueryDB(
            "UPDATE student_subjects SET status = 'dropped', dropped_at = NOW(), reason = ?, updated_at = NOW() WHERE id = ?",
            [$reason, $existing['id']]
        );
    } else {
        QueryDB(
            "INSERT INTO student_subjects (student_id, subject_id, session_id, status, dropped_at, reason, created_at, updated_at) VALUES (?, ?, ?, 'dropped', NOW(), ?, NOW(), NOW())",
            [$studentId, $subjectId, $sessionId, $reason]
        );
    }
    return true;
}

function enroll_student_subject(int $studentId, int $subjectId, ?int $sessionId = null): bool
{
    ensure_school_portal_schema();

    if (!schema_has_table('student_subjects')) {
        return false;
    }

    $sessionId ??= get_current_academic_session_id() ?? 0;
    
    $existing = QueryDB(
        "SELECT id FROM student_subjects WHERE student_id = ? AND subject_id = ? AND (session_id = ? OR session_id IS NULL) LIMIT 1",
        [$studentId, $subjectId, $sessionId]
    )->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        QueryDB(
            "UPDATE student_subjects SET status = 'enrolled', dropped_at = NULL, reason = NULL, updated_at = NOW() WHERE id = ?",
            [$existing['id']]
        );
    } else {
        QueryDB(
            "INSERT INTO student_subjects (student_id, subject_id, session_id, status, created_at, updated_at) VALUES (?, ?, ?, 'enrolled', NOW(), NOW())",
            [$studentId, $subjectId, $sessionId]
        );
    }
    return true;
}

function get_student_active_subjects(int $studentId, ?int $sessionId = null): array
{
    $allocated = get_student_allocated_subjects($studentId, $sessionId);
    return array_values(array_filter($allocated, fn($s) => ($s['status'] ?? '') === 'enrolled'));
}

function get_students_enrolled_in_subject(int $classId, int $subjectId, ?int $sessionId = null): array
{
    $sessionId ??= get_current_academic_session_id() ?? 0;
    $allStudents = get_students_by_class_and_session($classId, $sessionId);
    $subject = QueryDB("SELECT * FROM subjects WHERE id = ? LIMIT 1", [$subjectId])->fetch(PDO::FETCH_ASSOC);
    if (!$subject) {
        return $allStudents;
    }

    $subjDept = strtolower(trim((string) ($subject['department'] ?? 'general')));
    if ($subjDept === '' || $subjDept === 'all') {
        $subjDept = 'general';
    }

    $enrolledStudents = [];
    foreach ($allStudents as $student) {
        $studentDept = strtolower(trim((string) ($student['department'] ?? 'general')));
        if ($studentDept === '') {
            $studentDept = 'general';
        }

        // Check explicit student_subjects record
        $rec = null;
        if (schema_has_table('student_subjects')) {
            $rec = QueryDB(
                "SELECT status FROM student_subjects WHERE student_id = ? AND subject_id = ? AND (session_id = ? OR session_id IS NULL) LIMIT 1",
                [$student['id'], $subjectId, $sessionId]
            )->fetch(PDO::FETCH_ASSOC);
        }

        if ($rec) {
            if ($rec['status'] === 'enrolled') {
                $enrolledStudents[] = $student;
            }
            // if dropped, excluded
            continue;
        }

        // No record: check stream match
        if (subject_matches_student_department($subjDept, $studentDept)) {
            $enrolledStudents[] = $student;
        }
    }

    return $enrolledStudents;
}

function get_timetable_entries(?int $classId = null, ?int $sessionId = null): array
{
    if (!schema_has_table('class_timetables')) {
        return [];
    }

    $sessionId ??= get_current_academic_session_id() ?? 0;
    $classId ??= 0;

    return QueryDB(
        "SELECT ct.*, c.class_name, c.class_arm, c.class_level,
                s.subject_name, s.subject_code,
                t.teacher_id, t.first_name, t.last_name
         FROM class_timetables ct
         JOIN classes c ON ct.class_link = c.id
         JOIN subjects s ON ct.subject_link = s.id
         JOIN teachers t ON ct.teacher_link = t.id
         WHERE (? = 0 OR ct.class_link = ?)
           AND (? = 0 OR ct.academic_session_link = ?)
         ORDER BY c.class_level, c.class_name, c.class_arm, ct.day_of_week, ct.start_time",
        [$classId, $classId, $sessionId, $sessionId]
    )->fetchAll();
}

function save_timetable_entry(
    ?int $timetableId,
    int $classId,
    int $subjectId,
    int $teacherId,
    int $sessionId,
    int $dayOfWeek,
    string $startTime,
    string $endTime,
    ?string $room = null
): int {
    if (!schema_has_table('class_timetables')) {
        throw new RuntimeException('Timetable support is not available.');
    }

    if ($classId < 1 || $subjectId < 1 || $teacherId < 1 || $sessionId < 1) {
        throw new InvalidArgumentException('Class, subject, teacher, and session are required.');
    }

    if ($dayOfWeek < 1 || $dayOfWeek > 7) {
        throw new InvalidArgumentException('Please select a valid day of the week.');
    }

    if ($startTime >= $endTime) {
        throw new InvalidArgumentException('End time must be later than start time.');
    }

    $conflict = QueryDB(
        "SELECT COUNT(*) FROM class_timetables
         WHERE class_link = ?
           AND academic_session_link = ?
           AND day_of_week = ?
           AND id != ?
           AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?))",
        [$classId, $sessionId, $dayOfWeek, (int) ($timetableId ?? 0), $endTime, $startTime, $startTime, $endTime]
    )->fetchColumn();

    if ((int) $conflict > 0) {
        throw new RuntimeException('This timetable entry conflicts with another lesson for the selected class.');
    }

    $teacherConflict = QueryDB(
        "SELECT COUNT(*) FROM class_timetables
         WHERE teacher_link = ?
           AND academic_session_link = ?
           AND day_of_week = ?
           AND id != ?
           AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?))",
        [$teacherId, $sessionId, $dayOfWeek, (int) ($timetableId ?? 0), $endTime, $startTime, $startTime, $endTime]
    )->fetchColumn();

    if ((int) $teacherConflict > 0) {
        throw new RuntimeException('This teacher already has another timetable entry during that period.');
    }

    $room = $room !== null ? validate($room) : null;

    if ($timetableId) {
        QueryDB(
            'UPDATE class_timetables
             SET class_link = ?, subject_link = ?, teacher_link = ?, academic_session_link = ?, day_of_week = ?, start_time = ?, end_time = ?, room = ?, updated_at = NOW()
             WHERE id = ?',
            [$classId, $subjectId, $teacherId, $sessionId, $dayOfWeek, $startTime, $endTime, $room !== '' ? $room : null, $timetableId]
        );

        return $timetableId;
    }

    QueryDB(
        'INSERT INTO class_timetables (class_link, subject_link, teacher_link, academic_session_link, day_of_week, start_time, end_time, room, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
        [$classId, $subjectId, $teacherId, $sessionId, $dayOfWeek, $startTime, $endTime, $room !== '' ? $room : null]
    );

    global $pdo;

    return (int) $pdo->lastInsertId();
}

function delete_timetable_entry(int $timetableId): bool
{
    if (!schema_has_table('class_timetables')) {
        return false;
    }

    return QueryDB('DELETE FROM class_timetables WHERE id = ?', [$timetableId])->rowCount() > 0;
}

function allocate_fee_to_student(int $studentId, int $feeId, int $sessionId): bool
{
    $fee = QueryDB('SELECT * FROM fees WHERE id = ?', [$feeId])->fetch(PDO::FETCH_ASSOC);

    if (!$fee) {
        return false;
    }

    $termLink = schema_has_column('fees', 'term_link') ? (int) ($fee['term_link'] ?? 0) : 0;
    $existing = QueryDB(
        'SELECT COUNT(*) FROM student_fees WHERE student_link = ? AND fee_structure_link = ? AND academic_session_link = ? AND ((term_link IS NULL AND ? = 0) OR term_link = ?)',
        [$studentId, $feeId, $sessionId, $termLink, $termLink]
    )->fetchColumn();

    if ((int) $existing > 0) {
        return false;
    }

    $amount = (float) ($fee['fee_amount'] ?? 0);

    QueryDB(
        'INSERT INTO student_fees (student_link, fee_structure_link, amount_due, amount_paid, balance, status, due_date, academic_session_link, term_link, description, created_at, updated_at)
         VALUES (?, ?, ?, 0, ?, ?, DATE_ADD(CURDATE(), INTERVAL 30 DAY), ?, ?, ?, NOW(), NOW())',
        [
            $studentId,
            $feeId,
            $amount,
            $amount,
            $amount > 0 ? 'pending' : 'paid',
            $sessionId,
            $termLink > 0 ? $termLink : null,
            $fee['fee_description'] ?? null,
        ]
    );

    return true;
}

function allocate_fees_to_students_for_fee(int $feeId, ?int $sessionId = null): array
{
    $sessionId ??= get_current_academic_session_id();

    $fee = QueryDB('SELECT * FROM fees WHERE id = ?', [$feeId])->fetch(PDO::FETCH_ASSOC);
    if (!$fee) {
        return ['allocated' => 0, 'skipped' => 0];
    }

    $targetSessionId = (int) ($sessionId ?: $fee['fee_session'] ?: 0);
    $students = QueryDB(
        'SELECT id FROM students WHERE (? = 0 OR academic_session_link = ?) AND (? = 0 OR class_link = ?)',
        [$targetSessionId, $targetSessionId, (int) ($fee['fee_class'] ?? 0), (int) ($fee['fee_class'] ?? 0)]
    )->fetchAll(PDO::FETCH_COLUMN);

    $allocated = 0;
    $skipped = 0;

    foreach ($students as $studentId) {
        if (allocate_fee_to_student((int) $studentId, $feeId, $targetSessionId)) {
            $allocated++;
        } else {
            $skipped++;
        }
    }

    return ['allocated' => $allocated, 'skipped' => $skipped];
}

function allocate_active_session_fees(?int $sessionId = null, ?int $termId = null): array
{
    if (class_exists('\App\Services\AcademicSessionTransitionService')) {
        $res = \App\Services\AcademicSessionTransitionService::assignFeesToAllStudents($sessionId, $termId);
        return [
            'allocated' => $res['fees_allocated'],
            'skipped' => 0,
            'fees_processed' => $res['students_processed'],
        ];
    }

    $sessionId ??= get_current_academic_session_id();
    $termId ??= get_current_academic_term_id($sessionId) ?? 0;
    $fees = QueryDB(
        'SELECT id FROM fees WHERE status = ? AND (? IS NULL OR fee_session = ?) AND (? = 0 OR term_link = ? OR term_link IS NULL)',
        ['active', $sessionId, $sessionId, $termId, $termId]
    )->fetchAll(PDO::FETCH_COLUMN);

    $allocated = 0;
    $skipped = 0;

    foreach ($fees as $feeId) {
        $result = allocate_fees_to_students_for_fee((int) $feeId, $sessionId);
        $allocated += $result['allocated'];
        $skipped += $result['skipped'];
    }

    return ['allocated' => $allocated, 'skipped' => $skipped, 'fees_processed' => count($fees)];
}

function get_admin_payment_summary(?int $sessionId = null, ?int $termId = null): array
{
    $sessionId ??= get_current_academic_session_id() ?? 0;
    $termId ??= get_current_academic_term_id($sessionId) ?? 0;

    if (schema_has_table('student_fees')) {
        $whereConditions = [];
        $params = [];

        if ($sessionId > 0) {
            $whereConditions[] = "(fs.session_id = ? OR s.current_session_id = ? OR sf.id > 0)";
            $params[] = $sessionId;
            $params[] = $sessionId;
        }

        if ($termId > 0) {
            $whereConditions[] = "(fs.term_id IS NULL OR fs.term_id = ? OR s.current_term_id = ? OR sf.id > 0)";
            $params[] = $termId;
            $params[] = $termId;
        }

        $whereSql = $whereConditions ? implode(' AND ', $whereConditions) : '1=1';

        $summary = QueryDB(
            "SELECT COUNT(sf.id) AS total_records,
                    COUNT(DISTINCT sf.student_id) AS student_count,
                    COUNT(CASE WHEN sf.amount_paid > 0 THEN 1 END) AS payment_count,
                    COALESCE(SUM(sf.amount_due), 0) AS total_expected,
                    COALESCE(SUM(sf.amount_paid), 0) AS total_paid,
                    COALESCE(SUM(sf.balance), 0) AS outstanding
             FROM student_fees sf
             LEFT JOIN fee_structures fs ON sf.fee_structure_id = fs.id
             LEFT JOIN students s ON sf.student_id = s.id
             WHERE {$whereSql}",
            $params
        )->fetch(PDO::FETCH_ASSOC) ?: [];

        $cashPaid = 0.0;
        $bankPaid = 0.0;

        if (schema_has_table('payments')) {
            $pmSummary = QueryDB(
                "SELECT COALESCE(SUM(CASE WHEN payment_method = 'cash' THEN amount ELSE 0 END), 0) AS cash_paid,
                        COALESCE(SUM(CASE WHEN payment_method = 'bank_transfer' THEN amount ELSE 0 END), 0) AS bank_paid
                 FROM payments WHERE status = 'completed'"
            )->fetch(PDO::FETCH_ASSOC);
            if ($pmSummary) {
                $cashPaid = (float) $pmSummary['cash_paid'];
                $bankPaid = (float) $pmSummary['bank_paid'];
            }
        }

        return [
            'payment_count' => (int) ($summary['payment_count'] ?? 0),
            'student_count' => (int) ($summary['student_count'] ?? 0),
            'total_expected' => (float) ($summary['total_expected'] ?? 0),
            'total_paid' => (float) ($summary['total_paid'] ?? 0),
            'cash_paid' => $cashPaid,
            'bank_paid' => $bankPaid,
            'outstanding' => (float) ($summary['outstanding'] ?? 0),
        ];
    }

    return [
        'payment_count' => 0,
        'student_count' => 0,
        'total_expected' => 0.0,
        'total_paid' => 0.0,
        'cash_paid' => 0.0,
        'bank_paid' => 0.0,
        'outstanding' => 0.0,
    ];
}

function get_admin_payment_records(?int $sessionId = null, ?int $termId = null, int $limit = 100): array
{
    $sessionId ??= get_current_academic_session_id() ?? 0;
    $termId ??= get_current_academic_term_id($sessionId) ?? 0;
    $limit = max(1, $limit);

    if (schema_has_table('student_fees')) {
        $whereConditions = ["sf.amount_paid > 0"];
        $params = [];

        if ($sessionId > 0) {
            $whereConditions[] = "(fs.session_id = ? OR s.current_session_id = ? OR sf.id > 0)";
            $params[] = $sessionId;
            $params[] = $sessionId;
        }

        if ($termId > 0) {
            $whereConditions[] = "(fs.term_id IS NULL OR fs.term_id = ? OR s.current_term_id = ? OR sf.id > 0)";
            $params[] = $termId;
            $params[] = $termId;
        }

        $whereSql = implode(' AND ', $whereConditions);

        return QueryDB(
            "SELECT sf.id,
                    sf.student_id,
                    sf.amount_paid,
                    sf.amount_due,
                    sf.balance,
                    sf.status,
                    COALESCE(sf.updated_at, sf.created_at) AS payment_date,
                    s.first_name,
                    s.last_name,
                    s.admission_no,
                    COALESCE(fs.name, 'Student Fee') AS fee_type_name,
                    COALESCE(t.term_name, 'Session Fee') AS term_name,
                    'bank_transfer' AS payment_method
             FROM student_fees sf
             LEFT JOIN students s ON sf.student_id = s.id
             LEFT JOIN fee_structures fs ON sf.fee_structure_id = fs.id
             LEFT JOIN terms t ON fs.term_id = t.id
             WHERE {$whereSql}
             ORDER BY COALESCE(sf.updated_at, sf.created_at) DESC, sf.id DESC
             LIMIT {$limit}",
            $params
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    return [];
}

function get_admin_dashboard_overview(?int $sessionId = null, ?int $termId = null): array
{
    $sessionId ??= get_current_academic_session_id() ?? 0;
    $termId ??= get_current_academic_term_id($sessionId) ?? 0;
    $paymentSummary = get_admin_payment_summary($sessionId, $termId);

    // Check which column name is used for session link in students table
    $hasAcademicSessionLink = schema_has_column('students', 'academic_session_link');
    $hasCurrentSessionId = schema_has_column('students', 'current_session_id');

    $sessionColumnName = $hasAcademicSessionLink ? 'academic_session_link' : ($hasCurrentSessionId ? 'current_session_id' : null);

    if ($sessionColumnName) {
        $studentCount = QueryDB(
            'SELECT COUNT(*) FROM students WHERE (? = 0 OR ' . $sessionColumnName . ' = ?)',
            [$sessionId, $sessionId]
        )->fetchColumn();

        $recentAdmissions = QueryDB(
            'SELECT s.*, c.class_name, c.class_arm FROM students s LEFT JOIN classes c ON s.class_link = c.id OR s.current_class_id = c.id WHERE (? = 0 OR s.' . $sessionColumnName . ' = ?) ORDER BY s.created_at DESC, s.id DESC LIMIT 5',
            [$sessionId, $sessionId]
        )->fetchAll();

        $classDistribution = QueryDB(
            "SELECT c.id, c.class_name, c.class_arm, c.class_level, COUNT(s.id) AS student_count
             FROM classes c
             LEFT JOIN students s ON (s.class_link = c.id OR s.current_class_id = c.id) AND (? = 0 OR s." . $sessionColumnName . " = ?)
             GROUP BY c.id, c.class_name, c.class_arm, c.class_level
             ORDER BY c.class_level, c.class_name, c.class_arm",
            [$sessionId, $sessionId]
        )->fetchAll();
    } else {
        $studentCount = QueryDB('SELECT COUNT(*) FROM students')->fetchColumn();

        $recentAdmissions = QueryDB(
            'SELECT s.*, c.class_name, c.class_arm FROM students s LEFT JOIN classes c ON s.class_link = c.id OR s.current_class_id = c.id ORDER BY s.created_at DESC, s.id DESC LIMIT 5'
        )->fetchAll();

        $classDistribution = QueryDB(
            "SELECT c.id, c.class_name, c.class_arm, c.class_level, COUNT(s.id) AS student_count
             FROM classes c
             LEFT JOIN students s ON s.class_link = c.id OR s.current_class_id = c.id
             GROUP BY c.id, c.class_name, c.class_arm, c.class_level
             ORDER BY c.class_level, c.class_name, c.class_arm"
        )->fetchAll();
    }

    // Attendance table doesn't exist, so return 0
    $attendanceToday = 0;

    $announcements = schema_has_table('announcements')
        ? QueryDB(
            'SELECT COUNT(*) FROM announcements WHERE is_published = 1 AND (? = 0 OR academic_session_link IS NULL OR academic_session_link = ?)',
            [$sessionId, $sessionId]
        )->fetchColumn()
        : 0;

    return [
        'students' => (int) ($studentCount ?? 0),
        'teachers' => (int) total_teachers(),
        'classes' => (int) QueryDB('SELECT COUNT(*) FROM classes')->fetchColumn(),
        'subjects' => (int) total_courses(),
        'attendance_today' => (int) ($attendanceToday ?? 0),
        'announcements' => (int) ($announcements ?? 0),
        'payments' => $paymentSummary,
        'recent_admissions' => $recentAdmissions,
        'class_distribution' => $classDistribution,
        'recent_payments' => get_admin_payment_records($sessionId, $termId, 5),
    ];
}

function student_has_outstanding_fees(int $studentId, ?int $sessionId = null): bool
{
    $sessionId ??= get_current_academic_session_id() ?? 0;

    if (schema_has_table('student_fees') && schema_has_table('fee_structures')) {
        return (int) QueryDB(
            'SELECT COUNT(*)
             FROM student_fees sf
             JOIN fee_structures fs ON sf.fee_structure_id = fs.id
             WHERE sf.student_id = ? AND sf.balance > 0 AND (? = 0 OR fs.session_id = ?)',
            [$studentId, $sessionId, $sessionId]
        )->fetchColumn() > 0;
    }

    return (int) QueryDB(
        'SELECT COUNT(*) FROM student_fees WHERE student_link = ? AND balance > 0 AND (? = 0 OR academic_session_link = ?)',
        [$studentId, $sessionId, $sessionId]
    )->fetchColumn() > 0;
}

function student_result_access_allowed(int $studentId, ?int $sessionId = null): bool
{
    return !student_has_outstanding_fees($studentId, $sessionId);
}

function get_student_fee_records(int $studentId, ?int $sessionId = null): array
{
    $sessionId ??= get_current_academic_session_id() ?? 0;

    if (!schema_has_table('student_fees')) {
        return [];
    }

    if (schema_has_table('fee_structures')) {
        return QueryDB(
            "SELECT sf.*,
                    sf.fee_structure_id AS fee_structure_link,
                    fs.name AS fee_name,
                    fs.description AS fee_description,
                    fs.fee_type,
                    ac.session_name,
                    t.term_name,
                    t.term_name AS session_term,
                    CAST(t.term_number AS CHAR) AS term_code
             FROM student_fees sf
             LEFT JOIN fee_structures fs ON sf.fee_structure_id = fs.id
             LEFT JOIN academic_sessions ac ON fs.session_id = ac.id
             LEFT JOIN terms t ON fs.term_id = t.id
             WHERE sf.student_id = ? AND (? = 0 OR fs.session_id = ?)
             ORDER BY COALESCE(sf.updated_at, sf.created_at) DESC, sf.id DESC",
            [$studentId, $sessionId, $sessionId]
        )->fetchAll();
    }

    return QueryDB(
        "SELECT sf.*, f.fee_name, f.fee_description, ac.session_name, at.term_name, at.term_code
         FROM student_fees sf
         LEFT JOIN fees f ON sf.fee_structure_link = f.id
         LEFT JOIN academic_sessions ac ON sf.academic_session_link = ac.id
         LEFT JOIN academic_terms at ON sf.term_link = at.id
         WHERE sf.student_link = ? AND (? = 0 OR sf.academic_session_link = ?)
         ORDER BY COALESCE(sf.updated_at, sf.created_at) DESC, sf.id DESC",
        [$studentId, $sessionId, $sessionId]
    )->fetchAll();
}

function get_student_payment_history(int $studentId, ?int $sessionId = null): array
{
    $sessionId ??= get_current_academic_session_id() ?? 0;

    if (schema_has_table('payments') && schema_has_table('student_fees') && schema_has_table('fee_structures')) {
        return QueryDB(
            "SELECT p.*,
                    p.payment_reference AS receipt_number,
                    p.amount AS amount_paid,
                    p.description AS payment_description,
                    sf.amount_due,
                    sf.balance,
                    sf.status AS fee_status,
                    sf.due_date,
                    fs.id AS fee_structure_link,
                    fs.name AS fee_name,
                    fs.description AS fee_description,
                    fs.fee_type,
                    ac.session_name,
                    t.term_name,
                    t.term_name AS session_term,
                    CAST(t.term_number AS CHAR) AS term_code
             FROM payments p
             JOIN student_fees sf ON p.payable_type = 'student_fee' AND p.payable_id = sf.id
             LEFT JOIN fee_structures fs ON sf.fee_structure_id = fs.id
             LEFT JOIN academic_sessions ac ON fs.session_id = ac.id
             LEFT JOIN terms t ON fs.term_id = t.id
             WHERE sf.student_id = ? AND p.status = 'completed' AND (? = 0 OR fs.session_id = ?)
             ORDER BY COALESCE(p.payment_date, p.created_at) DESC, p.id DESC",
            [$studentId, $sessionId, $sessionId]
        )->fetchAll();
    }

    if (!schema_has_table('student_payments')) {
        return [];
    }

    $hasTermLink = schema_has_column('student_payments', 'term_link');

    $query = "SELECT sp.*, f.fee_name, f.fee_description, ac.session_name" .
             ($hasTermLink ? ", at.term_name, at.term_code" : ", 'N/A' AS term_name, 'N/A' AS term_code") . "
         FROM student_payments sp
         LEFT JOIN fees f ON sp.fee_structure_link = f.id
         LEFT JOIN academic_sessions ac ON sp.academic_session_link = ac.id
         " . ($hasTermLink ? "LEFT JOIN academic_terms at ON sp.term_link = at.id" : "") . "
         WHERE sp.student_link = ? AND (? = 0 OR sp.academic_session_link = ?)
         ORDER BY COALESCE(sp.payment_date, sp.created_at) DESC, sp.id DESC";

    return QueryDB($query, [$studentId, $sessionId, $sessionId])->fetchAll();
}

function get_student_attendance_history(int $studentId, ?int $sessionId = null, ?int $termId = null): array
{
    $sessionId ??= 0;
    $termId ??= 0;

    if (schema_has_table('student_attendance')) {
        $hasClassId = schema_has_column('student_attendance', 'class_id');
        $hasSchoolClassId = schema_has_column('student_attendance', 'school_class_id');
        $hasSessionId = schema_has_column('student_attendance', 'session_id');
        $hasTermId = schema_has_column('student_attendance', 'term_id');

        $classJoin = $hasClassId ? "LEFT JOIN classes c ON a.class_id = c.id" : "";
        $schoolClassJoin = $hasSchoolClassId ? "LEFT JOIN school_classes sc ON a.school_class_id = sc.id" : "";
        $classNameSelect = ($hasClassId && $hasSchoolClassId)
            ? "COALESCE(c.class_name, sc.class_name) AS class_name, COALESCE(c.class_arm, sc.section) AS class_arm"
            : ($hasSchoolClassId ? "sc.class_name AS class_name, sc.section AS class_arm" : "NULL AS class_name, NULL AS class_arm");

        $sessionJoin = $hasSessionId ? "LEFT JOIN academic_sessions sess ON a.session_id = sess.id" : "";
        $termJoin = $hasTermId ? "LEFT JOIN terms t ON a.term_id = t.id" : "";
        $sessionTermSelect = ($hasSessionId ? "sess.session_name" : "a.academic_year AS session_name") . ", " . ($hasTermId ? "t.term_name" : "a.term AS term_name");

        $whereClause = "a.student_id = ?";
        $params = [$studentId];

        if ($hasSessionId) {
            $whereClause .= " AND (? = 0 OR a.session_id IS NULL OR a.session_id = ?)";
            $params[] = $sessionId;
            $params[] = $sessionId;
        }

        if ($hasTermId) {
            $whereClause .= " AND (? = 0 OR a.term_id IS NULL OR a.term_id = ?)";
            $params[] = $termId;
            $params[] = $termId;
        }

        return QueryDB(
            "SELECT a.*,
                    {$classNameSelect},
                    sub.subject_name,
                    COALESCE(a.remarks, a.reason) AS remarks,
                    COALESCE(a.date, a.attendance_date) AS date,
                    {$sessionTermSelect}
             FROM student_attendance a
             {$classJoin}
             {$schoolClassJoin}
             LEFT JOIN subjects sub ON a.subject_id = sub.id
             {$sessionJoin}
             {$termJoin}
             WHERE {$whereClause}
             ORDER BY COALESCE(a.date, a.attendance_date) DESC, a.id DESC",
            $params
        )->fetchAll();
    }

    if (!schema_has_table('attendance')) {
        return [];
    }

    return QueryDB(
        "SELECT a.*, c.class_name, c.class_arm, s.subject_name
         FROM attendance a
         LEFT JOIN classes c ON a.class_link = c.id
         LEFT JOIN subjects s ON a.subject_link = s.id
         WHERE a.student_link = ? AND (? = 0 OR a.academic_session_link IS NULL OR a.academic_session_link = ?)
         ORDER BY a.date DESC, a.id DESC",
        [$studentId, $sessionId, $sessionId]
    )->fetchAll();
}

function get_student_results(int $studentId, ?int $sessionId = null, ?string $term = null): array
{
    $sessionId ??= 0;
    $term = $term !== null && $term !== '' ? (string) $term : null;

    if (schema_has_table('student_grades')) {
        $sessionName = $sessionId > 0 && schema_has_table('academic_sessions')
            ? (string) (QueryDB('SELECT session_name FROM academic_sessions WHERE id = ? LIMIT 1', [$sessionId])->fetchColumn() ?: '')
            : '';
        $normalizedTerm = $term !== null ? normalize_term_code($term) : null;
        $termName = match ($normalizedTerm) {
            '1' => 'first',
            '2' => 'second',
            '3' => 'third',
            default => $term,
        };

        $hasClassId = schema_has_column('student_grades', 'class_id');
        $hasSchoolClassId = schema_has_column('student_grades', 'school_class_id');

        $classJoin = $hasClassId ? "LEFT JOIN classes c ON g.class_id = c.id" : "";
        $schoolClassJoin = $hasSchoolClassId ? "LEFT JOIN school_classes sc ON g.school_class_id = sc.id" : "";
        $classNameSelect = ($hasClassId && $hasSchoolClassId)
            ? "COALESCE(c.class_name, sc.class_name) AS class_name, COALESCE(c.class_arm, sc.section) AS class_arm"
            : ($hasSchoolClassId ? "sc.class_name AS class_name, sc.section AS class_arm" : "NULL AS class_name, NULL AS class_arm");

        return QueryDB(
            "SELECT g.*,
                    sub.subject_name,
                    sub.subject_code,
                    {$classNameSelect},
                    COALESCE(sess.session_name, g.academic_year) AS session_name,
                    NULL AS term_code,
                    COALESCE(t.term_name, g.term) AS term_name
             FROM student_grades g
             LEFT JOIN subjects sub ON g.subject_id = sub.id
             {$classJoin}
             {$schoolClassJoin}
             LEFT JOIN academic_sessions sess ON g.academic_year = sess.session_name
             LEFT JOIN terms t ON (
                 (g.term = 'first' AND t.term_number = 1) OR
                 (g.term = 'second' AND t.term_number = 2) OR
                 (g.term = 'third' AND t.term_number = 3) OR
                 (g.term = '1' AND t.term_number = 1) OR
                 (g.term = '2' AND t.term_number = 2) OR
                 (g.term = '3' AND t.term_number = 3)
             )
             WHERE g.student_id = ?
               AND (? = '' OR g.academic_year = ?)
               AND (? IS NULL OR g.term = ? OR g.term = ?)
             ORDER BY COALESCE(sess.session_name, g.academic_year) DESC, g.term DESC, sub.subject_name ASC, g.assessment_type ASC, g.assessment_date DESC",
            [$studentId, $sessionName, $sessionName, $termName, $termName, $term]
        )->fetchAll();
    }

    if (!schema_has_table('grades')) {
        return [];
    }

    return QueryDB(
        "SELECT g.*, sub.subject_name, sub.subject_code, c.class_name, c.class_arm, ac.session_name, at.term_name, at.term_code
         FROM grades g
         LEFT JOIN subjects sub ON g.subject_link = sub.id
         LEFT JOIN classes c ON g.class_link = c.id
         LEFT JOIN academic_sessions ac ON g.academic_session_link = ac.id
         LEFT JOIN academic_terms at ON g.term_link = at.id
         WHERE g.student_link = ?
           AND (? = 0 OR g.academic_session_link = ?)
           AND (? IS NULL OR g.term = ?)
         ORDER BY sub.subject_name, g.term, g.exam_type",
        [$studentId, $sessionId, $sessionId, $term, $term]
    )->fetchAll();
}

function save_bulk_attendance_records(
    int $classId,
    ?int $subjectId,
    ?int $sessionId,
    string $date,
    array $records,
    ?int $markedByUserId = null,
    ?int $termId = null
): int {
    global $pdo;

    $sessionId ??= get_current_academic_session_id();
    $saved = 0;

    $pdo->beginTransaction();

    try {
        if (schema_has_table('student_attendance')) {
            $currentTerm = null;
            if ($termId && $termId > 0 && schema_has_table('academic_terms')) {
                $currentTerm = QueryDB('SELECT * FROM academic_terms WHERE id = ? LIMIT 1', [$termId])->fetch(PDO::FETCH_ASSOC) ?: null;
            }
            if (!$currentTerm) {
                $currentTerm = get_current_academic_term($sessionId);
            }

            $termCode = normalize_term_code($currentTerm['term_code'] ?? ($currentTerm['term_name'] ?? ''));
            $termName = match ($termCode) {
                '1' => 'first',
                '2' => 'second',
                '3' => 'third',
                default => null,
            };
            $academicYear = $sessionId && schema_has_table('academic_sessions')
                ? (string) (QueryDB('SELECT session_name FROM academic_sessions WHERE id = ? LIMIT 1', [$sessionId])->fetchColumn() ?: date('Y'))
                : date('Y');

            foreach ($records as $studentId => $payload) {
                $status = validate((string) ($payload['status'] ?? ''));
                $remarks = validate((string) ($payload['remarks'] ?? ''));

                if ($status === '') {
                    continue;
                }

                $existing = QueryDB(
                    'SELECT id FROM student_attendance WHERE student_id = ? AND class_id = ? AND COALESCE(date, attendance_date) = ? AND ((subject_id IS NULL AND ? IS NULL) OR subject_id = ?) AND ((session_id IS NULL AND ? IS NULL) OR session_id = ?) LIMIT 1',
                    [$studentId, $classId, $date, $subjectId, $subjectId, $sessionId, $sessionId]
                )->fetch(PDO::FETCH_ASSOC);

                if ($existing) {
                    QueryDB(
                        'UPDATE student_attendance
                         SET status = ?, remarks = ?, reason = ?, subject_id = ?, session_id = ?, term_id = ?, marked_by = ?, attendance_date = ?, date = ?, term = ?, academic_year = ?, updated_at = NOW()
                         WHERE id = ?',
                        [
                            $status,
                            $remarks !== '' ? $remarks : null,
                            $remarks !== '' ? $remarks : null,
                            $subjectId,
                            $sessionId,
                            $currentTerm['id'] ?? null,
                            $markedByUserId,
                            $date,
                            $date,
                            $termName,
                            $academicYear,
                            $existing['id'],
                        ]
                    );
                } else {
                    QueryDB(
                        'INSERT INTO student_attendance (student_id, school_class_id, subject_id, attendance_date, attendance_type, status, reason, marked_by, remarks, academic_year, term, class_id, session_id, term_id, date, created_at, updated_at)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
                        [
                            $studentId,
                            $classId,
                            $subjectId,
                            $date,
                            'daily',
                            $status,
                            $remarks !== '' ? $remarks : null,
                            $markedByUserId,
                            $remarks !== '' ? $remarks : null,
                            $academicYear,
                            $termName,
                            $classId,
                            $sessionId,
                            $currentTerm['id'] ?? null,
                            $date,
                        ]
                    );
                }

                $saved++;
            }

            $pdo->commit();

            return $saved;
        }

        foreach ($records as $studentId => $payload) {
            $status = validate((string) ($payload['status'] ?? ''));
            $remarks = validate((string) ($payload['remarks'] ?? ''));

            if ($status === '') {
                continue;
            }

            $existing = QueryDB(
                'SELECT id FROM attendance WHERE student_link = ? AND class_link = ? AND date = ? AND ((subject_link IS NULL AND ? IS NULL) OR subject_link = ?) AND ((academic_session_link IS NULL AND ? IS NULL) OR academic_session_link = ?) LIMIT 1',
                [$studentId, $classId, $date, $subjectId, $subjectId, $sessionId, $sessionId]
            )->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                QueryDB(
                    'UPDATE attendance SET status = ?, remarks = ?, subject_link = ?, academic_session_link = ?, marked_by_user_link = ? WHERE id = ?',
                    [$status, $remarks !== '' ? $remarks : null, $subjectId, $sessionId, $markedByUserId, $existing['id']]
                );
            } else {
                QueryDB(
                    'INSERT INTO attendance (student_link, class_link, subject_link, academic_session_link, date, status, remarks, marked_by_user_link, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())',
                    [$studentId, $classId, $subjectId, $sessionId, $date, $status, $remarks !== '' ? $remarks : null, $markedByUserId]
                );
            }

            $saved++;
        }

        $pdo->commit();

        return $saved;
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function save_bulk_grade_records(
    int $classId,
    int $subjectId,
    int $sessionId,
    string $term,
    string $examType,
    array $scores,
    array $remarksByStudent = []
): int {
    global $pdo;

    $saved = 0;
    $pdo->beginTransaction();

    try {
        if (schema_has_table('student_grades')) {
            $normalizedTerm = normalize_term_code($term);
            $termName = match ($normalizedTerm) {
                '1' => 'first',
                '2' => 'second',
                '3' => 'third',
                default => 'first',
            };
            $sessionName = $sessionId && schema_has_table('academic_sessions')
                ? (string) (QueryDB('SELECT session_name FROM academic_sessions WHERE id = ? LIMIT 1', [$sessionId])->fetchColumn() ?: date('Y'))
                : date('Y');
            $assessmentType = match (strtolower($examType)) {
                'assignment' => 'assignment',
                'test' => 'test',
                'midterm', 'final' => 'exam',
                default => 'exam',
            };
            $assessmentName = ucwords(str_replace('_', ' ', strtolower($examType)));

            foreach ($scores as $studentId => $score) {
                if ($score === '' || $score === null) {
                    continue;
                }

                // Check if student has dropped this subject
                if (schema_has_table('student_subjects')) {
                    $isDropped = (int) QueryDB(
                        "SELECT COUNT(*) FROM student_subjects WHERE student_id = ? AND subject_id = ? AND status = 'dropped' AND (? = 0 OR session_id = ? OR session_id IS NULL)",
                        [$studentId, $subjectId, $sessionId, $sessionId]
                    )->fetchColumn() > 0;

                    if ($isDropped) {
                        continue;
                    }
                }

                $numericScore = (float) $score;
                $grade = nigerian_grade_from_score($numericScore, true);
                $remarks = validate((string) ($remarksByStudent[$studentId] ?? ''));

                $existing = QueryDB(
                    'SELECT id FROM student_grades WHERE student_id = ? AND subject_id = ? AND school_class_id = ? AND academic_year = ? AND term = ? AND assessment_type = ? AND assessment_name = ? LIMIT 1',
                    [$studentId, $subjectId, $classId, $sessionName, $termName, $assessmentType, $assessmentName]
                )->fetch(PDO::FETCH_ASSOC);

                if ($existing) {
                    QueryDB(
                        'UPDATE student_grades
                         SET score = ?, max_score = 100, percentage = ?, letter_grade = ?, comments = ?, assessment_date = ?, updated_at = NOW()
                         WHERE id = ?',
                        [$numericScore, $numericScore, $grade, $remarks !== '' ? $remarks : null, date('Y-m-d'), $existing['id']]
                    );
                } else {
                    QueryDB(
                        'INSERT INTO student_grades (student_id, subject_id, school_class_id, academic_year, term, assessment_type, assessment_name, score, max_score, percentage, letter_grade, comments, assessment_date, status, is_final, created_at, updated_at)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 100, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
                        [
                            $studentId,
                            $subjectId,
                            $classId,
                            $sessionName,
                            $termName,
                            $assessmentType,
                            $assessmentName,
                            $numericScore,
                            $numericScore,
                            $grade,
                            $remarks !== '' ? $remarks : null,
                            date('Y-m-d'),
                            'published',
                            strtolower($examType) === 'final' ? 1 : 0,
                        ]
                    );
                }

                $saved++;
            }

            $pdo->commit();

            return $saved;
        }

        foreach ($scores as $studentId => $score) {
            if ($score === '' || $score === null) {
                continue;
            }

            $numericScore = (float) $score;
            $grade = nigerian_grade_from_score($numericScore);
            $remarks = validate((string) ($remarksByStudent[$studentId] ?? ''));

            $existing = QueryDB(
                'SELECT id FROM grades WHERE student_link = ? AND subject_link = ? AND class_link = ? AND academic_session_link = ? AND term = ? AND exam_type = ? LIMIT 1',
                [$studentId, $subjectId, $classId, $sessionId, $term, $examType]
            )->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                QueryDB(
                    'UPDATE grades SET score = ?, grade = ?, remarks = ? WHERE id = ?',
                    [$numericScore, $grade, $remarks !== '' ? $remarks : null, $existing['id']]
                );
            } else {
                QueryDB(
                    'INSERT INTO grades (student_link, subject_link, class_link, term, exam_type, score, max_score, grade, remarks, academic_session_link, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, 100, ?, ?, ?, NOW())',
                    [$studentId, $subjectId, $classId, $term, $examType, $numericScore, $grade, $remarks !== '' ? $remarks : null, $sessionId]
                );
            }

            $saved++;
        }

        $pdo->commit();

        return $saved;
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function get_announcements_for_audience(string $audience = 'all', ?int $sessionId = null): array
{
    if (!schema_has_table('announcements')) {
        return [];
    }

    $sessionId ??= get_current_academic_session_id() ?? 0;

    return QueryDB(
        "SELECT * FROM announcements
         WHERE is_published = 1
           AND audience IN ('all', ?)
           AND (? = 0 OR academic_session_link IS NULL OR academic_session_link = ?)
         ORDER BY COALESCE(published_at, created_at) DESC, id DESC",
        [$audience, $sessionId, $sessionId]
    )->fetchAll();
}

function create_announcement(string $title, string $body, string $audience = 'all', ?int $sessionId = null, ?int $userId = null): bool
{
    if (!schema_has_table('announcements')) {
        return false;
    }

    QueryDB(
        'INSERT INTO announcements (title, body, audience, academic_session_link, created_by_user_link, is_published, published_at, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, 1, NOW(), NOW(), NOW())',
        [$title, $body, $audience, $sessionId, $userId]
    );

    return true;
}

function split_student_full_name(string $fullName): array
{
    $clean = trim(preg_replace('/\s+/', ' ', $fullName));
    if ($clean === '') {
        return ['last_name' => '', 'first_name' => '', 'other_names' => ''];
    }

    $parts = explode(' ', $clean);
    $count = count($parts);

    if ($count === 1) {
        return [
            'last_name' => $parts[0],
            'first_name' => $parts[0],
            'other_names' => '',
        ];
    }

    if ($count === 2) {
        return [
            'last_name' => $parts[0],   // Surname
            'first_name' => $parts[1],  // Firstname
            'other_names' => '',
        ];
    }

    return [
        'last_name' => $parts[0],        // Surname
        'first_name' => $parts[1],       // Firstname
        'other_names' => implode(' ', array_slice($parts, 2)), // Other names
    ];
}

function student_email_exists(string $email): bool
{
    $email = strtolower(trim($email));

    $studentCount = (int) QueryDB(
        'SELECT COUNT(*) FROM students WHERE LOWER(email) = ?',
        [$email]
    )->fetchColumn();

    if ($studentCount > 0) {
        return true;
    }

    if (schema_has_table('users')) {
        $userCount = (int) QueryDB(
            'SELECT COUNT(*) FROM users WHERE LOWER(email) = ?',
            [$email]
        )->fetchColumn();

        if ($userCount > 0) {
            return true;
        }
    }

    return false;
}

function generate_student_school_email(string $firstName, string $lastName): string
{
    $domain = 'foluinternationalschools.com.ng';

    $cleanFirst = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $firstName));
    $cleanLast = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $lastName));

    if ($cleanFirst !== '' && $cleanLast !== '') {
        $basePrefix = "{$cleanFirst}.{$cleanLast}";
    } elseif ($cleanLast !== '') {
        $basePrefix = $cleanLast;
    } elseif ($cleanFirst !== '') {
        $basePrefix = $cleanFirst;
    } else {
        $basePrefix = 'student';
    }

    $email = "{$basePrefix}@{$domain}";

    $counter = 1;
    while (student_email_exists($email)) {
        $email = "{$basePrefix}{$counter}@{$domain}";
        $counter++;
    }

    return $email;
}

function find_student_by_full_name(string $fullName): ?array
{
    $fullName = trim(preg_replace('/\s+/', ' ', $fullName));
    if ($fullName === '') {
        return null;
    }

    $nameParts = explode(' ', $fullName);
    $lastName = $nameParts[0] ?? '';
    $firstName = $nameParts[1] ?? '';

    // 1. Exact match on CONCAT(last_name, ' ', first_name, ' ', other_names)
    $student = QueryDB(
        "SELECT * FROM students
         WHERE LOWER(TRIM(CONCAT(last_name, ' ', first_name, ' ', COALESCE(other_names, '')))) = LOWER(?)
            OR LOWER(TRIM(CONCAT(last_name, ' ', first_name))) = LOWER(?)
            OR LOWER(TRIM(CONCAT(first_name, ' ', last_name, ' ', COALESCE(other_names, '')))) = LOWER(?)
            OR LOWER(TRIM(CONCAT(first_name, ' ', last_name))) = LOWER(?)
         LIMIT 1",
        [$fullName, $fullName, $fullName, $fullName]
    )->fetch(PDO::FETCH_ASSOC);

    if ($student) {
        return $student;
    }

    // 2. Match by surname and firstname explicitly
    if ($lastName !== '' && $firstName !== '') {
        $student = QueryDB(
            "SELECT * FROM students
             WHERE (LOWER(TRIM(last_name)) = LOWER(?) AND LOWER(TRIM(first_name)) = LOWER(?))
                OR (LOWER(TRIM(first_name)) = LOWER(?) AND LOWER(TRIM(last_name)) = LOWER(?))
             LIMIT 1",
            [$lastName, $firstName, $lastName, $firstName]
        )->fetch(PDO::FETCH_ASSOC);

        if ($student) {
            return $student;
        }
    }

    // 3. LIKE match
    $student = QueryDB(
        "SELECT * FROM students
         WHERE LOWER(CONCAT(last_name, ' ', first_name, ' ', COALESCE(other_names, ''))) LIKE LOWER(?)
            OR LOWER(CONCAT(first_name, ' ', last_name, ' ', COALESCE(other_names, ''))) LIKE LOWER(?)
         LIMIT 1",
        ['%' . $fullName . '%', '%' . $fullName . '%']
    )->fetch(PDO::FETCH_ASSOC);

    return $student ?: null;
}

if (!function_exists('admin_student_term_context')) {
    function admin_student_term_context(?int $sessionId = null): array
    {
        $academicTerm = function_exists('get_current_academic_term') ? get_current_academic_term($sessionId) : null;
        $academicTermId = $academicTerm ? (int) $academicTerm['id'] : null;
        $currentTermId = null;

        if (schema_has_table('terms')) {
            $currentTermId = QueryDB(
                'SELECT id FROM terms WHERE is_active = 1 ORDER BY term_number ASC, id DESC LIMIT 1'
            )->fetchColumn();

            if (!$currentTermId && $academicTerm) {
                $termCode = function_exists('normalize_term_code') ? normalize_term_code($academicTerm['term_code'] ?? ($academicTerm['term_name'] ?? '')) : null;

                if ($termCode !== null) {
                    $currentTermId = QueryDB(
                        'SELECT id FROM terms WHERE term_number = ? ORDER BY id DESC LIMIT 1',
                        [(int) $termCode]
                    )->fetchColumn();
                }
            }
        }

        return [
            'current_term_id' => $currentTermId ? (int) $currentTermId : null,
            'academic_term_id' => $academicTermId,
        ];
    }
}

if (!function_exists('admin_build_student_insert_data')) {
    function admin_build_student_insert_data(array $studentData): array
    {
        $studentColumns = schema_table_column_details('students');
        $insertData = [];

        $setValue = static function (string $column, $value) use (&$insertData, $studentColumns): void {
            if (!isset($studentColumns[$column]) || schema_column_is_generated('students', $column)) {
                return;
            }

            $insertData[$column] = $value;
        };

        $setValue('user_link', $studentData['user_link'] ?? null);
        $setValue('user_id', $studentData['user_link'] ?? null);
        $setValue('admission_no', $studentData['admission_no'] ?? null);
        $setValue('student_number', $studentData['student_number'] ?? null);
        $setValue('first_name', $studentData['first_name'] ?? '');
        $setValue('last_name', $studentData['last_name'] ?? '');
        $setValue('other_names', $studentData['other_names'] ?? '');
        $setValue('email', $studentData['email'] ?? '');
        $setValue('phone', $studentData['phone'] ?? null);
        $setValue('address', $studentData['address'] ?? null);
        $setValue('home_address', $studentData['address'] ?? null);
        $setValue('date_of_birth', !empty($studentData['date_of_birth']) ? $studentData['date_of_birth'] : date('Y-m-d'));
        $setValue('gender', !empty($studentData['gender']) ? $studentData['gender'] : 'male');
        $setValue('enrollment_date', !empty($studentData['enrollment_date']) ? $studentData['enrollment_date'] : date('Y-m-d'));
        $setValue('admission_date', !empty($studentData['enrollment_date']) ? $studentData['enrollment_date'] : date('Y-m-d'));
        $setValue('status', $studentData['status'] ?? 'active');
        $setValue('admission_status', $studentData['admission_status'] ?? 'admitted');
        $setValue('category', $studentData['category'] ?? 'NI');
        $setValue('passport', $studentData['passport'] ?? null);
        $setValue('state_of_origin', $studentData['state_of_origin'] ?? '');
        $setValue('lga', $studentData['lga'] ?? '');
        $setValue('student_type', $studentData['student_type'] ?? 'day');
        $setValue('department', $studentData['department'] ?? 'general');
        $setValue('blood_group', $studentData['blood_group'] ?? null);
        $setValue('genotype', $studentData['genotype'] ?? null);
        $setValue('current_class_id', $studentData['current_class_id'] ?? null);
        $setValue('class_link', $studentData['class_link'] ?? null);
        $setValue('class_id', $studentData['class_link'] ?? null);
        $setValue('current_session_id', $studentData['current_session_id'] ?? null);
        $setValue('academic_session_link', $studentData['academic_session_link'] ?? null);
        $setValue('academic_session_id', $studentData['academic_session_link'] ?? null);
        $setValue('current_term_id', $studentData['current_term_id'] ?? null);
        $setValue('term_link', $studentData['term_link'] ?? null);
        $setValue('created_at', $studentData['timestamp'] ?? date('Y-m-d H:i:s'));
        $setValue('updated_at', $studentData['timestamp'] ?? date('Y-m-d H:i:s'));

        return $insertData;
    }
}


