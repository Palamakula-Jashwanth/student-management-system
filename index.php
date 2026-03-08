<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management System - Login</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background-color: #f4f7f6;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        .login-card .card-header {
            background-color: #0d6efd;
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 20px;
            text-align: center;
            font-weight: bold;
            font-size: 1.5rem;
            border-bottom: none;
        }
        .btn-login {
            border-radius: 20px;
            padding: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card login-card">
                <div class="card-header">
                    SMS Admin Panel
                </div>
                <div class="card-body p-4">
                    <?php
                    session_start();
                    if (isset($_SESSION['error_message'])) {
                        echo '<div class="alert alert-danger" role="alert">' . $_SESSION['error_message'] . '</div>';
                        unset($_SESSION['error_message']); // Clear message after displaying
                    }
                    ?>
                    <form action="login_action.php" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-login">Login</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
