<?php $__env->startSection('title', 'Student Promotions'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">Bulk Student Promotions</div>
            <div class="card-body">
                <p class="text-muted">Use this module at the end of a term or session to automatically advance students to the next term/class. New Intake (NI) students will be automatically re-categorized as Old Students (OS) upon promotion.</p>
                <form action="<?php echo e(route('admin.students.promotions.process')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5>From:</h5>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Select Current Class</label>
                            <select name="from_class_id" class="form-control" required>
                                <option value="">-- Select Class --</option>
                                <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($class->id); ?>"><?php echo e($class->class_name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>

                    <hr>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5>Promote To:</h5>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Select Target Class</label>
                            <select name="to_class_id" class="form-control" required>
                                <option value="">-- Select Class --</option>
                                <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($class->id); ?>"><?php echo e($class->class_name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Select Target Session</label>
                            <select name="to_session_id" class="form-control" required>
                                <option value="">-- Select Session --</option>
                                <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($session->id); ?>"><?php echo e($session->session_name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Select Target Term</label>
                            <select name="to_term_id" class="form-control" required>
                                <option value="">-- Select Term --</option>
                                <?php $__currentLoopData = $terms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $term): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($term->id); ?>"><?php echo e($term->term_name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-success mt-3" onclick="return confirm('Are you sure you want to bulk promote these students? This action cannot be easily undone.')">Process Promotions</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\folu\resources\views/admin/students/promotions.blade.php ENDPATH**/ ?>