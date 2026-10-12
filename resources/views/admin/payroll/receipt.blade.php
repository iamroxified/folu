<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Receipt #{{ $payroll->payment_reference }} - {{ $payroll->staff ? $payroll->staff->full_name : 'Staff' }}</title>
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --brand-primary: #720922;
            --brand-primary-dark: #500618;
            --brand-gold: #d97706;
            --slate-900: #0f172a;
            --slate-800: #1e293b;
            --slate-700: #334155;
            --slate-600: #475569;
            --slate-500: #64748b;
            --slate-200: #e2e8f0;
            --slate-100: #f1f5f9;
            --slate-50: #f8fafc;
            --success-green: #15803d;
            --success-light: #f0fdf4;
            --danger-red: #b91c1c;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #e2e8f0;
            color: var(--slate-800);
            line-height: 1.5;
            padding: 30px 15px;
            -webkit-font-smoothing: antialiased;
        }

        .action-bar {
            max-width: 820px;
            margin: 0 auto 20px auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background-color: var(--brand-primary);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(114, 9, 34, 0.25);
        }

        .btn-primary:hover {
            background-color: var(--brand-primary-dark);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background-color: #ffffff;
            color: var(--slate-700);
            border: 1px solid var(--slate-200);
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .btn-secondary:hover {
            background-color: var(--slate-50);
            border-color: var(--slate-500);
        }

        /* Printable Sheet */
        .receipt-sheet {
            max-width: 820px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(15, 23, 42, 0.08), 0 2px 6px rgba(15, 23, 42, 0.04);
            border: 1px solid var(--slate-200);
            position: relative;
            overflow: hidden;
        }

        /* Top Brand Accent Bar */
        .top-accent-stripe {
            height: 6px;
            background: linear-gradient(90deg, var(--brand-primary) 0%, #b91c1c 50%, var(--brand-gold) 100%);
        }

        /* Header Area */
        .receipt-header {
            padding: 28px 36px 20px 36px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid var(--slate-100);
            gap: 20px;
        }

        .brand-cluster {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .school-logo-frame {
            width: 74px;
            height: 74px;
            border-radius: 10px;
            background: #ffffff;
            border: 1px solid var(--slate-200);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            flex-shrink: 0;
        }

        .school-logo-frame img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            border-radius: 6px;
        }

        .school-info h1 {
            font-size: 20px;
            font-weight: 800;
            color: var(--brand-primary);
            letter-spacing: -0.3px;
            margin-bottom: 3px;
            text-transform: uppercase;
        }

        .school-info p {
            font-size: 12px;
            color: var(--slate-600);
            line-height: 1.4;
        }

        .school-motto {
            display: inline-block;
            font-size: 11px;
            font-style: italic;
            color: var(--brand-gold);
            font-weight: 600;
            margin-top: 2px;
        }

        .receipt-title-box {
            text-align: right;
            flex-shrink: 0;
        }

        .voucher-badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            padding: 4px 10px;
            background-color: var(--brand-primary);
            color: #ffffff;
            border-radius: 4px;
            margin-bottom: 6px;
        }

        .ref-tag {
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            font-weight: 700;
            color: var(--slate-900);
            background: var(--slate-100);
            padding: 4px 8px;
            border-radius: 4px;
            border: 1px solid var(--slate-200);
            display: inline-block;
        }

        /* Status Banner & Watermark Seal */
        .status-strip {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 36px;
            background-color: var(--slate-50);
            border-bottom: 1px solid var(--slate-200);
            font-size: 12px;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pill.status-paid {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }

        .status-pill.status-unpaid {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .status-pill.status-pending {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        /* Content Body */
        .receipt-body {
            padding: 28px 36px;
        }

        /* Two-Column Info Cards */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }

        .info-card {
            background-color: var(--slate-50);
            border: 1px solid var(--slate-200);
            border-radius: 8px;
            padding: 16px 18px;
        }

        .info-card-header {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--brand-primary);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
            padding-bottom: 6px;
            border-bottom: 1px solid var(--slate-200);
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            font-size: 13px;
            margin-bottom: 7px;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .info-label {
            color: var(--slate-500);
            font-weight: 500;
            font-size: 12px;
        }

        .info-value {
            font-weight: 700;
            color: var(--slate-900);
            text-align: right;
        }

        /* Financial Breakdown Table */
        .ledger-table-wrap {
            margin-bottom: 24px;
            border: 1px solid var(--slate-200);
            border-radius: 8px;
            overflow: hidden;
        }

        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .ledger-table thead th {
            background: var(--slate-800);
            color: #ffffff;
            padding: 10px 16px;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            text-align: left;
        }

        .ledger-table thead th.text-end {
            text-align: right;
        }

        .ledger-table tbody td {
            padding: 10px 16px;
            border-bottom: 1px solid var(--slate-200);
            color: var(--slate-800);
        }

        .ledger-table tbody tr:last-child td {
            border-bottom: none;
        }

        .ledger-table tbody tr:nth-child(even) td {
            background-color: #fafbfc;
        }

        .ledger-table td.amount-cell {
            text-align: right;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
            font-size: 13px;
        }

        .amount-positive {
            color: #15803d;
        }

        .amount-negative {
            color: #b91c1c;
        }

        .subtotal-row td {
            background-color: var(--slate-100) !important;
            font-weight: 700;
            color: var(--slate-900);
        }

        /* Net Pay Highlight Card */
        .net-pay-banner {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 2px solid #86efac;
            border-radius: 10px;
            padding: 18px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            gap: 20px;
        }

        .net-pay-meta .title {
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #166534;
            margin-bottom: 2px;
        }

        .net-pay-meta .words {
            font-size: 12px;
            color: #374151;
            font-style: italic;
            font-weight: 500;
        }

        .net-pay-amount {
            font-family: 'JetBrains Mono', monospace;
            font-size: 26px;
            font-weight: 800;
            color: #15803d;
            text-align: right;
            white-space: nowrap;
        }

        /* Remarks Box */
        .remarks-box {
            background-color: var(--slate-50);
            border-left: 4px solid var(--brand-primary);
            padding: 10px 14px;
            border-radius: 0 6px 6px 0;
            font-size: 12px;
            color: var(--slate-700);
            margin-bottom: 28px;
        }

        .remarks-box strong {
            color: var(--slate-900);
            font-weight: 700;
        }

        /* Signatures Block */
        .signatures-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            padding-top: 15px;
            border-top: 1px dashed var(--slate-200);
            margin-bottom: 24px;
        }

        .signature-slot {
            text-align: center;
        }

        .sig-line {
            width: 80%;
            height: 1px;
            background-color: var(--slate-400);
            margin: 45px auto 8px auto;
        }

        .sig-name {
            font-size: 12px;
            font-weight: 700;
            color: var(--slate-900);
        }

        .sig-role {
            font-size: 11px;
            color: var(--slate-500);
        }

        /* Security Footer */
        .receipt-footer {
            background-color: var(--slate-50);
            border-top: 1px solid var(--slate-200);
            padding: 14px 36px;
            font-size: 11px;
            color: var(--slate-500);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stamp-mark {
            font-family: 'JetBrains Mono', monospace;
            font-size: 10px;
            color: var(--slate-400);
        }

        /* Print Media Styles */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                color: #000000 !important;
            }

            .action-bar {
                display: none !important;
            }

            .receipt-sheet {
                box-shadow: none !important;
                border: 1px solid #cbd5e1 !important;
                max-width: 100% !important;
                border-radius: 0 !important;
                page-break-inside: avoid;
            }

            .net-pay-banner {
                background: #f0fdf4 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .status-pill {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .top-accent-stripe {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .ledger-table thead th {
                background-color: #1e293b !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            @page {
                size: A4 portrait;
                margin: 10mm 12mm;
            }
        }
    </style>
</head>
<body>

<?php
// Function to convert numbers into Nigerian Naira words
function numberToNairaWords(float $number): string {
    $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
    $whole = (int) floor($number);
    $fraction = (int) round(($number - $whole) * 100);
    
    $words = ucwords($f->format($whole)) . " Naira";
    if ($fraction > 0) {
        $words .= " and " . ucwords($f->format($fraction)) . " Kobo";
    }
    return $words . " Only";
}

$schoolName = $schoolSettings->school_name ?? 'Folu International Schools';
$schoolAddress = $schoolSettings->school_address ?? 'P.O Box 37, Itedo-Ijowa, Isanlu Kogi State Nigeria';
$schoolPhone = $schoolSettings->school_phone ?? '08165354191';
$schoolEmail = $schoolSettings->school_email ?? 'info@foluinternationalschools.com.ng';
$schoolMotto = $schoolSettings->school_motto ?? 'Knowledge and Discipline';
$logoPath = !empty($schoolSettings->school_logo) 
    ? (str_starts_with($schoolSettings->school_logo, '/') ? $schoolSettings->school_logo : '/storage/' . $schoolSettings->school_logo)
    : '/images/folu-logo.png';
?>

    <!-- Browser Action Bar -->
    <div class="action-bar">
        <div>
            <a href="{{ route('admin.payroll') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Payroll
            </a>
            <a href="{{ route('admin.payroll.show', $payroll->id) }}" class="btn btn-secondary">
                <i class="fas fa-file-invoice"></i> View Payslip
            </a>
        </div>
        <div>
            <button onclick="window.print();" class="btn btn-primary">
                <i class="fas fa-print"></i> Print Official Receipt
            </button>
        </div>
    </div>

    <!-- Printable Official Sheet -->
    <div class="receipt-sheet" id="printableArea">
        <div class="top-accent-stripe"></div>

        <!-- Header -->
        <header class="receipt-header">
            <div class="brand-cluster">
                <div class="school-logo-frame">
                    <img src="{{ $logoPath }}" alt="{{ $schoolName }} Logo" onerror="this.onerror=null; this.src='/images/folu-logo.png';">
                </div>
                <div class="school-info">
                    <h1>{{ $schoolName }}</h1>
                    <p><i class="fas fa-map-marker-alt" style="color: var(--brand-primary); margin-right: 4px;"></i> {{ $schoolAddress }}</p>
                    <p>
                        <i class="fas fa-phone-alt" style="color: var(--brand-primary); margin-right: 4px;"></i> {{ $schoolPhone }}
                        &nbsp;|&nbsp;
                        <i class="fas fa-envelope" style="color: var(--brand-primary); margin-right: 4px;"></i> {{ $schoolEmail }}
                    </p>
                    <span class="school-motto">Motto: {{ $schoolMotto }}</span>
                </div>
            </div>

            <div class="receipt-title-box">
                <span class="voucher-badge"><i class="fas fa-receipt me-1"></i> Salary Payment Receipt</span>
                <br>
                <span class="ref-tag">Ref: {{ $payroll->payment_reference }}</span>
            </div>
        </header>

        <!-- Status & Meta Bar -->
        <div class="status-strip">
            <div>
                <strong>Pay Period:</strong> <span style="color: var(--brand-primary); font-weight: 700;">{{ $payroll->pay_period_formatted }}</span>
                &nbsp;|&nbsp;
                <strong>Issued Date:</strong> {{ $payroll->pay_date ? $payroll->pay_date->format('F d, Y') : date('F d, Y') }}
            </div>
            <div>
                @if($payroll->status === 'paid')
                    <span class="status-pill status-paid"><i class="fas fa-check-circle"></i> Payment Completed</span>
                @elseif($payroll->status === 'not_paid' || $payroll->status === 'unpaid')
                    <span class="status-pill status-unpaid"><i class="fas fa-times-circle"></i> Not Paid</span>
                @else
                    <span class="status-pill status-pending"><i class="fas fa-hourglass-half"></i> {{ strtoupper($payroll->status) }}</span>
                @endif
            </div>
        </div>

        <div class="receipt-body">
            <!-- Two Cards: Staff Profile & Payment Info -->
            <div class="info-grid">
                <!-- Staff Details Card -->
                <div class="info-card">
                    <div class="info-card-header">
                        <i class="fas fa-user-tie"></i> Staff Beneficiary Details
                    </div>
                    <div class="info-row">
                        <span class="info-label">Full Name:</span>
                        <span class="info-value" style="font-size: 14px;">{{ $payroll->staff ? $payroll->staff->full_name : 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Staff ID:</span>
                        <span class="info-value">{{ $payroll->staff ? $payroll->staff->staff_number : 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Designation / Role:</span>
                        <span class="info-value">{{ $payroll->staff ? $payroll->staff->position : 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Department:</span>
                        <span class="info-value">{{ $payroll->staff && $payroll->staff->department ? $payroll->staff->department : 'Academic' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Employment Type:</span>
                        <span class="info-value">{{ $payroll->formatted_staff_type }}</span>
                    </div>
                </div>

                <!-- Payment Details Card -->
                <div class="info-card">
                    <div class="info-card-header">
                        <i class="fas fa-credit-card"></i> Disbursement & Teaching Details
                    </div>
                    <div class="info-row">
                        <span class="info-label">Class / Subject:</span>
                        <span class="info-value" style="color: var(--brand-primary);">
                            {{ $payroll->class_subject ?: ($payroll->staff ? $payroll->staff->class_or_subject : 'General') }}
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Payment Method:</span>
                        <span class="info-value">{{ strtoupper(str_replace('_', ' ', $payroll->payment_method)) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Disbursement Date:</span>
                        <span class="info-value">{{ $payroll->pay_date ? $payroll->pay_date->format('d M, Y') : 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Transaction Ref:</span>
                        <span class="info-value" style="font-family: 'JetBrains Mono', monospace; font-size: 12px;">{{ $payroll->payment_reference }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Office:</span>
                        <span class="info-value">Bursary & Accounts</span>
                    </div>
                </div>
            </div>

            <!-- Detailed Breakdown Table -->
            <div class="ledger-table-wrap">
                <table class="ledger-table">
                    <thead>
                        <tr>
                            <th style="width: 50%;">Salary Component / Description</th>
                            <th style="width: 25%;">Category</th>
                            <th class="text-end" style="width: 25%;">Amount (₦)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Basic / Base Salary</strong></td>
                            <td>Earnings</td>
                            <td class="amount-cell">₦{{ number_format($payroll->basic_salary, 2) }}</td>
                        </tr>

                        @if($payroll->allowances > 0)
                            <tr>
                                <td>Allowances (Housing, Transport & Medical)</td>
                                <td>Earnings</td>
                                <td class="amount-cell amount-positive">+ ₦{{ number_format($payroll->allowances, 2) }}</td>
                            </tr>
                        @endif

                        @if($payroll->bonuses > 0)
                            <tr>
                                <td>Performance Bonuses & Incentives</td>
                                <td>Earnings</td>
                                <td class="amount-cell amount-positive">+ ₦{{ number_format($payroll->bonuses, 2) }}</td>
                            </tr>
                        @endif

                        @if($payroll->overtime_pay > 0)
                            <tr>
                                <td>Overtime / Extra Hours Pay</td>
                                <td>Earnings</td>
                                <td class="amount-cell amount-positive">+ ₦{{ number_format($payroll->overtime_pay, 2) }}</td>
                            </tr>
                        @endif

                        <tr class="subtotal-row">
                            <td><strong>Gross Salary Earnings</strong></td>
                            <td><strong>Subtotal</strong></td>
                            <td class="amount-cell"><strong>₦{{ number_format($payroll->gross_pay, 2) }}</strong></td>
                        </tr>

                        @if($payroll->deductions > 0)
                            <tr>
                                <td>Statutory Deductions, Tax & Advances</td>
                                <td>Deductions</td>
                                <td class="amount-cell amount-negative">- ₦{{ number_format($payroll->deductions, 2) }}</td>
                            </tr>
                        @else
                            <tr>
                                <td>Deductions / Penalties</td>
                                <td>Deductions</td>
                                <td class="amount-cell" style="color: var(--slate-400);">₦0.00</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Net Payable Highlight Banner -->
            <div class="net-pay-banner">
                <div class="net-pay-meta">
                    <div class="title"><i class="fas fa-coins me-1"></i> Total Net Salary Disbursed</div>
                    <div class="words">{{ numberToNairaWords((float) $payroll->net_pay) }}</div>
                </div>
                <div class="net-pay-amount">
                    ₦{{ number_format($payroll->net_pay, 2) }}
                </div>
            </div>

            @if($payroll->remarks)
                <div class="remarks-box">
                    <strong>Official Remarks / Notes:</strong> {{ $payroll->remarks }}
                </div>
            @endif

            <!-- Dual Signatures Block -->
            <div class="signatures-grid">
                <div class="signature-slot">
                    <div class="sig-line"></div>
                    <div class="sig-name">{{ $payroll->staff ? $payroll->staff->full_name : 'Staff Member' }}</div>
                    <div class="sig-role">Beneficiary Signature & Date</div>
                </div>

                <div class="signature-slot">
                    <div class="sig-line"></div>
                    <div class="sig-name">Authorized Bursar / Administrator</div>
                    <div class="sig-role">School Accounts & Official Seal</div>
                </div>
            </div>
        </div>

        <!-- Security Footer -->
        <footer class="receipt-footer">
            <div>
                <i class="fas fa-shield-alt text-success me-1"></i> Computer-generated Official Payment Receipt &copy; {{ date('Y') }} {{ $schoolName }}.
            </div>
            <div class="stamp-mark">
                TXN-VERIFIED-{{ strtoupper(substr(md5($payroll->payment_reference . $payroll->id), 0, 10)) }}
            </div>
        </footer>
    </div>

</body>
</html>
