<?php
// index.php - the first page: staff go to the dashboard, everyone else sees the public home page
require_once __DIR__ . '/includes/config.php';

if (!empty($_SESSION['user'])) {
    header('Location: dashboard.php');
} else {
    header('Location: home.php');
}
exit;