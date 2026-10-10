<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

$postId = (int) ($_GET['id'] ?? $_POST['post_id'] ?? 0);
if ($postId < 1) {
    header('Location: /admin/blog/list.php');
    exit;
}

$post = QueryDB('SELECT * FROM posts WHERE id = ? LIMIT 1', [$postId])->fetch(PDO::FETCH_ASSOC);
if (!$post) {
    header('Location: /admin/blog/list.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = validate($_POST['title'] ?? '');
    $slugInput = validate($_POST['slug'] ?? '');
    $excerpt = validate($_POST['excerpt'] ?? '');
    $content = $_POST['content'] ?? ''; // Keep HTML content intact
    $isPublished = isset($_POST['is_published']) ? 1 : 0;
    $publishedAt = validate($_POST['published_at'] ?? '');

    $slug = Str::slug($slugInput !== '' ? $slugInput : $title);

    // Handle Featured Image Upload
    $imagePath = validate($_POST['image_url'] ?? $post['image_path']);
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['featured_image'];
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowedExts, true)) {
            $uploadDir = public_path('storage/posts');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $filename = 'post_' . time() . '_' . Str::random(6) . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $filename)) {
                $imagePath = 'storage/posts/' . $filename;
            }
        }
    }

    if ($title === '' || trim(strip_tags($content)) === '') {
        $error = 'Please provide both a post title and post content.';
    } elseif ($slug === '') {
        $error = 'Please enter a valid title/slug for this article.';
    } else {
        try {
            // Check for duplicate slug on other posts
            $existing = QueryDB('SELECT COUNT(*) FROM posts WHERE slug = ? AND id != ?', [$slug, $postId])->fetchColumn();
            if ((int) $existing > 0) {
                $slug .= '-' . time();
            }

            $pubDate = $publishedAt !== '' ? date('Y-m-d H:i:s', strtotime($publishedAt)) : ($isPublished ? date('Y-m-d H:i:s') : null);

            QueryDB(
                'UPDATE posts SET title = ?, slug = ?, content = ?, excerpt = ?, image_path = ?, is_published = ?, published_at = ?, updated_at = NOW() WHERE id = ?',
                [
                    $title,
                    $slug,
                    $content,
                    $excerpt ?: null,
                    $imagePath ?: null,
                    $isPublished,
                    $pubDate,
                    $postId
                ]
            );

            $post = QueryDB('SELECT * FROM posts WHERE id = ? LIMIT 1', [$postId])->fetch(PDO::FETCH_ASSOC);
            $success = "Blog post updated successfully!";
        } catch (Throwable $e) {
            $error = 'Failed to update blog post: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Edit Blog Post - Admin Panel</title>
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
                            <h2 class="text-dark pb-1 fw-bold"><i class="fas fa-edit me-2 text-warning"></i>Edit Blog Post</h2>
                            <p class="text-muted mb-0">Update article content, featured image, or publication settings.</p>
                        </div>
                        <div class="ms-md-auto py-2 py-md-0 d-flex gap-2">
                            <a href="{{ url('/blog/' . $post['slug']) }}" target="_blank" class="btn btn-info text-white fw-bold">
                                <i class="fas fa-eye me-1"></i> Preview Live Post
                            </a>
                            <a href="{{ url('/admin/blog/list.php') }}" class="btn btn-secondary fw-bold">
                                <i class="fas fa-arrow-left me-1"></i> All Blog Posts
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

                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="post_id" value="<?php echo (int) $post['id']; ?>">

                        <div class="row">
                            <!-- Left: Post Content -->
                            <div class="col-md-8 mb-4">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-warning text-dark">
                                        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-file-alt me-2"></i>Edit Article Body</h5>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="mb-3">
                                            <label for="title" class="form-label fw-bold">Post Title <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control form-control-lg fw-bold" id="title" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? $post['title']); ?>" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="slug" class="form-label fw-bold">URL Slug</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light text-muted">/blog/</span>
                                                <input type="text" class="form-control font-monospace" id="slug" name="slug" value="<?php echo htmlspecialchars($_POST['slug'] ?? $post['slug']); ?>">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="excerpt" class="form-label fw-bold">Summary / Excerpt</label>
                                            <textarea class="form-control" id="excerpt" name="excerpt" rows="3"><?php echo htmlspecialchars($_POST['excerpt'] ?? $post['excerpt']); ?></textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label for="content" class="form-label fw-bold">Full Post Article Body <span class="text-danger">*</span></label>
                                            <textarea class="form-control" id="content" name="content" rows="12" required><?php echo htmlspecialchars($_POST['content'] ?? $post['content']); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Publishing Options & Featured Image -->
                            <div class="col-md-4 mb-4">
                                <div class="card shadow-sm mb-4">
                                    <div class="card-header bg-dark text-white">
                                        <h5 class="card-title mb-0"><i class="fas fa-cog me-2"></i>Publishing Settings</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php $isPub = isset($_POST['is_published']) ? true : (empty($_POST) ? (bool) $post['is_published'] : false); ?>
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="is_published" name="is_published" value="1" <?php echo $isPub ? 'checked' : ''; ?>>
                                            <label class="form-check-label fw-bold" for="is_published">Published on Website</label>
                                        </div>

                                        <div class="mb-3">
                                            <label for="published_at" class="form-label fw-bold">Publish Date & Time</label>
                                            <?php 
                                            $pDateVal = $_POST['published_at'] ?? ($post['published_at'] ? date('Y-m-d\TH:i', strtotime($post['published_at'])) : date('Y-m-d\TH:i'));
                                            ?>
                                            <input type="datetime-local" class="form-control" id="published_at" name="published_at" value="<?php echo htmlspecialchars($pDateVal); ?>">
                                        </div>

                                        <button type="submit" class="btn btn-warning w-100 btn-lg fw-bold">
                                            <i class="fas fa-save me-1"></i> Update Post
                                        </button>
                                    </div>
                                </div>

                                <div class="card shadow-sm">
                                    <div class="card-header bg-secondary text-white">
                                        <h5 class="card-title mb-0"><i class="fas fa-image me-2"></i>Featured Cover Image</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if ($post['image_path']): ?>
                                            <div class="mb-3 text-center">
                                                <img src="<?php echo asset($post['image_path']); ?>" alt="Cover" class="img-fluid rounded border" style="max-height: 140px;">
                                            </div>
                                        <?php endif; ?>

                                        <div class="mb-3">
                                            <label for="featured_image" class="form-label fw-bold">Upload New Image</label>
                                            <input type="file" class="form-control" id="featured_image" name="featured_image" accept="image/*">
                                        </div>

                                        <div class="text-center my-2 text-muted fw-bold">OR</div>

                                        <div class="mb-3">
                                            <label for="image_url" class="form-label fw-bold">Image URL / Path</label>
                                            <input type="text" class="form-control" id="image_url" name="image_url" value="<?php echo htmlspecialchars($_POST['image_url'] ?? $post['image_path']); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @include('admin.partials.footer')
        </div>
    </div>
</body>
</html>
