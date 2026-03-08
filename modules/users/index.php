<?php
// modules/users/index.php
session_start();

// Only Admins can access User Management
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../index.php");
    exit();
}

require_once '../../config/db.php';

$success_msg = "";
$error_msg = "";

// Handle User Actions (Create / Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // --- ADD USER ---
        if ($_POST['action'] == 'add_user') {
            $username = trim($_POST['username']);
            $full_name = trim($_POST['full_name']);
            $email = trim($_POST['email']);
            $role = $_POST['role'];
            $password = $_POST['password'];
            
            if ($username && $full_name && $role && $password) {
                try {
                    // Check if username already exists
                    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                    $checkStmt->execute([$username]);
                    if ($checkStmt->rowCount() > 0) {
                        $error_msg = "Username already exists. Please choose another.";
                    } else {
                        // Hash the password securely
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        
                        $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$username, $hashed_password, $full_name, $email, $role]);
                        $success_msg = "User Account '$username' successfully created.";
                    }
                } catch(PDOException $e) {
                    $error_msg = "Error creating user: " . $e->getMessage();
                }
            } else {
                $error_msg = "Please fill all required fields.";
            }
        }
        
        // --- DELETE USER ---
        elseif ($_POST['action'] == 'delete_user') {
            $user_id_to_delete = $_POST['user_id'];
            
            // Prevent deleting oneself
            if ($user_id_to_delete == $_SESSION['user_id']) {
                $error_msg = "You cannot delete your own account while logged in.";
            } else {
                try {
                    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->execute([$user_id_to_delete]);
                    $success_msg = "User account successfully deleted.";
                } catch(PDOException $e) {
                    // It might fail if the user is linked to TC generation or other foreign keys in a real app,
                    // but our schema didn't enforce a hard block on it. Soft delete is often better.
                    $error_msg = "Error deleting user. Ensure they are not linked to critical records.";
                }
            }
        }
    }
}

// Fetch all users
try {
    $stmt = $pdo->query("SELECT id, username, full_name, email, role, created_at FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Database Error loading users.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - SMS</title>
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
            <h4 class="mb-0 text-primary"><i class="bi bi-people-fill me-2"></i>User & Role Management</h4>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal"><i class="bi bi-person-plus me-2"></i>Add New User</button>
        </div>
        
        <?php if($success_msg): ?>
            <div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i><?php echo $success_msg; ?></div>
        <?php endif; ?>
        <?php if($error_msg): ?>
            <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th># ID</th>
                                <th>Full Name</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Created On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($user['full_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td>
                                    <?php if($user['role'] === 'Admin'): ?>
                                        <span class="badge bg-danger"><i class="bi bi-shield-lock me-1"></i> Admin</span>
                                    <?php else: ?>
                                        <span class="badge bg-info text-dark"><i class="bi bi-person-workspace me-1"></i> Staff</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <?php if($user['id'] !== $_SESSION['user_id']): ?>
                                    <form method="POST" action="" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this user account?');">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Account"><i class="bi bi-trash"></i></button>
                                    </form>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Current Session</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Create New User Account</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="">
          <div class="modal-body">
              <input type="hidden" name="action" value="add_user">
              
              <div class="mb-3">
                  <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="full_name" required>
              </div>
              
              <div class="mb-3">
                  <label class="form-label fw-bold">Login Username <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="username" required>
              </div>
              
              <div class="mb-3">
                  <label class="form-label fw-bold">Email Address</label>
                  <input type="email" class="form-control" name="email">
              </div>
              
              <div class="mb-3">
                  <label class="form-label fw-bold">Account Role <span class="text-danger">*</span></label>
                  <select class="form-select" name="role" required>
                      <option value="Staff">Staff (Admissions & Data Entry)</option>
                      <option value="Admin">Admin (Full Access & User Management)</option>
                  </select>
                  <div class="form-text text-danger bg-warning-subtle p-2 mt-2 rounded small">
                      <i class="bi bi-shield-exclamation"></i> Only Admins can create or delete other accounts.
                  </div>
              </div>
              
              <div class="mb-3">
                  <label class="form-label fw-bold">Password <span class="text-danger">*</span></label>
                  <input type="password" class="form-control" name="password" required>
              </div>
              
          </div>
          <div class="modal-footer d-flex justify-content-between">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary"><i class="bi bi-person-check me-2"></i>Create Account</button>
          </div>
      </form>
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
