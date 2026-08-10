<?php
require_once "../includes/auth.php";
require_role("admin");
require_once "../config/database.php";

$page_title = "Reports";
$base_path = "../";

$total = (int)$conn->query("SELECT COUNT(*) AS total FROM tasks")->fetch_assoc()["total"];
$todo = (int)$conn->query("SELECT COUNT(*) AS total FROM tasks WHERE status = 'To-Do'")->fetch_assoc()["total"];
$progress = (int)$conn->query("SELECT COUNT(*) AS total FROM tasks WHERE status = 'In Progress'")->fetch_assoc()["total"];
$completed = (int)$conn->query("SELECT COUNT(*) AS total FROM tasks WHERE status = 'Completed'")->fetch_assoc()["total"];
$overdue = (int)$conn->query("SELECT COUNT(*) AS total FROM tasks WHERE due_date IS NOT NULL AND due_date < CURDATE() AND status <> 'Completed'")->fetch_assoc()["total"];

$member_report = $conn->query("
    SELECT u.name,
           COUNT(t.id) AS total_tasks,
           SUM(t.status = 'Completed') AS completed_tasks,
           SUM(t.status = 'In Progress') AS progress_tasks,
           SUM(t.status = 'To-Do') AS todo_tasks
    FROM users u
    LEFT JOIN tasks t ON u.id = t.assigned_to
    WHERE u.role = 'member'
    GROUP BY u.id
    ORDER BY u.name
");

require_once "../includes/header.php";
require_once "../includes/navbar.php";
?>

<main class="main-content">
    <header class="dashboard-header">
        <div>
            <h1>Reports</h1>
            <p>Overview of project task progress.</p>
        </div>
    </header>

    <section class="stats-grid">
        <div class="stat-card"><h3>Total</h3><p><?= $total ?></p></div>
        <div class="stat-card"><h3>To-Do</h3><p><?= $todo ?></p></div>
        <div class="stat-card"><h3>In Progress</h3><p><?= $progress ?></p></div>
        <div class="stat-card"><h3>Completed</h3><p><?= $completed ?></p></div>
        <div class="stat-card"><h3>Overdue</h3><p><?= $overdue ?></p></div>
    </section>

    <section class="recent-tasks">
        <div class="section-header"><h2>Member Progress</h2></div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Total</th>
                        <th>To-Do</th>
                        <th>In Progress</th>
                        <th>Completed</th>
                        <th>Progress</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $member_report->fetch_assoc()): ?>
                    <?php
                    $member_total = (int)$row["total_tasks"];
                    $member_completed = (int)$row["completed_tasks"];
                    $percentage = $member_total > 0 ? round(($member_completed / $member_total) * 100) : 0;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row["name"]) ?></td>
                        <td><?= $member_total ?></td>
                        <td><?= (int)$row["todo_tasks"] ?></td>
                        <td><?= (int)$row["progress_tasks"] ?></td>
                        <td><?= $member_completed ?></td>
                        <td>
                            <div class="progress-container">
                                <div class="progress-bar"><div class="progress-fill" style="width: <?= $percentage ?>%"></div></div>
                                <small><?= $percentage ?>%</small>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<?php require_once "../includes/footer.php"; ?>
