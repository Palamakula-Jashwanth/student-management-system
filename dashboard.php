<?php
require_once 'config/db.php';
include 'includes/header.php';

// Fetch key statistics
try {
    // Total Students (Active)
    $stmt = $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'Active'");
    $total_students = $stmt->fetchColumn();

    // New Admissions (This Month)
    $stmt = $pdo->query("SELECT COUNT(*) FROM students WHERE MONTH(admission_date) = MONTH(CURRENT_DATE()) AND YEAR(admission_date) = YEAR(CURRENT_DATE())");
    $new_admissions = $stmt->fetchColumn();

    // Students Transferred
    $stmt = $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'Transferred'");
    $transferred_students = $stmt->fetchColumn();
    
    // Total Classes setup (Distinct academic classes in current students table - simplified)
    $stmt = $pdo->query("SELECT COUNT(DISTINCT current_class) FROM students WHERE status = 'Active'");
    $total_classes = $stmt->fetchColumn();

} catch(PDOException $e) {
    $error = "Error fetching dashboard statistics.";
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Dashboard Overview</h2>
        <span class="text-muted"><?php echo date('l, F j, Y'); ?></span>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="row">
        <!-- Total Students Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 py-2" style="border-left: 4px solid #0d6efd !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Active Students</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo htmlspecialchars($total_students); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people-fill fs-1 text-gray-300" style="color: #dddfeb;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Admissions Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 py-2" style="border-left: 4px solid #198754 !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">New Admissions (This Month)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo htmlspecialchars($new_admissions); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-plus-fill fs-1 text-gray-300" style="color: #dddfeb;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Classes Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 py-2" style="border-left: 4px solid #0dcaf0 !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Active Classes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo htmlspecialchars($total_classes); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-building fs-1 text-gray-300" style="color: #dddfeb;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transferred Students Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 py-2" style="border-left: 4px solid #ffc107 !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Students Transferred</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo htmlspecialchars($transferred_students); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-file-earmark-text fs-1 text-gray-300" style="color: #dddfeb;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Content Area (E.g. Recent Admissions table) -->
    <div class="row mt-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Admissions</h6>
                    <a href="modules/students/index.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Adm. No</th>
                                    <th>Name</th>
                                    <th>Class</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                try {
                                    $recent_stmt = $pdo->query("SELECT admission_no, CONCAT(first_name, ' ', last_name) as full_name, CONCAT(current_class, '-', current_section) as class_info, admission_date FROM students ORDER BY admission_date DESC LIMIT 5");
                                    $recent_students = $recent_stmt->fetchAll();
                                    
                                    if(count($recent_students) > 0) {
                                        foreach($recent_students as $student) {
                                            echo '<tr>';
                                            echo '<td>' . htmlspecialchars($student['admission_no']) . '</td>';
                                            echo '<td>' . htmlspecialchars($student['full_name']) . '</td>';
                                            echo '<td>' . htmlspecialchars($student['class_info']) . '</td>';
                                            echo '<td>' . date('d M Y', strtotime($student['admission_date'])) . '</td>';
                                            echo '</tr>';
                                        }
                                    } else {
                                        echo '<tr><td colspan="4" class="text-center py-3 text-muted">No recent admissions found.</td></tr>';
                                    }
                                } catch(PDOException $e) {
                                    echo '<tr><td colspan="4" class="text-center text-danger">Error loading recent data</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
             <div class="card shadow-sm border-0 mb-4">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="modules/admission/index.php" class="btn btn-primary text-start"><i class="bi bi-person-plus me-2"></i> Add New Student</a>
                        <a href="modules/marks/index.php" class="btn btn-success text-start"><i class="bi bi-journal-check me-2"></i> Enter Marks</a>
                        <a href="modules/tc/index.php" class="btn btn-warning text-start"><i class="bi bi-file-earmark-arrow-up me-2"></i> Generate TC</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
