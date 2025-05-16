<?php
session_start();
require 'db_config.php';

$error = '';
$email = '';
if (isset($_SESSION['registration_success'])) {
    $success_message = "Registration successful! Please log in.";
    unset($_SESSION['registration_success']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = trim($_POST['identifier']);
    $password = trim($_POST['password']);

    // Check if input is email or student code
    $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'code';
    
    $sql = "SELECT id, code, password, firstname, lastname FROM student_list WHERE $field = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_code'] = $user['code'];
            $_SESSION['user_name'] = $user['firstname'] . ' ' . $user['lastname'];
            header("Location: dashboard.php"); // Redirect to dashboard
            exit();
        } else {
            $error = "Invalid email/code or password";
        }
    } else {
        $error = "Account not found";
    }
    
    $email = htmlspecialchars($identifier);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .login-form { max-width: 400px; margin: 50px auto; padding: 30px; border: 1px solid #ddd; border-radius: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-form">
            <h2 class="text-center mb-4">Student Login</h2>
            
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="mb-3">
                    <label class="form-label">Email or Student Code</label>
                    <input type="text" class="form-control" name="identifier" value="<?php echo $email; ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Login</button>
                    <a href="student.php" class="btn btn-link">Create New Account</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>