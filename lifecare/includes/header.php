<?php
// includes/header.php - top of every page after login: sidebar + top bar
require_once __DIR__ . '/auth.php';
require_login();

$role    = $_SESSION['user']['role'];
$current = basename($_SERVER['PHP_SELF']);

// audit log: remember who opened which patient's medical record
if ($current === 'records.php' && $_SERVER['REQUEST_METHOD'] === 'GET' && (int)($_GET['patient_id'] ?? 0) > 0) {
    try {
        $pdo->prepare('INSERT INTO audit_log (user_id, patient_id, action) VALUES (?, ?, ?)')
            ->execute([(int)$_SESSION['user']['id'], (int)$_GET['patient_id'], 'Opened medical record']);
    } catch (PDOException $ex) {
        // if the audit table is missing, do not stop the page from loading
    }
}

function nav_link($file, $label, $current) {
    $class = ($file === $current) ? ' class="active"' : '';
    echo '<a href="' . $file . '"' . $class . '>' . $label . '</a>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LIFECARE Clinic System</title>
    <?php require __DIR__ . '/theme.php'; ?>
</head>
<body>
<div class="bgshapes" aria-hidden="true"><i></i><i></i><i></i><i></i></div>
<div class="layout">

    <aside class="sidebar">
        <div class="brand">
            <div class="logo3d" aria-hidden="true"><div class="spin"><div class="layer l1"></div><div class="layer l2"></div><div class="layer l3"></div></div></div>
            <div class="name">LIFECARE<small>Medical and Dental Clinic</small></div>
        </div>
        <?php require __DIR__ . '/nav.php'; ?>
    </aside>

    <div class="content">
        <div class="topbar">
            <span><?php echo e($_SESSION['user']['full_name']); ?>
                <em class="role"><?php echo e($role); ?></em></span>
            <a class="logout" href="logout.php">Log out</a>
        </div>
        <main class="page">