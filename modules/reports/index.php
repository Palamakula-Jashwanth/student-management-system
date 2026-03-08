<?php
// modules/reports/index.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

require_once '../../config/db.php';

$report_type = $_GET['type'] ?? 'admissions';
$results = [];

try {
    if ($report_type === 'admissions') {
        $stmt = $pdo->query("
            SELECT admission_no, CONCAT(first_name, ' ', last_name) as full_name, admission_date, current_class, current_section, phone_number 
            FROM students 
            WHERE status = 'Active'
            ORDER BY admission_date DESC 
            LIMIT 50
        ");
        $results = $stmt->fetchAll();
    } 
    elseif ($report_type === 'transfers') {
        $stmt = $pdo->query("
            SELECT t.date_of_leaving, t.reason_for_leaving, s.admission_no, CONCAT(s.first_name, ' ', s.last_name) as full_name, s.current_class 
            FROM tc_records t
            JOIN students s ON t.student_id = s.id
            ORDER BY t.date_of_leaving DESC
            LIMIT 50
        ");
        $results = $stmt->fetchAll();
    }
    elseif ($report_type === 'class_strength') {
        $stmt = $pdo->query("
            SELECT current_class, current_section, COUNT(id) as total_students,
                   SUM(CASE WHEN gender = 'Male' THEN 1 ELSE 0 END) as boys,
                   SUM(CASE WHEN gender = 'Female' THEN 1 ELSE 0 END) as girls
            FROM students 
            WHERE status = 'Active'
            GROUP BY current_class, current_section
            ORDER BY current_class ASC, current_section ASC
        ");
        $results = $stmt->fetchAll();
    }
} catch(PDOException $e) {
    $error = "Failed to generate report.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports Dashboard - SMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        @media print {
            .wrapper { display: block; }
            #sidebar, .top-navbar, .report-controls { display: none !important; }
            #content { width: 100%; padding: 0; margin: 0; min-height: auto; }
            body { background: white; margin: 0; }
            .card { border: none; box-shadow: none !important; }
            .card-header { background: white !important; border-bottom: 2px solid #000; padding: 0 0 10px 0; margin-bottom: 20px;}
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

        <div class="row report-controls mb-4 no-print">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
                        <div class="d-flex gap-2">
                            <a href="?type=admissions" class="btn <?php echo $report_type == 'admissions' ? 'btn-primary' : 'btn-outline-primary'; ?>"><i class="bi bi-person-lines-fill me-2"></i> Recent Admissions</a>
                            <a href="?type=class_strength" class="btn <?php echo $report_type == 'class_strength' ? 'btn-primary' : 'btn-outline-primary'; ?>"><i class="bi bi-bar-chart-steps me-2"></i> Class Strength</a>
                            <a href="?type=transfers" class="btn <?php echo $report_type == 'transfers' ? 'btn-primary' : 'btn-outline-primary'; ?>"><i class="bi bi-box-arrow-right me-2"></i> Transfer Records</a>
                        </div>
                        <button onclick="window.print()" class="btn btn-secondary mt-2 mt-md-0"><i class="bi bi-printer me-2"></i> Print Report</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 text-primary">
                    <?php 
                        if($report_type == 'admissions') echo "Recent Admissions Report";
                        if($report_type == 'class_strength') echo "Class Strength & Demographics";
                        if($report_type == 'transfers') echo "Student Transfer & Exit Records";
                    ?>
                </h5>
                <p class="mb-0 small text-muted d-none d-print-block">Generated on: <?php echo date('d M Y, h:i A'); ?></p>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0">
                        <thead class="table-light">
                            <?php if($report_type == 'admissions'): ?>
                                <tr>
                                    <th>Adm No</th>
                                    <th>Student Name</th>
                                    <th>Class</th>
                                    <th>Admission Date</th>
                                    <th>Contact Phone</th>
                                </tr>
                            <?php elseif($report_type == 'transfers'): ?>
                                <tr>
                                    <th>Adm No</th>
                                    <th>Student Name</th>
                                    <th>Last Class</th>
                                    <th>Date Left</th>
                                    <th>Reason for Leaving</th>
                                </tr>
                            <?php elseif($report_type == 'class_strength'): ?>
                                <tr>
                                    <th>Class</th>
                                    <th>Section</th>
                                    <th class="text-center text-primary">Total Students</th>
                                    <th class="text-center text-info">Boys</th>
                                    <th class="text-center text-danger">Girls</th>
                                </tr>
                            <?php endif; ?>
                        </thead>
                        <tbody>
                            <?php if(empty($results)): ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted">No data available for this report.</td></tr>
                            <?php else: ?>
                                <?php foreach($results as $row): ?>
                                    <?php if($report_type == 'admissions'): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['admission_no']); ?></td>
                                            <td><strong><?php echo htmlspecialchars($row['full_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($row['current_class'].'-'.$row['current_section']); ?></td>
                                            <td><?php echo date('d M Y', strtotime($row['admission_date'])); ?></td>
                                            <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                                        </tr>
                                    <?php elseif($report_type == 'transfers'): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['admission_no']); ?></td>
                                            <td><strong><?php echo htmlspecialchars($row['full_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($row['current_class']); ?></td>
                                            <td><?php echo date('d M Y', strtotime($row['date_of_leaving'])); ?></td>
                                            <td><?php echo htmlspecialchars($row['reason_for_leaving']); ?></td>
                                        </tr>
                                    <?php elseif($report_type == 'class_strength'): ?>
                                        <tr>
                                            <td>Class <?php echo htmlspecialchars($row['current_class']); ?></td>
                                            <td>Sec <?php echo htmlspecialchars($row['current_section']); ?></td>
                                            <td class="text-center fw-bold text-primary"><?php echo $row['total_students']; ?></td>
                                            <td class="text-center"><?php echo $row['boys']; ?></td>
                                            <td class="text-center"><?php echo $row['girls']; ?></td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
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
