<?php
// modules/tc/generate.php
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
$tc_generated = false;

// 1. Fetch Student Data
try {
    $stmt = $pdo->prepare("SELECT id, admission_no, first_name, last_name, status, admission_date, current_class, current_section, father_name, date_of_birth FROM students WHERE id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch();
    
    if (!$student) {
        die("Student record not found.");
    }
    
    // Check if TC already exists
    $checkTcStmt = $pdo->prepare("SELECT id FROM tc_records WHERE student_id = ?");
    $checkTcStmt->execute([$student_id]);
    if ($checkTcStmt->rowCount() > 0) {
        $tc_generated = true;
        $existing_tc_id = $checkTcStmt->fetchColumn();
    }
    
} catch(PDOException $e) {
    die("Database Error.");
}

// 2. Process TC Generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$tc_generated) {
    if($student['status'] !== 'Active') {
         $error_msg = "A Transfer Certificate can only be generated for an Active student.";
    } else {
        $leaving_date = $_POST['date_of_leaving'];
        $reason = trim($_POST['reason_for_leaving']);
        $generated_by = $_SESSION['user_id'];
        
        if(empty($leaving_date) || empty($reason)) {
            $error_msg = "Please provide both the Date of Leaving and Reason.";
        } else {
            try {
                $pdo->beginTransaction();
                
                // Insert into tc_records
                $insertTc = $pdo->prepare("INSERT INTO tc_records (student_id, date_of_leaving, reason_for_leaving, generated_by) VALUES (?, ?, ?, ?)");
                $insertTc->execute([$student_id, $leaving_date, $reason, $generated_by]);
                $new_tc_id = $pdo->lastInsertId();
                
                // Update student status to 'Transferred'
                $updateStudent = $pdo->prepare("UPDATE students SET status = 'Transferred' WHERE id = ?");
                $updateStudent->execute([$student_id]);
                
                $pdo->commit();
                $tc_generated = true;
                $existing_tc_id = $new_tc_id;
                $success_msg = "Transfer Certificate successfully generated. Student status updated to Transferred.";
                
                // Refresh student data to reflect new status
                $student['status'] = 'Transferred';
                
            } catch(PDOException $e) {
                $pdo->rollBack();
                $error_msg = "Failed to generate TC: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate TC - SMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/style.css">
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
            </div>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0 text-primary">Generate Transfer Certificate</h4>
            <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to TC Portal</a>
        </div>
        
        <?php if($success_msg): ?>
            <div class="alert alert-success fw-bold"><i class="bi bi-check-circle-fill me-2"></i><?php echo $success_msg; ?></div>
        <?php endif; ?>
        <?php if($error_msg): ?>
            <div class="alert alert-danger fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <div class="row">
            <!-- Student Header Info -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm border-0 h-100 border-top border-primary border-4">
                    <div class="card-body">
                        <div class="text-center mb-4 mt-3">
                            <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                                <i class="bi bi-person"></i>
                            </div>
                            <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h5>
                            <p class="text-muted small">Adm No: <?php echo htmlspecialchars($student['admission_no']); ?></p>
                            
                            <?php if($student['status'] === 'Transferred'): ?>
                                <span class="badge bg-warning text-dark fs-6 px-3 py-2">Status: Transferred</span>
                            <?php else: ?>
                                <span class="badge bg-success fs-6 px-3 py-2">Status: Active</span>
                            <?php endif; ?>
                        </div>
                        
                        <hr>
                        <ul class="list-group list-group-flush small">
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Class:</span>
                                <strong><?php echo htmlspecialchars($student['current_class'] . '-' . $student['current_section']); ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Father:</span>
                                <strong><?php echo htmlspecialchars($student['father_name']); ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Date of Birth:</span>
                                <strong><?php echo date('d M Y', strtotime($student['date_of_birth'])); ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Admission Date:</span>
                                <strong><?php echo date('d M Y', strtotime($student['admission_date'])); ?></strong>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Generation Form -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 text-secondary"><i class="bi bi-file-earmark-text me-2"></i>TC Processing Details</h6>
                    </div>
                    <div class="card-body p-4">
                        <?php if($tc_generated): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-check-circle text-success mb-3" style="font-size: 4rem;"></i>
                                <h4>TC Already Generated</h4>
                                <p class="text-muted mb-4">A Transfer Certificate for this student has already been processed.</p>
                                <a href="print.php?id=<?php echo $existing_tc_id; ?>" class="btn btn-primary btn-lg px-5" target="_blank"><i class="bi bi-printer me-2"></i> Print Official TC</a>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning mb-4">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i> <strong>Warning!</strong> Generating a TC will irreversibly mark this student as 'Transferred' and remove them from active class rosters. Ensure all fee dues and library books are cleared before proceeding.
                            </div>
                            
                            <form method="POST" action="">
                                <div class="row mb-4">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <label class="form-label fw-bold">Date of Leaving <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="date_of_leaving" value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Processing Admin</label>
                                        <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($_SESSION['full_name']); ?>" readonly>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Reason for Leaving <span class="text-danger">*</span></label>
                                    <select class="form-select mb-2" id="reason_preset" onchange="document.getElementById('reason_area').value = this.value">
                                        <option value="">-- Select Preset Reason or Write Custom Below --</option>
                                        <option value="Completed Highest Class in School">Completed Highest Class in School</option>
                                        <option value="Parent Transfer / Relocation">Parent Transfer / Relocation</option>
                                        <option value="Financial Reasons">Financial Reasons</option>
                                        <option value="Medical Reasons">Medical Reasons</option>
                                        <option value="On Parent's Request">On Parent's Request</option>
                                    </select>
                                    <textarea class="form-control" id="reason_area" name="reason_for_leaving" rows="3" placeholder="Explain the reason for leaving..." required></textarea>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-danger btn-lg" onclick="return confirm('Are you absolutely sure you want to generate a TC and mark this student as transferred?');"><i class="bi bi-file-earmark-lock me-2"></i> Finalize and Generate TC</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
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
