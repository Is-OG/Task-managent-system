<?php
require_once "../includes/auth.php";
require_role("member");
require_once "../config/database.php";

$page_title = "Task Details";
$base_path = "../";

$user_id = (int)$_SESSION["user_id"];
$task_id = (int)($_GET["id"] ?? $_POST["task_id"] ?? 0);

if ($task_id <= 0) {
    header("Location: tasks.php");
    exit();
}

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["update_status"])) {
        $status = $_POST["status"] ?? "";

        if (!in_array($status, ["To-Do", "In Progress", "Completed"], true)) {
            $error = "Invalid status.";
        } else {
            $stmt = $conn->prepare("UPDATE tasks SET status = ? WHERE id = ? AND assigned_to = ?");
            $stmt->bind_param("sii", $status, $task_id, $user_id);

            if ($stmt->execute()) {
                $message = "Task status updated.";
            } else {
                $error = "Could not update task.";
            }
            $stmt->close();
        }
    }

    if (isset($_POST["add_comment"])) {
        $comment = trim($_POST["comment"] ?? "");

        if ($comment === "") {
            $error = "Please enter a comment.";
        } else {
            $stmt = $conn->prepare("
                INSERT INTO comments (task_id, user_id, comment)
                SELECT ?, ?, ?
                FROM tasks
                WHERE id = ? AND assigned_to = ?
            ");
            $stmt->bind_param("iisii", $task_id, $user_id, $comment, $task_id, $user_id);

            if ($stmt->execute() && $stmt->affected_rows === 1) {
                $message = "Comment added successfully.";
            } else {
                $error = "You cannot comment on this task.";
            }
            $stmt->close();
        }
    }
}

$stmt = $conn->prepare("
    SELECT tasks.*, projects.name AS project_name, users.name AS assigned_member
    FROM tasks
    LEFT JOIN projects ON tasks.project_id = projects.id
    LEFT JOIN users ON tasks.assigned_to = users.id
    WHERE tasks.id = ? AND tasks.assigned_to = ?
");
$stmt->bind_param("ii", $task_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: tasks.php");
    exit();
}

$task = $result->fetch_assoc();
$stmt->close();

$stmt = $conn->prepare("
    SELECT comments.comment, comments.created_at, users.name AS user_name
    FROM comments
    LEFT JOIN users ON comments.user_id = users.id
    WHERE comments.task_id = ?
    ORDER BY comments.created_at DESC
");
$stmt->bind_param("i", $task_id);
$stmt->execute();
$comments = $stmt->get_result();

require_once "../includes/header.php";
require_once "../includes/navbar.php";
?>

<main class="main-content">
    <header class="dashboard-header">
        <div>
            <h1>Task Details</h1>
            <p>View your task information and add comments.</p>
        </div>
    </header>

    <?php if ($message): ?><div class="success-message"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="error-message"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <section class="form-section">
        <h2><?= htmlspecialchars($task["title"]) ?></h2>

        <div class="task-detail-item"><strong>Project:</strong> <?= htmlspecialchars($task["project_name"] ?? "No Project") ?></div>
        <div class="task-detail-item"><strong>Description:</strong><p><?= $task["description"] ? nl2br(htmlspecialchars($task["description"])) : "No description provided." ?></p></div>
        <div class="task-detail-item"><strong>Priority:</strong> <?= htmlspecialchars($task["priority"]) ?></div>
        <div class="task-detail-item"><strong>Due Date:</strong> <?= $task["due_date"] ? date("d M Y", strtotime($task["due_date"])) : "No due date" ?></div>
        <div class="task-detail-item"><strong>Assigned To:</strong> <?= htmlspecialchars($task["assigned_member"]) ?></div>
        <div class="task-detail-item"><strong>Current Status:</strong> <?= htmlspecialchars($task["status"]) ?></div>

        <div class="task-detail-item">
            <h3>Update Status</h3>
            <form method="POST">
                <input type="hidden" name="task_id" value="<?= $task_id ?>">
                <select name="status">
                    <?php foreach (["To-Do", "In Progress", "Completed"] as $status): ?>
                        <option value="<?= $status ?>" <?= $task["status"] === $status ? "selected" : "" ?>><?= $status ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="update_status">Update Status</button>
            </form>
        </div>
    </section>

    <section class="form-section">
        <h2>Comments</h2>

        <form method="POST">
            <input type="hidden" name="task_id" value="<?= $task_id ?>">

            <div class="form-group">
                <label for="comment">Add Comment</label>
                <textarea id="comment" name="comment" rows="4" maxlength="1000" placeholder="Write your comment..." required></textarea>
                <small><span id="character-count">0</span>/1000 characters</small>
            </div>

            <button type="submit" name="add_comment">Add Comment</button>
        </form>

        <hr>

        <?php if ($comments->num_rows): ?>
            <?php while ($comment = $comments->fetch_assoc()): ?>
                <div class="comment-box">
                    <strong><?= htmlspecialchars($comment["user_name"]) ?></strong>
                    <small><?= date("d M Y H:i", strtotime($comment["created_at"])) ?></small>
                    <p><?= nl2br(htmlspecialchars($comment["comment"])) ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="muted">No comments yet.</p>
        <?php endif; ?>
    </section>

    <a class="button secondary-button" href="tasks.php">Back to My Tasks</a>
</main>

<?php require_once "../includes/footer.php"; ?>
