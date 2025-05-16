<?php
require 'db_config.php';

$checkIn = $_POST['checkIn'];
$checkOut = $_POST['checkOut'];

$stmt = $conn->prepare("
    SELECT h.*, 
    (h.capacity - COALESCE(SUM(g.number_of_guests), 0)) AS available
    FROM halls h
    LEFT JOIN guest_bookings g ON h.hall_id = g.hall_id
    AND (g.check_in_date <= ? AND g.check_out_date >= ?)
    GROUP BY h.hall_id
    HAVING available > 0
");
$stmt->bind_param("ss", $checkOut, $checkIn);
$stmt->execute();

echo json_encode([
    'data' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)
]);
?>