<?php
// includes/nav.php - the menu links in the sidebar (edit this file to add a menu link)
nav_link('dashboard.php', 'Dashboard', $current);
if (in_array($role, ['admin', 'receptionist'], true)) {
    nav_link('patients.php', 'Patients', $current);
    nav_link('appointments.php', 'Appointments', $current);
}
if (in_array($role, ['admin', 'doctor'], true)) {
    nav_link('records.php', 'Medical records', $current);
}
if ($role === 'admin') {
    nav_link('reports.php', 'Reports', $current);
    nav_link('audit.php', 'Audit log', $current);
    nav_link('services.php', 'Clinic page', $current);
    nav_link('users.php', 'Users', $current);
}
nav_link('profile.php', 'My account', $current);