<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

$search = trim((string) ($_GET['search'] ?? ''));
$statusFilter = trim((string) ($_GET['status'] ?? ''));

$success = '';
$error = '';

if (isset($_SESSION['blog_success'])) {
    $success = (string) $_SESSION['blog_success'];
    unset($_SESSION['blog_success']);
}
if (isset($_SESSION['blog_error'])) {
    $error = (string) $_SESSION['blog_error'];
    unset($_SESSION['blog_error']);
}

// Handle Delete Post
if (isset($_POST['action']) && $_POST['action'] === 'delete_post') {
    $deleteId = (int) ($_POST['post_id'] ?? 0);
    if ($deleteId > 0) {
        try {
            QueryDB('DELETE FROM posts WHERE id = ?', [$deleteId]);
            $_SESSION['blog_success'] = 'Blog post deleted successfully.';
            header('Location: /admin/blog/list.php');
            exit;
        } catch (Throwable $e) {
            $_SESSION['blog_error'] = 'Failed to delete post: ' . $e->getMessage();
            header('Location: /admin/blog/list.php');
            exit;
        }
    }
}

// Handle Toggle Publish Status
if (isset($_POST['action']) && $_POST['action'] === 'toggle_publish') {
    $postId = (int) ($_POST['post_id'] ?? 0);
    $newPublished = (int) ($_POST['new_published'] ?? 0);
    if ($postId > 0) {
        try {
            QueryDB(
                'UPDATE posts SET is_published = ?, published_at = IF(? = 1 AND published_at IS NULL, NOW(), published_at), updated_at = NOW() WHERE id = ?',
                [$newPublished, $newPublished, $postId]
            );
            $_SESSION['blog_success'] = 'Blog post publish status updated successfully.';
            header('Location: /admin/blog/list.php');
            exit;
        } catch (Throwable $e) {
            $_SESSION['blog_error'] = 'Failed to update publish status: ' . $e->getMessage();
            header('Location: /admin/blog/list.php');
            exit;
        }
    }
}

// Build Query
$searchParam = '%' . $search . '%';
$whereClause = "WHERE (1=1)";
$params = [];

if ($search !== '') {
    $whereClause .= " AND (p.title LIKE ? OR p.content LIKE ? OR p.excerpt LIKE ?)";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if ($statusFilter === '1') {
    $whereClause .= " AND p.is_published = 1";
} elseif ($statusFilter === '0') {
    $whereClause .= " AND p.is_published = 0";
}

$posts = QueryDB(
    "SELECT p.*, u.name AS author_name
     FROM posts p
     LEFT JOIN users u ON p.author_id = u.id
     {$whereClause}
     ORDER BY p.created_at DESC",
    $params
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Blog Posts Management - Admin Panel</title>
    @include('admin.partials.links')
</head>
<body>
    <div class="wrapper">
        @include('admin.partials.sidebar')

        <div class="main-panel">
            @include('admin.partials.header')
            <div class="container">
                <div class="page-inner">
                    <div class="d-flex align-items-left flex-column flex-md-row pt-2 pb-4">
                        <div>
                            <h2 class="text-dark pb-1 fw-bold"><i class="fas fa-newspaper me-2 text-primary"></i>Website Blog & News</h2>
                            <p class="text-muted mb-0">Publish stories, school news, and campus announcements to the website blog.</p>
                        </div>
                        <div class="ms-md-auto py-2 py-md-0 d-flex gap-2">
                            <a href="{{ url('/admin/blog/add.php') }}" class="btn btn-primary fw-bold">
                                <i class="fas fa-edit me-1"></i> Add New Blog Post
                            </a>
                            <a href="{{ url('/blog') }}" target="_blank" class="btn btn-outline-dark fw-bold">
                                <i class="fas fa-external-link-alt me-1"></i> View Live Blog
                            </a>
                        </div>
                    </div>

                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-1"></i> <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <?php if ($success !== ''): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-1"></i> <?php echo htmlspecialchars($success); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <form method="GET" class="row g-3 align-items-center">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Search Title or Keywords</label>
                                    <input type="text" name="search" class="form-control" placeholder="Search post title, excerpt or content..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Publish Status</label>
                                    <select name="status" class="form-select">
                                        <option value="">All Statuses</option>
                                        <option value="1" <?php echo $statusFilter === '1' ? 'selected' : ''; ?>>Published Only</option>
                                        <option value="0" <?php echo $statusFilter === '0' ? 'selected' : ''; ?>>Drafts Only</option>
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end" style="margin-top: 32px;">
                                    <button type="submit" class="btn btn-primary w-100 fw-bold me-2"><i class="fas fa-search me-1"></i> Filter</button>
                                    <a href="{{ url('/admin/blog/list.php') }}" class="btn btn-light border" title="Reset Filters"><i class="fas fa-undo"></i></a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0"><i class="fas fa-rss me-2"></i>All Blog Articles</h5>
                            <span class="badge bg-white text-primary fw-bold"><?php echo count($posts); ?> Total Posts</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 50px;">#</th>
                                            <th style="width: 80px;">Thumbnail</th>
                                            <th>Title & Excerpt</th>
                                            <th>Author</th>
                                            <th>Status</th>
                                            <th>Published Date</th>
                                            <th class="text-end" style="width: 200px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($posts)): ?>
                                            <tr>
                                                <td colspan="7" class="text-center py-4 text-muted">No blog posts found matching your criteria.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php $sn = 1; foreach ($posts as $post): ?>
                                                <tr>
                                                    <td><?php echo $sn++; ?></td>
                                                    <td>
                                                        <?php if ($post['image_path']): ?>
                                                            <img src="<?php echo asset($post['image_path']); ?>" alt="thumbnail" style="width: 60px; height: 42px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;">
                                                        <?php else: ?>
                                                            <div style="width: 60px; height: 42px; background: #e9ecef; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #adb5bd;">
                                                                <i class="fas fa-image"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="{{ url('/admin/blog/edit.php?id=' . $post['id']) }}" class="fw-bold text-primary text-decoration-none" style="font-size: 15px;">
                                                            <?php echo htmlspecialchars($post['title']); ?>
                                                        </a>
                                                        <div class="small text-muted font-monospace">/blog/<?php echo htmlspecialchars($post['slug']); ?></div>
                                                    </td>
                                                    <td><small class="fw-bold text-dark"><?php echo htmlspecialchars($post['author_name'] ?? 'Editorial Staff'); ?></small></td>
                                                    <td>
                                                        <?php if ((bool) $post['is_published']): ?>
                                                            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Published</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning text-dark"><i class="fas fa-file-alt me-1"></i>Draft</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <small>
                                                            <?php echo $post['published_at'] ? date('M d, Y H:i', strtotime($post['published_at'])) : 'Not Published'; ?>
                                                        </small>
                                                    </td>
                                                    <td class="text-end">
                                                        <a href="{{ url('/blog/' . $post['slug']) }}" target="_blank" class="btn btn-info btn-sm text-white" title="Preview Live Post">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ url('/admin/blog/edit.php?id=' . $post['id']) }}" class="btn btn-warning btn-sm fw-bold" title="Edit Post">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        
                                                        <form method="POST" class="d-inline" onsubmit="return confirm('Change publish status for this post?');">
                                                            <input type="hidden" name="action" value="toggle_publish">
                                                            <input type="hidden" name="post_id" value="<?php echo (int) $post['id']; ?>">
                                                            <input type="hidden" name="new_published" value="<?php echo $post['is_published'] ? 0 : 1; ?>">
                                                            <button type="submit" class="btn btn-outline-secondary btn-sm" title="Toggle Publish / Draft">
                                                                <i class="fas fa-paper-plane"></i>
                                                            </button>
                                                        </form>

                                                        <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this blog post?');">
                                                            <input type="hidden" name="action" value="delete_post">
                                                            <input type="hidden" name="post_id" value="<?php echo (int) $post['id']; ?>">
                                                            <button type="submit" class="btn btn-danger btn-sm" title="Delete Post">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @include('admin.partials.footer')
        </div>
    </div>
</body>
</html>
