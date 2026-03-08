<?php
// modules/students/index.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

require_once '../../config/db.php';
$base_path = '../../';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student List - SMS</title>
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

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap">
                        <h5 class="mb-0 text-primary"><i class="bi bi-people-fill me-2"></i>Students Directory</h5>
                        
                        <!-- Search Form -->
                        <form method="GET" action="" class="d-flex mt-2 mt-md-0">
                            <input type="text" class="form-control form-control-sm me-2" name="search" placeholder="Name, Adm No, Phone..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                            <button class="btn btn-sm btn-outline-primary" type="submit"><i class="bi bi-search"></i></button>
                            <?php if(isset($_GET['search'])): ?>
                                <a href="index.php" class="btn btn-sm btn-outline-secondary ms-1"><i class="bi bi-x-Lg"></i> Clear</a>
                            <?php endif; ?>
                        </form>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Adm No</th>
                                        <th>Student Name</th>
                                        <th>Class</th>
                                        <th>Date of Birth</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    try {
                                        $query = "SELECT id, admission_no, CONCAT(first_name, ' ', last_name) as full_name, CONCAT(current_class, '-', current_section) as class_info, date_of_birth, phone_number, status FROM students";
                                        $params = [];
                                        
                                        if(!empty($_GET['search'])) {
                                            $search = '%' . $_GET['search'] . '%';
                                            $query .= " WHERE status = 'Active' AND (admission_no LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR phone_number LIKE ?)";
                                            $params = [$search, $search, $search, $search];
                                        } else {
                                           $query .= " WHERE status = 'Active'";
                                        }
                                        
                                        $query .= " ORDER BY id DESC";
                                        
                                        $stmt = $pdo->prepare($query);
                                        $stmt->execute($params);
                                        $students = $stmt->fetchAll();

                                        if(count($students) > 0) {
                                            foreach($students as $s) {
                                                echo '<tr>';
                                                echo '<td><span class="badge bg-secondary">' . htmlspecialchars($s['admission_no']) . '</span></td>';
                                                echo '<td><strong>' . htmlspecialchars($s['full_name']) . '</strong></td>';
                                                echo '<td>' . htmlspecialchars($s['class_info']) . '</td>';
                                                echo '<td>' . date('d M Y', strtotime($s['date_of_birth'])) . '</td>';
                                                echo '<td>' . htmlspecialchars($s['phone_number']) . '</td>';
                                                
                                                $status_color = 'success';
                                                if($s['status'] != 'Active') $status_color = 'warning';
                                                echo '<td><span class="badge bg-' . $status_color . '">' . htmlspecialchars($s['status']) . '</span></td>';
                                                
                                                echo '<td class="text-center">';
                                                echo '<div class="btn-group btn-group-sm" role="group">';
                                                echo '<a href="view.php?id=' . $s['id'] . '" class="btn btn-info text-white" title="View Profile"><i class="bi bi-eye"></i></a>';
                                                echo '<a href="edit.php?id=' . $s['id'] . '" class="btn btn-primary" title="Edit Student"><i class="bi bi-pencil-square"></i></a>';
                                                echo '</div>';
                                                echo '</td>';
                                                echo '</tr>';
                                            }
                                        } else {
                                            echo '<tr><td colspan="7" class="text-center py-4 text-muted">No students found matching your criteria.</td></tr>';
                                        }
                                    } catch(PDOException $e) {
                                        echo '<tr><td colspan="7" class="text-center text-danger">Error fetching data.</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
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
