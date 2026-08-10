CREATE DATABASE IF NOT EXISTS task_management
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE task_management;


CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'member') NOT NULL DEFAULT 'member',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    start_date DATE NULL,
    end_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    project_id INT NOT NULL,
    assigned_to INT NULL,
    status ENUM('To-Do', 'In Progress', 'Completed') NOT NULL DEFAULT 'To-Do',
    priority ENUM('Low', 'Medium', 'High') NOT NULL DEFAULT 'Medium',
    due_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT tasks_fk_project
        FOREIGN KEY (project_id)
        REFERENCES projects(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT tasks_fk_user
        FOREIGN KEY (assigned_to)
        REFERENCES users(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT comments_fk_task
        FOREIGN KEY (task_id)
        REFERENCES tasks(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT comments_fk_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Demo accounts
-- Admin: admin@example.com / admin123
-- Member: member@example.com / member123

INSERT INTO users (name, email, password, role) VALUES
(
    'System Admin',
    'admin@example.com',
    '$2y$12$5d3.mta0RtIZ2f17raEENeDhC9Y7689.lUrvsAtjcbT9CTJ8wfUSS',
    'admin'
),
(
    'Demo Member',
    'member@example.com',
    '$2y$12$66TQI4ttQivZHd/8VCnx4et1JqFiWlDf5HyX9/h56vWT1cl51Kbui',
    'member'
);

INSERT INTO projects (name, description, start_date, end_date)
VALUES
(
    'Website Project',
    'Sample project for testing the task management system.',
    CURDATE(),
    DATE_ADD(CURDATE(), INTERVAL 30 DAY)
);

INSERT INTO tasks
(title, description, project_id, assigned_to, status, priority, due_date)
VALUES
(
    'Create Login Page',
    'Create and test the login page.',
    1,
    2,
    'To-Do',
    'High',
    DATE_ADD(CURDATE(), INTERVAL 7 DAY)
),
(
    'Create Dashboard',
    'Build the member dashboard.',
    1,
    2,
    'In Progress',
    'Medium',
    DATE_ADD(CURDATE(), INTERVAL 14 DAY)
);
