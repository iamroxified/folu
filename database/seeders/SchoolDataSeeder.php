<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\Term;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\FeeStructure;
use App\Models\Student;
use App\Models\StudentFee;
use App\Models\Payment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SchoolDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Academic Sessions
        $session2526 = AcademicSession::firstOrCreate(
            ['session_name' => '2025/2026'],
            ['start_date' => '2025-09-08', 'end_date' => '2026-07-24', 'is_active' => true]
        );
        $session2425 = AcademicSession::firstOrCreate(
            ['session_name' => '2024/2025'],
            ['start_date' => '2024-09-09', 'end_date' => '2025-07-25', 'is_active' => false]
        );

        $sessionId = $session2526->id;

        // 2. Terms
        $term1 = Term::firstOrCreate(['term_number' => 1], ['term_name' => 'First Term']);
        $term2 = Term::firstOrCreate(['term_number' => 2], ['term_name' => 'Second Term']);
        $term3 = Term::firstOrCreate(['term_number' => 3], ['term_name' => 'Third Term']);
        $term1Id = $term1->id;

        // 3. Classes
        $classesData = [
            ['class_name' => 'JSS 1A', 'section' => 'A', 'grade_level' => 'Junior Secondary', 'class_arm' => 'A', 'class_level' => 'JSS1'],
            ['class_name' => 'JSS 1B', 'section' => 'B', 'grade_level' => 'Junior Secondary', 'class_arm' => 'B', 'class_level' => 'JSS1'],
            ['class_name' => 'JSS 2A', 'section' => 'A', 'grade_level' => 'Junior Secondary', 'class_arm' => 'A', 'class_level' => 'JSS2'],
            ['class_name' => 'JSS 2B', 'section' => 'B', 'grade_level' => 'Junior Secondary', 'class_arm' => 'B', 'class_level' => 'JSS2'],
            ['class_name' => 'JSS 3A', 'section' => 'A', 'grade_level' => 'Junior Secondary', 'class_arm' => 'A', 'class_level' => 'JSS3'],
            ['class_name' => 'JSS 3B', 'section' => 'B', 'grade_level' => 'Junior Secondary', 'class_arm' => 'B', 'class_level' => 'JSS3'],
            ['class_name' => 'SSS 1A', 'section' => 'A', 'grade_level' => 'Senior Secondary', 'class_arm' => 'A', 'class_level' => 'SSS1'],
            ['class_name' => 'SSS 1B', 'section' => 'B', 'grade_level' => 'Senior Secondary', 'class_arm' => 'B', 'class_level' => 'SSS1'],
            ['class_name' => 'SSS 2A', 'section' => 'A', 'grade_level' => 'Senior Secondary', 'class_arm' => 'A', 'class_level' => 'SSS2'],
            ['class_name' => 'SSS 2B', 'section' => 'B', 'grade_level' => 'Senior Secondary', 'class_arm' => 'B', 'class_level' => 'SSS2'],
            ['class_name' => 'SSS 3A', 'section' => 'A', 'grade_level' => 'Senior Secondary', 'class_arm' => 'A', 'class_level' => 'SSS3'],
            ['class_name' => 'SSS 3B', 'section' => 'B', 'grade_level' => 'Senior Secondary', 'class_arm' => 'B', 'class_level' => 'SSS3'],
        ];

        $classMap = [];
        foreach ($classesData as $cd) {
            $sc = SchoolClass::firstOrCreate(
                ['class_name' => $cd['class_name'], 'section' => $cd['section']],
                [
                    'grade_level' => $cd['grade_level'],
                    'max_capacity' => 40,
                    'academic_year' => '2025-2026',
                    'status' => 'active',
                ]
            );
            $classMap[$cd['class_name']] = $sc->id;

            if (Schema::hasTable('classes')) {
                DB::table('classes')->updateOrInsert(
                    ['class_name' => $cd['class_name'], 'class_arm' => $cd['class_arm']],
                    [
                        'class_level' => $cd['class_level'],
                        'max_capacity' => 40,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        // 4. Subjects
        $subjectsData = [
            ['subject_code' => 'ENG', 'subject_name' => 'English Language', 'class_level' => 'ALL', 'department' => 'general', 'subject_type' => 'core'],
            ['subject_code' => 'MTH', 'subject_name' => 'Mathematics', 'class_level' => 'ALL', 'department' => 'general', 'subject_type' => 'core'],
            ['subject_code' => 'CIV', 'subject_name' => 'Civic Education', 'class_level' => 'ALL', 'department' => 'general', 'subject_type' => 'core'],
            ['subject_code' => 'BSC', 'subject_name' => 'Basic Science', 'class_level' => 'JSS', 'department' => 'science', 'subject_type' => 'core'],
            ['subject_code' => 'BTECH', 'subject_name' => 'Basic Technology', 'class_level' => 'JSS', 'department' => 'science', 'subject_type' => 'core'],
            ['subject_code' => 'PHY', 'subject_name' => 'Physics', 'class_level' => 'SSS', 'department' => 'science', 'subject_type' => 'elective'],
            ['subject_code' => 'CHE', 'subject_name' => 'Chemistry', 'class_level' => 'SSS', 'department' => 'science', 'subject_type' => 'elective'],
            ['subject_code' => 'BIO', 'subject_name' => 'Biology', 'class_level' => 'SSS', 'department' => 'science', 'subject_type' => 'elective'],
            ['subject_code' => 'ECO', 'subject_name' => 'Economics', 'class_level' => 'SSS', 'department' => 'commercial', 'subject_type' => 'elective'],
            ['subject_code' => 'GOV', 'subject_name' => 'Government', 'class_level' => 'SSS', 'department' => 'arts', 'subject_type' => 'elective'],
            ['subject_code' => 'GEO', 'subject_name' => 'Geography', 'class_level' => 'SSS', 'department' => 'arts', 'subject_type' => 'elective'],
            ['subject_code' => 'AGR', 'subject_name' => 'Agricultural Science', 'class_level' => 'ALL', 'department' => 'general', 'subject_type' => 'elective'],
        ];

        foreach ($subjectsData as $sd) {
            Subject::firstOrCreate(
                ['subject_code' => $sd['subject_code']],
                [
                    'subject_name' => $sd['subject_name'],
                    'class_level' => $sd['class_level'],
                    'department' => $sd['department'],
                    'subject_type' => $sd['subject_type'],
                    'credit_hours' => 3,
                    'status' => 'active',
                ]
            );
        }

        // 5. Fee Structures
        $feeStructuresData = [
            ['name' => 'JSS 1 Tuition & Development', 'amount' => 45000.00, 'category' => 'NI', 'class_name' => 'JSS 1A'],
            ['name' => 'JSS 2 Tuition Fee', 'amount' => 48000.00, 'category' => 'OS', 'class_name' => 'JSS 2A'],
            ['name' => 'JSS 3 Examination & Tuition', 'amount' => 52000.00, 'category' => 'OS', 'class_name' => 'JSS 3A'],
            ['name' => 'SSS 1 New Intake Package', 'amount' => 60000.00, 'category' => 'NI', 'class_name' => 'SSS 1A'],
            ['name' => 'SSS 2 Tuition & Practical Fee', 'amount' => 58000.00, 'category' => 'OS', 'class_name' => 'SSS 2A'],
            ['name' => 'SSS 3 WAEC & Graduation Fee', 'amount' => 75000.00, 'category' => 'OS', 'class_name' => 'SSS 3A'],
        ];

        $feeStructureMap = [];
        foreach ($feeStructuresData as $fd) {
            $classId = $classMap[$fd['class_name']] ?? null;
            $fs = FeeStructure::firstOrCreate(
                ['name' => $fd['name'], 'session_id' => $sessionId],
                [
                    'description' => $fd['name'] . ' for ' . $session2526->session_name,
                    'amount' => $fd['amount'],
                    'fee_type' => 'tuition',
                    'category' => $fd['category'],
                    'gender' => 'All',
                    'class_id' => $classId,
                    'term_id' => $term1Id,
                    'effective_from' => '2025-09-08',
                    'effective_to' => '2026-07-24',
                    'is_mandatory' => true,
                    'is_active' => true,
                ]
            );
            $feeStructureMap[$fd['class_name']] = $fs;
        }

        // 6. Sample Students
        $studentsData = [
            ['adm' => 'FIM/2025/001', 'fn' => 'Temiloluwa', 'ln' => 'Adebayo', 'gender' => 'female', 'cat' => 'NI', 'class' => 'JSS 1A', 'email' => 'temi.adebayo@student.fimocol.edu.ng'],
            ['adm' => 'FIM/2025/002', 'fn' => 'Chidiebere', 'ln' => 'Okonkwo', 'gender' => 'male', 'cat' => 'NI', 'class' => 'JSS 1A', 'email' => 'chidi.okonkwo@student.fimocol.edu.ng'],
            ['adm' => 'FIM/2025/003', 'fn' => 'Amina', 'ln' => 'Ibrahim', 'gender' => 'female', 'cat' => 'NI', 'class' => 'JSS 1B', 'email' => 'amina.ibrahim@student.fimocol.edu.ng'],
            ['adm' => 'FIM/2025/004', 'fn' => 'David', 'ln' => 'Williams', 'gender' => 'male', 'cat' => 'OS', 'class' => 'JSS 2A', 'email' => 'david.williams@student.fimocol.edu.ng'],
            ['adm' => 'FIM/2025/005', 'fn' => 'Hassan', 'ln' => 'Mohammed', 'gender' => 'male', 'cat' => 'OS', 'class' => 'JSS 2B', 'email' => 'hassan.mohammed@student.fimocol.edu.ng'],
            ['adm' => 'FIM/2025/006', 'fn' => 'Blessing', 'ln' => 'Eze', 'gender' => 'female', 'cat' => 'OS', 'class' => 'JSS 3A', 'email' => 'blessing.eze@student.fimocol.edu.ng'],
            ['adm' => 'FIM/2025/007', 'fn' => 'Fatima', 'ln' => 'Yusuf', 'gender' => 'female', 'cat' => 'OS', 'class' => 'JSS 3B', 'email' => 'fatima.yusuf@student.fimocol.edu.ng'],
            ['adm' => 'FIM/2025/008', 'fn' => 'Yusuf', 'ln' => 'Hassan', 'gender' => 'male', 'cat' => 'NI', 'class' => 'SSS 1A', 'email' => 'yusuf.hassan@student.fimocol.edu.ng'],
            ['adm' => 'FIM/2025/009', 'fn' => 'Praise', 'ln' => 'Daniel', 'gender' => 'female', 'cat' => 'NI', 'class' => 'SSS 1B', 'email' => 'praise.daniel@student.fimocol.edu.ng'],
            ['adm' => 'FIM/2025/010', 'fn' => 'Abubakar', 'ln' => 'Bello', 'gender' => 'male', 'cat' => 'OS', 'class' => 'SSS 2A', 'email' => 'abubakar.bello@student.fimocol.edu.ng'],
            ['adm' => 'FIM/2025/011', 'fn' => 'Khadija', 'ln' => 'Adamu', 'gender' => 'female', 'cat' => 'OS', 'class' => 'SSS 2B', 'email' => 'khadija.adamu@student.fimocol.edu.ng'],
            ['adm' => 'FIM/2025/012', 'fn' => 'Chioma', 'ln' => 'Okoro', 'gender' => 'female', 'cat' => 'OS', 'class' => 'SSS 3A', 'email' => 'chioma.okoro@student.fimocol.edu.ng'],
            ['adm' => 'FIM/2025/013', 'fn' => 'Ibrahim', 'ln' => 'Musa', 'gender' => 'male', 'cat' => 'OS', 'class' => 'SSS 3B', 'email' => 'ibrahim.musa@student.fimocol.edu.ng'],
            ['adm' => 'FIM/2025/014', 'fn' => 'Mercy', 'ln' => 'James', 'gender' => 'female', 'cat' => 'OS', 'class' => 'JSS 2A', 'email' => 'mercy.james@student.fimocol.edu.ng'],
            ['adm' => 'FIM/2025/015', 'fn' => 'Zainab', 'ln' => 'Ali', 'gender' => 'female', 'cat' => 'NI', 'class' => 'SSS 1A', 'email' => 'zainab.ali@student.fimocol.edu.ng'],
        ];

        $studentModels = [];
        foreach ($studentsData as $st) {
            $targetClassId = $classMap[$st['class']] ?? 1;
            $student = Student::firstOrCreate(
                ['admission_no' => $st['adm']],
                [
                    'student_number' => $st['adm'],
                    'first_name' => $st['fn'],
                    'last_name' => $st['ln'],
                    'other_names' => '',
                    'gender' => $st['gender'],
                    'email' => $st['email'],
                    'date_of_birth' => '2008-05-12',
                    'enrollment_date' => '2025-09-08',
                    'admission_date' => '2025-09-08',
                    'status' => 'active',
                    'admission_status' => 'admitted',
                    'category' => $st['cat'],
                    'student_type' => 'day',
                    'current_class_id' => $targetClassId,
                    'class_link' => $targetClassId,
                    'current_session_id' => $sessionId,
                    'academic_session_link' => $sessionId,
                    'current_term_id' => $term1Id,
                    'term_link' => $term1Id,
                ]
            );
            $studentModels[] = ['student' => $student, 'class_name' => $st['class']];
        }

        // 7. Student Fee Allocations & Payments
        $counter = 1;
        foreach ($studentModels as $item) {
            $student = $item['student'];
            $className = $item['class_name'];
            $feeStructure = $feeStructureMap[$className] ?? FeeStructure::where('is_active', true)->first();

            if (!$feeStructure) {
                continue;
            }

            $baseAmount = (float) $feeStructure->amount;

            $studentFee = StudentFee::firstOrCreate(
                ['student_id' => $student->id, 'session_id' => $sessionId, 'term_id' => $term1Id],
                [
                    'fee_structure_id' => $feeStructure->id,
                    'base_amount' => $baseAmount,
                    'additional_amount' => 0.00,
                    'total_payable' => $baseAmount,
                    'amount_due' => $baseAmount,
                    'amount_paid' => 0.00,
                    'amount_owed' => $baseAmount,
                    'balance' => $baseAmount,
                    'due_date' => now()->addDays(30),
                    'status' => 'unpaid',
                    'academic_year' => $session2526->session_name,
                    'semester' => 'First Term',
                    'notes' => 'Allocated ' . $feeStructure->name,
                ]
            );

            // Create sample payments: alternate paid, partially_paid, and unpaid
            if ($counter % 3 === 1) {
                // Full Payment
                $pmt = Payment::firstOrCreate(
                    ['payment_reference' => 'PAY-FIM-' . str_pad($counter, 4, '0', STR_PAD_LEFT)],
                    [
                        'payable_type' => 'App\\Models\\StudentFee',
                        'payable_id' => $studentFee->id,
                        'amount' => $baseAmount,
                        'payment_method' => 'bank_transfer',
                        'payment_date' => now()->subDays(rand(1, 15))->format('Y-m-d'),
                        'payer_name' => $student->first_name . ' ' . $student->last_name,
                        'description' => 'Full School Fee Payment',
                        'status' => 'completed',
                        'transaction_id' => 'TRX-' . rand(100000, 999999),
                    ]
                );
                $studentFee->recalculateTotals();
            } elseif ($counter % 3 === 2) {
                // Partial Payment (half)
                $half = round($baseAmount / 2, 2);
                $pmt = Payment::firstOrCreate(
                    ['payment_reference' => 'PAY-FIM-' . str_pad($counter, 4, '0', STR_PAD_LEFT)],
                    [
                        'payable_type' => 'App\\Models\\StudentFee',
                        'payable_id' => $studentFee->id,
                        'amount' => $half,
                        'payment_method' => 'cash',
                        'payment_date' => now()->subDays(rand(1, 10))->format('Y-m-d'),
                        'payer_name' => $student->first_name . ' ' . $student->last_name,
                        'description' => 'Part Payment (First Installment)',
                        'status' => 'completed',
                        'transaction_id' => 'CSH-' . rand(100000, 999999),
                    ]
                );
                $studentFee->recalculateTotals();
            }

            $counter++;
        }
    }
}
