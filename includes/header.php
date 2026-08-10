<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$page_title = $page_title ?? "Task Management System";
$base_path = $base_path ?? "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($base_path) ?>task.png">
    <link rel="stylesheet" href="<?= htmlspecialchars($base_path) ?>css/style.css">
</head>
<body>
