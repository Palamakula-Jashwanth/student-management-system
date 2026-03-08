<?php
// modules/tc/index.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

require_once '../../config/db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Certificates - SMS</title>
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
        
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0 bg-warning bg-opacity-10 border-start border-warning border-5">
                    <div class="card-body py-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark-person fs-1 text-warning me-4"></i>
                            <div>
                                <h4 class="fw-bold mb-1">Transfer Certificate (TC) Portal</h4>
                                <p class="mb-0 text-muted">Generate leaving certificates for students. When a TC is generated, the student's status automatically changes to <span class="badge bg-warning text-dark">Transferred</span>.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Left Side: Generate TC Action -->
            <div class="col-lg-5 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 text-primary"><i class="bi bi-box-arrow-right me-2"></i>Generate New TC</h6>
                    </div>
                    <div class="card-body text-center p-4">
                        <i class="bi bi-search text-muted mb-3" style="font-size: 3rem; opacity: 0.5;"></i>
                        <p class="text-muted mb-4">Search for an active student to generate their Transfer Certificate.</p>
                        
                        <form method="GET" action="../students/index.php" class="d-flex justify-content-center">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="Enter Admission No. or Name" required>
                                <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Find</button>
                            </div>
                        </form>
                        <hr class="my-4">
                        <p class="small text-start text-muted">Alternatively, go to a <a href="../students/index.php">Student's Profile</a> and select "Generate TC" from the Quick Actions menu.</p>
                    </div>
                </div>
            </div>
            
            <!-- Right Side: Recent TCs -->
            <div class="col-lg-7 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 text-secondary"><i class="bi bi-clock-history me-2"></i>Recently Generated TCs</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date Left</th>
                                        <th>Adm No.</th>
                                        <th>Student Name</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    try {
                                        $stmt = $pdo->query("
                                            SELECT t.id as tc_id, t.date_of_leaving, s.admission_no, CONCAT(s.first_name, ' ', s.last_name) as full_name, s.id as student_id
                                            FROM tc_records t
                                            JOIN students s ON t.student_id = s.id
                                            ORDER BY t.created_at DESC 
                                            LIMIT 8
                                        ");
                                        $recent_tcs = $stmt->fetchAll();
                                        
                                        if(count($recent_tcs) > 0) {
                                            foreach($recent_tcs as $tc) {
                                                echo '<tr>';
                                                echo '<td>' . date('d M Y', strtotime($tc['date_of_leaving'])) . '</td>';
                                                echo '<td><span class="badge bg-secondary">' . htmlspecialchars($tc['admission_no']) . '</span></td>';
                                                echo '<td><strong>' . htmlspecialchars($tc['full_name']) . '</strong></td>';
                                                echo '<td>';
                                                echo '<a href="print.php?id=' . $tc['tc_id'] . '" class="btn btn-sm btn-outline-info" target="_blank" title="Print TC"><i class="bi bi-printer"></i></a> ';
                                                echo '<a href="../students/view.php?id=' . $tc['student_id'] . '" class="btn btn-sm btn-outline-secondary" title="View Profile"><i class="bi bi-person"></i></a>';
                                                echo '</td>';
                                                echo '</tr>';
                                            }
                                        } else {
                                            echo '<tr><td colspan="4" class="text-center py-4 text-muted">No Transfer Certificates have been issued yet.</td></tr>';
                                        }
                                    } catch(PDOException $e) {
                                        echo '<tr><td colspan="4" class="text-center text-danger">Error loading records.</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
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
