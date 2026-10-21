<?php
// audit.php - admin only: who opened which patient's medical record
require_once 'includes/header.php';
require_role('admin');

$rows = $pdo->query(
    'SELECT l.created_at, l.action, u.full_name AS staff, u.role, p.full_name AS patient
     FROM audit_log l
     JOIN users u    ON u.user_id    = l.user_id
     JOIN patients p ON p.patient_id = l.patient_id
     ORDER BY l.log_id DESC
     LIMIT 200'
)->fetchAll();
?>
<h1>Audit log</h1>
<p>The last 200 times a medical record was opened. This protects patient privacy by showing who looked at what, and when.</p>

<div style="background:#fff;border:1px solid #e1e8ea;border-radius:12px;padding:8px 16px 16px;overflow-x:auto;">
    <?php if (count($rows) === 0): ?>
        <p style="text-align:center;color:#66797c;padding:24px 0;">Nothing logged yet. Open a patient's medical record and come back.</p>
    <?php else: ?>
    <table>
        <thead><tr><th>Date and time</th><th>Staff</th><th>Role</th><th>Action</th><th>Patient</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?php echo e($r['created_at']); ?></td>
                <td><?php echo e($r['staff']); ?></td>
                <td><?php echo e($r['role']); ?></td>
                <td><?php echo e($r['action']); ?></td>
                <td><?php echo e($r['patient']); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
<?php require 'includes/footer.php'; ?>