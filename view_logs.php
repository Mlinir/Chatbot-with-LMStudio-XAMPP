<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

require 'config.php';

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT id, content, created_date FROM log_entries WHERE user_id = ? ORDER BY created_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$logs = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<?php foreach ($logs as $log) : ?>
    <div class="log-entry" id="<?php echo $log['id']; ?>">
        <strong><?php echo htmlspecialchars($log['created_date']); ?></strong>
        <?php
        $log_content = json_decode($log['content'], true);
        $first_user_message = json_encode($log_content[0]['content']);
        ?>
        <p><?php echo htmlspecialchars($first_user_message); ?></p>
        <input type="submit" value="Delete" class="delete_log">
    </div>
<?php endforeach; ?>