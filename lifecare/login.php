<?php
// login.php - login screen with a 3D animated medical cross
require_once __DIR__ . '/includes/config.php';

if (!empty($_SESSION['user'])) { header('Location: dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $st = $pdo->prepare('SELECT * FROM users WHERE username = ? AND active = 1');
    $st->execute([trim($_POST['username'] ?? '')]);
    $user = $st->fetch();

    if ($user && password_verify($_POST['password'] ?? '', $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'        => $user['user_id'],
            'full_name' => $user['full_name'],
            'role'      => $user['role'],
        ];
        header('Location: welcome.php');
        exit;
    }
    $error = 'Invalid username or password';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>LIFECARE Medical and Dental Clinic - Log in</title>
<style>
* { box-sizing:border-box; }
html, body { margin:0; min-height:100%; }
body {
    font-family: system-ui, 'Segoe UI', Roboto, sans-serif;
    color:#fff;
    background:
        radial-gradient(circle at 20% 20%, #138a94 0%, transparent 45%),
        radial-gradient(circle at 85% 80%, #0e5a73 0%, transparent 50%),
        linear-gradient(135deg, #04262b 0%, #0b4a52 55%, #06353d 100%);
    min-height:100vh;
    overflow-x:hidden;
}

.wrap { min-height:100vh; display:grid; grid-template-columns: 1.15fr 1fr; align-items:center; gap:30px; padding:30px 5vw; }

/* ---------- left side: title and 3D scene ---------- */
.hero { text-align:center; }
.hero .badge { display:inline-block; padding:6px 16px; border:1px solid rgba(255,255,255,.35); border-radius:99px;
               font-size:13px; letter-spacing:.18em; text-transform:uppercase; color:#bfeef1; background:rgba(255,255,255,.06); }
.hero h1 { margin:16px 0 6px; font-size:clamp(40px, 6vw, 72px); letter-spacing:.14em; font-weight:800;
           background:linear-gradient(90deg, #ffffff, #9ee8ee, #ffffff); background-size:200% auto;
           -webkit-background-clip:text; background-clip:text; color:transparent;
           animation: shine 6s linear infinite; }
.hero .sub { margin:0; font-size:clamp(16px, 2vw, 22px); letter-spacing:.3em; text-transform:uppercase; color:#d6f5f7; }
.hero .tag { margin:18px auto 0; max-width:460px; color:#bfe3e6; line-height:1.6; }

.scene { height:340px; margin-top:10px; perspective:900px; display:flex; align-items:center; justify-content:center; position:relative; }
.stage { position:relative; width:220px; height:220px; transform-style:preserve-3d; animation: spin 14s linear infinite; }

/* the cross is two boxes: a tall one and a wide one */
.box { position:absolute; transform-style:preserve-3d; left:50%; top:50%; }
.box .f { position:absolute; left:0; top:0; border:1px solid rgba(255,255,255,.55);
          background:linear-gradient(145deg, rgba(120,235,240,.55), rgba(20,140,150,.45));
          box-shadow: inset 0 0 22px rgba(255,255,255,.25); backface-visibility:visible; }

/* tall bar 64 wide, 220 high, 64 deep */
.v { width:64px; height:220px; margin-left:-32px; margin-top:-110px; }
.v .f.fr, .v .f.bk { width:64px; height:220px; }
.v .f.fr { transform: translateZ(32px); }
.v .f.bk { transform: rotateY(180deg) translateZ(32px); }
.v .f.lt, .v .f.rt { width:64px; height:220px; }
.v .f.rt { transform: rotateY(90deg) translateZ(32px); }
.v .f.lt { transform: rotateY(-90deg) translateZ(32px); }
.v .f.tp, .v .f.bt { width:64px; height:64px; }
.v .f.tp { transform: rotateX(90deg) translateZ(32px); }
.v .f.bt { transform: rotateX(-90deg) translateZ(188px); }

/* wide bar 220 wide, 64 high, 64 deep */
.h { width:220px; height:64px; margin-left:-110px; margin-top:-32px; }
.h .f.fr, .h .f.bk { width:220px; height:64px; }
.h .f.fr { transform: translateZ(32px); }
.h .f.bk { transform: rotateY(180deg) translateZ(32px); }
.h .f.lt, .h .f.rt { width:64px; height:64px; }
.h .f.rt { transform: rotateY(90deg) translateZ(188px); }
.h .f.lt { transform: rotateY(-90deg) translateZ(32px); }
.h .f.tp, .h .f.bt { width:220px; height:64px; }
.h .f.tp { transform: rotateX(90deg) translateZ(32px); }
.h .f.bt { transform: rotateX(-90deg) translateZ(32px); }

/* floating glass shapes around the cross */
.orb { position:absolute; border-radius:50%; border:1px solid rgba(255,255,255,.5);
       background:radial-gradient(circle at 30% 30%, rgba(255,255,255,.7), rgba(120,235,240,.25) 60%, rgba(20,140,150,.15));
       animation: float 7s ease-in-out infinite; }
.orb.o1 { width:46px; height:46px; left:8%;  top:14%; animation-delay:0s; }
.orb.o2 { width:28px; height:28px; left:86%; top:22%; animation-delay:1.4s; }
.orb.o3 { width:64px; height:64px; left:80%; top:68%; animation-delay:2.6s; }
.orb.o4 { width:20px; height:20px; left:14%; top:76%; animation-delay:3.8s; }
.ring { position:absolute; left:50%; top:50%; width:300px; height:300px; margin:-150px 0 0 -150px; border-radius:50%;
        border:2px dashed rgba(190,240,243,.35); transform:rotateX(72deg); animation: ringspin 18s linear infinite; }

@keyframes spin     { from { transform: rotateX(-18deg) rotateY(0deg); } to { transform: rotateX(-18deg) rotateY(360deg); } }
@keyframes float    { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-22px); } }
@keyframes ringspin { from { transform:rotateX(72deg) rotateZ(0); } to { transform:rotateX(72deg) rotateZ(360deg); } }
@keyframes shine    { to { background-position:200% center; } }
@keyframes rise     { from { opacity:0; transform:translateY(24px); } to { opacity:1; transform:none; } }

/* ---------- right side: login card ---------- */
.card { width:100%; max-width:410px; margin:0 auto; padding:34px 32px; border-radius:20px;
        background:rgba(255,255,255,.10); border:1px solid rgba(255,255,255,.28);
        backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
        box-shadow:0 20px 60px rgba(0,0,0,.35); animation: rise .8s ease both; }
.card h2 { margin:0 0 4px; font-size:26px; }
.card p.hint { margin:0 0 22px; color:#cfeef0; font-size:14px; }
.card label { display:block; margin:14px 0 6px; font-size:13px; font-weight:600; color:#e5f8f9; }
.card input { width:100%; padding:13px 14px; border-radius:10px; border:1px solid rgba(255,255,255,.35);
              background:rgba(255,255,255,.14); color:#fff; font-size:15px; outline:none; transition:.2s; }
.card input::placeholder { color:#b6d9dc; }
.card input:focus { border-color:#9ee8ee; background:rgba(255,255,255,.22); box-shadow:0 0 0 3px rgba(158,232,238,.25); }
.card button { width:100%; margin-top:24px; padding:14px; border:0; border-radius:10px; cursor:pointer;
               font-size:16px; font-weight:700; color:#06353d;
               background:linear-gradient(90deg, #9ee8ee, #ffffff); transition:transform .2s, box-shadow .2s; }
.card button:hover { transform:translateY(-2px); box-shadow:0 10px 24px rgba(158,232,238,.35); }
.forgot { margin-top:14px; text-align:center; font-size:14px; }
.forgot a { color:#bfeef1; text-decoration:none; border-bottom:1px dashed rgba(191,238,241,.6); }
.forgot a:hover { color:#fff; border-bottom-color:#fff; }
.error { background:rgba(255,90,90,.2); border:1px solid rgba(255,150,150,.7); color:#ffe2e2; padding:10px 12px; border-radius:10px; margin-bottom:6px; font-size:14px; }
.foot { margin-top:18px; text-align:center; font-size:12px; color:#a9d3d6; }

/* ---------- phones and tablets ---------- */
@media (max-width: 900px) {
    .wrap { grid-template-columns:1fr; padding:24px 20px; }
    .scene { height:250px; }
    .stage { transform:scale(.7); }
}
/* people who prefer less motion get a still page */
@media (prefers-reduced-motion: reduce) {
    .stage, .orb, .ring, .hero h1, .card { animation:none !important; }
}
</style>
</head>
<body>
<div class="wrap">

    <section class="hero">
        <span class="badge">Welcome to</span>
        <h1>LIFECARE</h1>
        <p class="sub">Medical &amp; Dental Clinic</p>

        <div class="scene" id="scene" aria-hidden="true">
            <div class="ring"></div>
            <div class="orb o1"></div><div class="orb o2"></div><div class="orb o3"></div><div class="orb o4"></div>
            <div class="stage">
                <div class="box v">
                    <div class="f fr"></div><div class="f bk"></div><div class="f lt"></div>
                    <div class="f rt"></div><div class="f tp"></div><div class="f bt"></div>
                </div>
                <div class="box h">
                    <div class="f fr"></div><div class="f bk"></div><div class="f lt"></div>
                    <div class="f rt"></div><div class="f tp"></div><div class="f bt"></div>
                </div>
            </div>
        </div>

        <p class="tag">Caring for every heartbeat and every smile. Appointments and patient records, all in one place.</p>
    </section>

    <form method="post" class="card" autocomplete="off">
        <h2>Staff sign in</h2>
        <p class="hint">Enter your username and password to continue.</p>

        <?php if ($error !== ''): ?>
            <div class="error"><?php echo e($error); ?></div>
        <?php endif; ?>

        <label for="username">Username</label>
        <input id="username" name="username" placeholder="Your username" required autofocus autocomplete="off">

        <label for="password">Password</label>
        <input id="password" name="password" type="password" placeholder="Your password" required autocomplete="new-password">

        <button type="submit">Log in</button>
        <div class="forgot"><a href="forgot.php">Forgot password?</a></div>
        <div class="foot">&copy; <?php echo date('Y'); ?> LIFECARE Medical and Dental Clinic</div>
    </form>

</div>

<script>
// the scene leans slightly toward the mouse (small effect, ignored on touch screens)
var scene = document.getElementById('scene');
document.addEventListener('mousemove', function (ev) {
    var x = (ev.clientX / window.innerWidth  - 0.5) * 14;
    var y = (ev.clientY / window.innerHeight - 0.5) * -10;
    scene.style.transform = 'rotateY(' + x + 'deg) rotateX(' + y + 'deg)';
});
</script>
</body>
</html>