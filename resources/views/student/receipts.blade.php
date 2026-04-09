@extends('student.layout')

@section('title', 'My Receipts')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4">Payment Receipts</h1>
    </div>
</div>

@if($payments->count() > 0)
    <div class="row">
        @foreach($payments as $payment)
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h6>Receipt #{{ $payment->payment_reference }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <p><strong>Fee Type:</strong><br>{{ $payment->payable->feeStructure->name }}</p>
                            </div>
                            <div class="col-sm-6">
                                <p><strong>Amount:</strong><br>₦{{ number_format($payment->amount, 2) }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <p><strong>Payment Date:</strong><br>{{ $payment->payment_date->format('d/m/Y') }}</p>
                            </div>
                            <div class="col-sm-6">
                                <p><strong>Method:</strong><br>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</p>
                            </div>
                        </div>
                        @if($payment->description)
                            <p><strong>Description:</strong><br>{{ $payment->description }}</p>
                        @endif
                        <div class="mt-3">
                            <button class="btn btn-primary btn-sm" onclick="printReceipt('{{ $payment->id }}')">Print Receipt</button>
                            <button class="btn btn-secondary btn-sm" onclick="downloadReceipt('{{ $payment->id }}')">Download PDF</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="card">
        <div class="card-body text-center">
            <h5>No Receipts Available</h5>
            <p class="text-muted">Your payment receipts will appear here once payments are made.</p>
        </div>
    </div>
@endif

<script>
function printReceipt(paymentId) {
    // In a real application, this would open a printable receipt
    window.open('/student/receipt/' + paymentId + '/print', '_blank');
}

function downloadReceipt(paymentId) {
    // In a real application, this would download a PDF receipt
    window.open('/student/receipt/' + paymentId + '/download', '_blank');
}
</script>
@endsection