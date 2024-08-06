<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require 'config.php';

$log_id = $_POST['log_id'];
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT content FROM log_entries WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $log_id, $user_id);

$stmt->execute();
$result = $stmt->get_result();
$log_entry = $result->fetch_assoc();
$stmt->close();

$log_content = json_decode($log_entry['content'], true);
echo json_encode($log_content);
?>
