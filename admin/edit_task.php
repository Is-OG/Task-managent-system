<?php
require_once "../includes/auth.php";
require_role("admin");
require_once "../config/database.php";

$page_title = "Edit Task";
$base_path = "../";

$task_id = (int)($_GET["id"] ?? $_POST["task_id"] ?? 0);

if ($task_id <= 0) {
    header("Location: tasks.php");
    exit();
}

$error = "";

$stmt = $conn->prepare("SELECT * FROM tasks WHERE id = ?");
$stmt->bind_param("i", $task_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: tasks.php");
    exit();
}

$task = $result->fetch_assoc();
$stmt->close();

$projects = $conn->query("SELECT id, name FROM projects ORDER BY name");
$members = $conn->query("SELECT id, name, email FROM users WHERE role = 'member' ORDER BY name");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $project_id = (int)($_POST["project_id"] ?? 0);
    $assigned_to = (int)($_POST["assigned_to"] ?? 0);
    $status = $_POST["status"] ?? "To-Do";
    $priority = $_POST["priority"] ?? "Medium";
    $due_date = $_POST["due_date"] ?: null;

    if ($title === "" || $project_id <= 0) {
        $error = "Task title and project are required.";
    } else {
        $stmt = $conn->prepare("
            UPDATE tasks
            SET title = ?, description = ?, project_id = ?, assigned_to = NULLIF(?, 0),
                status = ?, priority = ?, due_date = ?
            WHERE id = ?
        ");
        $stmt->bind_param("ssiisssi", $title, $description, $project_id, $assigned_to, $status, $priority, $due_date, $task_id);

        if ($stmt->execute()) {
            header("Location: tasks.php");
            exit();
        }

        $error = "Could not update task: " . $stmt->error;
        $stmt->close();
    }
}

require_once "../includes/header.php";
require_once "../includes/navbar.php";
?>

<main class="main-content">
    <header class="dashboard-header">
        <div>
            <h1>Edit Task</h1>
            <p>Update task information and assignment.</p>
        </div>
    </header>

    <?php if ($error): ?><div class="error-message"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <section class="form-section">
        <form method="POST">
            <input type="hidden" name="task_id" value="<?= $task_id ?>">

            <div class="form-group">
                <label for="title">Task Title</label>
                <input type="text" id="title" name="title" required value="<?= htmlspecialchars($_POST["title"] ?? $task["title"]) ?>">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"><?= htmlspecialchars($_POST["description"] ?? $task["description"]) ?></textarea>
            </div>

            <div class="two-column">
                <div class="form-group">
                    <label for="project_id">Project</label>
                    <select id="project_id" name="project_id" required>
                        <?php while ($project = $projects->fetch_assoc()): ?>
                            <option value="<?= (int)$project["id"] ?>" <?= (($task["project_id"] == $project["id"]) ? "selected" : "") ?>>
                                <?= htmlspecialchars($project["name"]) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="assigned_to">Assign To</label>
                    <select id="assigned_to" name="assigned_to">
                        <option value="0">Unassigned</option>
                        <?php while ($member = $members->fetch_assoc()): ?>
                            <option value="<?= (int)$member["id"] ?>" <?= (($task["assigned_to"] == $member["id"]) ? "selected" : "") ?>>
                                <?= htmlspecialchars($member["name"]) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="three-column">
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <?php foreach (["To-Do", "In Progress", "Completed"] as $status): ?>
                            <option value="<?= $status ?>" <?= $task["status"] === $status ? "selected" : "" ?>><?= $status ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="priority">Priority</label>
                    <select id="priority" name="priority">
                        <?php foreach (["Low", "Medium", "High"] as $priority): ?>
                            <option value="<?= $priority ?>" <?= $task["priority"] === $priority ? "selected" : "" ?>><?= $priority ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="due_date">Due Date</label>
                    <input type="date" id="due_date" name="due_date" value="<?= htmlspecialchars($task["due_date"] ?? "") ?>">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit">Save Changes</button>
                <a class="button secondary-button" href="tasks.php">Cancel</a>
            </div>
        </form>
    </section>
</main>

<?php require_once "../includes/footer.php"; ?>
