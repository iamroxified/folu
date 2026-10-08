<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Record School Expenditure - Admin Panel</title>
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
                            <h2 class="text-dark fw-bold mb-1"><i class="fas fa-plus-circle me-2 text-danger"></i>Record School Expenditure</h2>
                            <p class="text-muted mb-0">Capture operational expenses, vendor payouts, and purchases made by the school.</p>
                        </div>
                        <div class="ms-md-auto py-2 py-md-0">
                            <a href="{{ route('admin.expenditures') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Expenditures
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

                    <div class="card shadow-sm">
                        <div class="card-header bg-danger text-white">
                            <h5 class="card-title mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>Expenditure Details Form</h5>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('admin.expenditures.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label for="title" class="form-label fw-bold">What was paid for? (Item / Description Title) <span class="text-danger">*</span></label>
                                        <input type="text" name="title" id="title" class="form-control form-control-lg" placeholder="e.g. Purchase of Whiteboard Markers & Science Lab Equipment" value="{{ old('title') }}" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="category" class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                                        <select name="category" id="category" class="form-select form-select-lg" required>
                                            <option value="">-- Select Category --</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="amount" class="form-label fw-bold">Amount Paid (₦) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" name="amount" id="amount" class="form-control form-control-lg fw-bold text-danger" placeholder="0.00" value="{{ old('amount') }}" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="expenditure_date" class="form-label fw-bold">Date of Expenditure <span class="text-danger">*</span></label>
                                        <input type="date" name="expenditure_date" id="expenditure_date" class="form-control form-control-lg" value="{{ old('expenditure_date', date('Y-m-d')) }}" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="status" class="form-label fw-bold">Payment Status <span class="text-danger">*</span></label>
                                        <select name="status" id="status" class="form-select form-select-lg" required>
                                            <option value="paid" {{ old('status', 'paid') == 'paid' ? 'selected' : '' }}>Paid</option>
                                            <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                            <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="vendor_recipient" class="form-label fw-bold">Vendor / Recipient (Paid To)</label>
                                        <input type="text" name="vendor_recipient" id="vendor_recipient" class="form-control" placeholder="e.g. ABC Educational Books Ltd" value="{{ old('vendor_recipient') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="payment_method" class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                                        <select name="payment_method" id="payment_method" class="form-select" required>
                                            <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                            <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                            <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                            <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>Card / POS</option>
                                            <option value="online" {{ old('payment_method') == 'online' ? 'selected' : '' }}>Online</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label fw-bold">Detailed Description / Notes</label>
                                    <textarea name="description" id="description" class="form-control" rows="3" placeholder="Provide extra details about the expense items or voucher justification...">{{ old('description') }}</textarea>
                                </div>

                                <div class="mb-4">
                                    <label for="receipt" class="form-label fw-bold">Attach Receipt / Invoice / Proof (Optional)</label>
                                    <input type="file" name="receipt" id="receipt" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                    <small class="form-text text-muted">Upload scan or PDF of receipt (Max: 5MB).</small>
                                </div>

                                <hr>

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.expenditures') }}" class="btn btn-light border btn-lg">Cancel</a>
                                    <button type="submit" class="btn btn-danger btn-lg px-4 fw-bold">
                                        <i class="fas fa-check-circle me-1"></i> Save Expenditure Record
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
            @include('admin.partials.footer')
        </div>
    </div>
</body>
</html>
