<?php
// modules/marks/entry.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

require_once '../../config/db.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$student_id = $_GET['id'];
$success_msg = "";
$error_msg = "";

// Fetch Student Basics
try {
    $stmt = $pdo->prepare("SELECT id, admission_no, first_name, last_name, current_class, current_section FROM students WHERE id = ? AND status = 'Active'");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch();
    
    if (!$student) {
        die("Student not found or is no longer active.");
    }
} catch(PDOException $e) {
    die("Database Error.");
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $academic_year = $_POST['academic_year'];
    $exam_type = $_POST['exam_type'];
    
    // Arrays from Form
    $subjects = $_POST['subject'];
    $marks_obtained = $_POST['marks_obtained'];
    $max_marks = $_POST['max_marks'];

    try {
        $pdo->beginTransaction();
        
        $insertStmt = $pdo->prepare("INSERT INTO marks (student_id, academic_year, class, exam_type, subject, marks_obtained, max_marks) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $insertedCount = 0;
        for($i=0; $i<count($subjects); $i++) {
            if(!empty($subjects[$i]) && $marks_obtained[$i] !== '') {
                $insertStmt->execute([
                    $student_id, 
                    $academic_year, 
                    $student['current_class'], // Locking class to current state at time of entry
                    $exam_type, 
                    $subjects[$i], 
                    $marks_obtained[$i], 
                    $max_marks[$i]
                ]);
                $insertedCount++;
            }
        }
        
        $pdo->commit();
        if($insertedCount > 0) {
            $success_msg = "Successfully added $insertedCount marks for " . htmlspecialchars($exam_type);
        } else {
            $error_msg = "No valid marks provided to save.";
        }
        
    } catch(PDOException $e) {
        $pdo->rollBack();
        $error_msg = "Failed to save marks: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Marks - SMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/style.css">
    <script>
        function addSubjectRow() {
            const container = document.getElementById('marks-container');
            const row = document.createElement('div');
            row.className = 'row g-3 mb-2 marks-row align-items-center';
            row.innerHTML = `
                <div class="col-md-5">
                    <input type="text" class="form-control" name="subject[]" placeholder="e.g. Mathematics" required>
                </div>
                <div class="col-md-3">
                    <input type="number" step="0.01" class="form-control" name="marks_obtained[]" placeholder="Obtained" required>
                </div>
                <div class="col-md-3">
                    <input type="number" step="0.01" class="form-control" name="max_marks[]" value="100" required>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.parentElement.parentElement.remove()"><i class="bi bi-trash"></i></button>
                </div>
            `;
            container.appendChild(row);
        }
    </script>
</head>
<body class="bg-light">

<div class="wrapper">
    <?php include '../../includes/sidebar.php'; ?>

    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light top-navbar mb-4">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn btn-outline-secondary d-md-none">
                    <i class="bi bi-list"></i>
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

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0 text-primary">Marks Entry</h4>
            <a href="../students/view.php?id=<?php echo $student_id; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Profile</a>
        </div>
        
        <?php if($success_msg): ?>
            <div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i><?php echo $success_msg; ?></div>
        <?php endif; ?>
        <?php if($error_msg): ?>
            <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body bg-light border-bottom">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-1 fw-bold"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h5>
                        <p class="mb-0 text-muted small">Admission No: <?php echo htmlspecialchars($student['admission_no']); ?></p>
                    </div>
                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                        <span class="badge bg-primary fs-6">Class: <?php echo htmlspecialchars($student['current_class'] . '-' . $student['current_section']); ?></span>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Academic Year</label>
                            <?php $curr_yr = date('Y').'-'.(date('Y')+1); ?>
                            <input type="text" class="form-control" name="academic_year" value="<?php echo $curr_yr; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Exam Type</label>
                            <select class="form-select" name="exam_type" required>
                                <option value="Unit Test 1">Unit Test 1</option>
                                <option value="Midterm">Midterm</option>
                                <option value="Unit Test 2">Unit Test 2</option>
                                <option value="Final Examination">Final Examination</option>
                            </select>
                        </div>
                    </div>
                    
                    <h6 class="border-bottom pb-2 mb-3 text-secondary d-flex justify-content-between">
                        Subject Marks
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addSubjectRow()"><i class="bi bi-plus"></i> Add Row</button>
                    </h6>
                    
                    <div class="row g-3 mb-2 text-muted small fw-bold d-none d-md-flex">
                        <div class="col-md-5">Subject Name</div>
                        <div class="col-md-3">Marks Obtained</div>
                        <div class="col-md-3">Maximum Marks</div>
                        <div class="col-md-1"></div>
                    </div>

                    <div id="marks-container">
                        <!-- Default 5 Rows for core subjects -->
                        <?php 
                        $core_subjects = ['Mathematics', 'Science', 'English', 'Social Studies', 'Language/Computer'];
                        foreach($core_subjects as $sub): 
                        ?>
                        <div class="row g-3 mb-2 marks-row align-items-center">
                            <div class="col-md-5">
                                <input type="text" class="form-control" name="subject[]" value="<?php echo $sub; ?>" placeholder="Subject Name" required>
                            </div>
                            <div class="col-md-3">
                                <input type="number" step="0.01" class="form-control" name="marks_obtained[]" placeholder="Obtained" required>
                            </div>
                            <div class="col-md-3">
                                <input type="number" step="0.01" class="form-control" name="max_marks[]" value="100" required>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.parentElement.parentElement.remove()"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <hr class="mt-4">
                    <div class="text-end">
                        <button type="submit" class="btn btn-success"><i class="bi bi-save me-2"></i> Save Marks to Record</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

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
