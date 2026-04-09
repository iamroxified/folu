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

// Add Student
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $first_name = validate($_POST['first_name'] ?? '');
    $last_name = validate($_POST['last_name'] ?? '');
    $other_names = validate($_POST['other_names'] ?? '');
    $student_email = validate($_POST['student_email'] ?? '');
    $date_of_birth = validate($_POST['date_of_birth'] ?? '');
    $gender = validate($_POST['gender'] ?? '');
    $state_of_origin = validate($_POST['state_of_origin'] ?? '');
    $lga = validate($_POST['lga'] ?? '');
    $home_address = validate($_POST['home_address'] ?? '');
    $class_link = filter_input(INPUT_POST, 'class_link', FILTER_VALIDATE_INT);
    $academic_session_link = filter_input(INPUT_POST, 'academic_session_link', FILTER_VALIDATE_INT);
    $admission_date = validate($_POST['admission_date'] ?? '');
    $student_type = validate($_POST['student_type'] ?? 'day');
    $blood_group = validate($_POST['blood_group'] ?? null);
    $genotype = validate($_POST['genotype'] ?? null);
    $status = validate($_POST['status'] ?? 'active');
    $parent_name = validate($_POST['parent_name'] ?? '');
    $parent_phone = validate($_POST['parent_phone'] ?? '');
    $parent_email = validate($_POST['parent_email'] ?? '');
    $parent_relationship = validate($_POST['parent_relationship'] ?? 'guardian');

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

            // 1. Generate a unique admission number
            $admission_no = generate_student_admission_no();

            // 2. Create a user account for the student
            $username = $admission_no;
            $email = $student_email !== '' ? $student_email : strtolower(str_replace('/', '.', $admission_no)) . '@student.fimocol.edu.ng';
            $password = password_hash('password', PASSWORD_DEFAULT); // Default password
            $role = 'student';

            $user_stmt = $pdo->prepare("INSERT INTO users (username, password, email, role, status) VALUES (?, ?, ?, ?, ?)");
            $user_stmt->execute([$username, $password, $email, $role, $status]);
            $user_id = $pdo->lastInsertId();

            // Insert into database
            $stmt = $pdo->prepare(
                'INSERT INTO students (user_link, admission_no, first_name, last_name, other_names, email, date_of_birth, gender, state_of_origin, lga, home_address, class_link, academic_session_link, admission_date, student_type, blood_group, genotype, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $user_id, $admission_no, $first_name, $last_name, $other_names, $email, $date_of_birth, $gender, $state_of_origin, $lga, $home_address, $class_link, $academic_session_link, $admission_date, $student_type, $blood_group, $genotype, $status
            ]);

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
$classes = QueryDB("SELECT id, class_name, class_arm FROM classes ORDER BY class_level, class_arm")->fetchAll();
$sessions = QueryDB("SELECT id, session_name, is_current FROM academic_sessions ORDER BY is_current DESC, start_date DESC")->fetchAll();
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
                            <option value="<?php echo $session['id']; ?>" <?php echo ((int) ($session['is_current'] ?? 0) === 1) ? 'selected' : ''; ?>>
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



