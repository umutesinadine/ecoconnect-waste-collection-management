<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';

// If already logged in, redirect to the correct dashboard based on the saved role
if (isLoggedIn()) {
    redirectToRoleDashboard();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    requireCSRF();
    
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        try {
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Activate pending accounts automatically so newly registered users can log in
                if ($user['status'] === 'pending') {
                    $activate_stmt = $db->prepare("UPDATE users SET status = 'active' WHERE user_id = ?");
                    $activate_stmt->execute([$user['user_id']]);
                    $user['status'] = 'active';
                }

                // Check if account is active
                if ($user['status'] !== 'active') {
                    if ($user['status'] === 'pending') {
                        $error = "Your account is pending approval. Please wait for admin activation.";
                    } else {
                        $error = "Your account has been deactivated. Contact administrator.";
                    }
                } else {
                    // Set session variables
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['role'] = normalizeRole($user['role']);
                    $_SESSION['phone'] = $user['phone'];
                    
                    // Check if user is a super cashier
                    $_SESSION['is_super_cashier'] = isSuperCashier($user['user_id']) ? 1 : 0;
                    
                    // Update last login
                    $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
                    $stmt->execute([$user['user_id']]);
                    
                    // Log activity
                    logActivity('Login', "User {$user['full_name']} logged in as {$user['role']}");
                    
                    // Create notification
                    createNotification($user['user_id'], 'Welcome Back!', "You have successfully logged in.", 'success');
                    
                    // Redirect based on the role stored in the database
                    redirectToRoleDashboard($user['role']);
                }
            } else {
                $error = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $error = "An error occurred. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EcoConnect</title>
    <link rel="icon" type="image/jpg" sizes="32x32" href="../favicon-32x32.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a936f 0%, #114b5f 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #2e7d32, #1a936f);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .login-header h2 {
            margin: 0;
            font-weight: 600;
        }
        .login-header p {
            margin: 10px 0 0;
            opacity: 0.9;
        }
        .login-body {
            padding: 40px;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #1a936f;
            box-shadow: 0 0 0 0.2rem rgba(26,147,111,0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #2e7d32, #1a936f);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            font-size: 16px;
            color: white;
            width: 100%;
            transition: all 0.3s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(26,147,111,0.4);
        }
        .input-group-text {
            background: transparent;
            border: 2px solid #e0e0e0;
            border-right: none;
            border-radius: 10px 0 0 10px;
        }
        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }
        .input-group:focus-within .input-group-text {
            border-color: #1a936f;
        }
        .register-link {
            text-align: center;
            margin-top: 20px;
        }
        .register-link a {
            color: #1a936f;
            font-weight: 600;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
        .back-home {
            text-align: center;
            margin-top: 15px;
        }
        .back-home a {
            color: #666;
            text-decoration: none;
            font-size: 14px;
        }
        .back-home a:hover {
            color: #1a936f;
        }
        .alert {
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="login-card">
                    <div class="login-header">
                        <img src="../images/gisellelogo.png" alt="EcoConnect" height="60" class="mb-3">
                        <h2>Welcome Back</h2>
                        <p>Sign in to your EcoConnect account</p>
                    </div>
                    <div class="login-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <?= csrfField() ?>
                            <div class="mb-4">
                                <label class="form-label fw-bold">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" name="email" placeholder="Enter your email" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" name="password" placeholder="Enter your password" required>
                                </div>
                            </div>
                            <button type="submit" class="btn-login">
                                <i class="fas fa-sign-in-alt me-2"></i>Sign In
                            </button>
                        </form>

                        <div class="alert alert-light border mt-4 mb-0">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-2"></i>
                                Use your own email and password. The system detects your role automatically and sends you to the correct dashboard.
                            </small>
                        </div>
                        
                        <div class="register-link">
                            <p>Don't have an account? <a href="register.php">Register here</a></p>
                        </div>
                        <div class="back-home">
                            <a href="../index.php"><i class="fas fa-arrow-left me-1"></i>Back to Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
