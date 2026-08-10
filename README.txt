TASK MANAGEMENT SYSTEM
======================

Technology:
- PHP
- MySQL
- HTML
- CSS
- JavaScript
- XAMPP

INSTALLATION
------------

1. Copy the "task-management-system" folder into:
   C:\xampp\htdocs\

2. Start Apache and MySQL from XAMPP Control Panel.

3. Open phpMyAdmin:
   http://localhost/phpmyadmin/

4. Import:
   database/task_management.sql

5. Open:
   http://localhost/task-management-system/

DEMO LOGIN
----------

Admin:
Email: admin@example.com
Password: admin123

Member:
Email: member@example.com
Password: member123

IMPORTANT
---------

The database script creates the database and demo data.

Tasks can be assigned to members using their user ID through the users table.
No notification system is included, as requested.

When a member is deleted:
- The member account is removed.
- Their tasks remain.
- assigned_to becomes NULL.
- The task appears as Unassigned.

The project uses password_hash/password_verify for passwords.
