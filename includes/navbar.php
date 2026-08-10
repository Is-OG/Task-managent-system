<?php
$role = $_SESSION["role"] ?? "";
$is_admin = ($role === "admin");
$base_path = $base_path ?? "";
?>
<button class="menu-button" type="button" onclick="toggleSidebar()">☰ Menu</button>

<aside class="sidebar" id="sidebar">
    <div class="brand">
        <div>
            <h2>Task Manager</h2>
            <span><?= $is_admin ? "Admin Panel" : "Member Panel" ?></span>
        </div>
    </div>

    <nav>
        <?php if ($is_admin): ?>
            <a href="<?= $base_path ?>admin/dashboard.php">Dashboard</a>
            <a href="<?= $base_path ?>admin/project.php">Projects</a>
            <a href="<?= $base_path ?>admin/tasks.php">Tasks</a>
            <a href="<?= $base_path ?>admin/add_task.php">Add Task</a>
            <a href="<?= $base_path ?>admin/users.php">Users</a>
            <a href="<?= $base_path ?>admin/reports.php">Reports</a>
        <?php else: ?>
            <a href="<?= $base_path ?>member/dashboard.php">Dashboard</a>
            <a href="<?= $base_path ?>member/tasks.php">My Tasks</a>
            <a href="<?= $base_path ?>member/reports.php">My Reports</a>
        <?php endif; ?>
        <a href="<?= $base_path ?>logout.php">Logout</a>
    </nav>
</aside>
