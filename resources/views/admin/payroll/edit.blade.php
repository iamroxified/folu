<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Edit Staff Payroll Record - Admin Panel</title>
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
                            <h2 class="text-dark fw-bold mb-1"><i class="fas fa-edit me-2 text-warning"></i>Edit Staff Payroll Record</h2>
                            <p class="text-muted mb-0">Update staff payment details, class/subject, or payment status.</p>
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

                    <form action="{{ route('admin.payroll.update', $payroll->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-lg-7">
                                <div class="card shadow-sm mb-4">
                                    <div class="card-header bg-warning text-white">
                                        <h5 class="card-title mb-0"><i class="fas fa-user-tie me-2"></i>1. Staff & Teaching Assignment Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="staff_id" class="form-label fw-bold">Staff Member <span class="text-danger">*</span></label>
                                            <select name="staff_id" id="staff_id" class="form-select select2" required>
                                                @foreach($staffList as $st)
                                                    <option value="{{ $st->id }}" {{ old('staff_id', $payroll->staff_id) == $st->id ? 'selected' : '' }}>
                                                        {{ $st->full_name }} ({{ $st->staff_number }}) - {{ $st->position }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="month" class="form-label fw-bold">Payment Month <span class="text-danger">*</span></label>
                                                <select name="month" id="month" class="form-select" required>
                                                    @foreach($months as $m)
                                                        <option value="{{ $m }}" {{ old('month', $payroll->month ?: date('F')) == $m ? 'selected' : '' }}>{{ $m }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="year" class="form-label fw-bold">Payment Year <span class="text-danger">*</span></label>
                                                <select name="year" id="year" class="form-select" required>
                                                    @foreach($years as $y)
                                                        <option value="{{ $y }}" {{ old('year', $payroll->year ?: date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="staff_type" class="form-label fw-bold">Staff Type <span class="text-danger">*</span></label>
                                                <select name="staff_type" id="staff_type" class="form-select" required>
                                                    <option value="full_time" {{ old('staff_type', $payroll->staff_type) == 'full_time' ? 'selected' : '' }}>Full Time</option>
                                                    <option value="part_time" {{ old('staff_type', $payroll->staff_type) == 'part_time' ? 'selected' : '' }}>Part Time</option>
                                                </select>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="class_subject" class="form-label fw-bold">Class or Subject Taken <span class="text-danger">*</span></label>
                                                <input type="text" name="class_subject" id="class_subject" class="form-control" value="{{ old('class_subject', $payroll->class_subject) }}" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card shadow-sm mb-4">
                                    <div class="card-header bg-secondary text-white">
                                        <h5 class="card-title mb-0"><i class="fas fa-wallet me-2"></i>2. Payment & Status</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="pay_date" class="form-label fw-bold">Date of Payment <span class="text-danger">*</span></label>
                                                <input type="date" name="pay_date" id="pay_date" class="form-control" value="{{ old('pay_date', $payroll->pay_date ? $payroll->pay_date->format('Y-m-d') : '') }}" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="status" class="form-label fw-bold">Payment Status <span class="text-danger">*</span></label>
                                                <select name="status" id="status" class="form-select" required>
                                                    <option value="paid" {{ old('status', $payroll->status) == 'paid' ? 'selected' : '' }}>Paid</option>
                                                    <option value="not_paid" {{ old('status', $payroll->status) == 'not_paid' || old('status', $payroll->status) == 'unpaid' ? 'selected' : '' }}>Not Paid</option>
                                                    <option value="pending" {{ old('status', $payroll->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="payment_method" class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                                                <select name="payment_method" id="payment_method" class="form-select" required>
                                                    <option value="bank_transfer" {{ old('payment_method', $payroll->payment_method) == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                                    <option value="cash" {{ old('payment_method', $payroll->payment_method) == 'cash' ? 'selected' : '' }}>Cash</option>
                                                    <option value="cheque" {{ old('payment_method', $payroll->payment_method) == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                                    <option value="card" {{ old('payment_method', $payroll->payment_method) == 'card' ? 'selected' : '' }}>Card</option>
                                                    <option value="mobile_money" {{ old('payment_method', $payroll->payment_method) == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="payment_reference" class="form-label fw-bold">Payment Reference</label>
                                                <input type="text" name="payment_reference" id="payment_reference" class="form-control" value="{{ old('payment_reference', $payroll->payment_reference) }}">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="remarks" class="form-label fw-bold">Remarks / Notes</label>
                                            <textarea name="remarks" id="remarks" class="form-control" rows="2">{{ old('remarks', $payroll->remarks) }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-5">
                                <div class="card shadow-sm border-warning mb-4">
                                    <div class="card-header bg-dark text-white">
                                        <h5 class="card-title mb-0"><i class="fas fa-calculator me-2 text-warning"></i>3. Salary Breakdown</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="basic_salary" class="form-label fw-bold">Base Salary (₦) <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" name="basic_salary" id="basic_salary" class="form-control form-control-lg fw-bold text-primary" value="{{ old('basic_salary', $payroll->basic_salary) }}" required>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="allowances" class="form-label fw-bold">Allowances (₦)</label>
                                                <input type="number" step="0.01" name="allowances" id="allowances" class="form-control" value="{{ old('allowances', $payroll->allowances) }}">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="bonuses" class="form-label fw-bold">Bonuses (₦)</label>
                                                <input type="number" step="0.01" name="bonuses" id="bonuses" class="form-control" value="{{ old('bonuses', $payroll->bonuses) }}">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="deductions" class="form-label fw-bold text-danger">Deductions / Tax (₦)</label>
                                            <input type="number" step="0.01" name="deductions" id="deductions" class="form-control border-danger" value="{{ old('deductions', $payroll->deductions) }}">
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
                                            <button type="submit" class="btn btn-warning text-white btn-lg fw-bold py-3">
                                                <i class="fas fa-save me-1"></i> Save Changes
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
            const basicSalaryInput = document.getElementById('basic_salary');
            const allowancesInput = document.getElementById('allowances');
            const bonusesInput = document.getElementById('bonuses');
            const deductionsInput = document.getElementById('deductions');

            const dispGross = document.getElementById('disp_gross');
            const dispDeductions = document.getElementById('disp_deductions');
            const dispNet = document.getElementById('disp_net');

            function calculateTotals() {
                const basic = parseFloat(basicSalaryInput.value) || 0;
                const allowances = parseFloat(allowancesInput.value) || 0;
                const bonuses = parseFloat(bonusesInput.value) || 0;
                const deductions = parseFloat(deductionsInput.value) || 0;

                const gross = basic + allowances + bonuses;
                const net = Math.max(0, gross - deductions);

                dispGross.textContent = '₦' + gross.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                dispDeductions.textContent = '- ₦' + deductions.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                dispNet.textContent = '₦' + net.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            [basicSalaryInput, allowancesInput, bonusesInput, deductionsInput].forEach(input => {
                input.addEventListener('input', calculateTotals);
            });

            calculateTotals();
        });
    </script>
</body>
</html>
