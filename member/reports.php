<?php
require_once "../includes/auth.php";
require_role("member");
require_once "../config/database.php";

$page_title = "My Reports";
$base_path = "../";

$user_id = (int)$_SESSION["user_id"];

$total = (int)$conn->query("SELECT COUNT(*) AS total FROM tasks WHERE assigned_to = $user_id")->fetch_assoc()["total"];
$todo = (int)$conn->query("SELECT COUNT(*) AS total FROM tasks WHERE assigned_to = $user_id AND status = 'To-Do'")->fetch_assoc()["total"];
$progress = (int)$conn->query("SELECT COUNT(*) AS total FROM tasks WHERE assigned_to = $user_id AND status = 'In Progress'")->fetch_assoc()["total"];
$completed = (int)$conn->query("SELECT COUNT(*) AS total FROM tasks WHERE assigned_to = $user_id AND status = 'Completed'")->fetch_assoc()["total"];
$overdue = (int)$conn->query("SELECT COUNT(*) AS total FROM tasks WHERE assigned_to = $user_id AND due_date IS NOT NULL AND due_date < CURDATE() AND status <> 'Completed'")->fetch_assoc()["total"];

$percentage = $total > 0 ? round(($completed / $total) * 100) : 0;

require_once "../includes/header.php";
require_once "../includes/navbar.php";
?>

<main class="main-content">
    <header class="dashboard-header">
        <div>
            <h1>My Reports</h1>
            <p>Your personal task progress summary.</p>
        </div>
    </header>

    <section class="stats-grid">
        <div class="stat-card"><h3>Total Tasks</h3><p><?= $total ?></p></div>
        <div class="stat-card"><h3>To-Do</h3><p><?= $todo ?></p></div>
        <div class="stat-card"><h3>In Progress</h3><p><?= $progress ?></p></div>
        <div class="stat-card"><h3>Completed</h3><p><?= $completed ?></p></div>
        <div class="stat-card"><h3>Overdue</h3><p><?= $overdue ?></p></div>
    </section>

    <section class="form-section">
        <h2>Overall Progress</h2>
        <div class="progress-container">
            <p><span>Completed tasks</span><strong><?= $percentage ?>%</strong></p>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= $percentage ?>%"></div>
            </div>
        </div>
    </section>
</main>

<?php require_once "../includes/footer.php"; ?>
