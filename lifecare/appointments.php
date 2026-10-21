<?php
// appointments.php - list, filter, complete and cancel appointments
require_once 'includes/auth.php';
require_role('admin', 'receptionist');

// mark an appointment as completed or cancelled
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id'], $_POST['action'])) {
    $aid    = (int)$_POST['appointment_id'];
    $action = $_POST['action'];

    if ($action === 'cancel') {
        $pdo->prepare("UPDATE appointments SET status = 'Cancelled'
                       WHERE appointment_id = ? AND status = 'Scheduled'")->execute([$aid]);
    } elseif ($action === 'complete') {
        $pdo->prepare("UPDATE appointments SET status = 'Completed'
                       WHERE appointment_id = ? AND status = 'Scheduled'")->execute([$aid]);
    }
    header('Location: appointments.php?updated=1');
    exit;
}

// filters
$date   = trim($_GET['date'] ?? '');
$status = trim($_GET['status'] ?? '');

$sql = 'SELECT a.*, p.full_name AS patient, p.phone, u.full_name AS doctor
        FROM appointments a
        JOIN patients p ON p.patient_id = a.patient_id
        JOIN users u    ON u.user_id    = a.doctor_id
        WHERE 1 = 1';
$params = [];

if ($date !== '') {
    $sql .= ' AND a.appt_date = ?';
    $params[] = $date;
}
if (in_array($status, ['Scheduled', 'Completed', 'Cancelled'], true)) {
    $sql .= ' AND a.status = ?';
    $params[] = $status;
}
$sql .= ' ORDER BY a.appt_date DESC, a.appt_time DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$appointments = $stmt->fetchAll();

$success = '';
if (isset($_GET['booked']))  { $success = 'Appointment booked successfully.'; }
if (isset($_GET['saved']))   { $success = 'Appointment updated successfully.'; }
if (isset($_GET['updated'])) { $success = 'Appointment status updated.'; }

require_once 'includes/header.php';
?>
<div class="toolbar">
    <h1>Appointments</h1>
    <a class="btn" href="appointment_form.php">Book appointment</a>
</div>

<?php if ($success !== ''): ?>
    <div class="alert success"><?php echo e($success); ?></div>
<?php endif; ?>

<div class="card">
    <form method="get" class="form-grid">
        <div>
            <label for="date">Date</label>
            <input id="date" name="date" type="date" value="<?php echo e($date); ?>">
        </div>
        <div>
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="">All</option>
                <?php foreach (['Scheduled', 'Completed', 'Cancelled'] as $s): ?>
                    <option value="<?php echo $s; ?>" <?php echo $status === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-buttons">
            <button class="btn">Filter</button>
            <a class="btn-secondary" href="appointments.php">Clear</a>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-wrap">
    <table>
        <thead>
            <tr><th>Date</th><th>Time</th><th>Patient</th><th>Doctor</th><th>Reason</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($appointments as $a): ?>
            <tr>
                <td><?php echo e($a['appt_date']); ?></td>
                <td><?php echo e(substr($a['appt_time'], 0, 5)); ?></td>
                <td><?php echo e($a['patient']); ?><br><small><?php echo e($a['phone']); ?></small></td>
                <td><?php echo e($a['doctor']); ?></td>
                <td><?php echo e($a['reason']); ?></td>
                <td><span class="pill <?php echo e($a['status']); ?>"><?php echo e($a['status']); ?></span></td>
                <td class="actions">
                    <?php if ($a['status'] === 'Scheduled'): ?>
                        <a href="appointment_form.php?id=<?php echo (int)$a['appointment_id']; ?>">Reschedule</a>
                        <form method="post" class="inline-form">
                            <input type="hidden" name="appointment_id" value="<?php echo (int)$a['appointment_id']; ?>">
                            <input type="hidden" name="action" value="complete">
                            <button class="link-plain">Complete</button>
                        </form>
                        <form method="post" class="inline-form"
                              onsubmit="return confirm('Cancel this appointment?');">
                            <input type="hidden" name="appointment_id" value="<?php echo (int)$a['appointment_id']; ?>">
                            <input type="hidden" name="action" value="cancel">
                            <button class="link-danger">Cancel</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (count($appointments) === 0): ?>
            <tr><td colspan="7">No appointments found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
<?php require 'includes/footer.php'; ?>