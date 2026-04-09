<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admission Letter - {{ $student->first_name }} {{ $student->last_name }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 40px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
        .school-name { font-size: 24px; font-weight: bold; text-transform: uppercase; }
        .school-details { font-size: 14px; color: #555; }
        .date { text-align: right; margin-bottom: 20px; }
        .student-details { margin-bottom: 30px; }
        .letter-body { text-align: justify; margin-bottom: 40px; }
        .signature { margin-top: 50px; }
        .print-btn { display: block; width: 100px; margin: 0 auto 30px auto; text-align: center; padding: 10px; background: #007bff; color: #fff; text-decoration: none; border-radius: 5px; }
        @media print {
            .print-btn { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body>

    <a href="javascript:window.print()" class="print-btn">Print Letter</a>

    <div class="header">
        <div class="school-name">{{ $schoolSettings->school_name ?? 'School Management System' }}</div>
        <div class="school-details">
            {{ $schoolSettings->school_address ?? '123 School Address, City' }}<br>
            Phone: {{ $schoolSettings->school_phone ?? '123-456-7890' }} | Email: {{ $schoolSettings->school_email ?? 'info@school.com' }}
        </div>
    </div>

    <div class="date">
        Date: {{ date('F j, Y') }}
    </div>

    <div class="student-details">
        <strong>To:</strong> {{ $student->first_name }} {{ $student->last_name }}<br>
        <strong>Admission Number:</strong> {{ $student->student_number }}<br>
        <strong>Class:</strong> {{ $student->currentClass ? $student->currentClass->class_name : 'N/A' }}
    </div>

    <div class="letter-body">
        <p>Dear {{ $student->first_name }},</p>
        <p>We are pleased to inform you that you have been offered provisional admission into <strong>{{ $schoolSettings->school_name ?? 'our school' }}</strong> for the <strong>{{ $student->currentSession ? $student->currentSession->session_name : 'current' }}</strong> academic session.</p>
        <p>Please note that this admission is strictly provisional and may be withdrawn if it is discovered that any of the information provided during your application is false or if you fail to meet the academic and behavioral standards of the school.</p>
        <p>You are expected to resume on the designated resumption date and complete your registration and fee payments promptly.</p>
        <p>Congratulations and welcome to our great citadel of learning.</p>
    </div>

    <div class="signature">
        <p>Yours faithfully,</p>
        <br><br>
        <p>_______________________</p>
        <p><strong>Principal / Administrator</strong></p>
    </div>

</body>
</html>
