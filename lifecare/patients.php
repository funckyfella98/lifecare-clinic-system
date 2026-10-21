<?php
// patients.php - list and search patients
require_once 'includes/auth.php';
require_role('admin', 'receptionist');

$q = trim($_GET['q'] ?? '');

$stmt = $pdo->prepare(
    'SELECT * FROM patients WHERE full_name LIKE ? OR phone LIKE ? ORDER BY full_name'
);
$stmt->execute(['%' . $q . '%', '%' . $q . '%']);
$patients = $stmt->fetchAll();

$success = isset($_GET['saved']) ? 'Patient saved successfully.' : '';

require_once 'includes/header.php';
?>
<div class="toolbar">
    <h1>Patients</h1>
    <a class="btn" href="patient_form.php">Register patient</a>
</div>

<?php if ($success !== ''): ?>
    <div class="alert success"><?php echo e($success); ?></div>
<?php endif; ?>

<div class="card">
    <form method="get" class="search-form">
        <label for="q">Search by name or phone</label>
        <input id="q" name="q" value="<?php echo e($q); ?>" placeholder="Type a name or phone number">
    </form>
</div>

<div class="card">
    <div class="table-wrap">
    <table>
        <thead>
            <tr><th>Name</th><th>Gender</th><th>Date of birth</th><th>Phone</th><th>Address</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($patients as $p): ?>
            <tr>
                <td><?php echo e($p['full_name']); ?></td>
                <td><?php echo e($p['gender']); ?></td>
                <td><?php echo e($p['dob']); ?></td>
                <td><?php echo e($p['phone']); ?></td>
                <td><?php echo e($p['address']); ?></td>
                <td><a href="patient_form.php?id=<?php echo (int)$p['patient_id']; ?>">Edit</a></td>
            </tr>
        <?php endforeach; ?>
        <?php if (count($patients) === 0): ?>
            <tr><td colspan="6">No patients found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
<?php require 'includes/footer.php'; ?>