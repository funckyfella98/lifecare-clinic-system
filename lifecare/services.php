<?php
// services.php - admin only: manage the public clinic page (contact details and the list of services)
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_login();
require_role('admin');

$messages = [
    'saved'   => ['success', 'Clinic details saved.'],
    'added'   => ['success', 'Service added.'],
    'deleted' => ['success', 'Service deleted.'],
    'toggled' => ['success', 'Service updated.'],
];
$errors = [];

// the clinic details that can be edited: key => label
$fields = [
    'clinic_phone'   => 'Phone number',
    'clinic_email'   => 'Email address',
    'clinic_address' => 'Address / location',
    'clinic_hours'   => 'Opening hours (for example: Mon - Sat, 8:00 AM to 5:00 PM)',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_info') {
        foreach ($fields as $key => $label) {
            $value = trim($_POST[$key] ?? '');
            if (strlen($value) > 255) {
                $errors[] = $label . ' is too long (255 characters at most).';
            }
            $posted[$key] = $value;
        }
        if (empty($errors) && $posted['clinic_email'] !== '' && !filter_var($posted['clinic_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if (empty($errors)) {
            $stmt = $pdo->prepare('INSERT INTO clinic_settings (setting_key, setting_value) VALUES (?, ?)
                                   ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
            foreach ($posted as $key => $value) {
                $stmt->execute([$key, $value]);
            }
            header('Location: services.php?msg=saved');
            exit;
        }
    }

    if ($action === 'add_service') {
        $name     = trim($_POST['name'] ?? '');
        $category = $_POST['category'] ?? '';
        $desc     = trim($_POST['description'] ?? '');
        if ($name === '' || strlen($name) > 100) {
            $errors[] = 'Please enter the service name (100 characters at most).';
        }
        if (!in_array($category, ['Medical', 'Dental'], true)) {
            $errors[] = 'Please choose Medical or Dental.';
        }
        if (strlen($desc) > 255) {
            $errors[] = 'The description is too long (255 characters at most).';
        }
        if (empty($errors)) {
            $pdo->prepare('INSERT INTO services (name, category, description) VALUES (?, ?, ?)')
                ->execute([$name, $category, $desc]);
            header('Location: services.php?msg=added');
            exit;
        }
    }

    if ($action === 'toggle_service') {
        $pdo->prepare('UPDATE services SET active = 1 - active WHERE service_id = ?')->execute([(int)($_POST['service_id'] ?? 0)]);
        header('Location: services.php?msg=toggled');
        exit;
    }

    if ($action === 'delete_service') {
        $pdo->prepare('DELETE FROM services WHERE service_id = ?')->execute([(int)($_POST['service_id'] ?? 0)]);
        header('Location: services.php?msg=deleted');
        exit;
    }
}

// current values to show in the form
$info = array_fill_keys(array_keys($fields), '');
foreach ($pdo->query('SELECT setting_key, setting_value FROM clinic_settings') as $row) {
    if (array_key_exists($row['setting_key'], $info)) {
        $info[$row['setting_key']] = $row['setting_value'];
    }
}
if (!empty($posted)) { $info = array_merge($info, $posted); }   // keep what was typed when there is an error

$services = $pdo->query('SELECT * FROM services ORDER BY category, name')->fetchAll();
$flash = $messages[$_GET['msg'] ?? ''] ?? null;

require_once 'includes/header.php';
?>
<div class="toolbar">
    <h1>Clinic page</h1>
    <a class="btn" href="home.php" target="_blank">View the public page</a>
</div>
<p>Whatever you save here appears on the public home page, which patients can open on a phone without logging in.</p>

<?php if ($flash): ?>
    <div class="alert <?php echo e($flash[0]); ?>"><?php echo e($flash[1]); ?></div>
<?php endif; ?>
<?php foreach ($errors as $err): ?>
    <div class="alert error"><?php echo e($err); ?></div>
<?php endforeach; ?>

<div class="card">
    <h2>Contact details and opening hours</h2>
    <form method="post" class="form-grid" autocomplete="off">
        <input type="hidden" name="action" value="save_info">
        <?php foreach ($fields as $key => $label): ?>
            <div class="<?php echo in_array($key, ['clinic_address', 'clinic_hours'], true) ? 'span-2' : ''; ?>">
                <label for="<?php echo e($key); ?>"><?php echo e($label); ?></label>
                <input id="<?php echo e($key); ?>" name="<?php echo e($key); ?>" value="<?php echo e($info[$key]); ?>">
            </div>
        <?php endforeach; ?>
        <div class="span-2 form-buttons"><button class="btn">Save details</button></div>
    </form>
</div>

<div class="card">
    <h2>Add a service</h2>
    <form method="post" class="form-grid" autocomplete="off">
        <input type="hidden" name="action" value="add_service">
        <div>
            <label for="name">Service name</label>
            <input id="name" name="name" required>
        </div>
        <div>
            <label for="category">Type</label>
            <select id="category" name="category">
                <option value="Medical">Medical</option>
                <option value="Dental">Dental</option>
            </select>
        </div>
        <div class="span-2">
            <label for="description">Short description (optional)</label>
            <input id="description" name="description">
        </div>
        <div class="span-2 form-buttons"><button class="btn">Add service</button></div>
    </form>
</div>

<h2>Services on the public page</h2>
<div class="table-wrap">
    <?php if (count($services) === 0): ?>
        <p style="text-align:center;color:#66797c;padding:22px 0;">No services yet. Add the first one above.</p>
    <?php else: ?>
    <table>
        <thead><tr><th>Service</th><th>Type</th><th>Description</th><th>Shown</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($services as $s): ?>
            <tr>
                <td><?php echo e($s['name']); ?></td>
                <td><?php echo e($s['category']); ?></td>
                <td><?php echo e($s['description']); ?></td>
                <td><span class="pill <?php echo $s['active'] ? 'ok' : 'off'; ?>"><?php echo $s['active'] ? 'Visible' : 'Hidden'; ?></span></td>
                <td class="actions">
                    <form method="post" class="inline-form">
                        <input type="hidden" name="action" value="toggle_service">
                        <input type="hidden" name="service_id" value="<?php echo (int)$s['service_id']; ?>">
                        <button class="link-plain"><?php echo $s['active'] ? 'Hide' : 'Show'; ?></button>
                    </form>
                    <form method="post" class="inline-form" onsubmit="return confirm('Delete this service?');">
                        <input type="hidden" name="action" value="delete_service">
                        <input type="hidden" name="service_id" value="<?php echo (int)$s['service_id']; ?>">
                        <button class="link-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
<?php require 'includes/footer.php'; ?>