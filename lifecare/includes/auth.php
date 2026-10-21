<?php
// includes/auth.php - checks who is logged in
require_once __DIR__ . '/config.php';

function require_login() {
    if (empty($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
}

function require_role(...$roles) {
    require_login();
    if (!in_array($_SESSION['user']['role'], $roles, true)) {
        http_response_code(403);
        die('Access denied: your role cannot open this page.');
    }
}