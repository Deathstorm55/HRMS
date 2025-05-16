<?php
session_start();
require 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_SESSION['user_id'];
    $type = $conn->real_escape_string($_POST['complaint_type']);
    $desc = $conn->real_escape_string($_POST['description']);

    $stmt = $conn->prepare("
        INSERT INTO complaints 
        (student_id, complaint_type, complaint_description)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("iss", $student_id, $type, $desc);
    $stmt->execute();
    
    header("Location: dashboard.php");
}
?>