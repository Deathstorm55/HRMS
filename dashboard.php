<?php
session_start();
require 'db_config.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get student info
$student_id = $_SESSION['user_id'];
$bookings = [];

// Fetch student details including room and price
$student_stmt = $conn->prepare("
    SELECT s.code, s.student_type, s.department, s.course, 
           r.name AS room_name, r.price, d.name AS dorm_name
    FROM student_list s
    LEFT JOIN room_list r ON s.room_id = r.id
    LEFT JOIN dorm_list d ON r.dorm_id = d.id
    WHERE s.id = ?
");
$student_stmt->bind_param("i", $student_id);
$student_stmt->execute();
$student = $student_stmt->get_result()->fetch_assoc();

// Get halls (dorms) available for student's type
$student_type = $student['student_type'];
$halls_stmt = $conn->prepare("
    SELECT d.id AS hall_id, d.name AS hall_name, 
           SUM(r.slots) AS capacity, 
           COALESCE(SUM(r.slots - (SELECT COUNT(*) FROM student_list sl WHERE sl.room_id = r.id)), 0) AS available
    FROM dorm_list d
    JOIN room_list r ON d.id = r.dorm_id
    WHERE d.category = ? AND d.status = 1 AND d.delete_flag = 0 
    AND r.status = 1 AND r.delete_flag = 0
    GROUP BY d.id, d.name
");
$halls_stmt->bind_param("s", $student_type);
$halls_stmt->execute();
$halls = $halls_stmt->get_result();

// Get student's bookings
$booking_stmt = $conn->prepare("
    SELECT g.*, d.name AS hall_name 
    FROM guest_bookings g
    JOIN dorm_list d ON g.hall_id = d.id
    WHERE g.student_id = ?
");
$booking_stmt->bind_param("i", $student_id);
$booking_stmt->execute();
$bookings = $booking_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['book_guest'])) {
        // Guest booking handling
        $hall_id = (int)$_POST['hall_id'];
        $guest_name = $conn->real_escape_string($_POST['guest_name']);
        $num_guests = (int)$_POST['num_guests'];
        $checkin = $conn->real_escape_string($_POST['checkin']);
        $checkout = $conn->real_escape_string($_POST['checkout']);

        // Validate number of guests
        if ($num_guests < 1 || $num_guests > 3) {
            $booking_error = "Number of guests must be between 1-3";
        } else {
            // Check availability
            $conn->autocommit(FALSE);
            try {
                // Check current capacity
                $hall = $conn->query("
                    SELECT SUM(r.slots) AS capacity, 
                           COALESCE(SUM(r.slots - (SELECT COUNT(*) FROM student_list sl WHERE sl.room_id = r.id)), 0) AS current_occupancy
                    FROM room_list r
                    WHERE r.dorm_id = $hall_id AND r.status = 1 AND r.delete_flag = 0
                ")->fetch_assoc();
                
                // Check date conflicts
                $conflict_check = $conn->prepare("
                    SELECT SUM(number_of_guests) AS total_guests 
                    FROM guest_bookings 
                    WHERE hall_id = ? 
                    AND (
                        (check_in_date <= ? AND check_out_date >= ?) 
                        OR (check_in_date BETWEEN ? AND ?)
                    )
                ");
                $conflict_check->bind_param("issss", $hall_id, $checkout, $checkin, $checkin, $checkout);
                $conflict_check->execute();
                $conflict_result = $conflict_check->get_result()->fetch_assoc();
                $total_guests = $conflict_result['total_guests'] ?? 0;
                
                if (($total_guests + $num_guests) > $hall['capacity']) {
                    throw new Exception("Not enough capacity for selected dates");
                }

                // Insert booking
                $stmt = $conn->prepare("
                    INSERT INTO guest_bookings 
                    (student_id, hall_id, guest_name, number_of_guests, check_in_date, check_out_date)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->bind_param("iisiss", $student_id, $hall_id, $guest_name, $num_guests, $checkin, $checkout);
                $stmt->execute();
                
                $conn->commit();
                header("Refresh:0"); // Reload page
            } catch (Exception $e) {
                $conn->rollback();
                $booking_error = $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .dashboard-section { margin-bottom: 2rem; padding: 1.5rem; border: 1px solid #dee2e6; border-radius: 0.25rem; }
        .hall-card { margin-bottom: 1rem; }
        .student-info { background-color: #f8f9fa; padding: 1rem; border-radius: 0.25rem; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></h2>
        
        <!-- Student Information -->
        <div class="dashboard-section student-info">
            <h3>Your Information</h3>
            <p><strong>Student Code:</strong> <?= htmlspecialchars($student['code']) ?></p>
            <p><strong>Student Type:</strong> <?= htmlspecialchars($student['student_type']) ?></p>
            <p><strong>Department:</strong> <?= htmlspecialchars($student['department']) ?></p>
            <p><strong>Course:</strong> <?= htmlspecialchars($student['course']) ?></p>
            <p><strong>Room:</strong> <?= htmlspecialchars($student['dorm_name'] . ' - ' . $student['room_name']) ?></p>
            <p><strong>Room Price:</strong> $<?= number_format($student['price'], 2) ?></p>
        </div>

        <!-- Hall Availability Check -->
        <div class="dashboard-section">
            <h3>Check Hall Availability</h3>
            <form id="availabilityForm" class="row g-3">
                <div class="col-md-4">
                    <input type="date" class="form-control" id="checkInDate" required>
                </div>
                <div class="col-md-4">
                    <input type="date" class="form-control" id="checkOutDate" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">Check Availability</button>
                </div>
            </form>
            <div id="availabilityResults" class="mt-3 row"></div>
        </div>

        <!-- Guest Booking -->
        <div class="dashboard-section">
            <h3>Guest Booking</h3>
            <?php if (isset($booking_error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($booking_error) ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-4">
                        <select class="form-select" name="hall_id" required>
                            <option value="">Select Hall</option>
                            <?php while ($hall = $halls->fetch_assoc()): ?>
                                <option value="<?= $hall['hall_id'] ?>">
                                    <?= htmlspecialchars($hall['hall_name']) ?> 
                                    (Available: <?= $hall['available'] ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="guest_name" placeholder="Guest(s) Name" required>
                    </div>
                    <div class="col-md-2">
                        <input type="number" class="form-control" name="num_guests" min="1" max="3" value="1" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" name="book_guest" class="btn btn-primary w-100">Book Now</button>
                    </div>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <input type="date" class="form-control" name="checkin" required>
                    </div>
                    <div class="col-md-6">
                        <input type="date" class="form-control" name="checkout" required>
                    </div>
                </div>
            </form>
            
            <!-- Booking List -->
            <div class="mt-4">
                <h5>Your Bookings (<?= count($bookings) ?>)</h5>
                <div class="list-group">
                    <?php foreach ($bookings as $booking): ?>
                        <div class="list-group-item">
                            <?= htmlspecialchars($booking['guest_name']) ?> - 
                            <?= htmlspecialchars($booking['hall_name']) ?> 
                            (<?= $booking['check_in_date'] ?> to <?= $booking['check_out_date'] ?>)
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Complaint Form -->
        <div class="dashboard-section">
            <h3>Submit Complaint/Query</h3>
            <form method="POST" action="submit_complaint.php">
                <div class="mb-3">
                    <select class="form-select" name="complaint_type" required>
                        <option value="">Select Type</option>
                        <option value="Hall">Hall Issue</option>
                        <option value="Hall">Room Issue</option>
                        <option value="Staff">Staff Related</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="mb-3">
                    <textarea class="form-control" name="description" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-warning">Submit Complaint</button>
            </form>
        </div>

        <a href="logout.php" class="btn btn-secondary mt-3">Logout</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // AJAX for availability check
    $(document).ready(function() {
        $('#availabilityForm').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'check_availability.php',
                method: 'POST',
                data: {
                    checkIn: $('#checkInDate').val(),
                    checkOut: $('#checkOutDate').val(),
                    studentType: '<?= $student['student_type'] ?>'
                },
                dataType: 'json',
                success: function(response) {
                    let html = '';
                    if (response.data && response.data.length > 0) {
                        response.data.forEach(hall => {
                            html += `
                                <div class="col-md-4">
                                    <div class="card hall-card">
                                        <div class="card-body">
                                            <h5>${hall.hall_name}</h5>
                                            <p>Available: ${hall.available}/${hall.capacity}</p>
                                            <p>Type: ${hall.category}</p>
                                        </div>
                                    </div>
                                </div>`;
                        });
                    } else {
                        html = '<div class="col">No available halls</div>';
                    }
                    $('#availabilityResults').html(html);
                },
                error: function() {
                    $('#availabilityResults').html('<div class="col">Error checking availability</div>');
                }
            });
        });
    });
    </script>
</body>
</html>
<?php
// Close statements and connection
$student_stmt->close();
$halls_stmt->close();
$booking_stmt->close();
$conn->close();
?>