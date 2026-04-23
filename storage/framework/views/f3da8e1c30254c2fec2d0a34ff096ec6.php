<?php $__env->startSection('title', 'Students'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Students</h2>
    <div>
        <a href="<?php echo e(route('admin.students.export')); ?>" class="btn btn-secondary me-2">Export CSV</a>
        <form action="<?php echo e(route('admin.students.import')); ?>" method="POST" enctype="multipart/form-data" class="d-inline-block me-2">
            <?php echo csrf_field(); ?>
            <div class="input-group">
                <input type="file" name="csv_file" class="form-control form-control-sm" accept=".csv" required>
                <button type="submit" class="btn btn-sm btn-success">Import CSV</button>
            </div>
        </form>
        <a href="<?php echo e(route('admin.students.create')); ?>" class="btn btn-primary">Add Student</a>
    </div>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Student Number</th>
            <th>Name</th>
            <th>Email</th>
            <th>Class</th>
            <th>Admission Status</th>
            <th>Category</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td><?php echo e($student->student_number); ?></td>
            <td><?php echo e($student->first_name); ?> <?php echo e($student->last_name); ?></td>
            <td><?php echo e($student->email); ?></td>
            <td><?php echo e($student->currentClass ? $student->currentClass->class_name : 'N/A'); ?></td>
            <td>
                <span class="badge bg-<?php echo e($student->admission_status == 'admitted' ? 'success' : ($student->admission_status == 'pending' ? 'warning' : 'danger')); ?>">
                    <?php echo e(ucfirst($student->admission_status)); ?>

                </span>
            </td>
            <td><?php echo e($student->category); ?></td>
            <td>
                <a href="<?php echo e(route('admin.students.show', $student)); ?>" class="btn btn-sm btn-info">View</a>
                <a href="<?php echo e(route('admin.students.edit', $student)); ?>" class="btn btn-sm btn-warning">Edit</a>
                <form action="<?php echo e(route('admin.students.destroy', $student)); ?>" method="POST" class="d-inline">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>

<?php echo e($students->links()); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\folu\resources\views/admin/students/index.blade.php ENDPATH**/ ?>