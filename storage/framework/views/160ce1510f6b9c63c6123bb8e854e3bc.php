<?php $__env->startSection('title', 'Add Class'); ?>

<?php $__env->startSection('content'); ?>
<h2>Add Class</h2>

<form action="<?php echo e(route('admin.classes.store')); ?>" method="POST">
    <?php echo csrf_field(); ?>
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="class_name" class="form-label">Class Name</label>
                <input type="text" class="form-control" id="class_name" name="class_name" required>
            </div>
            <div class="mb-3">
                <label for="grade_level" class="form-label">Grade Level</label>
                <input type="text" class="form-control" id="grade_level" name="grade_level" required>
            </div>
            <div class="mb-3">
                <label for="section" class="form-label">Section</label>
                <input type="text" class="form-control" id="section" name="section">
            </div>
            <div class="mb-3">
                <label for="academic_year" class="form-label">Academic Year</label>
                <input type="text" class="form-control" id="academic_year" name="academic_year" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="class_teacher_id" class="form-label">Class Teacher</label>
                <select class="form-control" id="class_teacher_id" name="class_teacher_id">
                    <option value="">No teacher assigned</option>
                    <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($teacher->id); ?>"><?php echo e($teacher->name); ?> (<?php echo e($teacher->email); ?>)</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="classroom_location" class="form-label">Classroom Location</label>
                <input type="text" class="form-control" id="classroom_location" name="classroom_location">
            </div>
            <div class="mb-3">
                <label for="max_capacity" class="form-label">Max Capacity</label>
                <input type="number" class="form-control" id="max_capacity" name="max_capacity" value="30" min="1">
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-control" id="status" name="status">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="archived">Archived</option>
                </select>
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Create Class</button>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\folu\resources\views/admin/classes/create.blade.php ENDPATH**/ ?>