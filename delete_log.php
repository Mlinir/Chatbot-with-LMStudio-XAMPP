<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require 'config.php';

$log_id = $_POST['log_id'];
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("DELETE FROM log_entries WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $log_id, $user_id);

$response = [];
if ($stmt->execute()) {
    $response['success'] = true;
} else {
    $response['error'] = 'Failed to delete the log entry';
}

$stmt->close();
echo json_encode($response);
?>
