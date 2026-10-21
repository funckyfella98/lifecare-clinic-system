<?php
// appointment_form.php - book a new appointment or reschedule an existing one
require_once 'includes/auth.php';
require_role('admin', 'receptionist');

// clinic opening hours (change these to suit the clinic)
$openTime  = '08:00';
$closeTime = '17:00';

$id = (int)($_GET['id'] ?? 0);

$appt = ['patient_id' => '', 'doctor_id' => '', 'appt_date' => '', 'appt_time' => '', 'reason' => ''];

// rescheduling? load the saved appointment (only Scheduled ones can be changed)
if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM appointments WHERE appointment_id = ? AND status = 'Scheduled'");
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found) {
        header('Location: appointments.php');
        exit;
    }
    $appt = $found;
    $appt['appt_time'] = substr($appt['appt_time'], 0, 5);
}

$patients = $pdo->query('SELECT patient_id, full_name, phone FROM patients ORDER BY full_name')->fetchAll();
$doctors  = $pdo->query("SELECT user_id, full_name FROM users WHERE role = 'doctor' AND active = 1 ORDER BY full_name")->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appt['patient_id'] = (int)$_POST['patient_id'];
    $appt['doctor_id']  = (int)$_POST['doctor_id'];
    $appt['appt_date']  = trim($_POST['appt_date']);
    $appt['appt_time']  = trim($_POST['appt_time']);
    $appt['reason']     = trim($_POST['reason']);

    if (!$appt['patient_id'] || !$appt['doctor_id'] || $appt['appt_date'] === '' || $appt['appt_time'] === '') {
        $error = 'Choose a patient, a doctor, a date and a time.';
    } elseif ($appt['appt_date'] . ' ' . $appt['appt_time'] <= date('Y-m-d H:i')) {
        $error = 'You cannot book an appointment in the past.';
    } elseif ($appt['appt_time'] < $openTime || $appt['appt_time'] > $closeTime) {
        $error = 'The clinic is open from ' . $openTime . ' to ' . $closeTime . '. Choose a time in that range.';
    } else {
        // clash 1: the doctor is already booked at that date and time
        $c1 = $pdo->prepare("SELECT COUNT(*) FROM appointments
            WHERE doctor_id = ? AND appt_date = ? AND appt_time = ?
              AND status = 'Scheduled' AND appointment_id <> ?");
        $c1->execute([$appt['doctor_id'], $appt['appt_date'], $appt['appt_time'], $id]);

        // clash 2: the patient already has an appointment at that date and time
        $c2 = $pdo->prepare("SELECT COUNT(*) FROM appointments
            WHERE patient_id = ? AND appt_date = ? AND appt_time = ?
              AND status = 'Scheduled' AND appointment_id <> ?");
        $c2->execute([$appt['patient_id'], $appt['appt_date'], $appt['appt_time'], $id]);

        if ($c1->fetchColumn() > 0) {
            $error = 'That doctor already has an appointment at this date and time. Choose another time or doctor.';
        } elseif ($c2->fetchColumn() > 0) {
            $error = 'This patient already has an appointment at this date and time.';
        } else {
            $values = [
                $appt['patient_id'], $appt['doctor_id'],
                $appt['appt_date'], $appt['appt_time'], $appt['reason'],
            ];

            if ($id > 0) {
                $values[] = $id;
                $pdo->prepare('UPDATE appointments
                               SET patient_id=?, doctor_id=?, appt_date=?, appt_time=?, reason=?
                               WHERE appointment_id=?')->execute($values);
                header('Location: appointments.php?saved=1');
            } else {
                $pdo->prepare('INSERT INTO appointments (patient_id, doctor_id, appt_date, appt_time, reason)
                               VALUES (?, ?, ?, ?, ?)')->execute($values);
                header('Location: appointments.php?booked=1');
            }
            exit;
        }
    }
}

require_once 'includes/header.php';
?>
<h1><?php echo $id > 0 ? 'Reschedule appointment' : 'Book appointment'; ?></h1>

<?php if ($error !== ''): ?>
    <div class="alert error"><?php echo e($error); ?></div>
<?php endif; ?>

<?php if (count($patients) === 0): ?>
    <div class="alert error">There are no patients yet. <a href="patient_form.php">Register a patient</a> first.</div>
<?php elseif (count($doctors) === 0): ?>
    <div class="alert error">There are no active doctors. Ask the admin to add a doctor on the Users page.</div>
<?php else: ?>
<div class="card">
    <form method="post" class="form-grid">
        <div>
            <label for="patient_id">Patient</label>
            <select id="patient_id" name="patient_id" required>
                <option value="">Select patient</option>
                <?php foreach ($patients as $p): ?>
                    <option value="<?php echo (int)$p['patient_id']; ?>"
                        <?php echo (int)$appt['patient_id'] === (int)$p['patient_id'] ? 'selected' : ''; ?>>
                        <?php echo e($p['full_name'] . ($p['phone'] !== '' ? ' - ' . $p['phone'] : '')); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="doctor_id">Doctor</label>
            <select id="doctor_id" name="doctor_id" required>
                <option value="">Select doctor</option>
                <?php foreach ($doctors as $d): ?>
                    <option value="<?php echo (int)$d['user_id']; ?>"
                        <?php echo (int)$appt['doctor_id'] === (int)$d['user_id'] ? 'selected' : ''; ?>>
                        <?php echo e($d['full_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="appt_date">Date</label>
            <input id="appt_date" name="appt_date" type="date" min="<?php echo date('Y-m-d'); ?>"
                   value="<?php echo e($appt['appt_date']); ?>" required>
        </div>
        <div>
            <label for="appt_time">Time (<?php echo e($openTime . ' to ' . $closeTime); ?>)</label>
            <input id="appt_time" name="appt_time" type="time"
                   min="<?php echo e($openTime); ?>" max="<?php echo e($closeTime); ?>"
                   value="<?php echo e($appt['appt_time']); ?>" required>
        </div>
        <div class="span-2">
            <label for="reason">Reason for visit</label>
            <input id="reason" name="reason" autocomplete="off" value="<?php echo e($appt['reason']); ?>">
        </div>
        <div class="span-2 form-buttons">
            <button class="btn"><?php echo $id > 0 ? 'Save changes' : 'Book appointment'; ?></button>
            <a class="btn-secondary" href="appointments.php">Cancel</a>
        </div>
    </form>
</div>
<?php endif; ?>
<?php require 'includes/footer.php'; ?>