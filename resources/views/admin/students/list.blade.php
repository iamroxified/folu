<?php
// Start session

// Include database configuration and functions
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

// Check if user is logged in
if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
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
                        $sessions = QueryDB("SELECT id, session_name FROM academic_sessions ORDER BY session_name DESC")->fetchAll();
                        foreach ($sessions as $session): ?>
                          <option value="<?php echo $session['id']; ?>"
                            <?php echo ($session_filter == $session['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($session['session_name']); ?>
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
                    <table id="add-row" class="display table table-striped table-hover datatable">
                      <thead>
                        <tr>
                          <th style="width: 50px;">S/N</th>
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
                        <?php $sn = 1; foreach ($students as $student): ?>
                        <tr>
                          <td><?php echo $sn++; ?></td>
                          <td><?php echo htmlspecialchars((string) ($student['admission_no'] ?? '')); ?></td>
                          <td><?php echo htmlspecialchars(trim(($student['first_name'] ?? '') . ' ' . ($student['other_names'] ?? '') . ' ' . ($student['last_name'] ?? ''))); ?></td>
                          <td><?php echo htmlspecialchars(trim(($student['class_name'] ?? '') . ' ' . ($student['class_arm'] ?? ''))); ?></td>
                          <td>
                            <?php
                              $sessionTerm = trim((string) ($student['session_term'] ?? ''));
                              echo htmlspecialchars((string) ($student['session_name'] ?? 'N/A'));
                              if ($sessionTerm !== '') {
                                  echo ' (' . htmlspecialchars($sessionTerm) . ')';
                              }
                            ?>
                          </td>
                          <td><?php echo htmlspecialchars((string) ($student['email'] ?? 'N/A')); ?></td>
                          <td>
                            <span class="badge bg-<?php echo ($student['status'] ?? '') === 'active' ? 'success' : 'warning'; ?>">
                              <?php echo ucfirst((string) ($student['status'] ?? 'Active')); ?>
                            </span>
                          </td>
                          <td>
                            <a href="view_students.php?id=<?php echo $student['id']; ?>" class="btn btn-info btn-sm">View</a>
                            <a href="edit_students.php?id=<?php echo $student['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="delete_students.php?id=<?php echo $student['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this student record?')">Delete</a>
                          </td>
                        </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      @include('admin.partials.footer')
      <script>
        $(document).ready(function () {
          if ($.fn.DataTable) {
            $.fn.dataTable.ext.errMode = 'none';
            if (!$.fn.DataTable.isDataTable('#add-row')) {
              $('#add-row').DataTable({
                retrieve: true,
                pageLength: 10,
                responsive: true
              });
            }
          }
        });
      </script>

</body>

</html>
<?php
function getStudents($searchParam = '', $class_filter = '', $session_filter = '') {
    $sessionColumn = schema_has_column('students', 'current_session_id')
        ? 'current_session_id'
        : (schema_has_column('students', 'academic_session_link') ? 'academic_session_link' : null);
    $termColumn = schema_has_column('students', 'current_term_id')
        ? 'current_term_id'
        : (schema_has_column('students', 'term_link') ? 'term_link' : null);
    $termJoin = schema_has_table('terms')
        ? 'LEFT JOIN terms ON students.' . $termColumn . ' = terms.id'
        : (schema_has_table('academic_terms')
            ? 'LEFT JOIN academic_terms ON students.' . $termColumn . ' = academic_terms.id'
            : '');
    $termSelect = schema_has_table('terms')
        ? 'terms.term_name'
        : (schema_has_table('academic_terms') ? 'academic_terms.term_name' : 'NULL');

    $sql = "SELECT
                students.*,
                classes.class_name,
                classes.class_arm,
                academic_sessions.session_name,
                {$termSelect} AS session_term
            FROM students
            LEFT JOIN classes ON students.class_link = classes.id
            LEFT JOIN academic_sessions ON students.{$sessionColumn} = academic_sessions.id
            {$termJoin}
            WHERE (students.first_name LIKE ? OR students.last_name LIKE ?)";

    $params = [$searchParam, $searchParam];

    if (!empty($class_filter)) {
        $sql .= " AND students.class_link = ?";
        $params[] = $class_filter;
    }

    if (!empty($session_filter)) {
        $sql .= " AND students.{$sessionColumn} = ?";
        $params[] = $session_filter;
    }

    $sql .= " ORDER BY students.id DESC";

    return QueryDB($sql, $params)->fetchAll();
}
?>


