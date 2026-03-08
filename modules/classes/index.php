<?php
// modules/classes/index.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

require_once '../../config/db.php';

$success_msg = "";
$error_msg = "";

// Handle Bulk Promotion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'promote_class') {
    $from_class = $_POST['from_class'];
    $to_class = $_POST['to_class'];
    $new_academic_year = $_POST['new_academic_year'];
    
    if($from_class && $to_class && $new_academic_year) {
        try {
            $pdo->beginTransaction();
            
            // 1. Get all active students in the 'from_class'
            $stmt = $pdo->prepare("SELECT id, current_section FROM students WHERE current_class = ? AND status = 'Active'");
            $stmt->execute([$from_class]);
            $studentsToPromote = $stmt->fetchAll();
            
            $promotedCount = 0;
            
            if(count($studentsToPromote) > 0) {
                // Prepare statements for reuse
                $updateStudent = $pdo->prepare("UPDATE students SET current_class = ? WHERE id = ?");
                $insertHistory = $pdo->prepare("INSERT INTO class_history (student_id, academic_year, class, section) VALUES (?, ?, ?, ?)");
                
                foreach($studentsToPromote as $student) {
                    // Update main table
                    $updateStudent->execute([$to_class, $student['id']]);
                    // Add new history record (keeping same section for bulk promote)
                    $insertHistory->execute([$student['id'], $new_academic_year, $to_class, $student['current_section']]);
                    $promotedCount++;
                }
                
                $pdo->commit();
                $success_msg = "Successfully promoted $promotedCount students from Class $from_class to Class $to_class.";
            } else {
                $pdo->rollBack();
                $error_msg = "No active students found in Class $from_class to promote.";
            }
        } catch(PDOException $e) {
            $pdo->rollBack();
            $error_msg = "Error during promotion: " . $e->getMessage();
        }
    } else {
        $error_msg = "Please fill all fields for bulk promotion.";
    }
}


// Fetch class statistics
try {
    $class_stats = [];
    $stmt = $pdo->query("
        SELECT current_class, current_section, COUNT(id) as student_count 
        FROM students 
        WHERE status = 'Active' 
        GROUP BY current_class, current_section 
        ORDER BY current_class ASC, current_section ASC
    ");
    
    while ($row = $stmt->fetch()) {
        $class_stats[$row['current_class']][] = $row;
    }
} catch(PDOException $e) {
    $error_msg = "Could not load class statistics.";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Management - SMS</title>
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
        
        <?php if($success_msg): ?>
            <div class="alert alert-success mt-3"><i class="bi bi-check-circle-fill me-2"></i><?php echo $success_msg; ?></div>
        <?php endif; ?>
        <?php if($error_msg): ?>
            <div class="alert alert-danger mt-3"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <div class="row">
            <!-- Active Classes Summary -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 text-primary"><i class="bi bi-list-check me-2"></i>Active Class Roster</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Class Name</th>
                                        <th>Sections</th>
                                        <th>Total Students</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($class_stats)): ?>
                                        <tr><td colspan="4" class="text-center py-4 text-muted">No active classes found. Admitting a student will create a class.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($class_stats as $class_name => $sections): 
                                            $total_class_students = 0;
                                            $section_badges = "";
                                            foreach($sections as $sec) {
                                                $total_class_students += $sec['student_count'];
                                                $section_badges .= '<span class="badge bg-secondary me-1" title="'.$sec['student_count'].' students"> Sec '.$sec['current_section'].' ('.$sec['student_count'].')</span>';
                                            }
                                        ?>
                                        <tr>
                                            <td><strong>Class <?php echo htmlspecialchars($class_name); ?></strong></td>
                                            <td><?php echo $section_badges; ?></td>
                                            <td><span class="badge bg-primary rounded-pill"><?php echo $total_class_students; ?></span></td>
                                            <td>
                                                <a href="../students/index.php?search=<?php echo urlencode($class_name); ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i> View Students</a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bulk Promotion Tool -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-info text-white py-3">
                        <h5 class="mb-0"><i class="bi bi-arrow-up-circle-fill me-2"></i>Bulk Class Promotion</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-4">Promote all students in a class to the next academic level. This updates their main record and creates a new history logger entry.</p>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="promote_class">
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">From Class</label>
                                <select class="form-select" name="from_class" required>
                                    <option value="">Select From Class</option>
                                    <?php foreach(array_keys($class_stats) as $c) echo "<option value='$c'>Class $c</option>"; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Promote To Class</label>
                                <select class="form-select" name="to_class" required>
                                    <option value="">Select To Class</option>
                                    <option value="UKG">UKG</option>
                                    <?php for($i=1; $i<=12; $i++) echo "<option value='$i'>Class $i</option>"; ?>
                                    <option value="Graduated">Graduated/Alumni</option>
                                </select>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">New Academic Year</label>
                                <?php $next_yr = date('Y').'-'.(date('Y')+1); ?>
                                <input type="text" class="form-control" name="new_academic_year" value="<?php echo $next_yr; ?>" placeholder="e.g. 2024-2025" required>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary" onclick="return confirm('Are you sure you want to promote the entire class? This action cannot be easily undone.');"><i class="bi bi-arrow-up-right me-2"></i> Execute Promotion</button>
                            </div>
                        </form>
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
