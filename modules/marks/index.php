<?php
// modules/marks/index.php
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
    <title>Manage Marks - SMS</title>
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
        
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 text-primary"><i class="bi bi-journal-check me-2"></i>Enter / Manage Student Marks</h5>
                    </div>
                    <div class="card-body p-4 text-center">
                        <p class="text-muted mb-4">To enter marks or view a report card, please search and select a specific student.</p>
                        
                        <form method="GET" action="../students/index.php" class="d-flex justify-content-center mb-4">
                            <div class="input-group" style="max-width: 500px;">
                                <input type="text" class="form-control" name="search" placeholder="Search by Name or Admission No..." required>
                                <button class="btn btn-primary" type="submit"><i class="bi bi-search me-1"></i> Search Students</button>
                            </div>
                        </form>
                        
                        <div class="alert alert-info d-inline-block text-start">
                            <h6 class="alert-heading fw-bold"><i class="bi bi-info-circle me-1"></i> How to add marks:</h6>
                            <ol class="mb-0 small">
                                <li>Search for the student using the bar above.</li>
                                <li>From the student list, click the <strong>Eye Icon (<i class="bi bi-eye"></i> View)</strong> to open their profile.</li>
                                <li>On their profile, click <strong>"Add Marks"</strong> under Quick Actions.</li>
                            </ol>
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
