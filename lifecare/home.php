<?php
// home.php - public home page (no login needed): services, doctors, contact details and opening hours
require_once __DIR__ . '/includes/config.php';

// clinic details saved by the admin on the "Clinic page"
$info = ['clinic_phone' => '', 'clinic_email' => '', 'clinic_address' => '', 'clinic_hours' => ''];
foreach ($pdo->query('SELECT setting_key, setting_value FROM clinic_settings') as $row) {
    if (array_key_exists($row['setting_key'], $info)) {
        $info[$row['setting_key']] = $row['setting_value'];
    }
}

// visible services, grouped by type
$groups = ['Medical' => [], 'Dental' => []];
foreach ($pdo->query('SELECT name, category, description FROM services WHERE active = 1 ORDER BY name') as $s) {
    $groups[$s['category']][] = $s;
}
$hasServices = count($groups['Medical']) + count($groups['Dental']) > 0;

// active doctors (name only, nothing private is shown)
$doctors = $pdo->query("SELECT full_name FROM users WHERE role = 'doctor' AND active = 1 ORDER BY full_name")->fetchAll();

function doctor_title($name) {
    return preg_match('/^dr\.?\s/i', $name) ? $name : 'Dr. ' . $name;
}
function initials($name) {
    $name = trim(preg_replace('/^dr\.?\s+/i', '', $name));
    $parts = preg_split('/\s+/', $name);
    $out = strtoupper(substr($parts[0], 0, 1));
    if (count($parts) > 1) { $out .= strtoupper(substr(end($parts), 0, 1)); }
    return $out;
}
$phoneLink = preg_replace('/[^0-9+]/', '', $info['clinic_phone']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>LIFECARE Medical and Dental Clinic</title>
<style>
* { box-sizing:border-box; }
html { scroll-behavior:smooth; }
body { margin:0; font-family: system-ui, 'Segoe UI', Roboto, sans-serif; color:#1b2e31; background:#f1f7f8; }
a { color:inherit; }

/* ---------- top bar ---------- */
.nav { position:fixed; top:0; left:0; right:0; z-index:10; display:flex; justify-content:space-between; align-items:center;
       padding:14px 5vw; background:rgba(4,38,43,.82); backdrop-filter:blur(10px); color:#fff; }
.nav .logo { font-weight:800; letter-spacing:.14em; font-size:19px; text-decoration:none; }
.nav .links { display:flex; align-items:center; gap:22px; }
.nav .links a { text-decoration:none; color:#cfeef0; font-weight:600; font-size:15px; }
.nav .links a:hover { color:#fff; }
.nav .login { background:linear-gradient(90deg,#9ee8ee,#fff); color:#06353d !important; padding:9px 18px; border-radius:99px; }

/* ---------- hero ---------- */
.hero { min-height:100vh; display:grid; grid-template-columns:1.1fr 1fr; align-items:center; gap:30px; padding:110px 5vw 60px; color:#fff;
        background: radial-gradient(circle at 20% 25%, #138a94 0%, transparent 45%), radial-gradient(circle at 85% 80%, #0e5a73 0%, transparent 50%),
                    linear-gradient(135deg, #04262b 0%, #0b4a52 55%, #06353d 100%); overflow:hidden; }
.hero .badge { display:inline-block; padding:6px 16px; border:1px solid rgba(255,255,255,.35); border-radius:99px; font-size:13px; letter-spacing:.18em; text-transform:uppercase; color:#bfeef1; }
.hero h1 { margin:18px 0 10px; font-size:clamp(34px, 5vw, 62px); line-height:1.1; }
.hero h1 span { background:linear-gradient(90deg,#fff,#9ee8ee,#fff); background-size:200% auto; -webkit-background-clip:text; background-clip:text; color:transparent; animation:shine 6s linear infinite; }
.hero p { margin:0 0 26px; max-width:520px; color:#cfeef0; line-height:1.7; font-size:17px; }
.btns { display:flex; gap:12px; flex-wrap:wrap; }
.btn { display:inline-block; padding:13px 24px; border-radius:99px; text-decoration:none; font-weight:700; transition:transform .2s, box-shadow .2s; }
.btn:hover { transform:translateY(-3px); }
.btn.main { background:linear-gradient(90deg,#9ee8ee,#fff); color:#06353d; }
.btn.main:hover { box-shadow:0 10px 24px rgba(158,232,238,.35); }
.btn.ghost { border:1px solid rgba(255,255,255,.6); color:#fff; }
.btn.ghost:hover { background:rgba(255,255,255,.14); }

.scene { height:380px; perspective:900px; display:flex; align-items:center; justify-content:center; position:relative; }
.stage { position:relative; width:220px; height:220px; transform-style:preserve-3d; animation:spin 14s linear infinite; }
.box { position:absolute; transform-style:preserve-3d; left:50%; top:50%; }
.box .f { position:absolute; left:0; top:0; border:1px solid rgba(255,255,255,.55); background:linear-gradient(145deg, rgba(120,235,240,.55), rgba(20,140,150,.45)); box-shadow:inset 0 0 22px rgba(255,255,255,.25); }
.v { width:64px; height:220px; margin-left:-32px; margin-top:-110px; }
.v .f.fr, .v .f.bk, .v .f.lt, .v .f.rt { width:64px; height:220px; }
.v .f.fr { transform:translateZ(32px); } .v .f.bk { transform:rotateY(180deg) translateZ(32px); }
.v .f.rt { transform:rotateY(90deg) translateZ(32px); } .v .f.lt { transform:rotateY(-90deg) translateZ(32px); }
.v .f.tp, .v .f.bt { width:64px; height:64px; }
.v .f.tp { transform:rotateX(90deg) translateZ(32px); } .v .f.bt { transform:rotateX(-90deg) translateZ(188px); }
.h { width:220px; height:64px; margin-left:-110px; margin-top:-32px; }
.h .f.fr, .h .f.bk { width:220px; height:64px; }
.h .f.fr { transform:translateZ(32px); } .h .f.bk { transform:rotateY(180deg) translateZ(32px); }
.h .f.lt, .h .f.rt { width:64px; height:64px; }
.h .f.rt { transform:rotateY(90deg) translateZ(188px); } .h .f.lt { transform:rotateY(-90deg) translateZ(32px); }
.h .f.tp, .h .f.bt { width:220px; height:64px; }
.h .f.tp { transform:rotateX(90deg) translateZ(32px); } .h .f.bt { transform:rotateX(-90deg) translateZ(32px); }
.orb { position:absolute; border-radius:50%; border:1px solid rgba(255,255,255,.5); background:radial-gradient(circle at 30% 30%, rgba(255,255,255,.7), rgba(120,235,240,.25) 60%, rgba(20,140,150,.15)); animation:float 7s ease-in-out infinite; }
.orb.o1 { width:46px; height:46px; left:8%; top:14%; } .orb.o2 { width:28px; height:28px; left:86%; top:22%; animation-delay:1.4s; }
.orb.o3 { width:64px; height:64px; left:80%; top:68%; animation-delay:2.6s; } .orb.o4 { width:20px; height:20px; left:14%; top:76%; animation-delay:3.8s; }
.ring { position:absolute; left:50%; top:50%; width:300px; height:300px; margin:-150px 0 0 -150px; border-radius:50%; border:2px dashed rgba(190,240,243,.35); transform:rotateX(72deg); animation:ringspin 18s linear infinite; }
@keyframes spin { from { transform:rotateX(-18deg) rotateY(0); } to { transform:rotateX(-18deg) rotateY(360deg); } }
@keyframes float { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-22px); } }
@keyframes ringspin { from { transform:rotateX(72deg) rotateZ(0); } to { transform:rotateX(72deg) rotateZ(360deg); } }
@keyframes shine { to { background-position:200% center; } }

/* ---------- sections ---------- */
section.block { padding:80px 5vw; }
section.block h2 { margin:0 0 8px; font-size:clamp(26px, 3.4vw, 38px); }
section.block .lead { margin:0 0 34px; color:#66797c; max-width:620px; }
.grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(250px, 1fr)); gap:18px; }
.card { background:#fff; border:1px solid #e1e8ea; border-radius:16px; padding:22px; box-shadow:0 4px 18px rgba(11,107,115,.08); transition:transform .25s, box-shadow .25s; }
.card:hover { transform:translateY(-6px); box-shadow:0 14px 30px rgba(11,107,115,.16); }
.card h3 { margin:12px 0 6px; font-size:18px; }
.card p { margin:0; color:#66797c; line-height:1.55; font-size:15px; }
.tag { display:inline-block; font-size:12px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; padding:4px 12px; border-radius:99px; }
.tag.Medical { background:#e3f2f3; color:#0b6b73; } .tag.Dental { background:#e6efff; color:#2f5fb3; }
.group-title { margin:28px 0 14px; font-size:20px; color:#0b6b73; }
.avatar { width:68px; height:68px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:24px; font-weight:800; color:#fff; background:linear-gradient(135deg, #0e8791, #0b4a52); box-shadow:0 8px 18px rgba(11,107,115,.3); }
.doc { text-align:center; }
.doc .avatar { margin:0 auto 12px; }
.empty { padding:26px; text-align:center; color:#66797c; background:#fff; border:1px dashed #c9d6d8; border-radius:14px; }
.dark { background:linear-gradient(135deg, #04262b, #0b4a52); color:#fff; }
.dark .lead { color:#bfe3e6; }
.dark .card { background:rgba(255,255,255,.08); border-color:rgba(255,255,255,.2); box-shadow:none; }
.dark .card p { color:#cfeef0; } .dark .card h3 { color:#fff; }
.dark .card a { color:#9ee8ee; font-weight:600; }
footer { padding:26px 5vw; text-align:center; background:#031d21; color:#a9d3d6; font-size:14px; }

/* cards appear as you scroll */
.reveal { opacity:0; transform:translateY(26px); transition:opacity .6s ease, transform .6s ease; }
.reveal.show { opacity:1; transform:none; }

@media (max-width:900px) {
    .hero { grid-template-columns:1fr; text-align:center; padding-top:100px; }
    .hero p { margin-left:auto; margin-right:auto; } .btns { justify-content:center; }
    .scene { height:280px; } .stage { transform:scale(.72); }
    .nav .links a:not(.login) { display:none; }
}
@media (prefers-reduced-motion: reduce) {
    .stage, .orb, .ring, .hero h1 span { animation:none !important; }
    .reveal { opacity:1; transform:none; transition:none; }
}
</style>
</head>
<body>

<nav class="nav">
    <a class="logo" href="#top">LIFECARE</a>
    <div class="links">
        <a href="#services">Services</a>
        <a href="#doctors">Doctors</a>
        <a href="#contact">Contact</a>
        <a class="login" href="login.php">Staff login</a>
    </div>
</nav>

<header class="hero" id="top">
    <div>
        <span class="badge">Medical &amp; Dental Clinic</span>
        <h1>Caring for every <span>heartbeat</span> and every <span>smile</span></h1>
        <p>Friendly doctors, modern care and well-kept records, all under one roof. See what we offer and how to reach us.</p>
        <div class="btns">
            <a class="btn main" href="#services">Our services</a>
            <?php if ($phoneLink !== ''): ?>
                <a class="btn ghost" href="tel:<?php echo e($phoneLink); ?>">Call <?php echo e($info['clinic_phone']); ?></a>
            <?php else: ?>
                <a class="btn ghost" href="#contact">Find us</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="scene" aria-hidden="true">
        <div class="ring"></div>
        <div class="orb o1"></div><div class="orb o2"></div><div class="orb o3"></div><div class="orb o4"></div>
        <div class="stage">
            <div class="box v"><div class="f fr"></div><div class="f bk"></div><div class="f lt"></div><div class="f rt"></div><div class="f tp"></div><div class="f bt"></div></div>
            <div class="box h"><div class="f fr"></div><div class="f bk"></div><div class="f lt"></div><div class="f rt"></div><div class="f tp"></div><div class="f bt"></div></div>
        </div>
    </div>
</header>

<section class="block" id="services">
    <h2>Our services</h2>
    <p class="lead">From check-ups to dental care, here is what we can help you with.</p>

    <?php if (!$hasServices): ?>
        <div class="empty">Our list of services will appear here soon.</div>
    <?php endif; ?>

    <?php foreach ($groups as $type => $list): ?>
        <?php if (count($list) > 0): ?>
            <h3 class="group-title"><?php echo e($type); ?> care</h3>
            <div class="grid">
                <?php foreach ($list as $s): ?>
                    <div class="card reveal">
                        <span class="tag <?php echo e($type); ?>"><?php echo e($type); ?></span>
                        <h3><?php echo e($s['name']); ?></h3>
                        <?php if ($s['description'] !== null && $s['description'] !== ''): ?>
                            <p><?php echo e($s['description']); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</section>

<section class="block" id="doctors" style="background:#fff">
    <h2>Meet our doctors</h2>
    <p class="lead">The team looking after you.</p>
    <?php if (count($doctors) === 0): ?>
        <div class="empty">Our doctors will be listed here soon.</div>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($doctors as $d): ?>
                <div class="card doc reveal">
                    <div class="avatar"><?php echo e(initials($d['full_name'])); ?></div>
                    <h3><?php echo e(doctor_title($d['full_name'])); ?></h3>
                    <p>LIFECARE Medical and Dental Clinic</p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="block dark" id="contact">
    <h2>Visit or contact us</h2>
    <p class="lead">We would love to hear from you.</p>
    <div class="grid">
        <div class="card reveal">
            <h3>Phone</h3>
            <p><?php if ($info['clinic_phone'] !== ''): ?><a href="tel:<?php echo e($phoneLink); ?>"><?php echo e($info['clinic_phone']); ?></a><?php else: ?>Coming soon<?php endif; ?></p>
        </div>
        <div class="card reveal">
            <h3>Email</h3>
            <p><?php if ($info['clinic_email'] !== ''): ?><a href="mailto:<?php echo e($info['clinic_email']); ?>"><?php echo e($info['clinic_email']); ?></a><?php else: ?>Coming soon<?php endif; ?></p>
        </div>
        <div class="card reveal">
            <h3>Where to find us</h3>
            <p><?php echo $info['clinic_address'] !== '' ? e($info['clinic_address']) : 'Coming soon'; ?></p>
        </div>
        <div class="card reveal">
            <h3>Opening hours</h3>
            <p><?php echo $info['clinic_hours'] !== '' ? e($info['clinic_hours']) : 'Coming soon'; ?></p>
        </div>
    </div>
</section>

<footer>&copy; <?php echo date('Y'); ?> LIFECARE Medical and Dental Clinic &middot; <a href="login.php">Staff login</a></footer>

<script>
// cards fade in as they scroll into view
var items = document.querySelectorAll('.reveal');
if ('IntersectionObserver' in window) {
    var seen = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) { if (en.isIntersecting) { en.target.classList.add('show'); seen.unobserve(en.target); } });
    }, { threshold: 0.15 });
    items.forEach(function (el) { seen.observe(el); });
} else {
    items.forEach(function (el) { el.classList.add('show'); });
}
</script>
</body>
</html>