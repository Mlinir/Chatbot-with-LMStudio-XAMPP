<?php
session_start();
if (isset($_POST['id'])) {
    $_SESSION['id'] = $_POST['id'];
    echo 'Session ID updated successfully';
} else {
    echo 'No ID provided';
}
?>
