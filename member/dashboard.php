<?php
require_once "../includes/auth.php";
require_role("member");
require_once "../config/database.php";

$page_title = "Member Dashboard";
$base_path = "../";

$user_id = (int)$_SESSION["user_id"];

$total = (int)$conn->query("SELECT COUNT(*) AS total FROM tasks WHERE assigned_to = $user_id")->fetch_assoc()["total"];
$todo = (int)$conn->query("SELECT COUNT(*) AS total FROM tasks WHERE assigned_to = $user_id AND status = 'To-Do'")->fetch_assoc()["total"];
$progress = (int)$conn->query("SELECT COUNT(*) AS total FROM tasks WHERE assigned_to = $user_id AND status = 'In Progress'")->fetch_assoc()["total"];
$completed = (int)$conn->query("SELECT COUNT(*) AS total FROM tasks WHERE assigned_to = $user_id AND status = 'Completed'")->fetch_assoc()["total"];

$stmt = $conn->prepare("
    SELECT tasks.id, tasks.title, tasks.status, tasks.priority, tasks.due_date,
           projects.name AS project_name
    FROM tasks
    LEFT JOIN projects ON tasks.project_id = projects.id
    WHERE tasks.assigned_to = ?
    ORDER BY tasks.due_date ASC, tasks.created_at DESC
    LIMIT 8
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$tasks = $stmt->get_result();

require_once "../includes/header.php";
require_once "../includes/navbar.php";
?>

<main class="main-content">
    <header class="dashboard-header">
        <div>
            <h1>Member Dashboard</h1>
            <p>Welcome, <?= htmlspecialchars($_SESSION["name"]) ?>. Here are your assigned tasks.</p>
        </div>
    </header>

    <section class="stats-grid">
        <div class="stat-card"><h3>My Tasks</h3><p><?= $total ?></p></div>
        <div class="stat-card"><h3>To-Do</h3><p><?= $todo ?></p></div>
        <div class="stat-card"><h3>In Progress</h3><p><?= $progress ?></p></div>
        <div class="stat-card"><h3>Completed</h3><p><?= $completed ?></p></div>
    </section>

    <section class="recent-tasks">
        <div class="section-header">
            <h2>My Recent Tasks</h2>
            <a class="button" href="tasks.php">View All</a>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Project</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Due Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($tasks->num_rows): ?>
                    <?php while ($task = $tasks->fetch_assoc()): ?>
                        <tr>
                            <td><a class="link" href="task_details.php?id=<?= (int)$task["id"] ?>"><?= htmlspecialchars($task["title"]) ?></a></td>
                            <td><?= htmlspecialchars($task["project_name"] ?? "No Project") ?></td>
                            <td><?= htmlspecialchars($task["status"]) ?></td>
                            <td><?= htmlspecialchars($task["priority"]) ?></td>
                            <td><?= $task["due_date"] ? date("d M Y", strtotime($task["due_date"])) : "No due date" ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="empty-cell">No tasks have been assigned to you yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<?php require_once "../includes/footer.php"; ?>
