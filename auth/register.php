<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';

// If already logged in, redirect to the correct dashboard
if (isLoggedIn()) {
    redirectToRoleDashboard();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (
        empty($full_name) ||
        empty($email) ||
        empty($phone) ||
        empty($address) ||
        empty($password)
    ) {

        $error = "All fields are required.";

    } elseif (!validateEmail($email)) {

        $error = "Please enter a valid email address.";

    } elseif (!validatePhone($phone)) {

        $error = "Please enter a valid phone number.";

    } elseif (strlen($password) < 6) {

        $error = "Password must be at least 6 characters.";

    } elseif ($password !== $confirm_password) {

        $error = "Passwords do not match.";

    } else {

        try {

            // Check existing email
            $stmt = $db->prepare(
                "SELECT COUNT(*) as count FROM users WHERE email = ?"
            );

            $stmt->execute([$email]);

            if ($stmt->fetch()['count'] > 0) {

                $error = "An account with this email already exists.";

            } else {

                // Encrypt password
                $hashed_password = password_hash(
                    $password,
                    PASSWORD_BCRYPT
                );


                // Create username
                $username = explode('@', $email)[0];


                // Create customer account
                $stmt = $db->prepare(
                    "INSERT INTO users 
                    (
                        username,
                        email,
                        password,
                        full_name,
                        phone,
                        address,
                        role,
                        status
                    )
                    VALUES (?, ?, ?, ?, ?, ?, 'customer', 'active')"
                );


                $stmt->execute([
                    $username,
                    $email,
                    $hashed_password,
                    $full_name,
                    $phone,
                    $address
                ]);


                $new_user_id = $db->lastInsertId();


                // Save activity
                logActivity(
                    'Registration',
                    "New customer registered: $full_name"
                );


                // Notify admin
                $admin_stmt = $db->query(
                    "SELECT user_id 
                     FROM users 
                     WHERE role='admin' 
                     LIMIT 1"
                );


                $admin = $admin_stmt->fetch();


                if ($admin) {

                    createNotification(
                        $admin['user_id'],
                        'New Registration',
                        "New customer $full_name registered successfully.",
                        'info',
                        '../admin/customers.php'
                    );

                }


                $success =
                "Registration successful! 
                You can now login using your email and password.";

            }


        } catch (PDOException $e) {

            error_log(
                "Registration error: ".$e->getMessage()
            );

            $error =
            "An error occurred. Please try again.";

        }
    }
}
?> <!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Register - EcoConnect</title>

<link rel="icon" type="image/jpg" sizes="32x32" href="../favicon-32x32.jpg">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">


<style>

body {

    background: linear-gradient(
        135deg,
        #1a936f 0%,
        #114b5f 100%
    );

    min-height:100vh;

    display:flex;

    align-items:center;

    font-family:
    'Segoe UI',
    Tahoma,
    Geneva,
    Verdana,
    sans-serif;

    padding:40px 0;

}



.register-card {

    background:white;

    border-radius:15px;

    box-shadow:
    0 20px 60px rgba(0,0,0,0.3);

    overflow:hidden;

}



.register-header {


    background:
    linear-gradient(
        135deg,
        #2e7d32,
        #1a936f
    );


    color:white;

    padding:30px;

    text-align:center;

}



.register-header h2 {

    margin:0;

    font-weight:600;

}



.register-header p {

    margin-top:10px;

    opacity:.9;

}



.register-body {

    padding:40px;

}



.form-control {

    border-radius:10px;

    padding:12px 15px;

    border:2px solid #e0e0e0;

}



.form-control:focus {

    border-color:#1a936f;

    box-shadow:
    0 0 0 .2rem rgba(26,147,111,.25);

}



.btn-register {


    background:
    linear-gradient(
        135deg,
        #2e7d32,
        #1a936f
    );


    border:none;

    border-radius:10px;

    padding:12px;

    font-weight:600;

    font-size:16px;

    color:white;

    width:100%;

}



.btn-register:hover {

    transform:translateY(-2px);

}



.login-link {

    text-align:center;

    margin-top:20px;

}



.login-link a {

    color:#1a936f;

    font-weight:600;

    text-decoration:none;

}



.back-home {

    text-align:center;

    margin-top:15px;

}



.back-home a {

    color:#666;

    text-decoration:none;

}



.alert {

    border-radius:10px;

}

</style>

</head>


<body>


<div class="container">

<div class="row justify-content-center">


<div class="col-md-6">


<div class="register-card">



<div class="register-header">


<img 
src="../images/gisellelogo.png"
alt="EcoConnect"
height="60"
class="mb-3"
>


<h2>Create Account</h2>


<p>
Join EcoConnect waste management services
</p>


</div>



<div class="register-body">



<div class="alert alert-info mb-4">

<i class="fas fa-info-circle me-2"></i>

New customers register with Customer role and can login immediately.

</div>




<?php if ($error): ?>


<div class="alert alert-danger">

<?= htmlspecialchars($error) ?>

</div>


<?php endif; ?>




<?php if ($success): ?>


<div class="alert alert-success">

<?= htmlspecialchars($success) ?>

</div>


<div class="login-link">

<a href="login.php">

Click here to login

</a>

</div>



<?php else: ?>




<form method="POST" action="">



<div class="mb-3">

<label class="form-label fw-bold">

Full Name

</label>


<input 
type="text"
class="form-control"
name="full_name"
placeholder="Enter your full name"
required
>

</div>





<div class="row">


<div class="col-md-6 mb-3">


<label class="form-label fw-bold">

Email Address

</label>


<input
type="email"
class="form-control"
name="email"
placeholder="Enter your email"
required
>


</div>



<div class="col-md-6 mb-3">


<label class="form-label fw-bold">

Phone Number

</label>


<input
type="text"
class="form-control"
name="phone"
placeholder="Enter phone number"
required
>


</div>


</div>





<div class="mb-3">


<label class="form-label fw-bold">

Address / Location

</label>


<textarea

class="form-control"

name="address"

rows="2"

placeholder="Enter your address"

required

></textarea>


</div>






<div class="row">


<div class="col-md-6 mb-3">


<label class="form-label fw-bold">

Password

</label>


<input

type="password"

class="form-control"

name="password"

placeholder="Create password"

required

>


</div>




<div class="col-md-6 mb-3">


<label class="form-label fw-bold">

Confirm Password

</label>


<input

type="password"

class="form-control"

name="confirm_password"

placeholder="Confirm password"

required

>


</div>



</div>






<button 
type="submit"
class="btn-register mt-3"
>


<i class="fas fa-user-plus me-2"></i>

Create Account


</button>




</form>





<div class="login-link">


<p>

Already have an account?

<a href="login.php">

Login here

</a>

</p>


</div>



<?php endif; ?>





<div class="back-home">


<a href="../index.php">


<i class="fas fa-arrow-left me-1"></i>

Back to Home


</a>


</div>




</div>


</div>


</div>

</div>

</div>




<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>


</body>

</html>