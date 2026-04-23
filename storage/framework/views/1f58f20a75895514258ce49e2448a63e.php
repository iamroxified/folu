<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Total Students</h5>
                <h2><?php echo e($stats['total_students']); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Total Staff</h5>
                <h2><?php echo e($stats['total_staff']); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Active Sessions</h5>
                <h2><?php echo e($stats['active_sessions']); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Pending Admissions</h5>
                <h2><?php echo e($stats['pending_admissions']); ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                Quick Actions
            </div>
            <div class="card-body">
                <a href="<?php echo e(route('admin.students.create')); ?>" class="btn btn-primary">Add Student</a>
                <a href="<?php echo e(route('admin.fees.create')); ?>" class="btn btn-success">Create Fee</a>
                <a href="<?php echo e(route('admin.sessions.create')); ?>" class="btn btn-info">Add Session</a>
                <a href="<?php echo e(route('admin.staff.create')); ?>" class="btn btn-warning">Add Staff</a>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\folu\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>