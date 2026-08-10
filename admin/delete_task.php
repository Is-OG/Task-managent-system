<?php
require_once "../includes/auth.php";
require_role("admin");
require_once "../config/database.php";

$task_id = (int)($_GET["id"] ?? 0);

if ($task_id > 0) {
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->bind_param("i", $task_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: tasks.php");
exit();
?>
