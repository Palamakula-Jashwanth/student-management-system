<?php
// modules/marks/report.php
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
$exam_filter = $_GET['exam_type'] ?? null;

try {
    // 1. Fetch Student Details
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch();
    
    if (!$student) die("Student not found.");

    // 2. Fetch Available Exams for this Student (to build the dropdown filter)
    $examListStmt = $pdo->prepare("SELECT DISTINCT exam_type, academic_year FROM marks WHERE student_id = ? ORDER BY created_at DESC");
    $examListStmt->execute([$student_id]);
    $available_exams = $examListStmt->fetchAll();
    
    // Default to the most recent exam if none selected
    if(!$exam_filter && count($available_exams) > 0) {
        $exam_filter = $available_exams[0]['exam_type'];
    }

    // 3. Fetch specific marks if an exam is selected
    $marks = [];
    if ($exam_filter) {
        $marksStmt = $pdo->prepare("SELECT * FROM marks WHERE student_id = ? AND exam_type = ?");
        $marksStmt->execute([$student_id, $exam_filter]);
        $marks = $marksStmt->fetchAll();
    }
    
} catch(PDOException $e) {
    die("Database Error.");
}

// Helper function to calculate Grade based on Percentage
function getGrade($percentage) {
    if ($percentage >= 90) return ['A+', 'text-success'];
    if ($percentage >= 80) return ['A' , 'text-success'];
    if ($percentage >= 70) return ['B+', 'text-primary'];
    if ($percentage >= 60) return ['B' , 'text-primary'];
    if ($percentage >= 50) return ['C' , 'text-warning'];
    if ($percentage >= 40) return ['D' , 'text-warning'];
    return ['F (Fail)', 'text-danger fw-bold'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Card - <?php echo htmlspecialchars($student['first_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .report-card {
            border: 2px solid #2c3e50;
            padding: 30px;
            background: #fff;
        }
        .school-header {
            text-align: center;
            border-bottom: 3px double #2c3e50;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .table-marks th {
            background-color: #f8f9fa !important;
            border-top: 2px solid #2c3e50;
            border-bottom: 2px solid #2c3e50;
        }
        @media print {
            .wrapper { display: block; }
            #sidebar, .top-navbar, .no-print { display: none !important; }
            #content { width: 100%; padding: 0; margin: 0; min-height: auto; }
            body { background: white; margin: 0; }
            .report-card { border: none; padding: 0; }
            .container-fluid { padding: 0; }
        }
    </style>
</head>
<body class="bg-light">

<div class="wrapper">
    <?php include '../../includes/sidebar.php'; ?>

    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light top-navbar mb-4 no-print">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn btn-outline-secondary d-md-none">
                    <i class="bi bi-list"></i>
                </button>
            </div>
        </nav>

        <div class="row mb-3 no-print">
            <div class="col d-flex justify-content-between align-items-center">
                <a href="../students/view.php?id=<?php echo $student_id; ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Profile</a>
                <div>
                    <form method="GET" class="d-inline-block me-2">
                        <input type="hidden" name="id" value="<?php echo $student_id; ?>">
                        <select name="exam_type" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                            <option value="">-- Select Exam --</option>
                            <?php foreach($available_exams as $ex): ?>
                                <option value="<?php echo htmlspecialchars($ex['exam_type']); ?>" <?php if($exam_filter == $ex['exam_type']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($ex['exam_type']) . ' (' . htmlspecialchars($ex['academic_year']) . ')'; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                    <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer me-2"></i> Print Report Card</button>
                </div>
            </div>
        </div>

        <?php if(empty($available_exams)): ?>
            <div class="alert alert-warning">No marks have been recorded for this student yet.</div>
        <?php elseif($exam_filter && count($marks) > 0): ?>
            
            <!-- PRINTABLE REPORT CARD START -->
            <div class="report-card mx-auto shadow-sm" style="max-width: 800px;">
                <div class="school-header">
                    <h2 class="text-uppercase fw-bold text-primary mb-1">Global Standard High School</h2>
                    <p class="mb-0 text-muted">Excellence in Education. Wisdom in Practice.</p>
                    <h4 class="mt-3 text-secondary text-uppercase" style="letter-spacing: 2px;">Academic Report Card</h4>
                </div>
                
                <div class="row mb-4">
                    <div class="col-6">
                        <p class="mb-1"><strong>Student Name:</strong> <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></p>
                        <p class="mb-1"><strong>Admission No:</strong> <?php echo htmlspecialchars($student['admission_no']); ?></p>
                        <p class="mb-1"><strong>Father's Name:</strong> <?php echo htmlspecialchars($student['father_name']); ?></p>
                    </div>
                    <div class="col-6 text-end">
                        <p class="mb-1"><strong>Class:</strong> <?php echo htmlspecialchars($marks[0]['class']); ?></p>
                        <p class="mb-1"><strong>Academic Year:</strong> <?php echo htmlspecialchars($marks[0]['academic_year']); ?></p>
                        <p class="mb-1"><strong>Examination:</strong> <?php echo htmlspecialchars($exam_filter); ?></p>
                    </div>
                </div>
                
                <table class="table table-bordered table-marks text-center mb-4">
                    <thead>
                        <tr>
                            <th class="text-start">Subject</th>
                            <th>Maximum Marks</th>
                            <th>Marks Obtained</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_obtained = 0;
                        $total_max = 0;
                        foreach($marks as $m): 
                            $total_obtained += $m['marks_obtained'];
                            $total_max += $m['max_marks'];
                            
                            $percent = ($m['marks_obtained'] / $m['max_marks']) * 100;
                            list($gradeStr, $gradeClass) = getGrade($percent);
                        ?>
                        <tr>
                            <td class="text-start fw-bold"><?php echo htmlspecialchars($m['subject']); ?></td>
                            <td><?php echo floatval($m['max_marks']); ?></td>
                            <td><?php echo floatval($m['marks_obtained']); ?></td>
                            <td class="<?php echo $gradeClass; ?>"><?php echo $gradeStr; ?></td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php 
                        // Grand Totals
                        $overall_percent = ($total_max > 0) ? ($total_obtained / $total_max) * 100 : 0;
                        list($overallGrade, $overallClass) = getGrade($overall_percent);
                        ?>
                        <tr class="table-light border-top">
                            <td class="text-start fs-5"><strong>GRAND TOTAL</strong></td>
                            <td class="fs-5 fw-bold"><?php echo floatval($total_max); ?></td>
                            <td class="fs-5 fw-bold text-primary"><?php echo floatval($total_obtained); ?></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="row align-items-center bg-light p-3 rounded mb-5 border">
                    <div class="col-md-6 text-center border-end">
                        <h5 class="mb-0 text-secondary">Overall Percentage</h5>
                        <h2 class="mb-0 text-primary fw-bold"><?php echo number_format($overall_percent, 2); ?>%</h2>
                    </div>
                    <div class="col-md-6 text-center">
                        <h5 class="mb-0 text-secondary">Final Grade</h5>
                        <h2 class="mb-0 <?php echo $overallClass; ?> fw-bold"><?php echo $overallGrade; ?></h2>
                    </div>
                </div>
                
                <div class="row mt-5 pt-5 text-center">
                    <div class="col-4">
                        <hr class="w-75 mx-auto border-dark">
                        <p class="small text-muted fw-bold">Class Teacher Signature</p>
                    </div>
                    <div class="col-4">
                         <div style="width: 80px; height: 80px; border: 2px dashed #ccc; border-radius: 50%; margin: -40px auto 10px auto; line-height: 80px; color: #ccc;" class="small">School Seal</div>
                    </div>
                    <div class="col-4">
                        <hr class="w-75 mx-auto border-dark">
                        <p class="small text-muted fw-bold">Principal Signature</p>
                    </div>
                </div>
            </div>
            <!-- PRINTABLE REPORT CARD END -->

        <?php elseif($exam_filter && count($marks) == 0): ?>
            <div class="alert alert-info">No marks found for the selected examination.</div>
        <?php endif; ?>

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
