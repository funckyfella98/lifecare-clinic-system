<?php
// profile.php - every logged-in user: see account details and change own password
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_login();

$uid    = (int)$_SESSION['user']['id'];
$errors = [];

// load this user's record
$stmt = $pdo->prepare('SELECT user_id, full_name, username, password_hash, role FROM users WHERE user_id = ?');
$stmt->execute([$uid]);
$me = $stmt->fetch();
if (!$me) {
    header('Location: logout.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!password_verify($current, $me['password_hash'])) {
        $errors[] = 'Your current password is not correct.';
    }
    if (strlen($new) < 8) {
        $errors[] = 'The new password must be at least 8 characters long.';
    }
    if ($new !== $confirm) {
        $errors[] = 'The new password and the confirmation do not match.';
    }
    if (empty($errors) && $new === $current) {
        $errors[] = 'The new password must be different from the current one.';
    }

    if (empty($errors)) {
        $pdo->prepare('UPDATE users SET password_hash = ? WHERE user_id = ?')
            ->execute([password_hash($new, PASSWORD_DEFAULT), $uid]);
        session_regenerate_id(true);
        header('Location: profile.php?msg=changed');
        exit;
    }
}

$done = (($_GET['msg'] ?? '') === 'changed');

require_once 'includes/header.php';
?>
<h1>My account</h1>

<?php if ($done): ?>
    <div class="alert success">Your password has been changed.</div>
<?php endif; ?>
<?php foreach ($errors as $err): ?>
    <div class="alert error"><?php echo e($err); ?></div>
<?php endforeach; ?>

<div class="card">
    <h2>Account details</h2>
    <div class="summary-grid">
        <div><span>Full name</span><strong><?php echo e($me['full_name']); ?></strong></div>
        <div><span>Username</span><strong><?php echo e($me['username']); ?></strong></div>
        <div><span>Role</span><strong><?php echo e(ucfirst($me['role'])); ?></strong></div>
    </div>
</div>

<div class="card">
    <h2>Change password</h2>
    <form method="post" class="form-grid" autocomplete="off">
        <div class="span-2">
            <label for="current_password">Current password</label>
            <input id="current_password" name="current_password" type="password" required autocomplete="new-password">
        </div>
        <div>
            <label for="new_password">New password (8+ characters)</label>
            <input id="new_password" name="new_password" type="password" required autocomplete="new-password">
        </div>
        <div>
            <label for="confirm_password">Repeat the new password</label>
            <input id="confirm_password" name="confirm_password" type="password" required autocomplete="new-password">
        </div>
        <div class="span-2 form-buttons"><button class="btn">Change password</button></div>
    </form>
</div>
<?php require 'includes/footer.php'; ?>