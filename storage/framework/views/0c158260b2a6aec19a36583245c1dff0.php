<?php $__env->startSection('title', 'Announcements'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Create Announcement</div>
            <div class="card-body">
                <form action="<?php echo e(route('admin.announcements.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Audience</label>
                        <select name="audience" class="form-control" required>
                            <option value="all">All</option>
                            <option value="students">Students</option>
                            <option value="teachers">Teachers</option>
                            <option value="admins">Admins</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Session (Optional)</label>
                        <select name="academic_session_id" class="form-control">
                            <option value="">All Sessions</option>
                            <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($session->id); ?>"><?php echo e($session->session_name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Body</label>
                        <textarea name="body" class="form-control" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Publish</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Published Announcements</div>
            <div class="card-body">
                <?php if($announcements->isEmpty()): ?>
                    <p>No announcements found.</p>
                <?php else: ?>
                    <?php $__currentLoopData = $announcements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ann): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="border rounded p-3 mb-3">
                            <h5><?php echo e($ann->title); ?> <span class="badge bg-secondary"><?php echo e(ucfirst($ann->audience)); ?></span></h5>
                            <p class="mb-1"><?php echo e($ann->body); ?></p>
                            <small class="text-muted">Published: <?php echo e($ann->published_at->format('M d, Y h:i A')); ?></small>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php echo e($announcements->links()); ?>

                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\folu\resources\views/admin/announcements/index.blade.php ENDPATH**/ ?>