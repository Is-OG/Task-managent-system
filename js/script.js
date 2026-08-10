// ==========================================
// TASK MANAGEMENT SYSTEM JAVASCRIPT
// ==========================================

function confirmDelete(taskName) {
    return confirm("Are you sure you want to delete this task?\n\nTask: " + taskName);
}

function confirmProjectDelete(projectName) {
    return confirm("Are you sure you want to delete this project?\n\nProject: " + projectName);
}

function toggleSidebar() {
    const sidebar = document.getElementById("sidebar");

    if (sidebar) {
        sidebar.classList.toggle("sidebar-open");
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const messages = document.querySelectorAll(".success-message");

    messages.forEach(function (message) {
        setTimeout(function () {
            message.style.opacity = "0";

            setTimeout(function () {
                message.style.display = "none";
            }, 500);
        }, 3000);
    });

    const commentBox = document.getElementById("comment");
    const characterCount = document.getElementById("character-count");

    if (commentBox && characterCount) {
        commentBox.addEventListener("input", function () {
            characterCount.textContent = commentBox.value.length;
        });
    }
});
