<?php
require_once "../includes/auth.php";
require_role("admin");
require_once "../config/database.php";

$page_title = "Manage Tasks";
$base_path = "../";

$status_filter = $_GET["status"] ?? "";

if (in_array($status_filter, ["To-Do", "In Progress", "Completed"], true)) {
    $stmt = $conn->prepare("
        SELECT tasks.*, projects.name AS project_name, users.name AS member_name
        FROM tasks
        LEFT JOIN projects ON tasks.project_id = projects.id
        LEFT JOIN users ON tasks.assigned_to = users.id
        WHERE tasks.status = ?
        ORDER BY tasks.due_date ASC, tasks.created_at DESC
    ");
    $stmt->bind_param("s", $status_filter);
    $stmt->execute();
    $tasks = $stmt->get_result();
} else {
    $tasks = $conn->query("
        SELECT tasks.*, projects.name AS project_name, users.name AS member_name
        FROM tasks
        LEFT JOIN projects ON tasks.project_id = projects.id
        LEFT JOIN users ON tasks.assigned_to = users.id
        ORDER BY tasks.due_date ASC, tasks.created_at DESC
    ");
}

require_once "../includes/header.php";
require_once "../includes/navbar.php";
?>

<main class="main-content">
    <header class="dashboard-header">
        <div>
            <h1>Tasks</h1>
            <p>Manage and assign project tasks.</p>
        </div>
        <a class="button" href="add_task.php">+ Add Task</a>
    </header>

    <section class="recent-tasks">
        <div class="task-filters">
            <a class="button filter-button" href="tasks.php">All</a>
            <a class="button filter-button" href="tasks.php?status=To-Do">To-Do</a>
            <a class="button filter-button" href="tasks.php?status=In%20Progress">In Progress</a>
            <a class="button filter-button" href="tasks.php?status=Completed">Completed</a>
        </div>
    </section>

    <section class="recent-tasks">
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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($tasks && $tasks->num_rows): ?>
                    <?php while ($task = $tasks->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($task["title"]) ?></strong>
                                <?php if ($task["description"]): ?>
                                    <small class="block-text"><?= htmlspecialchars($task["description"]) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($task["project_name"] ?? "No Project") ?></td>
                            <td><?= htmlspecialchars($task["member_name"] ?? "Unassigned") ?></td>
                            <td>
                                <span class="status <?= $task["status"] === "Completed" ? "status-completed" : ($task["status"] === "In Progress" ? "status-progress" : "status-todo") ?>">
                                    <?= htmlspecialchars($task["status"]) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($task["priority"]) ?></td>
                            <td><?= $task["due_date"] ? date("d M Y", strtotime($task["due_date"])) : "No due date" ?></td>
                            <td>
                                <div class="table-actions">
                                    <a class="button small-button" href="edit_task.php?id=<?= (int)$task["id"] ?>">Edit</a>
                                    <a class="button small-button danger-button" href="delete_task.php?id=<?= (int)$task["id"] ?>" onclick="return confirmDelete('<?= htmlspecialchars(addslashes($task["title"])) ?>');">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="empty-cell">No tasks found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<?php require_once "../includes/footer.php"; ?>
