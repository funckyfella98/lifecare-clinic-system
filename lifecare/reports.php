<?php
// reports.php - admin only: appointment report with date range, summary, CSV download and print
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_login();
require_role('admin');

// read the filters (default: first day of this month up to today)
$from   = $_GET['from']   ?? date('Y-m-01');
$to     = $_GET['to']     ?? date('Y-m-d');
$status = $_GET['status'] ?? '';
$doctor = (int)($_GET['doctor'] ?? 0);

// make sure the dates are valid, otherwise fall back to defaults
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) { $from = date('Y-m-01'); }
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to))   { $to   = date('Y-m-d'); }
if (!in_array($status, ['Scheduled', 'Completed', 'Cancelled'], true)) { $status = ''; }

$error = '';
if ($from > $to) {
    $error = 'The "From" date cannot be after the "To" date.';
}

// build the query
$sql = 'SELECT a.appt_date, a.appt_time, a.reason, a.status,
               p.full_name AS patient, p.phone, u.full_name AS doctor
        FROM appointments a
        JOIN patients p ON p.patient_id = a.patient_id
        JOIN users u    ON u.user_id    = a.doctor_id
        WHERE a.appt_date BETWEEN ? AND ?';
$params = [$from, $to];
if ($status !== '') { $sql .= ' AND a.status = ?';    $params[] = $status; }
if ($doctor > 0)    { $sql .= ' AND a.doctor_id = ?'; $params[] = $doctor; }
$sql .= ' ORDER BY a.appt_date, a.appt_time';

$rows = [];
if ($error === '') {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
}

// CSV download: must happen before any HTML is printed
if ($error === '' && isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="lifecare_report_' . $from . '_to_' . $to . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // so Excel shows the characters correctly
    fputcsv($out, ['Date', 'Time', 'Patient', 'Phone', 'Doctor', 'Reason', 'Status']);
    foreach ($rows as $r) {
        fputcsv($out, [$r['appt_date'], substr($r['appt_time'], 0, 5), $r['patient'], $r['phone'], $r['doctor'], $r['reason'], $r['status']]);
    }
    fclose($out);
    exit;
}

// summary counts
$counts = ['Scheduled' => 0, 'Completed' => 0, 'Cancelled' => 0];
foreach ($rows as $r) { $counts[$r['status']]++; }

// doctors for the filter box
$doctors = $pdo->query("SELECT user_id, full_name FROM users WHERE role = 'doctor' ORDER BY full_name")->fetchAll();

$queryString = http_build_query(['from' => $from, 'to' => $to, 'status' => $status, 'doctor' => $doctor]);

require_once 'includes/header.php';
?>
<style>
.rep-head { display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:16px; }
.rep-head h1 { margin:0; }
.rep-filter { background:#fff; border:1px solid #e1e8ea; border-radius:12px; padding:18px 20px; margin-bottom:18px;
              display:flex; gap:14px; flex-wrap:wrap; align-items:flex-end; }
.rep-filter label { display:block; font-size:13px; font-weight:600; color:#33484b; margin-bottom:4px; }
.rep-filter input, .rep-filter select { padding:8px 10px; border:1px solid #c9d5d7; border-radius:8px; font-size:14px; }
.rep-stats { display:grid; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); gap:14px; margin-bottom:18px; }
.rep-stat { background:#fff; border:1px solid #e1e8ea; border-top:4px solid #0b6b73; border-radius:12px; padding:14px 18px; }
.rep-stat:nth-child(2) { border-top-color:#d9892b; }
.rep-stat:nth-child(3) { border-top-color:#3c9d5b; }
.rep-stat:nth-child(4) { border-top-color:#c0392b; }
.rep-stat .num { font-size:30px; font-weight:700; }
.rep-stat .lbl { color:#66797c; font-size:13px; }
.rep-box { background:#fff; border:1px solid #e1e8ea; border-radius:12px; padding:8px 16px 16px; overflow-x:auto; }
.rep-btn { display:inline-block; padding:9px 16px; border-radius:8px; border:0; background:#0b6b73; color:#fff;
           font-weight:600; text-decoration:none; cursor:pointer; font-size:14px; }
.rep-btn.light { background:#e3f2f3; color:#0b6b73; }
.rep-print-title { display:none; }
@media print {
    .sidebar, .topbar, .rep-filter, .rep-actions { display:none !important; }
    .rep-print-title { display:block; margin-bottom:10px; }
    .content { margin:0 !important; }
}
</style>

<div class="rep-head">
    <h1>Appointment report</h1>
    <div class="rep-actions">
        <?php if ($error === ''): ?>
            <a class="rep-btn" href="reports.php?<?php echo e($queryString); ?>&export=csv">Download CSV</a>
            <button class="rep-btn light" type="button" onclick="window.print()">Print</button>
        <?php endif; ?>
    </div>
</div>

<form class="rep-filter" method="get" action="reports.php">
    <div><label for="from">From</label><input type="date" id="from" name="from" value="<?php echo e($from); ?>"></div>
    <div><label for="to">To</label><input type="date" id="to" name="to" value="<?php echo e($to); ?>"></div>
    <div>
        <label for="status">Status</label>
        <select id="status" name="status">
            <option value="">All</option>
            <?php foreach (['Scheduled', 'Completed', 'Cancelled'] as $s): ?>
                <option value="<?php echo $s; ?>" <?php echo $status === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="doctor">Doctor</label>
        <select id="doctor" name="doctor">
            <option value="0">All doctors</option>
            <?php foreach ($doctors as $d): ?>
                <option value="<?php echo (int)$d['user_id']; ?>" <?php echo $doctor === (int)$d['user_id'] ? 'selected' : ''; ?>><?php echo e($d['full_name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div><button class="rep-btn" type="submit">Show report</button></div>
</form>

<?php if ($error !== ''): ?>
    <div class="alert error"><?php echo e($error); ?></div>
<?php else: ?>

<p class="rep-print-title"><strong>LIFECARE Medical and Dental Clinic</strong> - appointments from <?php echo e($from); ?> to <?php echo e($to); ?></p>

<div class="rep-stats">
    <div class="rep-stat"><div class="num"><?php echo count($rows); ?></div><div class="lbl">Total appointments</div></div>
    <div class="rep-stat"><div class="num"><?php echo $counts['Scheduled']; ?></div><div class="lbl">Scheduled</div></div>
    <div class="rep-stat"><div class="num"><?php echo $counts['Completed']; ?></div><div class="lbl">Completed</div></div>
    <div class="rep-stat"><div class="num"><?php echo $counts['Cancelled']; ?></div><div class="lbl">Cancelled</div></div>
</div>

<div class="rep-box">
    <?php if (count($rows) === 0): ?>
        <p style="text-align:center;color:#66797c;padding:24px 0;">No appointments found for these filters.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr><th>Date</th><th>Time</th><th>Patient</th><th>Phone</th><th>Doctor</th><th>Reason</th><th>Status</th></tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?php echo e($r['appt_date']); ?></td>
                <td><?php echo e(substr($r['appt_time'], 0, 5)); ?></td>
                <td><?php echo e($r['patient']); ?></td>
                <td><?php echo e($r['phone']); ?></td>
                <td><?php echo e($r['doctor']); ?></td>
                <td><?php echo e($r['reason']); ?></td>
                <td><span class="pill <?php echo e($r['status']); ?>"><?php echo e($r['status']); ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php endif; ?>
<?php require 'includes/footer.php'; ?>