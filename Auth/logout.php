<?php
session_start();

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Clear remember me cookie if it exists
if(isset($_COOKIE['username'])){
    setcookie('username', '', time() - 3600, '/');
}

// Redirect with success message or show logout confirmation
if(isset($_GET['confirm'])){
    // Show logout confirmation page
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Out - Grocenix</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logout-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 40px;
            text-align: center;
            max-width: 400px;
            width: 100%;
        }
        .logout-icon {
            color: #28a745;
            font-size: 4rem;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="logout-container">
                    <div class="logout-icon">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <h2 class="h4 mb-3">Successfully Logged Out</h2>
                    <p class="text-muted mb-4">You have been securely logged out of your Grocenix account.</p>
                    <p class="text-muted small mb-4">
                        <i class="bi bi-clock me-1"></i>
                        Redirecting to login page in <span id="countdown">5</span> seconds...
                    </p>

                    <div class="d-grid gap-2">
                        <a href="login.php" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Login Again
                        </a>
                        <a href="../index.php" class="btn btn-outline-secondary">
                            <i class="bi bi-house me-2"></i>Go to Homepage
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Auto redirect after 5 seconds -->
    <script>
        let countdown = 5;
        const countdownElement = document.getElementById('countdown');

        const timer = setInterval(function() {
            countdown--;
            if (countdownElement) {
                countdownElement.textContent = countdown;
            }
            if (countdown <= 0) {
                clearInterval(timer);
                window.location.href = 'login.php';
            }
        }, 1000);
    </script>
<?php
} else {
    // Simple redirect to login
    header("Location: login.php");
    exit();
}
?>