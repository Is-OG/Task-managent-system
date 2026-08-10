<?php
require_once "../includes/auth.php";
require_role("admin");
require_once "../config/database.php";

$page_title = "Admin Dashboard";
$base_path = "../";

$total_projects = $conn->query("SELECT COUNT(*) AS total FROM projects")->fetch_assoc()["total"];
$total_tasks = $conn->query("SELECT COUNT(*) AS total FROM tasks")->fetch_assoc()["total"];
$total_members = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'member'")->fetch_assoc()["total"];
$completed_tasks = $conn->query("SELECT COUNT(*) AS total FROM tasks WHERE status = 'Completed'")->fetch_assoc()["total"];

$recent = $conn->query("
    SELECT tasks.id, tasks.title, tasks.status, tasks.priority, tasks.due_date,
           projects.name AS project_name, users.name AS member_name
    FROM tasks
    LEFT JOIN projects ON tasks.project_id = projects.id
    LEFT JOIN users ON tasks.assigned_to = users.id
    ORDER BY tasks.created_at DESC
    LIMIT 8
");

require_once "../includes/header.php";
require_once "../includes/navbar.php";
?>

<main class="main-content">
    <header class="dashboard-header">
        <div>
            <h1>Admin Dashboard</h1>
            <p>Welcome, <?= htmlspecialchars($_SESSION["name"]) ?>. Manage projects, tasks and team members.</p>
        </div>
    </header>

    <section class="stats-grid">
        <div class="stat-card"><h3>Total Projects</h3><p><?= $total_projects ?></p></div>
        <div class="stat-card"><h3>Total Tasks</h3><p><?= $total_tasks ?></p></div>
        <div class="stat-card"><h3>Team Members</h3><p><?= $total_members ?></p></div>
        <div class="stat-card"><h3>Completed Tasks</h3><p><?= $completed_tasks ?></p></div>
    </section>

    <section class="recent-tasks">
        <div class="section-header">
            <h2>Recent Tasks</h2>
            <a class="button" href="add_task.php">+ Add Task</a>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Project</th>
                        <th>Assigned To</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Due Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($recent && $recent->num_rows): ?>
                    <?php while ($task = $recent->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($task["title"]) ?></td>
                            <td><?= htmlspecialchars($task["project_name"] ?? "No Project") ?></td>
                            <td><?= htmlspecialchars($task["member_name"] ?? "Unassigned") ?></td>
                            <td>
                                <span class="status <?= $task["status"] === "Completed" ? "status-completed" : ($task["status"] === "In Progress" ? "status-progress" : "status-todo") ?>">
                                    <?= htmlspecialchars($task["status"]) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($task["priority"]) ?></td>
                            <td><?= $task["due_date"] ? date("d M Y", strtotime($task["due_date"])) : "No due date" ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="empty-cell">No tasks yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<?php require_once "../includes/footer.php"; ?>
