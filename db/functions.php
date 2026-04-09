

<?php
 // $date = date('D, dS F Y @ H:i:s A');
///////////////DACOMSOTAL ///////////////////
 ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



function QueryDB($sql, $params = []) {
  global $pdo; // assumes $pdo is your PDO connection

  if (!empty($params)) {
      $stmt = $pdo->prepare($sql);
      $stmt->execute($params);
      return $stmt;
  } else {
      return $pdo->query($sql);
  }
}

require_once __DIR__ . '/school_portal_helpers.php';

function get_code(){

  global $conn;
  $alphabets ="ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvvxyz";
  $shuffled = str_shuffle($alphabets);
  $serials = substr($shuffled, 0,7).rand(100,999);
  $final = str_shuffle($serials);
  return $final;
  
}

function d_code(){

  global $conn;
  $alphabets ="ABCDEFGHJKLMNPQRSTUVWXYZ23456789";
  $shuffled = str_shuffle($alphabets);
  $serials = substr($shuffled, 0,5).rand(100,999);
  $final = str_shuffle($serials);
  return $final; 
}

function ans_code(){

  global $conn;
  $alphabets ="ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvvxyz";
  $shuffled = str_shuffle($alphabets);
  $serials = substr($shuffled, 0,2).rand(100,999);
  $final = str_shuffle($serials);
  return $final;
}

function author_code(){

  global $conn;
  
  $alphabets ="ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvvxyz";
  
  $shuffled = str_shuffle($alphabets);
  
  $serials = substr($shuffled, 0,5).rand(100,999);
  
  return $serials;
  
}

function validate($value){
  $value = trim($value);
  $value = stripslashes($value);
  $value = htmlspecialchars($value);
  $value = str_replace('"','&quot;', $value);
  $value = str_replace("'",'&apos;', $value);
  return $value;
}

function linker($value){
  $value = validate($value);
  $value = str_replace (' ','_',$value);
  $value = str_replace (" ","_",$value);
  $value = str_replace ("-","_",$value);
  $value = str_replace (".","_",$value);
  $value = str_replace ("/","_",$value);
  $value = str_replace ("'","",$value);
  $value = str_replace ("&","",$value);
  $value = str_replace ("&apos;","",$value);
  $value = str_replace ("&quot;","",$value);
  $value = strtolower ($value);
  return $value;

}

function code_pics($count){
  $alphabets ='ABCDEFGHIJKLMNOPQRSTUVWXYZ'; $rCode = rand(10,99);
  $class_unique = rand(10,99).substr(str_shuffle($alphabets),0,$count).$rCode;
  return $class_unique;
}

function _greetin(){
  date_default_timezone_set('Africa/lagos');



// 24-hour format of an hour without leading zeros (0 through 23)

  $Hour = date('G');
  if ( $Hour >= 1 && $Hour <= 11 ) {
    $salute = 'Good Morning   ';

  } else if ( $Hour >= 12 && $Hour <= 16 ) {
    $salute = 'Good Afternoon  ';

  } else if ( $Hour >= 17 || $Hour <= 22 ) {
    $salute = 'Good Evening   ';
  }
  else if ( $Hour >= 23 || $Hour <= 24 ) {
    $salute = 'Keeping Late Night?   ';
  }
  return $salute;

}

function get_time_ago( $time )

{

  $time_difference = time() - $time;

  if( $time_difference < 1 ) { return 'less than 1 second ago'; }

  $condition = array( 12 * 30 * 24 * 60 * 60 =>  'year',

    30 * 24 * 60 * 60       =>  'month',

    24 * 60 * 60            =>  'day',

    60 * 60                 =>  'hour',

    60                      =>  'minute',

    1                       =>  'second'

  );



  foreach( $condition as $secs => $str )

  {

    $d = $time_difference / $secs;

    if( $d >= 1 )

    {

      $t = round( $d );

      return $t . ' ' . $str . ( $t > 1 ? 's' : '' ) . ' ago';

    }

  }

}

///////////// FUNCTIONS FOR BOOKS//////////////////////

/////////////////////////////////////////////////////


 //NORMAL USERS LOGGEN IN ON THE PORTAL//

 function username($mid){
  $get =  QueryDB("SELECT lname, fname, mname from users where (username ='$mid' or bmid ='$mid') ");
  $getter = $get->fetch(PDO::FETCH_ASSOC);
  return $getter['lname'].' '.$getter['fname'].' '.$getter['mname'];
}

function ausername($user_id) {
    try {
        global $pdo;
        $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['username'] : '';
    } catch (Exception $e) {
        return '';
    }
}

 function user_bmid($mid){
  $get =  QueryDB("SELECT bmid from users where username ='$mid'  ");
  $getter = $get->fetch(PDO::FETCH_ASSOC);
  return $getter['bmid'];
}

 function fimocol_username($mid){
  $get =  QueryDB("SELECT username from users where bmid ='$mid' ");
  $getter = $get->fetch(PDO::FETCH_ASSOC);
  return $getter['username'];
}

 function get_country_code($mid){
  $get =  QueryDB("SELECT sortname from countries where id ='$mid' ");
  $getter = $get->fetch(PDO::FETCH_ASSOC);
  return $getter['sortname'];
}


function get_user_details($email){
  $get = QueryDB("SELECT * FROM users WHERE username = '$email' ");
  return $get->fetch(PDO::FETCH_ASSOC);
}




function all_users(){
  return QueryDB("SELECT COUNT(*) FROM users ")->fetchColumn();
}

function all_students(){
  return QueryDB("SELECT COUNT(*) FROM students ")->fetchColumn();
}

function all_staff(){
  return QueryDB("SELECT COUNT(*) FROM staff ")->fetchColumn();
}

function generate_student_admission_no() {
    global $pdo;
    
    $year = date('Y');
    $prefix = "FIMOCOL/{$year}/";

    // Find the last admission number for the current year
    $stmt = $pdo->prepare("SELECT admission_no FROM students WHERE admission_no LIKE ? ORDER BY admission_no DESC LIMIT 1");
    $stmt->execute([$prefix . '%']);
    $last_admission_no = $stmt->fetchColumn();

    if ($last_admission_no) {
        // Extract the numeric part and increment it
        $last_number_str = substr($last_admission_no, strlen($prefix));
        $new_number = intval($last_number_str) + 1;
    } else {
        // If no admission number for this year, start from 1
        $new_number = 1;
    }

    // Format the new number with leading zeros. The user example FIMOCOL/2025/001 has 3 digits.
    $new_admission_no = $prefix . str_pad($new_number, 3, '0', STR_PAD_LEFT);

    return $new_admission_no;
}


function lan_house_count($id){
  return QueryDB("SELECT COUNT(*) FROM houses where landlord_id='$id' ")->fetchColumn();
}

function get_username($id){
  $get = QueryDB("SELECT name FROM users WHERE id = '$id' ");
  $getter = $get->fetch(PDO::FETCH_ASSOC);
  return $getter['name'];
}

function total_amount($id){
  $get = QueryDB("SELECT SUM(COALESCE(amount, 0)) AS total_amount FROM applications where tenant_id='$id' ");
  $getter = $get->fetch(PDO::FETCH_ASSOC);
  return $getter['total_amount'] ?? 0;
}

function total_rentamount($id){
  $get = QueryDB("SELECT SUM(COALESCE(amount, 0)) AS total_amount FROM applications where house_id='$id' and status='approved' ");
  $getter = $get->fetch(PDO::FETCH_ASSOC);
  return $getter['total_amount'] ?? 0;
}

function get_house_id($id){
  $get = QueryDB("SELECT id  FROM houses where landlord_id='$id' ");
  $getter = $get->fetch(PDO::FETCH_ASSOC);
  return $getter['id'] ?? 0;
}

function get_payment_details($email){
  $get = QueryDB("SELECT * FROM payment WHERE pay_user = '$email' ");
  return $get->fetch(PDO::FETCH_ASSOC);
  
}


function pay_time($email){
  $get = QueryDB("SELECT confirm_time from payment where pay_user ='$email' ");
  $getter = $get->fetch(PDO::FETCH_ASSOC);
  return $getter['confirm_time'];
}


function get_user_name_from_code($email){
  $get = QueryDB("SELECT fname from f_users where ucode ='$email' ");
  $getter = $get->fetch(PDO::FETCH_ASSOC);
  return $getter['fname'];
}

function get_email_from_code($email)
{
  $get = QueryDB("SELECT email from f_users where track='$email' ");
  $getter = $get->fetch(PDO::FETCH_ASSOC);
  return $getter['email'];
}

function get_user_photo_from_code($email){
  $get = QueryDB("SELECT passport from f_users where ucode ='$email' ");
  $getter = $get->fetch(PDO::FETCH_ASSOC);
  return $getter['passport'];
}

function get_fname($code){
  $get = QueryDB("SELECT fname from f_users where ucode ='$code' ");
  $getter = $get->fetch(PDO::FETCH_ASSOC);
  return $getter['fname'];
}


function get_track_name($code){
  $get = QueryDB("SELECT track_name from tracks where track_id ='$code' ");
  $getter = $get->fetch(PDO::FETCH_ASSOC);
  return $getter['track_name'];
}

/**
 * School Management Functions
 * Additional functions for FIMOCOL school management system
 */

/**
 * Get total number of students
 * @return int
 */
function total_students() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM students");
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error getting total students: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get total number of teachers/staff
 * @return int
 */
function total_teachers() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM teachers");
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error getting total teachers: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get total number of subjects (courses)
 * @return int
 */
function total_courses() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM subjects");
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error getting total subjects: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get total fee collections
 * @return float
 */
function total_fee_collections() {
    global $pdo;
    try {
        if (schema_has_table('student_payments')) {
            $stmt = $pdo->query("SELECT SUM(amount_paid) FROM student_payments");
        } else {
            $stmt = $pdo->query("SELECT SUM(amount) FROM fees WHERE status = 'paid'");
        }
        return $stmt->fetchColumn() ?? 0;
    } catch (PDOException $e) {
        error_log("Error getting total fee collections: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get pending fees
 * @return float
 */
function pending_fees() {
    global $pdo;
    try {
        if (schema_has_table('student_fees')) {
            $stmt = $pdo->query("SELECT SUM(balance) FROM student_fees WHERE balance > 0");
        } else {
            $stmt = $pdo->query("SELECT SUM(amount) FROM fees WHERE status = 'pending'");
        }
        return $stmt->fetchColumn() ?? 0;
    } catch (PDOException $e) {
        error_log("Error getting pending fees: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get recent students
 * @param int $limit
 * @return array
 */
function recent_students($limit = 5) {
    global $pdo;
    $limit = max(1, (int) $limit);
    $stmt = $pdo->query("SELECT * FROM students ORDER BY created_at DESC LIMIT {$limit}");
    return $stmt->fetchAll();
}

/**
 * Get students by class
 * @param string $class
 * @return array
 */
function students_by_class($class) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT s.* FROM students s 
                          JOIN student_course_enrollments sce ON s.id = sce.student_id 
                          JOIN courses c ON sce.course_id = c.id 
                          WHERE c.course_name = ?");
    $stmt->execute([$class]);
    return $stmt->fetchAll();
}

/**
 * Get attendance percentage for a student
 * @param int $student_id
 * @return float
 */
function student_attendance_percentage($student_id) {
    global $pdo;
    $studentColumn = schema_has_column('attendance', 'student_link') ? 'student_link' : 'student_id';
    
    try {
        // Get total attendance records
        $total_stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE {$studentColumn} = ?");
        $total_stmt->execute([$student_id]);
        $total = $total_stmt->fetchColumn();
        
        if ($total == 0) return 0;
        
        // Get present records
        $present_stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE {$studentColumn} = ? AND status = 'present'");
        $present_stmt->execute([$student_id]);
        $present = $present_stmt->fetchColumn();
        
        return ($present / $total) * 100;
    } catch (PDOException $e) {
        error_log("Error getting student attendance percentage: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get student grade average
 * @param int $student_id
 * @return float
 */
function student_grade_average($student_id) {
    global $pdo;
    $studentColumn = schema_has_column('grades', 'student_link') ? 'student_link' : 'student_id';
    try {
        $stmt = $pdo->prepare("SELECT AVG(score) FROM grades WHERE {$studentColumn} = ?");
        $stmt->execute([$student_id]);
        return $stmt->fetchColumn() ?? 0;
    } catch (PDOException $e) {
        error_log("Error getting student grade average: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get overdue fees count
 * @return int
 */
function overdue_fees_count() {
    global $pdo;
    try {
        if (schema_has_table('student_fees')) {
            $stmt = $pdo->query("SELECT COUNT(*) FROM student_fees WHERE status = 'overdue' OR balance > 0");
        } else {
            $stmt = $pdo->query("SELECT COUNT(*) FROM fees WHERE status = 'overdue'");
        }
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error getting overdue fees count: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get today's attendance count
 * @return int
 */
function today_attendance_count() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM attendance WHERE DATE(date) = CURDATE() AND status = 'present'");
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error getting today's attendance count: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get monthly fee collection
 * @param int $month
 * @param int $year
 * @return float
 */
function monthly_fee_collection($month = null, $year = null) {
    global $pdo;
    
    if (!$month) $month = date('m');
    if (!$year) $year = date('Y');
    
    try {
        if (schema_has_table('student_payments')) {
            $stmt = $pdo->prepare("SELECT SUM(amount_paid) FROM student_payments 
                              WHERE MONTH(payment_date) = ? AND YEAR(payment_date) = ?");
        } else {
            $stmt = $pdo->prepare("SELECT SUM(amount) FROM fees 
                              WHERE MONTH(payment_date) = ? AND YEAR(payment_date) = ? 
                              AND status = 'paid'");
        }
        $stmt->execute([$month, $year]);
        return $stmt->fetchColumn() ?? 0;
    } catch (PDOException $e) {
        error_log("Error getting monthly fee collection: " . $e->getMessage());
        return 0;
    }
}

/**
 * Generate student ID
 * @return string
 */
function generate_student_id() {
    global $pdo;

    try {
        $studentColumns = QueryDB("SHOW COLUMNS FROM students")->fetchAll(PDO::FETCH_COLUMN);

        if (in_array('admission_no', $studentColumns, true)) {
            return generate_student_admission_no();
        }
    } catch (PDOException $e) {
        error_log("Error checking student identifier column: " . $e->getMessage());
    }
    
    do {
        $id = 'STU' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE student_number = ?");
        $stmt->execute([$id]);
    } while ($stmt->fetchColumn() > 0);
    
    return $id;
}

/**
 * Generate staff ID
 * @return string
 */
function generate_staff_id() {
    global $pdo;
    
    do {
        $id = 'STF' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM staff WHERE staff_number = ?");
        $stmt->execute([$id]);
    } while ($stmt->fetchColumn() > 0);
    
    return $id;
}

/**
 * Generate teacher employee ID
 * @return string
 */
function generate_teacher_id() {
    return generate_teacher_identifier();
}

function generate_employee_id() {
    return generate_teacher_identifier();
}

/**
 * Get school statistics for dashboard
 * @return array
 */
function get_school_stats() {
    return [
        'total_students' => total_students(),
        'total_teachers' => total_teachers(),
        'total_courses' => total_courses(),
        'total_collections' => total_fee_collections(),
        'pending_fees' => pending_fees(),
        'overdue_fees' => overdue_fees_count(),
        'today_attendance' => today_attendance_count(),
        'monthly_collection' => monthly_fee_collection()
    ];
}
?>
