<?php
require_once "../includes/auth.php";
require_role("admin");
require_once "../config/database.php";

$page_title = "Add Task";
$base_path = "../";

$error = "";

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

    $allowed_statuses = ["To-Do", "In Progress", "Completed"];
    $allowed_priorities = ["Low", "Medium", "High"];

    if ($title === "") {
        $error = "Task title is required.";
    } elseif (!in_array($status, $allowed_statuses, true) || !in_array($priority, $allowed_priorities, true)) {
        $error = "Invalid status or priority.";
    } elseif ($project_id <= 0) {
        $error = "Please select a project.";
    } else {
        if ($assigned_to > 0) {
            $check = $conn->prepare("SELECT id FROM users WHERE id = ? AND role = 'member'");
            $check->bind_param("i", $assigned_to);
            $check->execute();
            if ($check->get_result()->num_rows === 0) {
                $assigned_to = 0;
            }
            $check->close();
        }

        $stmt = $conn->prepare("
            INSERT INTO tasks (title, description, project_id, assigned_to, status, priority, due_date)
            VALUES (?, ?, ?, NULLIF(?, 0), ?, ?, ?)
        ");
        $stmt->bind_param("ssiisss", $title, $description, $project_id, $assigned_to, $status, $priority, $due_date);

        if ($stmt->execute()) {
            header("Location: tasks.php");
            exit();
        }
        $error = "Could not create task: " . $stmt->error;
        $stmt->close();
    }
}

require_once "../includes/header.php";
require_once "../includes/navbar.php";
?>

<main class="main-content">
    <header class="dashboard-header">
        <div>
            <h1>Add Task</h1>
            <p>Create a task and assign it to a team member.</p>
        </div>
    </header>

    <?php if ($error): ?><div class="error-message"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <section class="form-section">
        <form method="POST">
            <div class="form-group">
                <label for="title">Task Title</label>
                <input type="text" id="title" name="title" required value="<?= htmlspecialchars($_POST["title"] ?? "") ?>">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"><?= htmlspecialchars($_POST["description"] ?? "") ?></textarea>
            </div>

            <div class="two-column">
                <div class="form-group">
                    <label for="project_id">Project</label>
                    <select id="project_id" name="project_id" required>
                        <option value="">Select project</option>
                        <?php while ($project = $projects->fetch_assoc()): ?>
                            <option value="<?= (int)$project["id"] ?>" <?= (($_POST["project_id"] ?? "") == $project["id"]) ? "selected" : "" ?>>
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
                            <option value="<?= (int)$member["id"] ?>" <?= (($_POST["assigned_to"] ?? "") == $member["id"]) ? "selected" : "" ?>>
                                <?= htmlspecialchars($member["name"]) ?> (<?= htmlspecialchars($member["email"]) ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="three-column">
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="To-Do">To-Do</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="priority">Priority</label>
                    <select id="priority" name="priority">
                        <option value="Low">Low</option>
                        <option value="Medium" selected>Medium</option>
                        <option value="High">High</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="due_date">Due Date</label>
                    <input type="date" id="due_date" name="due_date" value="<?= htmlspecialchars($_POST["due_date"] ?? "") ?>">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit">Create Task</button>
                <a class="button secondary-button" href="tasks.php">Cancel</a>
            </div>
        </form>
    </section>
</main>

<?php require_once "../includes/footer.php"; ?>
