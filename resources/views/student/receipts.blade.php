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
                                <p><strong>Fee Type:</strong><br>{{ $payment->payable?->feeStructure?->name ?? $payment->payable?->feeStructure?->description ?? $payment->description ?? 'School Fee' }}</p>
                            </div>
                            <div class="col-sm-6">
                                <p><strong>Amount:</strong><br>₦{{ number_format($payment->amount, 2) }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <p><strong>Payment Date:</strong><br>{{ !empty($payment->payment_date) ? \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') : $payment->created_at?->format('d/m/Y') }}</p>
                            </div>
                            <div class="col-sm-6">
                                <p><strong>Method:</strong><br>{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'N/A')) }}</p>
                            </div>
                        </div>
                        @if($payment->description)
                            <p><strong>Description:</strong><br>{{ $payment->description }}</p>
                        @endif
                        <div class="mt-3">
                            <button class="btn btn-primary btn-sm" onclick="printReceipt('{{ $payment->id }}')">Print Receipt</button>
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
    window.open('/student/receipt.php?id=' + paymentId, '_blank');
}
</script>
@endsection