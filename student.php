<?php
// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'dms_db');

// Connect to MySQL database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$errors = [];
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize inputs
    $code = mysqli_real_escape_string($conn, $_POST['code']);
    $firstname = mysqli_real_escape_string($conn, $_POST['firstname']);
    $middlename = mysqli_real_escape_string($conn, $_POST['middlename']);
    $lastname = mysqli_real_escape_string($conn, $_POST['lastname']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);
    $course = mysqli_real_escape_string($conn, $_POST['course']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $emergency_name = mysqli_real_escape_string($conn, $_POST['emergency_name']);
    $emergency_contact = mysqli_real_escape_string($conn, $_POST['emergency_contact']);
    $emergency_address = mysqli_real_escape_string($conn, $_POST['emergency_address']);
    $emergency_relation = mysqli_real_escape_string($conn, $_POST['emergency_relation']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

  

   
    // Validation
    if (empty($code)) $errors[] = "Student code is required";
    if (empty($firstname)) $errors[] = "First name is required";
    if (empty($lastname)) $errors[] = "Last name is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if (strlen($contact) < 10) $errors[] = "Invalid contact number";
    
      // Add password validation
      if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    } elseif (!preg_match("/[A-Z]/", $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    } elseif (!preg_match("/[a-z]/", $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    } elseif (!preg_match("/[0-9]/", $password)) {
        $errors[] = "Password must contain at least one number";
    } elseif (!preg_match("/[^A-Za-z0-9]/", $password)) {
        $errors[] = "Password must contain at least one special character";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    if (empty($errors)) {
        // Hash password before storing
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Update SQL query to include password
        $sql = "INSERT INTO student_list (
            code, password, firstname, middlename, lastname, department, 
            course, gender, contact, email, address, emergency_name, 
            emergency_contact, emergency_address, emergency_relation
        ) VALUES (
            '$code', '$hashed_password', '$firstname', '$middlename', 
            '$lastname', '$department', '$course', '$gender', '$contact', 
            '$email', '$address', '$emergency_name', '$emergency_contact', 
            '$emergency_address', '$emergency_relation'
        )";

if ($conn->query($sql)) {
    $_SESSION['registration_success'] = true;
    header("Location: login.php");
    exit();
} else {
    $errors[] = "Error: " . $conn->error;
}
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .registration-form { max-width: 800px; margin: 50px auto; padding: 30px; border: 1px solid #ddd; border-radius: 10px; }
        .section-title { border-bottom: 2px solid #007bff; padding-bottom: 10px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="registration-form">
            <h2 class="text-center mb-4">Student Registration Form</h2>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo $error; ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    Registration successful!
                </div>
            <?php else: ?>

            <form method="POST" action="">
                <!-- Personal Information -->
                <div class="section-title">Personal Information</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Student Code</label>
                        <input type="text" class="form-control" name="code" required>
                    </div>
                    
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Password</label>
                                <input type="password" class="form-control" name="password" id="password" required>
                                <div class="form-text">Must contain: 8+ characters, uppercase, lowercase, number, and special character</div>
                                <div id="password-strength" class="mt-1"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" name="confirm_password" required>
                            </div>
                        </div>
                        <div class="form-check mt-2">
                            <input type="checkbox" class="form-check-input" id="showPassword">
                            <label class="form-check-label" for="showPassword">Show Passwords</label>
                        </div>
                    <div class="col-md-6">
                        <label class="form-label">Gender</label>
                        <select class="form-select" name="gender" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">First Name</label>
                        <input type="text" class="form-control" name="firstname" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Middle Name</label>
                        <input type="text" class="form-control" name="middlename">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Last Name</label>
                        <input type="text" class="form-control" name="lastname" required>
                    </div>
                </div>

                <!-- Academic Information -->
                <div class="section-title mt-4">Academic Information</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Department</label>
                        <select class="form-select" name="department" required>
                            <option value="College of Engineering">College of Engineering</option>
                            <option value="College of Social Science">College of Social Science</option>
                            <!-- Add more departments as needed -->
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Course</label>
                        <select class="form-select" name="course" required>
                            <option value="Bachelor of Science in Computer Science">BSC Computer Science</option>
                            <option value="Bachelor of Science in Psychology">BSC Psychology</option>
                            <!-- Add more courses as needed -->
                        </select>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="section-title mt-4">Contact Information</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Contact Number</label>
                        <input type="tel" class="form-control" name="contact" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" rows="3" required></textarea>
                    </div>
                </div>

                <!-- Emergency Contact -->
                <div class="section-title mt-4">Emergency Contact</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="emergency_name" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Relationship</label>
                        <input type="text" class="form-control" name="emergency_relation" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact Number</label>
                        <input type="tel" class="form-control" name="emergency_contact" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="emergency_address" rows="2" required></textarea>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary w-100">Submit Registration</button>
                </div>
                <p>Already a student?</p><a href="login.php">Login</a>
            </form>
            
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
<script>
// Password strength indicator
document.getElementById('password').addEventListener('input', function(e) {
    const password = e.target.value;
    const strengthBadge = document.getElementById('password-strength');
    let strength = 0;

    // Strength criteria
    if (password.match(/[A-Z]/)) strength++;
    if (password.match(/[a-z]/)) strength++;
    if (password.match(/[0-9]/)) strength++;
    if (password.match(/[^A-Za-z0-9]/)) strength++;
    if (password.length >= 8) strength++;

    const strengthText = ['Very Weak', 'Weak', 'Moderate', 'Strong', 'Very Strong'][strength - 1] || '';
    const colors = ['#dc3545', '#ffc107', '#ffc107', '#28a745', '#28a745'];
    
    strengthBadge.style.color = colors[strength - 1] || '#000';
    strengthBadge.textContent = strengthText;
});

// Show password toggle
document.getElementById('showPassword').addEventListener('change', function(e) {
    const passwordFields = document.querySelectorAll('input[type="password"]');
    passwordFields.forEach(field => {
        field.type = e.target.checked ? 'text' : 'password';
    });
});
</script>