<?php
// Start session

// Include database configuration and functions
require_once base_path('db/config.php');
require_once base_path('db/functions.php');
// require_once resource_path('views/admin/school_functions.php');


// Check if user is logged in
if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

if (!function_exists('admin_student_term_context')) {
    function admin_student_term_context(?int $sessionId = null): array
    {
        $academicTerm = get_current_academic_term($sessionId);
        $academicTermId = $academicTerm ? (int) $academicTerm['id'] : null;
        $currentTermId = null;

        if (schema_has_table('terms')) {
            $currentTermId = QueryDB(
                'SELECT id FROM terms WHERE is_active = 1 ORDER BY term_number ASC, id DESC LIMIT 1'
            )->fetchColumn();

            if (!$currentTermId && $academicTerm) {
                $termCode = normalize_term_code($academicTerm['term_code'] ?? ($academicTerm['term_name'] ?? ''));

                if ($termCode !== null) {
                    $currentTermId = QueryDB(
                        'SELECT id FROM terms WHERE term_number = ? ORDER BY id DESC LIMIT 1',
                        [(int) $termCode]
                    )->fetchColumn();
                }
            }
        }

        return [
            'current_term_id' => $currentTermId ? (int) $currentTermId : null,
            'academic_term_id' => $academicTermId,
        ];
    }
}

if (!function_exists('admin_build_student_insert_data')) {
    function admin_build_student_insert_data(array $studentData): array
    {
        $studentColumns = schema_table_column_details('students');
        $insertData = [];

        $setValue = static function (string $column, $value) use (&$insertData, $studentColumns): void {
            if (!isset($studentColumns[$column]) || schema_column_is_generated('students', $column)) {
                return;
            }

            $insertData[$column] = $value;
        };

        $setValue('user_link', $studentData['user_link'] ?? null);
        $setValue('admission_no', $studentData['admission_no'] ?? null);
        $setValue('student_number', $studentData['student_number'] ?? null);
        $setValue('first_name', $studentData['first_name'] ?? '');
        $setValue('last_name', $studentData['last_name'] ?? '');
        $setValue('other_names', $studentData['other_names'] ?? '');
        $setValue('email', $studentData['email'] ?? '');
        $setValue('phone', $studentData['phone'] ?? null);
        $setValue('address', $studentData['address'] ?? null);
        $setValue('home_address', $studentData['address'] ?? null);
        $setValue('date_of_birth', $studentData['date_of_birth'] ?? null);
        $setValue('gender', $studentData['gender'] ?? null);
        $setValue('enrollment_date', $studentData['enrollment_date'] ?? null);
        $setValue('admission_date', $studentData['enrollment_date'] ?? null);
        $setValue('status', $studentData['status'] ?? 'active');
        $setValue('admission_status', $studentData['admission_status'] ?? 'admitted');
        $setValue('category', $studentData['category'] ?? 'NI');
        $setValue('passport', $studentData['passport'] ?? null);
        $setValue('state_of_origin', $studentData['state_of_origin'] ?? '');
        $setValue('lga', $studentData['lga'] ?? '');
        $setValue('student_type', $studentData['student_type'] ?? 'day');
        $setValue('blood_group', $studentData['blood_group'] ?? null);
        $setValue('genotype', $studentData['genotype'] ?? null);
        $setValue('current_class_id', $studentData['current_class_id'] ?? null);
        $setValue('class_link', $studentData['class_link'] ?? null);
        $setValue('current_session_id', $studentData['current_session_id'] ?? null);
        $setValue('academic_session_link', $studentData['academic_session_link'] ?? null);
        $setValue('current_term_id', $studentData['current_term_id'] ?? null);
        $setValue('term_link', $studentData['term_link'] ?? null);
        $setValue('created_at', $studentData['timestamp'] ?? null);
        $setValue('updated_at', $studentData['timestamp'] ?? null);

        return $insertData;
    }
}

// Add Student
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $first_name = validate((string) ($_POST['first_name'] ?? ''));
    $last_name = validate((string) ($_POST['last_name'] ?? ''));
    $other_names = validate((string) ($_POST['other_names'] ?? ''));
    $student_email = validate((string) ($_POST['student_email'] ?? ''));
    $date_of_birth = validate((string) ($_POST['date_of_birth'] ?? ''));
    $gender = validate((string) ($_POST['gender'] ?? ''));
    $state_of_origin = validate((string) ($_POST['state_of_origin'] ?? ''));
    $lga = validate((string) ($_POST['lga'] ?? ''));
    $home_address = validate((string) ($_POST['home_address'] ?? ''));
    $class_link = filter_input(INPUT_POST, 'class_link', FILTER_VALIDATE_INT);
    $academic_session_link = filter_input(INPUT_POST, 'academic_session_link', FILTER_VALIDATE_INT);
    $admission_date = validate((string) ($_POST['admission_date'] ?? ''));
    $student_type = validate((string) ($_POST['student_type'] ?? 'day'));
    $blood_group = validate((string) ($_POST['blood_group'] ?? ''));
    $genotype = validate((string) ($_POST['genotype'] ?? ''));
    $status = validate((string) ($_POST['status'] ?? 'active'));
    $parent_name = validate((string) ($_POST['parent_name'] ?? ''));
    $parent_phone = validate((string) ($_POST['parent_phone'] ?? ''));
    $parent_email = validate((string) ($_POST['parent_email'] ?? ''));
    $parent_relationship = validate((string) ($_POST['parent_relationship'] ?? 'guardian'));

    // Basic validation
    if (
        empty($first_name) || empty($last_name) || empty($date_of_birth) || empty($gender) ||
        empty($class_link) || empty($academic_session_link) || empty($admission_date) ||
        empty($parent_name) || empty($parent_phone) || empty($parent_email)
    ) {
        $error = 'Please fill all required fields.';
    } elseif (!filter_var($parent_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid parent email address.';
    } elseif ($student_email !== '' && !filter_var($student_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid student email address.';
    } else {
        try {
            $pdo->beginTransaction();

            $admission_no = generate_student_admission_no();
            $student_number = schema_has_column('students', 'student_number') ? generate_student_id() : $admission_no;
            $display_name = trim($first_name . ' ' . $last_name . ' ' . $other_names);

            $username = $admission_no;
            $email = $student_email !== '' ? $student_email : strtolower(str_replace('/', '.', $admission_no)) . '@student.fimocol.edu.ng';
            $password = password_hash('password', PASSWORD_DEFAULT); // Default password
            $roleId = get_role_id_by_name('student') ?? 4;

            $existingUser = QueryDB(
                'SELECT COUNT(*) FROM users WHERE username = ? OR email = ?',
                [$username, $email]
            )->fetchColumn();

            if ((int) $existingUser > 0) {
                throw new Exception('That admission number or email address is already in use.');
            }

            if (schema_has_column('students', 'email')) {
                $existingStudentEmail = QueryDB(
                    'SELECT COUNT(*) FROM students WHERE email = ?',
                    [$email]
                )->fetchColumn();

                if ((int) $existingStudentEmail > 0) {
                    throw new Exception('That student email address is already in use.');
                }
            }

            $user_id = create_portal_user([
                'username' => $username,
                'name' => $display_name,
                'email' => $email,
                'password' => $password,
                'role_name' => 'student',
                'role_id' => $roleId,
                'status' => $status,
            ]);

            $termContext = admin_student_term_context($academic_session_link ?: null);
            $studentInsertData = admin_build_student_insert_data([
                'user_link' => $user_id,
                'admission_no' => $admission_no,
                'student_number' => $student_number,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'other_names' => $other_names,
                'email' => $email,
                'phone' => $parent_phone !== '' ? $parent_phone : null,
                'address' => $home_address !== '' ? $home_address : null,
                'date_of_birth' => $date_of_birth,
                'gender' => $gender,
                'enrollment_date' => $admission_date,
                'status' => $status,
                'admission_status' => 'admitted',
                'category' => 'NI',
                'state_of_origin' => $state_of_origin,
                'lga' => $lga,
                'student_type' => $student_type,
                'blood_group' => $blood_group !== '' ? $blood_group : null,
                'genotype' => $genotype !== '' ? $genotype : null,
                'current_class_id' => $class_link ?: null,
                'class_link' => $class_link ?: null,
                'current_session_id' => $academic_session_link ?: null,
                'academic_session_link' => $academic_session_link ?: null,
                'current_term_id' => $termContext['current_term_id'],
                'term_link' => $termContext['academic_term_id'],
                'timestamp' => date('Y-m-d H:i:s'),
            ]);

            if ($studentInsertData === []) {
                throw new Exception('Student table does not contain any writable columns for this form.');
            }

            $studentColumns = implode(', ', array_keys($studentInsertData));
            $studentPlaceholders = implode(', ', array_fill(0, count($studentInsertData), '?'));
            $stmt = $pdo->prepare("INSERT INTO students ({$studentColumns}) VALUES ({$studentPlaceholders})");
            $stmt->execute(array_values($studentInsertData));

            $student_id = (int) $pdo->lastInsertId();
            $pdo->commit();

            save_primary_parent_contact($student_id, [
                'first_name' => $parent_name,
                'last_name' => '',
                'email' => $parent_email,
                'phone' => $parent_phone,
                'relationship' => $parent_relationship,
            ]);

            $success = 'Student added successfully';

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Failed to add student: ' . $e->getMessage();
        }
    }
}

// Fetch data for dropdowns
$classes = schema_has_column('students', 'current_class_id') && schema_has_table('school_classes')
    ? QueryDB(
        "SELECT id, class_name, COALESCE(section, '') AS class_arm, grade_level AS class_level
         FROM school_classes
         WHERE status = 'active'
         ORDER BY grade_level, class_name, section"
    )->fetchAll()
    : QueryDB("SELECT id, class_name, class_arm, class_level FROM classes ORDER BY class_level, class_arm")->fetchAll();
$sessions = QueryDB("SELECT id, session_name, is_active FROM academic_sessions ORDER BY is_active DESC, start_date DESC")->fetchAll();
$states = QueryDB("SELECT id, name FROM state ORDER BY name ASC")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Add New Student</title>
  @include('admin.partials.links')
</head>

<body>
  <div class="wrapper">
    @include('admin.partials.sidebar')

    <div class="main-panel">
      @include('admin.partials.header')
      <div class="container">
        <div class="page-inner">
          <div class="d-flex align-items-left flex-column flex-md-row">
            <h2 class="text-dark pb-2 fw-bold">Add New Student</h2>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="card">
                <div class="card-header">
                  <div class="card-title">Student Details</div>
                </div>
                <div class="card-body">
                  <?php if (isset($error)): ?>
                  <div class="alert alert-danger">
                    <?php echo $error; ?>
                  </div>
                  <?php endif; ?>
                  <?php if (isset($success)): ?>
                  <div class="alert alert-success">
                    <?php echo $success; ?>
                  </div>
                  <?php endif; ?>
                  <form method="POST" action="">
                    <div class="row">
                      <div class="col-md-4">
                        <div class="form-group">
                          <label for="first_name">First Name <span class="text-danger">*</span></label>
                          <input type="text" class="form-control" name="first_name" id="first_name" required>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-group">
                          <label for="last_name">Last Name <span class="text-danger">*</span></label>
                          <input type="text" class="form-control" name="last_name" id="last_name" required>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-group">
                          <label for="other_names">Other Names</label>
                          <input type="text" class="form-control" name="other_names" id="other_names">
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-4">
                        <div class="form-group">
                          <label for="date_of_birth">Date of Birth <span class="text-danger">*</span></label>
                          <input type="date" class="form-control" name="date_of_birth" id="date_of_birth" required>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-group">
                          <label for="gender">Gender <span class="text-danger">*</span></label>
                          <select class="form-control" name="gender" id="gender" required>
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                          </select>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-group">
                          <label for="admission_date">Admission Date <span class="text-danger">*</span></label>
                          <input type="date" class="form-control" name="admission_date" id="admission_date"
                            value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="student_email">Student Email</label>
                          <input type="email" class="form-control" name="student_email" id="student_email" placeholder="Optional personal or school email">
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-group">
                                                    <label for="state_of_origin">State of Origin <span class="text-danger">*</span></label>
                                                    <select class="form-control" name="state_of_origin" id="state_of_origin" required>
                                                        <option value="">Select State</option>
                                                        <?php foreach ($states as $state): ?>
                                                            <option value="<?php echo htmlspecialchars($state['name']); ?>" data-id="<?php echo $state['id']; ?>"><?php echo htmlspecialchars($state['name']); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group">
                                                    <label for="lga">LGA <span class="text-danger">*</span></label>
                                                    <select class="form-control" name="lga" id="lga" required>
                                                        <option value="">Select State First</option>
                                                        <!-- LGAs will be populated by JavaScript -->
                                                    </select>
                        </div>
                      </div>
                    </div>
                    <div class="form-group">
                      <label for="home_address">Home Address</label>
                      <textarea class="form-control" name="home_address" id="home_address" rows="3"></textarea>
                    </div>
                    <hr>
                    <h4 class="mb-3">Parent / Guardian Contact</h4>
                    <div class="row">
                      <div class="col-md-4">
                        <div class="form-group">
                          <label for="parent_name">Parent / Guardian Name <span class="text-danger">*</span></label>
                          <input type="text" class="form-control" name="parent_name" id="parent_name" required>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-group">
                          <label for="parent_phone">Parent Phone <span class="text-danger">*</span></label>
                          <input type="text" class="form-control" name="parent_phone" id="parent_phone" required>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-group">
                          <label for="parent_email">Parent Email <span class="text-danger">*</span></label>
                          <input type="email" class="form-control" name="parent_email" id="parent_email" required>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-4">
                        <div class="form-group">
                          <label for="parent_relationship">Relationship</label>
                          <select class="form-control" name="parent_relationship" id="parent_relationship">
                            <option value="father">Father</option>
                            <option value="mother">Mother</option>
                            <option value="guardian">Guardian</option>
                            <option value="other">Other</option>
                          </select>
                        </div>
                      </div>
                    </div>
                    <hr>
                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="class_link">Assign to Class <span class="text-danger">*</span></label>
                          <select class="form-control" name="class_link" id="class_link" required>
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['id']; ?>">
                              <?php echo htmlspecialchars($class['class_name'] . ' ' . $class['class_arm']); ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="academic_session_link">Academic Session <span class="text-danger">*</span></label>
                          <select class="form-control" name="academic_session_link" id="academic_session_link" required>
                            <?php foreach ($sessions as $session): ?>
                            <option value="<?php echo $session['id']; ?>" <?php echo ((int) ($session['is_active'] ?? 0) === 1) ? 'selected' : ''; ?>>
                              <?php echo htmlspecialchars($session['session_name']); ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-4">
                        <div class="form-group">
                          <label for="student_type">Student Type</label>
                          <select class="form-control" name="student_type" id="student_type">
                            <option value="day">Day</option>
                            <option value="boarding">Boarding</option>
                          </select>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-group">
                          <label for="blood_group">Blood Group</label>
                          <select class="form-control" name="blood_group" id="blood_group">
                            <option value="">Select</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                          </select>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-group">
                          <label for="genotype">Genotype</label>
                          <select class="form-control" name="genotype" id="genotype">
                            <option value="">Select</option>
                            <option value="AA">AA</option>
                            <option value="AS">AS</option>
                            <option value="SS">SS</option>
                            <option value="AC">AC</option>
                            <option value="SC">SC</option>
                          </select>
                        </div>
                      </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Student</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      @include('admin.partials.footer')
    </div>

  </div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const stateSelect = document.getElementById("state_of_origin");
        const lgaSelect = document.getElementById("lga");

        stateSelect.addEventListener("change", function() {
            const selectedOption = this.options[this.selectedIndex];
            const stateId = selectedOption.getAttribute('data-id');

            // Reset and disable LGA dropdown
            lgaSelect.innerHTML = '<option value="">Loading...</option>';
            lgaSelect.disabled = true;

            if (stateId) {
                // Fetch LGAs for the selected state
                fetch(`fetch_lgas.php?state_id=${stateId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(lgas => {
                        lgaSelect.innerHTML = '<option value="">Select LGA</option>';
                        lgas.forEach(lga => {
                            lgaSelect.innerHTML += `<option value="${lga.name}">${lga.name}</option>`;
                        });
                        lgaSelect.disabled = false; // Enable LGA dropdown
                    })
                    .catch(error => {
                        console.error('Error fetching LGAs:', error);
                        lgaSelect.innerHTML = '<option value="">Could not load LGAs</option>';
                    });
            } else {
                lgaSelect.innerHTML = '<option value="">Select State First</option>';
            }
        });
    });
</script>

</body>

</html>


