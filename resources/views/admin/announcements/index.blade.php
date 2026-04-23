<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

$message = '';
$error = '';
$currentSessionId = get_current_academic_session_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $title = validate($_POST['title'] ?? '');
        $body = validate($_POST['body'] ?? '');
        $audience = validate($_POST['audience'] ?? 'all');
        $sessionId = (int) ($_POST['academic_session_link'] ?? 0);

        if ($title === '' || $body === '') {
            throw new Exception('Title and announcement body are required.');
        }

        create_announcement(
            $title,
            $body,
            $audience,
            $sessionId > 0 ? $sessionId : null,
            isset($_SESSION['adid']) ? (int) $_SESSION['adid'] : null
        );

        $message = 'Announcement published successfully.';
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$sessions = QueryDB('SELECT * FROM academic_sessions ORDER BY start_date DESC, id DESC')->fetchAll();
$announcements = get_announcements_for_audience('all');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Announcements</title>
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
                        <h2 class="text-dark pb-2 fw-bold">Announcements</h2>
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
                                    <div class="card-title">Create Announcement</div>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <div class="form-group">
                                            <label for="title">Title</label>
                                            <input type="text" class="form-control" id="title" name="title" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="audience">Audience</label>
                                            <select class="form-control" id="audience" name="audience">
                                                <option value="all">Everyone</option>
                                                <option value="students">Students</option>
                                                <option value="teachers">Teachers</option>
                                                <option value="admins">Admins</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="academic_session_link">Session</label>
                                            <select class="form-control" id="academic_session_link" name="academic_session_link">
                                                <option value="">All Sessions</option>
                                                <?php foreach ($sessions as $session): ?>
                                                    <option value="<?php echo (int) $session['id']; ?>" <?php echo $currentSessionId === (int) $session['id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($session['session_name'] . ' - ' . session_term_label($session['session_term'])); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="body">Announcement</label>
                                            <textarea class="form-control" id="body" name="body" rows="6" required></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Publish</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Published Announcements</div>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($announcements)): ?>
                                        <div class="alert alert-info mb-0">No announcements have been published yet.</div>
                                    <?php else: ?>
                                        <?php foreach ($announcements as $announcement): ?>
                                            <div class="border rounded p-3 mb-3">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h5 class="mb-1"><?php echo htmlspecialchars($announcement['title']); ?></h5>
                                                        <small class="text-muted">
                                                            Audience: <?php echo htmlspecialchars(ucfirst($announcement['audience'])); ?>
                                                            <?php if (!empty($announcement['published_at'])): ?>
                                                                | Published: <?php echo htmlspecialchars($announcement['published_at']); ?>
                                                            <?php endif; ?>
                                                        </small>
                                                    </div>
                                                </div>
                                                <p class="mb-0 mt-2"><?php echo nl2br(htmlspecialchars($announcement['body'])); ?></p>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @include('admin.partials.footer')
</body>
</html>
