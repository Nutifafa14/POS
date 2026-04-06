<?php
session_start();
include("../config/database.php");

// Redirect if already logged in
if(isset($_SESSION['user'])){
    header("Location: ../pages/dashboard.php");
    exit();
}

if(isset($_POST['login'])){
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password']; // Don't escape password as it will be hashed

    $query = "SELECT * FROM users WHERE username='$username'";
    $result = mysqli_query($conn, $query);

    if(mysqli_num_rows($result) == 1){
        $user = mysqli_fetch_assoc($result);

        // Verify password (works with both hashed and plain text passwords)
        if(password_verify($password, $user['password']) || $user['password'] === $password){
            $_SESSION['user'] = $username;
            $_SESSION['role'] = $user['role'];

            // Handle "Remember Me" functionality
            if(isset($_POST['remember_me'])){
                setcookie('username', $username, time() + (86400 * 30), "/"); // 30 days
            }

            if($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'manager'){
                header("Location: ../pages/dashboard.php");
            } else {
                header("Location: ../pages/cashier_dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid username or password";
        }
    }else{
        $error = "Invalid username or password";
    }
}

// Pre-fill username if cookie exists
$remembered_username = isset($_COOKIE['username']) ? $_COOKIE['username'] : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Grocenix</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .login-form {
            padding: 30px 20px;
        }
        .form-control:focus {
            border-color: #4a90e2;
            box-shadow: 0 0 0 0.2rem rgba(74, 144, 226, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(74, 144, 226, 0.4);
        }
        .input-group-text {
            background-color: #f8f9fa;
            border-color: #dee2e6;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="login-container">
                    <!-- Header -->
                    <div class="login-header">
                        <i class="bi bi-shop display-4 mb-3"></i>
                        <h2 class="h4 mb-1">Welcome to Grocenix</h2>
                        <p class="mb-0 opacity-75">Sign in to your account</p>
                    </div>

                    <!-- Login Form -->
                    <div class="login-form">
                        <?php if(isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label fw-semibold">
                                    <i class="bi bi-person me-1"></i>Username
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-person-fill"></i>
                                    </span>
                                    <input type="text" class="form-control form-control-lg" id="username"
                                           name="username" placeholder="Enter your username"
                                           value="<?php echo htmlspecialchars($remembered_username); ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold">
                                    <i class="bi bi-lock me-1"></i>Password
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-lock-fill"></i>
                                    </span>
                                    <input type="password" class="form-control form-control-lg" id="password"
                                           name="password" placeholder="Enter your password" required>
                                </div>
                            </div>

                            <div class="mb-3 d-flex justify-content-between align-items-center">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember_me" name="remember_me"
                                           <?php echo $remembered_username ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="remember_me">
                                        Remember me
                                    </label>
                                </div>
                                <a href="#" class="text-decoration-none text-muted small">
                                    Forgot password?
                                </a>
                            </div>

                            <div class="d-grid">
                                <button type="submit" name="login" class="btn btn-primary btn-lg btn-login">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                                </button>
                            </div>
                        </form>

                        <hr class="my-4">

                        <div class="text-center">
                            <small class="text-muted">
                                Don't have an account?
                                <a href="signup.php" class="text-decoration-none fw-semibold">
                                    <i class="bi bi-person-plus me-1"></i>Sign Up
                                </a>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Auto-focus on username field -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const usernameField = document.getElementById('username');
            if (usernameField.value === '') {
                usernameField.focus();
            } else {
                document.getElementById('password').focus();
            }
        });
    </script>
</body>
</html>