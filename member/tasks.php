<?php
require_once "../includes/auth.php";
require_role("member");
require_once "../config/database.php";

$page_title = "My Tasks";
$base_path = "../";

$user_id = (int)$_SESSION["user_id"];
$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_status"])) {
    $task_id = (int)($_POST["task_id"] ?? 0);
    $status = $_POST["status"] ?? "";

    if (!in_array($status, ["To-Do", "In Progress", "Completed"], true)) {
        $error = "Invalid task status.";
    } else {
        $stmt = $conn->prepare("UPDATE tasks SET status = ? WHERE id = ? AND assigned_to = ?");
        $stmt->bind_param("sii", $status, $task_id, $user_id);

        if ($stmt->execute() && $stmt->affected_rows >= 0) {
            $message = "Task status updated.";
        } else {
            $error = "Could not update task.";
        }
        $stmt->close();
    }
}

$filter = $_GET["status"] ?? "";

if (in_array($filter, ["To-Do", "In Progress", "Completed"], true)) {
    $stmt = $conn->prepare("
        SELECT tasks.*, projects.name AS project_name
        FROM tasks
        LEFT JOIN projects ON tasks.project_id = projects.id
        WHERE tasks.assigned_to = ? AND tasks.status = ?
        ORDER BY tasks.due_date ASC, tasks.created_at DESC
    ");
    $stmt->bind_param("is", $user_id, $filter);
    $stmt->execute();
    $tasks = $stmt->get_result();
} else {
    $stmt = $conn->prepare("
        SELECT tasks.*, projects.name AS project_name
        FROM tasks
        LEFT JOIN projects ON tasks.project_id = projects.id
        WHERE tasks.assigned_to = ?
        ORDER BY tasks.due_date ASC, tasks.created_at DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $tasks = $stmt->get_result();
}

require_once "../includes/header.php";
require_once "../includes/navbar.php";
?>

<main class="main-content">
    <header class="dashboard-header">
        <div>
            <h1>My Tasks</h1>
            <p>View and update your assigned tasks.</p>
        </div>
    </header>

    <?php if ($message): ?><div class="success-message"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="error-message"><?= htmlspecialchars($error) ?></div><?php endif; ?>

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
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Due Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($tasks->num_rows): ?>
                    <?php while ($task = $tasks->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($task["title"]) ?></strong>
                                <?php if ($task["description"]): ?>
                                    <small class="block-text"><?= htmlspecialchars($task["description"]) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($task["project_name"] ?? "No Project") ?></td>
                            <td>
                                <form method="POST" class="inline-status-form">
                                    <input type="hidden" name="task_id" value="<?= (int)$task["id"] ?>">
                                    <select name="status" onchange="this.form.submit()">
                                        <?php foreach (["To-Do", "In Progress", "Completed"] as $status): ?>
                                            <option value="<?= $status ?>" <?= $task["status"] === $status ? "selected" : "" ?>><?= $status ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                            </td>
                            <td><?= htmlspecialchars($task["priority"]) ?></td>
                            <td><?= $task["due_date"] ? date("d M Y", strtotime($task["due_date"])) : "No due date" ?></td>
                            <td><a class="button small-button" href="task_details.php?id=<?= (int)$task["id"] ?>">View Details</a></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="empty-cell">No tasks found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<?php require_once "../includes/footer.php"; ?>
