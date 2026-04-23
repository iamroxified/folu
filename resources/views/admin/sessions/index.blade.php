<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

$message = '';
$error = '';
$selectedSessionId = (int) ($_REQUEST['session_id'] ?? (get_current_academic_session_id() ?? 0));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    try {
        if ($action === 'create_session') {
            $sessionName = validate($_POST['session_name'] ?? '');
            $startDate = validate($_POST['start_date'] ?? '');
            $endDate = validate($_POST['end_date'] ?? '');
            $setCurrent = isset($_POST['set_current']);

            if ($sessionName === '' || $startDate === '' || $endDate === '') {
                throw new Exception('Please fill in the session name and dates.');
            }

            $duplicate = (int) QueryDB(
                'SELECT COUNT(*) FROM academic_sessions WHERE session_name = ?',
                [$sessionName]
            )->fetchColumn();

            if ($duplicate > 0) {
                throw new Exception('That academic session already exists.');
            }

            QueryDB(
                'INSERT INTO academic_sessions (session_name, start_date, end_date, is_current, created_at) VALUES (?, ?, ?, 0, NOW())',
                [$sessionName, $startDate, $endDate]
            );

            $selectedSessionId = (int) $pdo->lastInsertId();

            if ($setCurrent) {
                set_current_academic_session($selectedSessionId);
            }

            $message = 'Academic session created successfully.';
        } elseif ($action === 'set_current_session') {
            $selectedSessionId = (int) ($_POST['session_id'] ?? 0);

            if ($selectedSessionId < 1) {
                throw new Exception('Please select a valid session.');
            }

            set_current_academic_session($selectedSessionId);
            $message = 'Active session updated successfully.';
        } elseif ($action === 'create_term') {
            $selectedSessionId = (int) ($_POST['session_id'] ?? 0);
            $termCode = normalize_term_code($_POST['term_code'] ?? null);
            $startDate = validate($_POST['start_date'] ?? '');
            $endDate = validate($_POST['end_date'] ?? '');
            $setCurrent = isset($_POST['set_current_term']);

            if ($selectedSessionId < 1 || $termCode === null || $startDate === '' || $endDate === '') {
                throw new Exception('Please choose a session, term, and dates.');
            }

            $duplicate = (int) QueryDB(
                'SELECT COUNT(*) FROM academic_terms WHERE academic_session_link = ? AND term_code = ?',
                [$selectedSessionId, $termCode]
            )->fetchColumn();

            if ($duplicate > 0) {
                throw new Exception('That term already exists for the selected session.');
            }

            QueryDB(
                'INSERT INTO academic_terms (academic_session_link, term_code, term_name, start_date, end_date, is_current, created_at) VALUES (?, ?, ?, ?, ?, 0, NOW())',
                [$selectedSessionId, $termCode, session_term_label($termCode), $startDate, $endDate]
            );

            $termId = (int) $pdo->lastInsertId();

            if ($setCurrent) {
                set_current_academic_term($termId);
            }

            $message = 'Academic term created successfully.';
        } elseif ($action === 'set_current_term') {
            $termId = (int) ($_POST['term_id'] ?? 0);

            if ($termId < 1) {
                throw new Exception('Please select a valid term.');
            }

            set_current_academic_term($termId);
            $message = 'Active term updated successfully.';
        } elseif ($action === 'assign_fees') {
            $selectedSessionId = (int) ($_POST['session_id'] ?? 0);
            $termId = (int) ($_POST['term_id'] ?? 0);
            $result = allocate_active_session_fees($selectedSessionId > 0 ? $selectedSessionId : null, $termId > 0 ? $termId : null);
            $message = 'Fees assigned successfully. New allocations: ' . $result['allocated'] . '. Existing allocations skipped: ' . $result['skipped'] . '.';
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$currentSession = get_current_academic_session();
$currentTerm = get_current_academic_term($currentSession['id'] ?? null);
$sessions = QueryDB('SELECT * FROM academic_sessions ORDER BY start_date DESC, id DESC')->fetchAll();
$selectedTerms = $selectedSessionId > 0 ? get_terms_for_session($selectedSessionId) : [];
$allTerms = QueryDB(
    'SELECT at.*, ac.session_name
     FROM academic_terms at
     JOIN academic_sessions ac ON at.academic_session_link = ac.id
     ORDER BY ac.start_date DESC, ac.id DESC, at.term_code ASC'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Academic Sessions & Terms</title>
    @include('admin.partials.links')
</head>
<body>
    <div class="wrapper">
        @include('admin.partials.sidebar')

        <div class="main-panel">
            @include('admin.partials.header')
            <div class="container">
                <div class="page-inner">
                    <div class="d-flex align-items-left flex-column flex-md-row">
                        <h2 class="text-dark pb-2 fw-bold">Academic Sessions & Terms</h2>
                    </div>

                    <?php if ($message !== ''): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>
                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Create Session</div>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="create_session">
                                        <div class="form-group">
                                            <label for="session_name">Session Name</label>
                                            <input type="text" class="form-control" id="session_name" name="session_name" placeholder="2026/2027" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="session_start_date">Start Date</label>
                                            <input type="date" class="form-control" id="session_start_date" name="start_date" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="session_end_date">End Date</label>
                                            <input type="date" class="form-control" id="session_end_date" name="end_date" required>
                                        </div>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="set_current" name="set_current">
                                            <label class="form-check-label" for="set_current">Set as active session immediately</label>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Save Session</button>
                                    </form>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <div class="card-title">Create Term</div>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="create_term">
                                        <div class="form-group">
                                            <label for="term_session_id">Session</label>
                                            <select class="form-control" id="term_session_id" name="session_id" required>
                                                <option value="">Select Session</option>
                                                <?php foreach ($sessions as $session): ?>
                                                    <option value="<?php echo (int) $session['id']; ?>" <?php echo $selectedSessionId === (int) $session['id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars((string) $session['session_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="term_code">Term</label>
                                            <select class="form-control" id="term_code" name="term_code" required>
                                                <option value="">Select Term</option>
                                                <option value="1">First Term</option>
                                                <option value="2">Second Term</option>
                                                <option value="3">Third Term</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="term_start_date">Start Date</label>
                                            <input type="date" class="form-control" id="term_start_date" name="start_date" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="term_end_date">End Date</label>
                                            <input type="date" class="form-control" id="term_end_date" name="end_date" required>
                                        </div>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="set_current_term" name="set_current_term">
                                            <label class="form-check-label" for="set_current_term">Set as active term immediately</label>
                                        </div>
                                        <button type="submit" class="btn btn-info">Save Term</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Current Academic Context</div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="border rounded p-3 h-100">
                                                <h5 class="mb-1"><?php echo htmlspecialchars(session_label($currentSession)); ?></h5>
                                                <div class="text-muted">Active Session</div>
                                                <?php if ($currentSession): ?>
                                                    <small class="d-block mt-2"><?php echo htmlspecialchars($currentSession['start_date']); ?> to <?php echo htmlspecialchars($currentSession['end_date']); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="border rounded p-3 h-100">
                                                <h5 class="mb-1"><?php echo htmlspecialchars(term_label($currentTerm)); ?></h5>
                                                <div class="text-muted">Active Term</div>
                                                <?php if ($currentTerm): ?>
                                                    <small class="d-block mt-2"><?php echo htmlspecialchars((string) ($currentTerm['start_date'] ?? '')); ?> to <?php echo htmlspecialchars((string) ($currentTerm['end_date'] ?? '')); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <div class="card-title">Sessions</div>
                                    <div class="card-category">Sessions now stand alone. Terms are created separately beneath each session.</div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Session</th>
                                                    <th>Dates</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($sessions as $session): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars((string) $session['session_name']); ?></td>
                                                        <td><?php echo htmlspecialchars((string) ($session['start_date'] . ' to ' . $session['end_date'])); ?></td>
                                                        <td>
                                                            <?php if ((int) ($session['is_current'] ?? 0) === 1): ?>
                                                                <span class="badge badge-success">Active Session</span>
                                                            <?php else: ?>
                                                                <span class="badge badge-secondary">Inactive</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex flex-wrap gap-2">
                                                                <form method="POST" class="me-2">
                                                                    <input type="hidden" name="action" value="set_current_session">
                                                                    <input type="hidden" name="session_id" value="<?php echo (int) $session['id']; ?>">
                                                                    <button type="submit" class="btn btn-sm btn-primary">Set Active</button>
                                                                </form>
                                                                <a href="{{ url('/admin/sessions.php?session_id=' . $session['id']) }}" class="btn btn-sm btn-outline-secondary">View Terms</a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <div class="card-title">Terms</div>
                                    <div class="card-category">Switch the active term and run session-term fee allocation from here.</div>
                                </div>
                                <div class="card-body">
                                    <?php if ($selectedSessionId > 0): ?>
                                        <div class="alert alert-light border">
                                            Selected session: <strong><?php echo htmlspecialchars((string) (QueryDB('SELECT session_name FROM academic_sessions WHERE id = ?', [$selectedSessionId])->fetchColumn() ?: 'Unknown Session')); ?></strong>
                                        </div>
                                    <?php endif; ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Session</th>
                                                    <th>Term</th>
                                                    <th>Dates</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($allTerms as $term): ?>
                                                    <?php if ($selectedSessionId > 0 && (int) $term['academic_session_link'] !== $selectedSessionId) { continue; } ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars((string) $term['session_name']); ?></td>
                                                        <td><?php echo htmlspecialchars(term_label($term)); ?></td>
                                                        <td><?php echo htmlspecialchars((string) (($term['start_date'] ?? '') . ' to ' . ($term['end_date'] ?? ''))); ?></td>
                                                        <td>
                                                            <?php if ((int) ($term['is_current'] ?? 0) === 1): ?>
                                                                <span class="badge badge-info">Active Term</span>
                                                            <?php else: ?>
                                                                <span class="badge badge-secondary">Inactive</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex flex-wrap gap-2">
                                                                <form method="POST" class="me-2">
                                                                    <input type="hidden" name="action" value="set_current_term">
                                                                    <input type="hidden" name="term_id" value="<?php echo (int) $term['id']; ?>">
                                                                    <button type="submit" class="btn btn-sm btn-info">Set Active</button>
                                                                </form>
                                                                <form method="POST">
                                                                    <input type="hidden" name="action" value="assign_fees">
                                                                    <input type="hidden" name="session_id" value="<?php echo (int) $term['academic_session_link']; ?>">
                                                                    <input type="hidden" name="term_id" value="<?php echo (int) $term['id']; ?>">
                                                                    <button type="submit" class="btn btn-sm btn-success">Assign Fees</button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @include('admin.partials.footer')
</body>
</html>
