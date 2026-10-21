<?php
// dashboard.php - role-based summary, today's schedule and tomorrow's reminders
require_once 'includes/header.php';

$role = $_SESSION['user']['role'];
$uid  = (int)$_SESSION['user']['id'];

$today    = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));

// greeting that depends on the time of day
$hour = (int)date('G');
if ($hour < 12)      { $greeting = 'Good morning'; }
elseif ($hour < 17)  { $greeting = 'Good afternoon'; }
else                 { $greeting = 'Good evening'; }
$firstName = explode(' ', trim($_SESSION['user']['full_name']))[0];

// run a COUNT query and return the number
function count_rows($pdo, $sql, $params = []) {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
}

// appointments on one day (doctors only see their own)
function appointments_on($pdo, $date, $role, $uid, $onlyScheduled) {
    $sql = 'SELECT a.*, p.full_name AS patient, p.phone, u.full_name AS doctor
            FROM appointments a
            JOIN patients p ON p.patient_id = a.patient_id
            JOIN users u    ON u.user_id    = a.doctor_id
            WHERE a.appt_date = ?';
    $params = [$date];

    if ($onlyScheduled) {
        $sql .= " AND a.status = 'Scheduled'";
    } else {
        $sql .= " AND a.status <> 'Cancelled'";
    }
    if ($role === 'doctor') {
        $sql .= ' AND a.doctor_id = ?';
        $params[] = $uid;
    }
    $sql .= ' ORDER BY a.appt_time';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// summary cards and quick actions for each role
$cards   = [];
$actions = [];
if ($role === 'admin') {
    $cards[] = ['Registered patients', count_rows($pdo, 'SELECT COUNT(*) FROM patients'), 'All patients on record'];
    $cards[] = ['Active staff', count_rows($pdo, 'SELECT COUNT(*) FROM users WHERE active = 1'), 'Admins, doctors, receptionists'];
    $cards[] = ["Today's appointments", count_rows($pdo, "SELECT COUNT(*) FROM appointments WHERE appt_date = ? AND status <> 'Cancelled'", [$today]), 'Not counting cancelled'];
    $cards[] = ['Upcoming scheduled', count_rows($pdo, "SELECT COUNT(*) FROM appointments WHERE appt_date >= ? AND status = 'Scheduled'", [$today]), 'From today onwards'];
    $actions = [['patient_form.php', 'Register patient'], ['appointment_form.php', 'Book appointment'], ['users.php', 'Manage users']];
} elseif ($role === 'receptionist') {
    $cards[] = ['Registered patients', count_rows($pdo, 'SELECT COUNT(*) FROM patients'), 'All patients on record'];
    $cards[] = ["Today's appointments", count_rows($pdo, "SELECT COUNT(*) FROM appointments WHERE appt_date = ? AND status <> 'Cancelled'", [$today]), 'Not counting cancelled'];
    $cards[] = ['Upcoming scheduled', count_rows($pdo, "SELECT COUNT(*) FROM appointments WHERE appt_date >= ? AND status = 'Scheduled'", [$today]), 'From today onwards'];
    $actions = [['patient_form.php', 'Register patient'], ['appointment_form.php', 'Book appointment']];
} else {
    $cards[] = ['My appointments today', count_rows($pdo, "SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND appt_date = ? AND status <> 'Cancelled'", [$uid, $today]), 'Not counting cancelled'];
    $cards[] = ['My upcoming appointments', count_rows($pdo, "SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND appt_date >= ? AND status = 'Scheduled'", [$uid, $today]), 'From today onwards'];
    $cards[] = ['Records I have written', count_rows($pdo, 'SELECT COUNT(*) FROM medical_records WHERE doctor_id = ?', [$uid]), 'Diagnoses saved by me'];
    $actions = [['records.php', 'Open medical records']];
}

$todayList    = appointments_on($pdo, $today, $role, $uid, false);
$reminderList = appointments_on($pdo, $tomorrow, $role, $uid, true);
?>
<style>
/* styles for this page only */
.dash-hero { background:linear-gradient(120deg, #0b6b73 0%, #118a93 100%); color:#fff; border-radius:14px;
             padding:28px 30px; margin-bottom:22px; display:flex; justify-content:space-between;
             align-items:center; gap:20px; flex-wrap:wrap; }
.dash-hero h1 { margin:0 0 6px; font-size:26px; color:#fff; }
.dash-hero p  { margin:0; opacity:.9; }
.dash-actions { display:flex; gap:10px; flex-wrap:wrap; }
.dash-actions a { background:rgba(255,255,255,.16); border:1px solid rgba(255,255,255,.55); color:#fff;
                  padding:9px 16px; border-radius:8px; text-decoration:none; font-weight:600; }
.dash-actions a:hover { background:#fff; color:#0b6b73; }

.dash-stats { display:grid; grid-template-columns:repeat(auto-fit, minmax(210px, 1fr)); gap:16px; margin-bottom:22px; }
.dash-stat  { background:#fff; border:1px solid #e1e8ea; border-top:4px solid #0b6b73; border-radius:12px; padding:18px 20px; }
.dash-stat:nth-child(2) { border-top-color:#2f7bd0; }
.dash-stat:nth-child(3) { border-top-color:#d9892b; }
.dash-stat:nth-child(4) { border-top-color:#3c9d5b; }
.dash-stat .num   { font-size:36px; font-weight:700; line-height:1.1; color:#1b2e31; }
.dash-stat .label { margin-top:6px; font-weight:600; color:#33484b; }
.dash-stat .hint  { margin-top:2px; font-size:13px; color:#66797c; }

.dash-columns { display:grid; grid-template-columns:minmax(0, 1.5fr) minmax(0, 1fr); gap:22px; align-items:start; }
.dash-panel   { background:#fff; border:1px solid #e1e8ea; border-radius:12px; padding:20px; }
.dash-panel h2 { margin:0 0 4px; font-size:18px; }
.dash-panel .sub { margin:0 0 14px; color:#66797c; font-size:14px; }
.dash-empty   { padding:22px 10px; text-align:center; color:#66797c; background:#f6f9f9; border-radius:8px; }

.dash-item { display:flex; gap:14px; align-items:center; padding:12px 0; border-bottom:1px solid #eef2f3; }
.dash-item:last-child { border-bottom:0; }
.dash-time { background:#e3f2f3; color:#0b6b73; font-weight:700; border-radius:8px; padding:8px 10px; min-width:64px; text-align:center; }
.dash-info { flex:1; min-width:0; }
.dash-info strong { display:block; }
.dash-info span   { color:#66797c; font-size:13px; }
.dash-item .open  { color:#0b6b73; font-weight:600; text-decoration:none; white-space:nowrap; }

@media (max-width: 900px) { .dash-columns { grid-template-columns:1fr; } }
</style>

<div class="dash-hero">
    <div>
        <h1><?php echo e($greeting . ', ' . $firstName); ?></h1>
        <p><?php echo date('l, d F Y'); ?> &middot; <?php echo e(ucfirst($role)); ?> dashboard</p>
    </div>
    <div class="dash-actions">
        <?php foreach ($actions as $a): ?>
            <a href="<?php echo e($a[0]); ?>"><?php echo e($a[1]); ?></a>
        <?php endforeach; ?>
    </div>
</div>

<div class="dash-stats">
    <?php foreach ($cards as $c): ?>
        <div class="dash-stat">
            <div class="num"><?php echo (int)$c[1]; ?></div>
            <div class="label"><?php echo e($c[0]); ?></div>
            <div class="hint"><?php echo e($c[2]); ?></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="dash-columns">

    <div class="dash-panel">
        <h2>Today's schedule</h2>
        <p class="sub"><?php echo count($todayList); ?> appointment(s) today</p>
        <?php if (count($todayList) === 0): ?>
            <div class="dash-empty">No appointments today.</div>
        <?php endif; ?>
        <?php foreach ($todayList as $a): ?>
            <div class="dash-item">
                <div class="dash-time"><?php echo e(substr($a['appt_time'], 0, 5)); ?></div>
                <div class="dash-info">
                    <strong><?php echo e($a['patient']); ?></strong>
                    <span><?php echo e($a['doctor']); ?><?php echo $a['reason'] !== '' ? ' &middot; ' . e($a['reason']) : ''; ?></span>
                </div>
                <span class="pill <?php echo e($a['status']); ?>"><?php echo e($a['status']); ?></span>
                <?php if ($role === 'doctor'): ?>
                    <a class="open" href="records.php?patient_id=<?php echo (int)$a['patient_id']; ?>">Open record</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="dash-panel">
        <h2>Reminders for tomorrow</h2>
        <p class="sub"><?php echo e(date('l, d M Y', strtotime($tomorrow))); ?> &middot; call these patients</p>
        <?php if (count($reminderList) === 0): ?>
            <div class="dash-empty">No appointments booked for tomorrow.</div>
        <?php endif; ?>
        <?php foreach ($reminderList as $a): ?>
            <div class="dash-item">
                <div class="dash-time"><?php echo e(substr($a['appt_time'], 0, 5)); ?></div>
                <div class="dash-info">
                    <strong><?php echo e($a['patient']); ?></strong>
                    <span><?php echo e($a['phone']); ?> &middot; <?php echo e($a['doctor']); ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>
<?php require 'includes/footer.php'; ?>