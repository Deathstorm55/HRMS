<?php
require 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $check_in = $conn->real_escape_string($_POST['checkIn']);
    $check_out = $conn->real_escape_string($_POST['checkOut']);
    $student_type = $conn->real_escape_string($_POST['studentType']);

    // Query to get available halls (dorms) for the student type
    $stmt = $conn->prepare("
        SELECT d.id AS hall_id, d.name AS hall_name, d.category,
               SUM(r.slots) AS capacity,
               COALESCE(SUM(r.slots - (SELECT COUNT(*) FROM student_list sl WHERE sl.room_id = r.id)), 0) AS available
        FROM dorm_list d
        JOIN room_list r ON d.id = r.dorm_id
        WHERE d.category = ? AND d.status = 1 AND d.delete_flag = 0 
        AND r.status = 1 AND r.delete_flag = 0
        AND NOT EXISTS (
            SELECT 1 FROM guest_bookings g
            WHERE g.hall_id = d.id
            AND (
                (g.check_in_date <= ? AND g.check_out_date >= ?) 
                OR (g.check_in_date BETWEEN ? AND ?)
            )
            AND SUM(g.number_of_guests) >= SUM(r.slots)
        )
        GROUP BY d.id, d.name, d.category
    ");
    $stmt->bind_param("sssss", $student_type, $check_out, $check_in, $check_in, $check_out);
    $stmt->execute();
    $result = $stmt->get_result();
    $halls = $result->fetch_all(MYSQLI_ASSOC);

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode(['data' => $halls]);

    $stmt->close();
    $conn->close();
}
?>