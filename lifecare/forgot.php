<?php
// forgot.php - a staff member asks the administrator to reset their password
require_once __DIR__ . '/includes/config.php';

if (!empty($_SESSION['user'])) { header('Location: dashboard.php'); exit; }

$sent  = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');

    if ($username === '') {
        $error = 'Please type your username.';
    } else {
        $st = $pdo->prepare('SELECT user_id FROM users WHERE username = ? AND active = 1');
        $st->execute([$username]);
        $uid = $st->fetchColumn();

        if ($uid) {
            // only one waiting request per person
            $chk = $pdo->prepare("SELECT COUNT(*) FROM password_requests WHERE user_id = ? AND status = 'pending'");
            $chk->execute([$uid]);
            if ((int)$chk->fetchColumn() === 0) {
                $pdo->prepare("INSERT INTO password_requests (user_id) VALUES (?)")->execute([$uid]);
            }
        }
        // same answer whether or not the username exists, so nobody can guess usernames
        $sent = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>LIFECARE Medical and Dental Clinic - Forgot password</title>
<style>
* { box-sizing:border-box; }
html, body { margin:0; min-height:100%; }
body {
    font-family: system-ui, 'Segoe UI', Roboto, sans-serif; color:#fff; min-height:100vh;
    display:flex; align-items:center; justify-content:center; padding:24px; overflow:hidden; position:relative;
    background:
        radial-gradient(circle at 20% 20%, #138a94 0%, transparent 45%),
        radial-gradient(circle at 85% 80%, #0e5a73 0%, transparent 50%),
        linear-gradient(135deg, #04262b 0%, #0b4a52 55%, #06353d 100%);
}
.orb { position:absolute; border-radius:50%; border:1px solid rgba(255,255,255,.5);
       background:radial-gradient(circle at 30% 30%, rgba(255,255,255,.7), rgba(120,235,240,.25) 60%, rgba(20,140,150,.15));
       animation: float 7s ease-in-out infinite; }
.o1 { width:60px; height:60px; left:10%; top:16%; }
.o2 { width:34px; height:34px; left:85%; top:20%; animation-delay:1.4s; }
.o3 { width:80px; height:80px; left:82%; top:70%; animation-delay:2.6s; }
.o4 { width:24px; height:24px; left:14%; top:78%; animation-delay:3.8s; }
@keyframes float { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-22px); } }
@keyframes rise  { from { opacity:0; transform:translateY(24px); } to { opacity:1; transform:none; } }

.card { position:relative; width:100%; max-width:430px; padding:34px 32px; border-radius:20px;
        background:rgba(255,255,255,.10); border:1px solid rgba(255,255,255,.28);
        backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
        box-shadow:0 20px 60px rgba(0,0,0,.35); animation: rise .8s ease both; }
.brand { text-align:center; letter-spacing:.18em; font-weight:800; font-size:22px; color:#e7fbfc; }
.brand small { display:block; letter-spacing:.25em; font-size:11px; font-weight:500; color:#bfeef1; margin-top:2px; text-transform:uppercase; }
h2 { margin:22px 0 6px; font-size:24px; }
p.hint { margin:0 0 18px; color:#cfeef0; font-size:14px; line-height:1.55; }
label { display:block; margin:12px 0 6px; font-size:13px; font-weight:600; color:#e5f8f9; }
input { width:100%; padding:13px 14px; border-radius:10px; border:1px solid rgba(255,255,255,.35);
        background:rgba(255,255,255,.14); color:#fff; font-size:15px; outline:none; transition:.2s; }
input::placeholder { color:#b6d9dc; }
input:focus { border-color:#9ee8ee; background:rgba(255,255,255,.22); box-shadow:0 0 0 3px rgba(158,232,238,.25); }
button { width:100%; margin-top:22px; padding:14px; border:0; border-radius:10px; cursor:pointer;
         font-size:16px; font-weight:700; color:#06353d; background:linear-gradient(90deg, #9ee8ee, #ffffff);
         transition:transform .2s, box-shadow .2s; }
button:hover { transform:translateY(-2px); box-shadow:0 10px 24px rgba(158,232,238,.35); }
.error { background:rgba(255,90,90,.2); border:1px solid rgba(255,150,150,.7); color:#ffe2e2; padding:10px 12px; border-radius:10px; margin-bottom:6px; font-size:14px; }
.done  { background:rgba(80,220,150,.18); border:1px solid rgba(140,240,190,.7); color:#e3fff0; padding:14px; border-radius:12px; font-size:14.5px; line-height:1.6; }
.back { margin-top:18px; text-align:center; font-size:14px; }
.back a { color:#bfeef1; text-decoration:none; border-bottom:1px dashed rgba(191,238,241,.6); }
.back a:hover { color:#fff; }
@media (prefers-reduced-motion: reduce) { .orb, .card { animation:none !important; } }
</style>
</head>
<body>
<div class="orb o1"></div><div class="orb o2"></div><div class="orb o3"></div><div class="orb o4"></div>

<div class="card">
    <div class="brand">LIFECARE<small>Medical &amp; Dental Clinic</small></div>
    <h2>Forgot your password?</h2>

    <?php if ($sent): ?>
        <div class="done">
            Your request has been sent to the clinic administrator.
            Please see the administrator. They will give you a new password,
            and you can change it later under <strong>My account</strong>.
        </div>
    <?php else: ?>
        <p class="hint">Type your username. The administrator will be told, and will set a new password for you.</p>
        <?php if ($error !== ''): ?>
            <div class="error"><?php echo e($error); ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <label for="username">Username</label>
            <input id="username" name="username" placeholder="Your username" required autofocus autocomplete="off">
            <button type="submit">Send request</button>
        </form>
    <?php endif; ?>

    <div class="back"><a href="login.php">&larr; Back to log in</a></div>
</div>
</body>
</html>