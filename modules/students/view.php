<?php
// modules/students/view.php
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
$error = null;

try {
    // Fetch Student Details
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch();

    if (!$student) {
        $error = "Student not found.";
    } else {
        // Fetch Class History
        $hist_stmt = $pdo->prepare("SELECT * FROM class_history WHERE student_id = ? ORDER BY academic_year DESC");
        $hist_stmt->execute([$student_id]);
        $history = $hist_stmt->fetchAll();
        
        // Fetch Recent Marks limit to 5
        $marks_stmt = $pdo->prepare("SELECT * FROM marks WHERE student_id = ? ORDER BY created_at DESC LIMIT 5");
        $marks_stmt->execute([$student_id]);
        $marks = $marks_stmt->fetchAll();
    }
} catch(PDOException $e) {
    $error = "Database Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile - SMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .profile-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
            color: white;
            border-radius: 10px 10px 0 0;
            padding: 30px;
        }
        .profile-img-placeholder {
            width: 120px;
            height: 120px;
            background-color: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            border: 4px solid white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .info-label {
            font-size: 0.85rem;
            text-transform: uppercase;
            color: #6c757d;
            font-weight: 600;
            margin-bottom: 0.2rem;
        }
        .info-value {
            font-size: 1.05rem;
            color: #212529;
            margin-bottom: 1rem;
        }
    </style>
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
                <div class="ms-auto">
                    <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to List</a>
                </div>
            </div>
        </nav>

        <?php if ($error): ?>
            <div class="alert alert-danger ms-3 me-3"><?php echo $error; ?></div>
        <?php elseif(isset($student)): ?>
            <div class="container-fluid">
                <div class="card shadow-sm border-0 mb-4">
                    <!-- Profile Header -->
                    <div class="profile-header d-flex align-items-center">
                        <div class="me-4 table-responsive">
                            <?php if(!empty($student['photo_path'])): ?>
                                <!-- <img src="../../<?php echo htmlspecialchars($student['photo_path']); ?>" alt="Profile" class="rounded-circle border border-4 border-white shadow-sm" style="width:120px; height:120px; object-fit:cover;"> -->
                                <div class="profile-img-placeholder"><i class="bi bi-person"></i></div>
                            <?php else: ?>
                                <div class="profile-img-placeholder">
                                    <?php echo strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-grow-1">
                            <h2 class="mb-1 fw-bold"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h2>
                            <h5 class="mb-2 text-white-50">Admission No: <?php echo htmlspecialchars($student['admission_no']); ?></h5>
                            <div class="d-flex gap-3 mt-3">
                                <span class="badge bg-light text-dark px-3 py-2" style="font-size: 0.9rem;"><i class="bi bi-building me-1"></i> Class <?php echo htmlspecialchars($student['current_class'] . '-' . $student['current_section']); ?></span>
                                <span class="badge <?php echo $student['status'] == 'Active' ? 'bg-success' : 'bg-warning'; ?> px-3 py-2" style="font-size: 0.9rem;">
                                    <?php echo htmlspecialchars($student['status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div class="row">
                            <!-- Personal & Contact Info -->
                            <div class="col-lg-8">
                                <h5 class="border-bottom pb-2 mb-3 text-primary"><i class="bi bi-person-lines-fill me-2"></i>Personal Details</h5>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="info-label">Date of Birth</div>
                                        <div class="info-value"><?php echo date('d M Y', strtotime($student['date_of_birth'])); ?></div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="info-label">Gender</div>
                                        <div class="info-value"><?php echo htmlspecialchars($student['gender']); ?></div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="info-label">Blood Group</div>
                                        <div class="info-value"><?php echo htmlspecialchars($student['blood_group'] ?: 'N/A'); ?></div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="info-label">Father's Name</div>
                                        <div class="info-value"><?php echo htmlspecialchars($student['father_name']); ?></div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="info-label">Mother's Name</div>
                                        <div class="info-value"><?php echo htmlspecialchars($student['mother_name']); ?></div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="info-label">Phone Number</div>
                                        <div class="info-value"><?php echo htmlspecialchars($student['phone_number']); ?></div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="info-label">Emergency Contact</div>
                                        <div class="info-value text-danger fw-bold"><?php echo htmlspecialchars($student['emergency_contact']); ?></div>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <div class="info-label">Address</div>
                                        <div class="info-value"><?php echo nl2br(htmlspecialchars($student['address'])); ?></div>
                                    </div>
                                </div>
                                
                                <h5 class="border-bottom pb-2 mb-3 mt-4 text-primary"><i class="bi bi-clock-history me-2"></i>Class History</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Academic Year</th>
                                                <th>Class</th>
                                                <th>Section</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($history as $h): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($h['academic_year']); ?></td>
                                                    <td><?php echo htmlspecialchars($h['class']); ?></td>
                                                    <td><?php echo htmlspecialchars($h['section']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php if(empty($history)): ?>
                                                <tr><td colspan="3" class="text-center text-muted">No history found</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Action Panel -->
                            <div class="col-lg-4 border-start">
                                <h6 class="text-secondary fw-bold mb-3">Quick Actions</h6>
                                <div class="d-grid gap-2 mb-4">
                                    <a href="edit.php?id=<?php echo $student['id']; ?>" class="btn btn-outline-primary"><i class="bi bi-pencil me-2"></i> Edit Profile</a>
                                    <a href="../marks/entry.php?id=<?php echo $student['id']; ?>" class="btn btn-outline-success"><i class="bi bi-plus-circle me-2"></i> Add Marks</a>
                                    <a href="../marks/report.php?id=<?php echo $student['id']; ?>" class="btn btn-outline-info"><i class="bi bi-file-text me-2"></i> View Report Card</a>
                                    <?php if($student['status'] == 'Active'): ?>
                                    <a href="../tc/generate.php?id=<?php echo $student['id']; ?>" class="btn btn-outline-warning"><i class="bi bi-box-arrow-right me-2"></i> Generate TC</a>
                                    <?php endif; ?>
                                </div>

                                <h6 class="text-secondary fw-bold mb-3">Recent Academic Marks</h6>
                                <?php if(empty($marks)): ?>
                                    <p class="text-muted small">No marks recorded yet.</p>
                                <?php else: ?>
                                    <ul class="list-group list-group-flush small">
                                        <?php foreach($marks as $m): ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                <span><strong><?php echo htmlspecialchars($m['subject']); ?></strong> <br><span class="text-muted" style="font-size:0.75rem"><?php echo htmlspecialchars($m['exam_type']); ?></span></span>
                                                <span class="badge bg-primary rounded-pill"><?php echo htmlspecialchars($m['marks_obtained']) . '/' . htmlspecialchars($m['max_marks']); ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
