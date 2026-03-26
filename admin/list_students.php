<?php
// Start session
session_start();

// Include database configuration and functions
require '../db/config.php';
require '../db/functions.php';

// Check if user is logged in
if (!isset($_SESSION['adid'])) {
    header('location:../login.php');
    exit;
}

// Fetch students data with search
$search = $_GET['search'] ?? '';
$searchParam = '%' . $search . '%';
$class_filter = $_GET['class_filter'] ?? '';
$session_filter = $_GET['session_filter'] ?? '';

$students = getStudents($searchParam, $class_filter, $session_filter);

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Students List</title>
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
            <h2 class="text-dark pb-2 fw-bold">Students List</h2>

          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="card">
                <div class="card-header">
                  <div class="card-title">All Students</div>
                </div>
                <div class="container ml-md-auto py-2 py-md-0">
                  <form method="GET" action="list_students.php" class="form-inline">

                    <div class="row">
                      <div class="form-group col-md-4">
                        <select class="form-control mr-2" name="class_filter">
                          <option value="">All Classes</option>
                          <?php
                        $classes = QueryDB("SELECT id, class_name, class_arm FROM classes ORDER BY class_name")->fetchAll();
                        foreach ($classes as $class): ?>
                          <option value="<?php echo $class['id']; ?>"
                            <?php echo ($class_filter == $class['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($class['class_name'] . ' ' . $class['class_arm']); ?>
                          </option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="form-group col-md-4">
                        <select class="form-control mr-2" name="session_filter">
                          <option value="">All Sessions</option>
                          <?php
                        $sessions = QueryDB("SELECT id, session_name, session_term FROM academic_sessions ORDER BY session_name DESC")->fetchAll();
                        foreach ($sessions as $session): ?>
                          <option value="<?php echo $session['id']; ?>"
                            <?php echo ($session_filter == $session['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($session['session_name']).' (' . $session['session_term'].' Term)'; ?>
                          </option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="form-group col-md-4">
                        <button type="submit" class="btn btn-primary">Filter</button>
                      </div>
                    </div>
                  </form>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table id="add-row" class="display table  table-hover">
                      <thead>
                        <tr>
                          <th>SN</th>
                          <th>Admission No</th>
                          <th>Full Name</th>
                          <th>Class</th>
                          <th>Session</th>
                       
                          <th>Email</th>
                          <th>Status</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php $sn=1; foreach ($students as $student): ?>
                        <tr>
                          <td><?php echo $sn; ?></td>
                          <td><?php echo htmlspecialchars($student['admission_no']); ?></td>
                          <td><?php echo htmlspecialchars($student['first_name']).' '.htmlspecialchars($student['last_name']); ?></td>
                          <td><?php echo htmlspecialchars($student['class_name'] . ' ' . $student['class_arm']); ?></td>
                          <td> <?php echo htmlspecialchars($student['session_name']).' (' . $student['session_term'].' Term)'; ?></td>
                          
                          <td><?php echo htmlspecialchars($student['email']); ?></td>
                          <td><?php echo $student['status']; ?></td>
                          <td>
                            <a href="edit_students.php?id=<?php echo $student['id']; ?>"
                              class="btn btn-warning btn-sm">Edit</a>
                            <a href="view_students.php?id=<?php echo $student['id']; ?>"
                              class="btn btn-info btn-sm">View</a>
                            <a href="delete_students.php?id=<?php echo $student['id']; ?>"
                              class="btn btn-danger btn-sm">Delete</a>
                          </td>
                        </tr>
                        <?php $sn++; endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <?php include('nav/footer.php'); ?>
      <script>
        function copyToClipboard2() {
          const copyInput = document.getElementById("copyText2");
          copyInput.select();
          copyInput.setSelectionRange(0, 99999); // for mobile
          document.execCommand("copy");
          alert("Copied: " + copyInput.value);
        }

        function copyToClipboard() {
          const copyInput = document.getElementById("copyText");
          copyInput.select();
          copyInput.setSelectionRange(0, 99999); // for mobile
          document.execCommand("copy");
          alert("Copied: " + copyInput.value);
        }
      </script>

      <script>
        $(document).ready(function () {
          $("#basic-datatables").DataTable({});

          $("#multi-filter-select").DataTable({
            pageLength: 5,
            initComplete: function () {
              this.api()
                .columns()
                .every(function () {
                  var column = this;
                  var select = $(
                      '<select class="form-select"><option value=""></option></select>'
                    )
                    .appendTo($(column.footer()).empty())
                    .on("change", function () {
                      var val = $.fn.dataTable.util.escapeRegex($(this).val());

                      column
                        .search(val ? "^" + val + "$" : "", true, false)
                        .draw();
                    });

                  column
                    .data()
                    .unique()
                    .sort()
                    .each(function (d, j) {
                      select.append(
                        '<option value="' + d + '">' + d + "</option>"
                      );
                    });
                });
            },
          });

          // Add Row
          $("#add-row").DataTable({
            pageLength: 10,
          });


        });
      </script>

</body>

</html>
<?php
function getStudents($searchParam = '', $class_filter = '', $session_filter = '') {
    global $pdo;

    $sql = "SELECT 
                students.*, 
                classes.class_name, classes.class_arm, 
                academic_sessions.session_name, 
                academic_sessions.session_term
            FROM students
            JOIN classes ON students.class_link = classes.id
            JOIN academic_sessions ON students.academic_session_link = academic_sessions.id
            WHERE (students.first_name LIKE ? OR students.last_name LIKE ?) ORDER BY students.id DESC";

    $params = [$searchParam, $searchParam];

    if (!empty($class_filter)) {
        $sql .= " AND students.class_link = ?";
        $params[] = $class_filter;
    }

    if (!empty($session_filter)) {
        $sql .= " AND students.academic_session_link = ?";
        $params[] = $session_filter;
    }

    return QueryDB($sql, $params)->fetchAll();
}
?>