<?php
// Start session
session_start();

// Include database configuration and functions
require '../db/config.php';
require '../db/functions.php';
require 'school_functions.php';


// Check if user is logged in
if (!isset($_SESSION['adid'])) {
    header('location:../../login.php');
    exit;
}

// Fetch student information
$studentId = $_GET['id'] ?? null;
if ($studentId) {
    $student_stmt = $pdo->prepare("SELECT s.*, u.status as user_status FROM students s JOIN users u ON s.user_link = u.id WHERE s.id = ?");
    $student_stmt->execute([$studentId]);
    $student = $student_stmt->fetch();

    if (!$student) {
        die('Student not found');
    }
} else {
    die('Student ID is required');
}
// Update student
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $first_name = validate($_POST['first_name'] ?? '');
    $last_name = validate($_POST['last_name'] ?? '');
    $other_names = validate($_POST['other_names'] ?? '');
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

    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($date_of_birth) || empty($gender) || empty($class_link) || empty($academic_session_link) || empty($admission_date)) {
        $error = 'Please fill all required fields.';
    } else {
        try {
            $pdo->beginTransaction();

            // Update students table
            $stmt = $pdo->prepare(
                'UPDATE students SET 
                    first_name = ?, last_name = ?, other_names = ?, date_of_birth = ?, gender = ?, 
                    state_of_origin = ?, lga = ?, home_address = ?, class_link = ?, academic_session_link = ?, 
                    admission_date = ?, student_type = ?, blood_group = ?, genotype = ?, status = ?
                 WHERE id = ?'
            );
            $stmt->execute([
                $first_name, $last_name, $other_names, $date_of_birth, $gender, 
                $state_of_origin, $lga, $home_address, $class_link, $academic_session_link, 
                $admission_date, $student_type, $blood_group, $genotype, $status,
                $studentId
            ]);

            // Also update the status in the users table to keep it in sync
            $user_stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
            $user_stmt->execute([$status, $student['user_link']]);

            $pdo->commit();
            $success = 'Student updated successfully';

            // Refresh student data
            $student_stmt->execute([$studentId]);
            $student = $student_stmt->fetch();

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Failed to update student: ' . $e->getMessage();
        }
    }
}

// Fetch data for dropdowns
$classes = QueryDB("SELECT id, class_name, class_arm FROM classes ORDER BY class_level, class_arm")->fetchAll();
$sessions = QueryDB("SELECT id, session_name, session_term FROM academic_sessions ORDER BY start_date DESC")->fetchAll();
$states = QueryDB("SELECT id, name FROM state ORDER BY name ASC")->fetchAll();

// Fetch LGAs for the student's current state to pre-populate the LGA dropdown
$lgas = [];
if (!empty($student['state_of_origin'])) {
    $state_info_stmt = $pdo->prepare("SELECT id FROM state WHERE name = ?");
    $state_info_stmt->execute([$student['state_of_origin']]);
    $state_info = $state_info_stmt->fetch();
    if ($state_info) {
        $lga_stmt = $pdo->prepare("SELECT name FROM local_governments WHERE state_id = ? ORDER BY name ASC");
        $lga_stmt->execute([$state_info['id']]);
        $lgas = $lga_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Edit Student - <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></title>
  <?php include('nav/links.php'); ?>
</head>

<body>
  <div class="wrapper">
    <?php include('nav/sidebar.php'); ?>

    <div class="main-panel">
      <?php include('nav/header.php'); ?>
      <div class="container">
        <div class="page-inner">
          <div class="d-flex align-items-left flex-column flex-md-row">
            <h2 class="text-dark pb-2 fw-bold">Edit Student:
              <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h2>
            <div class="ms-md-auto py-2 py-md-0"><a href="list_students.php" class="btn btn-secondary btn-round">Back to
                List</a></div>

          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="card">
                <div class="card-header">
                  <div class="card-title">Edit Student Details</div>
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
                          <input type="text" class="form-control" name="first_name" id="first_name"
                            value="<?php echo htmlspecialchars($student['first_name']); ?>" required>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-group">
                          <label for="last_name">Last Name <span class="text-danger">*</span></label>
                          <input type="text" class="form-control" name="last_name" id="last_name"
                            value="<?php echo htmlspecialchars($student['last_name']); ?>" required>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-group">
                          <label for="other_names">Other Names</label>
                          <input type="text" class="form-control" name="other_names" id="other_names"
                            value="<?php echo htmlspecialchars($student['other_names']); ?>">
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-4">
                        <div class="form-group">
                          <label for="date_of_birth">Date of Birth <span class="text-danger">*</span></label>
                          <input type="date" class="form-control" name="date_of_birth" id="date_of_birth"
                            value="<?php echo htmlspecialchars($student['date_of_birth']); ?>" required>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-group">
                          <label for="gender">Gender <span class="text-danger">*</span></label>
                          <select class="form-control" name="gender" id="gender" required>
                            <option value="">Select Gender</option>
                            <option value="male" <?php echo ($student['gender'] == 'male') ? 'selected' : ''; ?>>Male
                            </option>
                            <option value="female" <?php echo ($student['gender'] == 'female') ? 'selected' : ''; ?>>
                              Female</option>
                          </select>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-group">
                          <label for="admission_date">Admission Date <span class="text-danger">*</span></label>
                          <input type="date" class="form-control" name="admission_date" id="admission_date"
                            value="<?php echo htmlspecialchars($student['admission_date']); ?>" required>
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
                            <option value="<?php echo htmlspecialchars($state['name']); ?>"
                              data-id="<?php echo $state['id']; ?>"
                              <?php echo ($student['state_of_origin'] == $state['name']) ? 'selected' : ''; ?>>
                              <?php echo htmlspecialchars($state['name']); ?>
                            </option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="lga">LGA <span class="text-danger">*</span></label>
                          <select class="form-control" name="lga" id="lga" required>
                            <option value="">Select State First</option>
                            <?php foreach ($lgas as $lga_item): ?>
                            <option value="<?php echo htmlspecialchars($lga_item['name']); ?>"
                              <?php echo ($student['lga'] == $lga_item['name']) ? 'selected' : ''; ?>>
                              <?php echo htmlspecialchars($lga_item['name']); ?>
                            </option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="form-group">
                      <label for="home_address">Home Address</label>
                      <textarea class="form-control" name="home_address" id="home_address"
                        rows="3"><?php echo htmlspecialchars($student['home_address']); ?></textarea>
                    </div>
                    <hr>
                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="class_link">Assign to Class <span class="text-danger">*</span></label>
                          <select class="form-control" name="class_link" id="class_link" required>
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['id']; ?>"
                              <?php echo ($student['class_link'] == $class['id']) ? 'selected' : ''; ?>>
                              <?php echo htmlspecialchars($class['class_name'] . ' ' . $class['class_arm']); ?>
                            </option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="academic_session_link">Academic Session <span class="text-danger">*</span></label>
                          <select class="form-control" name="academic_session_link" id="academic_session_link" required>
                            <?php foreach ($sessions as $session): ?>
                            <option value="<?php echo $session['id']; ?>"
                              <?php echo ($student['academic_session_link'] == $session['id']) ? 'selected' : ''; ?>>
                              <?php echo htmlspecialchars($session['session_name']).' ('.$session['session_term'].' Term)'; ?>
                            </option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-3">
                        <div class="form-group">
                          <label for="student_type">Student Type</label>
                          <select class="form-control" name="student_type" id="student_type">
                            <option value="day" <?php echo ($student['student_type'] == 'day') ? 'selected' : ''; ?>>Day
                            </option>
                            <option value="boarding"
                              <?php echo ($student['student_type'] == 'boarding') ? 'selected' : ''; ?>>Boarding
                            </option>
                          </select>
                        </div>
                      </div>
                      <div class="col-md-3">
                        <div class="form-group">
                          <label for="blood_group">Blood Group</label>
                          <select class="form-control" name="blood_group" id="blood_group">
                            <option value="">Select</option>
                            <option value="A+" <?php echo ($student['blood_group'] == 'A+') ? 'selected' : ''; ?>>A+
                            </option>
                            <option value="A-" <?php echo ($student['blood_group'] == 'A-') ? 'selected' : ''; ?>>A-
                            </option>
                            <option value="B+" <?php echo ($student['blood_group'] == 'B+') ? 'selected' : ''; ?>>B+
                            </option>
                            <option value="B-" <?php echo ($student['blood_group'] == 'B-') ? 'selected' : ''; ?>>B-
                            </option>
                            <option value="AB+" <?php echo ($student['blood_group'] == 'AB+') ? 'selected' : ''; ?>>AB+
                            </option>
                            <option value="AB-" <?php echo ($student['blood_group'] == 'AB-') ? 'selected' : ''; ?>>AB-
                            </option>
                            <option value="O+" <?php echo ($student['blood_group'] == 'O+') ? 'selected' : ''; ?>>O+
                            </option>
                            <option value="O-" <?php echo ($student['blood_group'] == 'O-') ? 'selected' : ''; ?>>O-
                            </option>
                          </select>
                        </div>
                      </div>
                      <div class="col-md-3">
                        <div class="form-group">
                          <label for="genotype">Genotype</label>
                          <select class="form-control" name="genotype" id="genotype">
                            <option value="">Select</option>
                            <option value="AA" <?php echo ($student['genotype'] == 'AA') ? 'selected' : ''; ?>>AA
                            </option>
                            <option value="AS" <?php echo ($student['genotype'] == 'AS') ? 'selected' : ''; ?>>AS
                            </option>
                            <option value="SS" <?php echo ($student['genotype'] == 'SS') ? 'selected' : ''; ?>>SS
                            </option>
                            <option value="AC" <?php echo ($student['genotype'] == 'AC') ? 'selected' : ''; ?>>AC
                            </option>
                            <option value="SC" <?php echo ($student['genotype'] == 'SC') ? 'selected' : ''; ?>>SC
                            </option>
                          </select>
                        </div>
                      </div>
                      <div class="col-md-3">
                        <div class="form-group">
                          <label for="status">Status</label>
                          <select class="form-control" name="status" id="status" required>
                            <option value="active" <?php if ($student['status'] === 'active') echo 'selected'; ?>>Active
                            </option>
                            <option value="inactive" <?php if ($student['status'] === 'inactive') echo 'selected'; ?>>
                              Inactive</option>
                            <option value="graduated" <?php if ($student['status'] === 'graduated') echo 'selected'; ?>>
                              Graduated</option>
                            <option value="transferred"
                              <?php if ($student['status'] === 'transferred') echo 'selected'; ?>>Transferred</option>
                            <option value="expelled" <?php if ($student['status'] === 'expelled') echo 'selected'; ?>>
                              Expelled</option>
                          </select>
                        </div>
                      </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Student</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php include('nav/footer.php'); ?>
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const stateSelect = document.getElementById("state_of_origin");
      const lgaSelect = document.getElementById("lga");

      stateSelect.addEventListener("change", function () {
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