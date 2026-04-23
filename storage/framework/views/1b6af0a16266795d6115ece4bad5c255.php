<?php $__env->startSection('title', 'Class Assignments'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">Class & Subject Assignments</div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>Note:</strong> The assignments module is currently being ported from the legacy interface.
                    Please check back later or use the legacy interface.
                </div>
                <a href="<?php echo e(route('admin.legacy', ['path' => 'classes/assign_students.php'])); ?>" class="btn btn-primary me-2">Assign Students to Classes</a>
                <a href="<?php echo e(route('admin.legacy', ['path' => 'teachers/assign_subjects.php'])); ?>" class="btn btn-primary">Assign Subjects to Teachers</a>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\folu\resources\views/admin/classes/assignments.blade.php ENDPATH**/ ?>