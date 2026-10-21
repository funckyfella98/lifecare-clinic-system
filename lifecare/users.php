<?php
// users.php - admin adds and manages staff accounts
require_once 'includes/auth.php';
require_role('admin');

$error   = '';
$success = '';

// enable or disable an account
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    if ($id !== (int)$_SESSION['user']['id']) {   // admin cannot disable himself
        $pdo->prepare('UPDATE users SET active = 1 - active WHERE user_id = ?')->execute([$id]);
    }
    header('Location: users.php');
    exit;
}

// admin sets a new password for someone who asked for a reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reset') {
    $rid  = (int)($_POST['request_id'] ?? 0);
    $new  = $_POST['new_password'] ?? '';

    if (strlen($new) < 8) {
        $error = 'The new password must be at least 8 characters.';
    } else {
        $q = $pdo->prepare("SELECT user_id FROM password_requests WHERE request_id = ? AND status = 'pending'");
        $q->execute([$rid]);
        $uid = $q->fetchColumn();
        if ($uid) {
            $pdo->prepare('UPDATE users SET password_hash = ? WHERE user_id = ?')
                ->execute([password_hash($new, PASSWORD_DEFAULT), $uid]);
            $pdo->prepare("UPDATE password_requests SET status = 'done', handled_at = NOW() WHERE request_id = ?")
                ->execute([$rid]);
            header('Location: users.php?reset=1');
            exit;
        }
        $error = 'That request was already handled.';
    }
}

// add a new user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $name     = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role     = $_POST['role'];

    if ($name === '' || $username === '' || strlen($password) < 8) {
        $error = 'Fill in every field. Password must be at least 8 characters.';
    } elseif (!in_array($role, ['admin', 'doctor', 'receptionist'], true)) {
        $error = 'Choose a valid role.';
    } else {
        $check = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
        $check->execute([$username]);

        if ($check->fetchColumn() > 0) {
            $error = 'That username is already taken.';
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO users (full_name, username, password_hash, role) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$name, $username, password_hash($password, PASSWORD_DEFAULT), $role]);
            header('Location: users.php?added=1');
            exit;
        }
    }
}

if (isset($_GET['added'])) {
    $success = 'User added successfully.';
}
if (isset($_GET['reset'])) {
    $success = 'New password saved. Tell the staff member the new password in person.';
}

$requests = $pdo->query(
    "SELECT r.request_id, r.requested_at, u.full_name, u.username, u.role
     FROM password_requests r JOIN users u ON u.user_id = r.user_id
     WHERE r.status = 'pending' ORDER BY r.requested_at"
)->fetchAll();

$users = $pdo->query('SELECT * FROM users ORDER BY role, full_name')->fetchAll();

require_once 'includes/header.php';
?>
<h1>Users</h1>

<?php if ($error !== ''): ?>
    <div class="alert error"><?php echo e($error); ?></div>
<?php endif; ?>
<?php if ($success !== ''): ?>
    <div class="alert success"><?php echo e($success); ?></div>
<?php endif; ?>

<?php if ($requests): ?>
<div class="card">
    <h2>Password reset requests (<?php echo count($requests); ?>)</h2>
    <p style="margin:0 0 12px;color:#5b7378;">These people said they forgot their password. Type a temporary password (8+ characters), save, then tell them in person.</p>
    <div class="table-wrap">
    <table>
        <thead>
            <tr><th>Full name</th><th>Username</th><th>Role</th><th>Asked on</th><th>New password</th></tr>
        </thead>
        <tbody>
        <?php foreach ($requests as $r): ?>
            <tr>
                <td><?php echo e($r['full_name']); ?></td>
                <td><?php echo e($r['username']); ?></td>
                <td><?php echo e($r['role']); ?></td>
                <td><?php echo e(date('d M Y, H:i', strtotime($r['requested_at']))); ?></td>
                <td>
                    <form method="post" style="display:flex;gap:8px;align-items:center;margin:0;">
                        <input type="hidden" name="action" value="reset">
                        <input type="hidden" name="request_id" value="<?php echo (int)$r['request_id']; ?>">
                        <input name="new_password" type="text" placeholder="Temporary password" minlength="8" required autocomplete="off" style="min-width:170px;">
                        <button class="btn">Save</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <h2>Add a new user</h2>
    <form method="post" class="form-grid">
        <input type="hidden" name="action" value="add">
        <div>
            <label for="full_name">Full name</label>
            <input id="full_name" name="full_name" required>
        </div>
        <div>
            <label for="username">Username</label>
            <input id="username" name="username" required>
        </div>
        <div>
            <label for="password">Password (8+ characters)</label>
            <input id="password" name="password" type="password" required>
        </div>
        <div>
            <label for="role">Role</label>
            <select id="role" name="role">
                <option value="receptionist">Receptionist</option>
                <option value="doctor">Doctor</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <div class="form-action">
            <button class="btn">Add user</button>
        </div>
    </form>
</div>

<div class="card">
    <h2>All users</h2>
    <div class="table-wrap">
    <table>
        <thead>
            <tr><th>Full name</th><th>Username</th><th>Role</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?php echo e($u['full_name']); ?></td>
                <td><?php echo e($u['username']); ?></td>
                <td><?php echo e($u['role']); ?></td>
                <td>
                    <span class="pill <?php echo $u['active'] ? 'ok' : 'off'; ?>">
                        <?php echo $u['active'] ? 'Active' : 'Disabled'; ?>
                    </span>
                </td>
                <td>
                    <?php if ((int)$u['user_id'] !== (int)$_SESSION['user']['id']): ?>
                        <a href="users.php?toggle=<?php echo (int)$u['user_id']; ?>">
                            <?php echo $u['active'] ? 'Disable' : 'Enable'; ?>
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<?php require 'includes/footer.php'; ?>