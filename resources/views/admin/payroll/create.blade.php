<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Record Staff Payment - Payroll System</title>
    @include('admin.partials.links')
</head>
<body>
    <div class="wrapper">
        @include('admin.partials.sidebar')

        <div class="main-panel">
            @include('admin.partials.header')
            <div class="container">
                <div class="page-inner">
                    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
                        <div>
                            <h2 class="text-dark fw-bold mb-1"><i class="fas fa-plus-circle me-2 text-primary"></i>Record Staff Payment</h2>
                            <p class="text-muted mb-0">Fetch staff information from the staff database and log a payroll payment record.</p>
                        </div>
                        <div class="ms-md-auto py-2 py-md-0">
                            <a href="{{ route('admin.payroll') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Payroll List
                            </a>
                        </div>
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('admin.payroll.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <!-- Left Column: Staff & Assignment Details -->
                            <div class="col-lg-7">
                                <div class="card shadow-sm mb-4">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="card-title mb-0"><i class="fas fa-user-tie me-2"></i>1. Staff & Teaching Assignment Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="staff_id" class="form-label fw-bold">Select Staff Member <span class="text-danger">*</span></label>
                                            <select name="staff_id" id="staff_id" class="form-select select2" required>
                                                <option value="">-- Choose Staff Member from Database --</option>
                                                @foreach($staffList as $st)
                                                    <option value="{{ $st->id }}"
                                                        data-staff-type="{{ $st->staff_type ?? 'full_time' }}"
                                                        data-class-subject="{{ $st->class_or_subject }}"
                                                        data-salary="{{ $st->salary }}"
                                                        data-position="{{ $st->position }}"
                                                        {{ old('staff_id') == $st->id ? 'selected' : '' }}>
                                                        {{ $st->full_name }} ({{ $st->staff_number }}) - {{ $st->position }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="form-text text-muted">Selecting a staff member automatically populates their staff type, class/subject, and salary from database.</small>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="month" class="form-label fw-bold">Payment Month <span class="text-danger">*</span></label>
                                                <select name="month" id="month" class="form-select" required>
                                                    @foreach($months as $m)
                                                        <option value="{{ $m }}" {{ old('month', date('F')) == $m ? 'selected' : '' }}>{{ $m }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="year" class="form-label fw-bold">Payment Year <span class="text-danger">*</span></label>
                                                <select name="year" id="year" class="form-select" required>
                                                    @foreach($years as $y)
                                                        <option value="{{ $y }}" {{ old('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="staff_type" class="form-label fw-bold">Staff Type <span class="text-danger">*</span></label>
                                                <select name="staff_type" id="staff_type" class="form-select" required>
                                                    <option value="full_time" {{ old('staff_type') == 'full_time' ? 'selected' : '' }}>Full Time</option>
                                                    <option value="part_time" {{ old('staff_type') == 'part_time' ? 'selected' : '' }}>Part Time</option>
                                                </select>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="class_subject" class="form-label fw-bold">Class or Subject Taken <span class="text-danger">*</span></label>
                                                <input type="text" name="class_subject" id="class_subject" class="form-control" placeholder="e.g. Grade 1 / Mathematics" value="{{ old('class_subject') }}" required>
                                                <small class="form-text text-muted">Class or subject assigned to this staff member.</small>
                                            </div>
                                        </div>

                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" name="update_staff_profile" id="update_staff_profile" value="1" checked>
                                            <label class="form-check-label text-dark fw-semibold" for="update_staff_profile">
                                                Save updated staff type & class/subject back to Staff Database profile
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="card shadow-sm mb-4">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="card-title mb-0"><i class="fas fa-wallet me-2"></i>2. Payment & Transaction Details</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="pay_date" class="form-label fw-bold">Date of Payment <span class="text-danger">*</span></label>
                                                <input type="date" name="pay_date" id="pay_date" class="form-control" value="{{ old('pay_date', date('Y-m-d')) }}" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="status" class="form-label fw-bold">Payment Status <span class="text-danger">*</span></label>
                                                <select name="status" id="status" class="form-select" required>
                                                    <option value="paid" {{ old('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                                    <option value="not_paid" {{ old('status') == 'not_paid' ? 'selected' : '' }}>Not Paid</option>
                                                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="payment_method" class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                                                <select name="payment_method" id="payment_method" class="form-select" required>
                                                    <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                                    <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                                    <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                                    <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                                                    <option value="mobile_money" {{ old('payment_method') == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="payment_reference" class="form-label fw-bold">Payment Reference / Txn Ref</label>
                                                <input type="text" name="payment_reference" id="payment_reference" class="form-control" placeholder="Auto-generated if empty" value="{{ old('payment_reference') }}">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="remarks" class="form-label fw-bold">Remarks / Notes</label>
                                            <textarea name="remarks" id="remarks" class="form-control" rows="2" placeholder="e.g. September salary payment via Zenith Bank transfer">{{ old('remarks') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Salary & Calculation Breakdown -->
                            <div class="col-lg-5">
                                <div class="card shadow-sm border-primary mb-4">
                                    <div class="card-header bg-dark text-white">
                                        <h5 class="card-title mb-0"><i class="fas fa-calculator me-2 text-warning"></i>3. Salary Breakdown & Calculation</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="basic_salary" class="form-label fw-bold">Base Salary (₦) <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" name="basic_salary" id="basic_salary" class="form-control form-control-lg fw-bold text-primary" placeholder="0.00" value="{{ old('basic_salary', '0.00') }}" required>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="allowances" class="form-label fw-bold">Allowances (₦)</label>
                                                <input type="number" step="0.01" name="allowances" id="allowances" class="form-control calc-input" value="{{ old('allowances', '0.00') }}">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="bonuses" class="form-label fw-bold">Bonuses (₦)</label>
                                                <input type="number" step="0.01" name="bonuses" id="bonuses" class="form-control calc-input" value="{{ old('bonuses', '0.00') }}">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="overtime_pay" class="form-label fw-bold">Overtime Pay (₦)</label>
                                                <input type="number" step="0.01" name="overtime_pay" id="overtime_pay" class="form-control calc-input" value="{{ old('overtime_pay', '0.00') }}">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="deductions" class="form-label fw-bold text-danger">Deductions / Tax (₦)</label>
                                                <input type="number" step="0.01" name="deductions" id="deductions" class="form-control calc-input border-danger" value="{{ old('deductions', '0.00') }}">
                                            </div>
                                        </div>

                                        <hr class="my-3">

                                        <div class="p-3 bg-light rounded border">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-muted fw-bold">Gross Pay:</span>
                                                <span class="fw-bold fs-6" id="disp_gross">₦0.00</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2 text-danger">
                                                <span class="fw-bold">Total Deductions:</span>
                                                <span class="fw-bold fs-6" id="disp_deductions">- ₦0.00</span>
                                            </div>
                                            <div class="d-flex justify-content-between pt-2 border-top">
                                                <span class="fs-5 fw-bold text-dark">Net Payable Salary:</span>
                                                <span class="fs-4 fw-bold text-success" id="disp_net">₦0.00</span>
                                            </div>
                                        </div>

                                        <div class="d-grid gap-2 mt-4">
                                            <button type="submit" class="btn btn-primary btn-lg fw-bold py-3">
                                                <i class="fas fa-check-circle me-1"></i> Submit & Record Payroll
                                            </button>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const staffSelect = document.getElementById('staff_id');
            const staffTypeSelect = document.getElementById('staff_type');
            const classSubjectInput = document.getElementById('class_subject');
            const basicSalaryInput = document.getElementById('basic_salary');

            const allowancesInput = document.getElementById('allowances');
            const bonusesInput = document.getElementById('bonuses');
            const overtimeInput = document.getElementById('overtime_pay');
            const deductionsInput = document.getElementById('deductions');

            const dispGross = document.getElementById('disp_gross');
            const dispDeductions = document.getElementById('disp_deductions');
            const dispNet = document.getElementById('disp_net');

            function calculateTotals() {
                const basic = parseFloat(basicSalaryInput.value) || 0;
                const allowances = parseFloat(allowancesInput.value) || 0;
                const bonuses = parseFloat(bonusesInput.value) || 0;
                const overtime = parseFloat(overtimeInput.value) || 0;
                const deductions = parseFloat(deductionsInput.value) || 0;

                const gross = basic + allowances + bonuses + overtime;
                const net = Math.max(0, gross - deductions);

                dispGross.textContent = '₦' + gross.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                dispDeductions.textContent = '- ₦' + deductions.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                dispNet.textContent = '₦' + net.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            staffSelect.addEventListener('change', function() {
                const selectedOption = staffSelect.options[staffSelect.selectedIndex];
                if (!selectedOption || !selectedOption.value) return;

                const staffId = selectedOption.value;

                // Fetch details via AJAX
                fetch(`/admin/staff/${staffId}/details`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.staff_type) staffTypeSelect.value = data.staff_type;
                        if (data.class_or_subject) classSubjectInput.value = data.class_or_subject;
                        if (data.salary) basicSalaryInput.value = parseFloat(data.salary).toFixed(2);
                        calculateTotals();
                    })
                    .catch(() => {
                        // Fallback to data attributes
                        const stType = selectedOption.getAttribute('data-staff-type');
                        const clSub = selectedOption.getAttribute('data-class-subject');
                        const sal = selectedOption.getAttribute('data-salary');

                        if (stType) staffTypeSelect.value = stType;
                        if (clSub) classSubjectInput.value = clSub;
                        if (sal) basicSalaryInput.value = parseFloat(sal).toFixed(2);
                        calculateTotals();
                    });
            });

            [basicSalaryInput, allowancesInput, bonusesInput, overtimeInput, deductionsInput].forEach(input => {
                input.addEventListener('input', calculateTotals);
            });

            calculateTotals();
        });
    </script>
</body>
</html>
