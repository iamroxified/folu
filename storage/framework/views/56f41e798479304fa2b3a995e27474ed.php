<?php $__env->startSection('title', 'Classes'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Classes</h2>
    <a href="<?php echo e(route('admin.classes.create')); ?>" class="btn btn-primary">Add Class</a>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>Grade Level</th>
            <th>Section</th>
            <th>Academic Year</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td><?php echo e($class->class_name); ?></td>
            <td><?php echo e($class->grade_level); ?></td>
            <td><?php echo e($class->section ?? 'N/A'); ?></td>
            <td><?php echo e($class->academic_year); ?></td>
            <td><?php echo e(ucfirst($class->status)); ?></td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\folu\resources\views/admin/classes/index.blade.php ENDPATH**/ ?>