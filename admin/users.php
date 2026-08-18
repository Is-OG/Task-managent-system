<?php
require_once "../includes/auth.php";
require_role("admin");
require_once "../config/database.php";

$page_title = "Users";
$base_path = "../";

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "add") {
        $name = trim($_POST["name"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $password = $_POST["password"] ?? "";
        $role = $_POST["role"] ?? "member";

        if ($name === "" || $email === "" || $password === "") {
            $error = "Name, email and password are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email.";
        } elseif (!in_array($role, ["admin", "member"], true)) {
            $error = "Invalid role.";
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param("ssss", $name, $email, $password, $role);

            if ($stmt->execute()) {
                $message = "User created successfully.";
            } else {
                $error = $stmt->errno === 1062
                    ? "That email is already registered."
                    : "Could not create user.";
            }

            $stmt->close();
        }
    }

    if ($action === "delete") {
    $user_id = (int)($_POST["user_id"] ?? 0);

    if ($user_id === (int)$_SESSION["user_id"]) {
        $error = "You cannot delete your own account.";
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            $message = "User deleted. Their tasks are now unassigned.";
        } else {
            $error = "Could not delete user.";
        }

        $stmt->close();
    }
}
}

$users = $conn->query("
    SELECT u.id, u.name, u.email, u.role, u.created_at,
           COUNT(t.id) AS task_count
    FROM users u
    LEFT JOIN tasks t ON u.id = t.assigned_to
    GROUP BY u.id
    ORDER BY u.created_at DESC
");

require_once "../includes/header.php";
require_once "../includes/navbar.php";
?>

<main class="main-content">

    <header class="dashboard-header">
        <div>
            <h1>Users</h1>
            <p>Add team members and manage user accounts.</p>
        </div>
    </header>

    <?php if ($message): ?>
        <div class="success-message">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error-message">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <section class="form-section">

        <h2>Add User</h2>

        <form method="POST">

            <input type="hidden" name="action" value="add">

            <div class="three-column">

                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

            </div>

            <div class="form-group">

                <label for="role">Role</label>

                <select id="role" name="role">
                    <option value="member">Member</option>
                    <option value="admin">Admin</option>
                </select>

            </div>

            <button type="submit">Create User</button>

        </form>

    </section>

    <section class="recent-tasks">

        <div class="section-header">
            <h2>All Users</h2>
        </div>

        <div class="table-wrap">

            <table>

                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Assigned Tasks</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                <?php while ($user = $users->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars($user["name"]) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($user["email"]) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(ucfirst($user["role"])) ?>
                        </td>

                        <td>
                            <?= (int)$user["task_count"] ?>
                        </td>

                        <td>
                            <?= date("d M Y", strtotime($user["created_at"])) ?>
                        </td>

                        <td>

                            <?php if ((int)$user["id"] !== (int)$_SESSION["user_id"]): ?>

                                <form
                                    method="POST"
                                    class="inline-form delete-form"
                                    onsubmit="return confirm('Delete this user? Their assigned tasks will become unassigned.');"
                                >

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="delete"
                                    >

                                    <input
                                        type="hidden"
                                        name="user_id"
                                        value="<?= (int)$user["id"] ?>"
                                    >

                                    <button
                                        class="danger-button small-button"
                                        type="submit"
                                    >
                                        Delete
                                    </button>

                                </form>

                            <?php else: ?>

                                <span class="muted">
                                    Current account
                                </span>

                            <?php endif; ?>

                        </td>

                    </tr>

                <?php endwhile; ?>

                </tbody>

            </table>

        </div>

    </section>

</main>

<?php require_once "../includes/footer.php"; ?>