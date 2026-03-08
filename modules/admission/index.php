<?php
// modules/admission/index.php
session_start();
// Basic authentication check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

$page_title = "New Admission";
// include header from root - we need to adjust path
require_once '../../config/db.php';
// Header inclusion requires path trickery or setting a base path
$base_path = '../../';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Admission - SMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body class="bg-light">

<div class="wrapper">
    <!-- Adjusted Sidebar Include -->
    <?php include '../../includes/sidebar.php'; ?>

    <!-- Page Content -->
    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light top-navbar mb-4">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn btn-outline-secondary d-md-none">
                    <i class="bi bi-list"></i>
                    <span>Menu</span>
                </button>
                <div class="ms-auto d-flex align-items-center">
                   <div class="dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle fs-5 me-1"></i> <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="../../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <?php
        $success_msg = "";
        $error_msg = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Collect and sanitize POST data
            $admission_no = trim($_POST['admission_no']);
            $first_name = trim($_POST['first_name']);
            $last_name = trim($_POST['last_name']);
            $dob = $_POST['date_of_birth'];
            $gender = $_POST['gender'];
            $blood_group = $_POST['blood_group'] ?? null;
            $father_name = trim($_POST['father_name']);
            $mother_name = trim($_POST['mother_name']);
            $address = trim($_POST['address']);
            $phone = trim($_POST['phone_number']);
            $email = trim($_POST['email']) ?? null;
            $prev_school = trim($_POST['previous_school']) ?? null;
            $admission_date = $_POST['admission_date'];
            $class = $_POST['current_class'];
            $section = $_POST['current_section'];
            $guardian_name = trim($_POST['guardian_name']) ?? null;
            $guardian_phone = trim($_POST['guardian_phone']) ?? null;
            $emergency_contact = trim($_POST['emergency_contact']);

            if (empty($admission_no) || empty($first_name) || empty($last_name) || empty($phone)) {
                 $error_msg = "Please fill in all required fields.";
            } else {
                try {
                    $pdo->beginTransaction();

                    // Insert into students table
                    $stmt = $pdo->prepare("INSERT INTO students (admission_no, first_name, last_name, date_of_birth, gender, blood_group, father_name, mother_name, address, phone_number, email, previous_school, admission_date, current_class, current_section, guardian_name, guardian_phone, emergency_contact, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active')");
                    
                    $stmt->execute([$admission_no, $first_name, $last_name, $dob, $gender, $blood_group, $father_name, $mother_name, $address, $phone, $email, $prev_school, $admission_date, $class, $section, $guardian_name, $guardian_phone, $emergency_contact]);
                    
                    $new_student_id = $pdo->lastInsertId();

                    // Insert initial class_history record (assuming current academic year is year of admission)
                    $academic_year = date('Y', strtotime($admission_date)) . '-' . (date('Y', strtotime($admission_date)) + 1);
                    $hist_stmt = $pdo->prepare("INSERT INTO class_history (student_id, academic_year, class, section) VALUES (?, ?, ?, ?)");
                    $hist_stmt->execute([$new_student_id, $academic_year, $class, $section]);

                    $pdo->commit();
                    $success_msg = "Student admitted successfully! Admission Number: " . htmlspecialchars($admission_no);
                } catch(PDOException $e) {
                    $pdo->rollBack();
                    // Handle duplicate admission numbers gracefully
                    if ($e->getCode() == 23000) {
                        $error_msg = "Admission Number already exists. Please use a unique number.";
                    } else {
                        $error_msg = "Database Error: " . $e->getMessage();
                    }
                }
            }
        }
        
        // Auto-generate next admission number (simple heuristic)
        $next_adm_no = '';
        try {
            $stmt = $pdo->query("SELECT admission_no FROM students ORDER BY id DESC LIMIT 1");
            $last_adm = $stmt->fetchColumn();
            if ($last_adm) {
                // If it's a number, increment it
                 if(is_numeric($last_adm)) {
                     $next_adm_no = $last_adm + 1;
                 } else {
                     $next_adm_no = $last_adm . '-NEW';
                 }
            } else {
                $next_adm_no = date('Y') . "0001";
            }
        } catch(PDOException $e) {}
        ?>

        <div class="row mb-3">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-primary"><i class="bi bi-person-plus-fill me-2"></i>New Student Admission</h5>
                    </div>
                    <div class="card-body">
                        <?php if($success_msg): ?>
                            <div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i><?php echo $success_msg; ?></div>
                        <?php endif; ?>
                        <?php if($error_msg): ?>
                            <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error_msg; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <h6 class="border-bottom pb-2 mb-3 text-secondary">Academic Details</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <label class="form-label form-label-sm fw-bold">Admission Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="admission_no" value="<?php echo htmlspecialchars($next_adm_no); ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label form-label-sm fw-bold">Admission Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="admission_date" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label form-label-sm fw-bold">Class Joining <span class="text-danger">*</span></label>
                                    <select class="form-select" name="current_class" required>
                                        <option value="">Select Class</option>
                                        <?php for($i=1; $i<=12; $i++) echo "<option value='$i'>Class $i</option>"; ?>
                                        <option value="LKG">LKG</option>
                                        <option value="UKG">UKG</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label form-label-sm fw-bold">Section <span class="text-danger">*</span></label>
                                    <select class="form-select" name="current_section" required>
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="C">C</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label form-label-sm fw-bold">Previous School (If any)</label>
                                    <input type="text" class="form-control" name="previous_school" placeholder="School Name">
                                </div>
                            </div>

                            <h6 class="border-bottom pb-2 mb-3 text-secondary">Personal Details</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label form-label-sm fw-bold">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="first_name" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label form-label-sm fw-bold">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="last_name" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label form-label-sm fw-bold">Date of Birth <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="date_of_birth" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label form-label-sm fw-bold">Gender <span class="text-danger">*</span></label>
                                    <select class="form-select" name="gender" required>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label form-label-sm fw-bold">Blood Group</label>
                                    <select class="form-select" name="blood_group">
                                        <option value="">Unknown</option>
                                        <option value="A+">A+</option><option value="A-">A-</option>
                                        <option value="B+">B+</option><option value="B-">B-</option>
                                        <option value="O+">O+</option><option value="O-">O-</option>
                                        <option value="AB+">AB+</option><option value="AB-">AB-</option>
                                    </select>
                                </div>
                            </div>

                            <h6 class="border-bottom pb-2 mb-3 text-secondary">Parent/Guardian & Contact Details</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label form-label-sm fw-bold">Father's Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="father_name" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label form-label-sm fw-bold">Mother's Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="mother_name" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label form-label-sm fw-bold">Primary Phone <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" name="phone_number" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label form-label-sm fw-bold">Local Guardian Name</label>
                                    <input type="text" class="form-control" name="guardian_name">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label form-label-sm fw-bold">Guardian Phone</label>
                                    <input type="tel" class="form-control" name="guardian_phone">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label form-label-sm fw-bold">Email Address</label>
                                    <input type="email" class="form-control" name="email">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label form-label-sm fw-bold">Residential Address <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="address" rows="2" required></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label form-label-sm fw-bold text-danger">Emergency Contact Number <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control border-danger" name="emergency_contact" required>
                                </div>
                            </div>
                            
                            <hr>
                            <div class="d-flex justify-content-end">
                                <button type="reset" class="btn btn-secondary me-2">Clear Form</button>
                                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i> Admitting Student</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- End content -->
</div> <!-- End wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const sidebarBtn = document.getElementById('sidebarCollapse');
        if (sidebarBtn) {
            sidebarBtn.addEventListener('click', function () {
                document.getElementById('sidebar').classList.toggle('active');
            });
        }
    });
</script>
</body>
</html>
