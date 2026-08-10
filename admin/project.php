<?php
require_once "../includes/auth.php";
require_role("admin");
require_once "../config/database.php";

$page_title = "Projects";
$base_path = "../";

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "add") {
        $name = trim($_POST["name"] ?? "");
        $description = trim($_POST["description"] ?? "");
        $start_date = $_POST["start_date"] ?: null;
        $end_date = $_POST["end_date"] ?: null;

        if ($name === "") {
            $error = "Project name is required.";
        } else {
            $stmt = $conn->prepare("INSERT INTO projects (name, description, start_date, end_date) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $description, $start_date, $end_date);
            if ($stmt->execute()) {
                $message = "Project created successfully.";
            } else {
                $error = "Could not create project.";
            }
            $stmt->close();
        }
    }

    if ($action === "delete") {
        $project_id = (int)($_POST["project_id"] ?? 0);

        $stmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->bind_param("i", $project_id);

        if ($stmt->execute()) {
            $message = "Project deleted successfully.";
        } else {
            $error = "Could not delete project.";
        }
        $stmt->close();
    }
}

$projects = $conn->query("
    SELECT p.*,
           COUNT(t.id) AS task_count
    FROM projects p
    LEFT JOIN tasks t ON p.id = t.project_id
    GROUP BY p.id
    ORDER BY p.created_at DESC
");

require_once "../includes/header.php";
require_once "../includes/navbar.php";
?>

<main class="main-content">
    <header class="dashboard-header">
        <div>
            <h1>Projects</h1>
            <p>Create and manage your projects.</p>
        </div>
    </header>

    <?php if ($message): ?><div class="success-message"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="error-message"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <section class="form-section">
        <h2>Create Project</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add">

            <div class="form-group">
                <label for="name">Project Name</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"></textarea>
            </div>

            <div class="two-column">
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date">
                </div>

                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date">
                </div>
            </div>

            <button type="submit">Create Project</button>
        </form>
    </section>

    <section class="recent-tasks">
        <div class="section-header"><h2>All Projects</h2></div>

        <div class="project-grid">
        <?php if ($projects && $projects->num_rows): ?>
            <?php while ($project = $projects->fetch_assoc()): ?>
                <div class="project-card">
                    <h3><?= htmlspecialchars($project["name"]) ?></h3>
                    <p><?= nl2br(htmlspecialchars($project["description"] ?: "No description.")) ?></p>
                    <div class="project-meta">
                        <span><?= (int)$project["task_count"] ?> task(s)</span>
                        <span>
                            <?= $project["start_date"] ? date("d M Y", strtotime($project["start_date"])) : "No start date" ?>
                            -
                            <?= $project["end_date"] ? date("d M Y", strtotime($project["end_date"])) : "No end date" ?>
                        </span>
                    </div>
                    <form method="POST" class="inline-form delete-form" onsubmit="return confirmProjectDelete('<?= htmlspecialchars(addslashes($project["name"])) ?>');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="project_id" value="<?= (int)$project["id"] ?>">
                        <button type="submit" class="danger-button">Delete</button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state"><h3>No projects</h3><p>Create your first project above.</p></div>
        <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once "../includes/footer.php"; ?>
