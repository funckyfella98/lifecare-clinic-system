<?php
// records.php - doctor views a patient's history and adds diagnoses
require_once 'includes/auth.php';
require_role('admin', 'doctor');

$isDoctor = ($_SESSION['user']['role'] === 'doctor');
$error    = '';
$success  = '';
$pid      = (int)($_GET['patient_id'] ?? 0);

// save a new medical record (doctors only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isDoctor) {
    $pid       = (int)$_POST['patient_id'];
    $diagnosis = trim($_POST['diagnosis']);
    $treatment = trim($_POST['treatment']);
    $notes     = trim($_POST['notes']);

    $check = $pdo->prepare('SELECT COUNT(*) FROM patients WHERE patient_id = ?');
    $check->execute([$pid]);

    if ($pid === 0 || $check->fetchColumn() == 0) {
        $error = 'Choose a patient first.';
    } elseif ($diagnosis === '') {
        $error = 'The diagnosis cannot be empty.';
    } else {
        $pdo->prepare('INSERT INTO medical_records (patient_id, doctor_id, diagnosis, treatment, notes)
                       VALUES (?, ?, ?, ?, ?)')
            ->execute([$pid, $_SESSION['user']['id'], $diagnosis, $treatment, $notes]);
        header('Location: records.php?patient_id=' . $pid . '&saved=1');
        exit;
    }
}

if (isset($_GET['saved'])) {
    $success = 'Medical record saved.';
}

$patients = $pdo->query('SELECT patient_id, full_name, phone FROM patients ORDER BY full_name')->fetchAll();

$patient      = null;
$records      = [];
$appointments = [];

if ($pid > 0) {
    $stmt = $pdo->prepare('SELECT * FROM patients WHERE patient_id = ?');
    $stmt->execute([$pid]);
    $patient = $stmt->fetch();

    if ($patient) {
        $stmt = $pdo->prepare('SELECT r.*, u.full_name AS doctor
                               FROM medical_records r
                               JOIN users u ON u.user_id = r.doctor_id
                               WHERE r.patient_id = ?
                               ORDER BY r.created_at DESC');
        $stmt->execute([$pid]);
        $records = $stmt->fetchAll();

        $stmt = $pdo->prepare('SELECT a.*, u.full_name AS doctor
                               FROM appointments a
                               JOIN users u ON u.user_id = a.doctor_id
                               WHERE a.patient_id = ?
                               ORDER BY a.appt_date DESC, a.appt_time DESC
                               LIMIT 5');
        $stmt->execute([$pid]);
        $appointments = $stmt->fetchAll();
    }
}

// age from date of birth
$age = '';
if ($patient && $patient['dob']) {
    $age = (new DateTime($patient['dob']))->diff(new DateTime('today'))->y . ' years';
}

require_once 'includes/header.php';
?>
<h1>Medical records</h1>

<?php if ($error !== ''): ?>
    <div class="alert error"><?php echo e($error); ?></div>
<?php endif; ?>
<?php if ($success !== ''): ?>
    <div class="alert success"><?php echo e($success); ?></div>
<?php endif; ?>

<div class="card">
    <form method="get" class="form-grid">
        <div class="span-2">
            <label for="patient_id">Select a patient</label>
            <select id="patient_id" name="patient_id" onchange="this.form.submit()">
                <option value="">Choose a patient</option>
                <?php foreach ($patients as $p): ?>
                    <option value="<?php echo (int)$p['patient_id']; ?>"
                        <?php echo $pid === (int)$p['patient_id'] ? 'selected' : ''; ?>>
                        <?php echo e($p['full_name'] . ($p['phone'] !== '' ? ' - ' . $p['phone'] : '')); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-buttons">
            <button class="btn">View</button>
        </div>
    </form>
</div>

<?php if ($patient): ?>

<div class="card">
    <h2><?php echo e($patient['full_name']); ?></h2>
    <div class="summary-grid">
        <div><span>Age</span><strong><?php echo e($age !== '' ? $age : 'Not recorded'); ?></strong></div>
        <div><span>Gender</span><strong><?php echo e($patient['gender'] ?: 'Not recorded'); ?></strong></div>
        <div><span>Phone</span><strong><?php echo e($patient['phone'] ?: 'Not recorded'); ?></strong></div>
        <div><span>Address</span><strong><?php echo e($patient['address'] ?: 'Not recorded'); ?></strong></div>
    </div>
    <div class="history-box">
        <strong>Known history:</strong>
        <?php echo e($patient['history'] !== '' ? $patient['history'] : 'None recorded'); ?>
    </div>
</div>

<?php if ($isDoctor): ?>
<div class="card">
    <h2>Add a diagnosis</h2>
    <form method="post" class="form-grid">
        <input type="hidden" name="patient_id" value="<?php echo (int)$pid; ?>">
        <div class="span-2">
            <label for="diagnosis">Diagnosis</label>
            <textarea id="diagnosis" name="diagnosis" rows="3" required></textarea>
        </div>
        <div class="span-2">
            <label for="treatment">Treatment</label>
            <textarea id="treatment" name="treatment" rows="3"></textarea>
        </div>
        <div class="span-2">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" rows="2"></textarea>
        </div>
        <div class="span-2 form-buttons">
            <button class="btn">Save record</button>
        </div>
    </form>
</div>
<?php endif; ?>

<div class="card">
    <h2>Recent appointments</h2>
    <?php if (count($appointments) === 0): ?>
        <p>No appointments for this patient.</p>
    <?php else: ?>
    <div class="table-wrap">
    <table>
        <thead><tr><th>Date</th><th>Time</th><th>Doctor</th><th>Reason</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($appointments as $a): ?>
            <tr>
                <td><?php echo e($a['appt_date']); ?></td>
                <td><?php echo e(substr($a['appt_time'], 0, 5)); ?></td>
                <td><?php echo e($a['doctor']); ?></td>
                <td><?php echo e($a['reason']); ?></td>
                <td><span class="pill <?php echo e($a['status']); ?>"><?php echo e($a['status']); ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Medical history (newest first)</h2>
    <?php if (count($records) === 0): ?>
        <p>No medical records yet for this patient.</p>
    <?php endif; ?>
    <?php foreach ($records as $r): ?>
        <div class="record-item">
            <div class="meta"><?php echo e($r['created_at']); ?> &middot; <?php echo e($r['doctor']); ?></div>
            <p><strong>Diagnosis:</strong> <?php echo e($r['diagnosis']); ?></p>
            <?php if ($r['treatment'] !== null && $r['treatment'] !== ''): ?>
                <p><strong>Treatment:</strong> <?php echo e($r['treatment']); ?></p>
            <?php endif; ?>
            <?php if ($r['notes'] !== null && $r['notes'] !== ''): ?>
                <p><strong>Notes:</strong> <?php echo e($r['notes']); ?></p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<?php endif; ?>
<?php require 'includes/footer.php'; ?>