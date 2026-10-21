<?php
// welcome.php - short animated welcome scene shown after login, then goes to the dashboard
require_once __DIR__ . '/includes/auth.php';
require_login();

$name = $_SESSION['user']['full_name'];
$role = $_SESSION['user']['role'];
$shown = ($role === 'doctor' ? 'Dr. ' : '') . $name;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Welcome - LIFECARE</title>
<noscript><meta http-equiv="refresh" content="1;url=dashboard.php"></noscript>
<style>
* { box-sizing:border-box; }
html, body { margin:0; height:100%; overflow:hidden; }
body { font-family: system-ui, 'Segoe UI', Roboto, sans-serif; color:#fff;
       background: radial-gradient(circle at 25% 25%, #138a94 0%, transparent 45%),
                   linear-gradient(135deg, #04262b 0%, #0b4a52 60%, #06353d 100%); }

/* the whole scene slides up at the end, like a page being pulled away */
.scene { position:fixed; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center;
         animation: pullaway .9s cubic-bezier(.7,0,.3,1) 7.1s forwards; }
@keyframes pullaway { to { transform:translateY(-105%); } }

.title { text-align:center; margin-bottom:6px; animation: fadein .8s ease both; }
.title b { font-size:clamp(26px, 4vw, 40px); letter-spacing:.18em; }
.title span { display:block; font-size:13px; letter-spacing:.3em; text-transform:uppercase; color:#bfeef1; margin-top:4px; }
@keyframes fadein { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:none; } }

svg { width:min(860px, 96vw); height:auto; max-height:62vh; }

/* ----- doctor's reaching arm ----- */
.arm { transform-origin:385px 232px; transform-box:view-box; transform: rotate(8deg);
       animation: armmove 5s ease-in-out .6s forwards; }
@keyframes armmove {
    0%   { transform: rotate(8deg)   scaleY(1); }
    20%  { transform: rotate(-88deg) scaleY(1); }
    26%  { transform: rotate(-88deg) scaleY(1); }
    52%  { transform: rotate(-88deg) scaleY(.5); }
    75%  { transform: rotate(-88deg) scaleY(.5); }
    100% { transform: rotate(8deg)   scaleY(1); }
}

/* ----- drawer slides out, pages rise from it ----- */
.drawer { animation: drawout 1.3s ease-in-out 1.9s forwards; }
@keyframes drawout { to { transform: translateX(-70px); } }
.pg { opacity:0; transform: translateY(26px); animation: pgrise .7s ease-out forwards; }
.pg1 { animation-delay:3.2s; } .pg2 { animation-delay:3.45s; } .pg3 { animation-delay:3.7s; }
@keyframes pgrise { to { opacity:1; transform: translateY(-34px); } }

/* ----- the doctor blinks and floats gently ----- */
.doc { animation: bob 3s ease-in-out infinite; }
@keyframes bob { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-4px); } }
.eye { animation: blink 4s infinite; transform-origin:center; transform-box:fill-box; }
@keyframes blink { 0%,94%,100% { transform:scaleY(1); } 97% { transform:scaleY(.1); } }

/* ----- the page that flies out and shows the welcome message ----- */
.sheet { position:fixed; left:50%; top:50%; width:min(440px, 86vw); padding:34px 30px; text-align:center;
         background:#fff; color:#06353d; border-radius:6px; box-shadow:0 30px 70px rgba(0,0,0,.45);
         opacity:0; transform: translate(-50%,-50%) translate(26vw, 4vh) scale(.12) rotate(10deg);
         animation: sheetfly 1.1s cubic-bezier(.2,.8,.2,1) 4.6s forwards; }
.sheet::before { content:''; position:absolute; left:0; right:0; top:0; height:8px; background:linear-gradient(90deg,#0b6b73,#9ee8ee); border-radius:6px 6px 0 0; }
.sheet small { letter-spacing:.25em; text-transform:uppercase; color:#66797c; font-size:12px; }
.sheet h1 { margin:10px 0 6px; font-size:clamp(24px, 4vw, 32px); }
.sheet p { margin:0; color:#33484b; }
.bar { margin:20px auto 0; height:6px; width:70%; background:#e3f2f3; border-radius:99px; overflow:hidden; }
.bar i { display:block; height:100%; width:0; background:linear-gradient(90deg,#0b6b73,#3fc1c9); animation: load 2s linear 5.2s forwards; }
@keyframes sheetfly { to { opacity:1; transform: translate(-50%,-50%) scale(1) rotate(0); } }
@keyframes load { to { width:100%; } }

.skip { position:fixed; right:20px; bottom:20px; z-index:5; padding:10px 18px; border-radius:99px;
        border:1px solid rgba(255,255,255,.5); background:rgba(255,255,255,.12); color:#fff; text-decoration:none; font-weight:600; }
.skip:hover { background:#fff; color:#06353d; }

@media (prefers-reduced-motion: reduce) { .doc, .eye { animation:none; } }
</style>
</head>
<body>

<div class="scene">
    <div class="title"><b>LIFECARE</b><span>Medical &amp; Dental Clinic</span></div>

    <svg viewBox="0 0 800 460" aria-hidden="true">
        <!-- floor -->
        <ellipse cx="400" cy="425" rx="340" ry="14" fill="rgba(0,0,0,.25)"/>

        <!-- filing cabinet -->
        <g>
            <rect x="470" y="70" width="210" height="350" rx="12" fill="#124f58" stroke="#9ee8ee" stroke-opacity=".5"/>
            <!-- top and bottom drawers (fixed) -->
            <rect x="485" y="85"  width="180" height="100" rx="8" fill="#1c6f79"/>
            <rect x="560" y="125" width="30" height="8" rx="4" fill="#d6f5f7"/>
            <rect x="485" y="305" width="180" height="100" rx="8" fill="#1c6f79"/>
            <rect x="560" y="345" width="30" height="8" rx="4" fill="#d6f5f7"/>
            <!-- middle drawer (moves) -->
            <g class="drawer">
                <rect x="485" y="195" width="180" height="100" rx="8" fill="#238893"/>
                <rect x="500" y="240" width="30" height="8" rx="4" fill="#ffffff"/>
                <!-- pages inside the drawer -->
                <g class="pg pg1"><rect x="520" y="190" width="95" height="62" rx="3" fill="#ffffff" stroke="#c9d6d8"/><rect x="530" y="200" width="60" height="4" fill="#9ee8ee"/><rect x="530" y="210" width="72" height="3" fill="#c9d6d8"/><rect x="530" y="218" width="50" height="3" fill="#c9d6d8"/></g>
                <g class="pg pg2"><rect x="535" y="184" width="95" height="62" rx="3" fill="#f4fbfb" stroke="#c9d6d8"/><rect x="545" y="194" width="60" height="4" fill="#4fc3cc"/><rect x="545" y="204" width="72" height="3" fill="#c9d6d8"/></g>
                <g class="pg pg3"><rect x="550" y="178" width="95" height="62" rx="3" fill="#ffffff" stroke="#c9d6d8"/><rect x="560" y="188" width="60" height="4" fill="#0b6b73"/><rect x="560" y="198" width="72" height="3" fill="#c9d6d8"/></g>
                <!-- front panel again so pages look like they come out of the drawer -->
                <rect x="485" y="252" width="180" height="43" rx="8" fill="#238893"/>
                <rect x="500" y="266" width="30" height="8" rx="4" fill="#ffffff"/>
            </g>
        </g>

        <!-- doctor -->
        <g class="doc">
            <!-- legs -->
            <rect x="300" y="360" width="30" height="62" rx="8" fill="#1b2e31"/>
            <rect x="345" y="360" width="30" height="62" rx="8" fill="#1b2e31"/>
            <rect x="292" y="414" width="46" height="12" rx="6" fill="#0b2a2e"/>
            <rect x="338" y="414" width="46" height="12" rx="6" fill="#0b2a2e"/>
            <!-- far arm (hangs down) -->
            <rect x="268" y="224" width="26" height="110" rx="13" fill="#f4fbfb"/>
            <circle cx="281" cy="338" r="13" fill="#f1c9a5"/>
            <!-- coat -->
            <rect x="285" y="205" width="105" height="170" rx="34" fill="#ffffff"/>
            <path d="M337 205 L337 372" stroke="#d3e4e6" stroke-width="2"/>
            <rect x="350" y="290" width="26" height="22" rx="4" fill="none" stroke="#c9d6d8" stroke-width="2"/>
            <path d="M312 205 L337 250 L362 205 Z" fill="#9ee8ee"/>
            <!-- name badge cross -->
            <path d="M298 262h14M305 255v14" stroke="#0b6b73" stroke-width="4" stroke-linecap="round"/>
            <!-- neck and head -->
            <rect x="326" y="180" width="22" height="28" rx="8" fill="#f1c9a5"/>
            <circle cx="337" cy="148" r="40" fill="#f1c9a5"/>
            <path d="M297 142 C297 100 377 100 377 142 C365 122 322 120 297 142 Z" fill="#2b1d17"/>
            <ellipse class="eye" cx="322" cy="152" rx="4" ry="5" fill="#2b1d17"/>
            <ellipse class="eye" cx="352" cy="152" rx="4" ry="5" fill="#2b1d17"/>
            <path d="M324 168 Q337 180 350 168" stroke="#8a4b2d" stroke-width="3" fill="none" stroke-linecap="round"/>
            <!-- doctor's head band -->
            <rect x="300" y="118" width="74" height="9" rx="4" fill="#4fc3cc"/>
            <!-- stethoscope -->
            <path d="M318 210 C312 262 362 262 356 210" stroke="#124f58" stroke-width="5" fill="none"/>
            <circle cx="337" cy="262" r="8" fill="#9ee8ee" stroke="#124f58" stroke-width="3"/>

            <!-- reaching arm (animated) -->
            <g class="arm">
                <rect x="372" y="222" width="26" height="120" rx="13" fill="#f4fbfb"/>
                <circle cx="385" cy="344" r="14" fill="#f1c9a5"/>
            </g>
        </g>
    </svg>
</div>

<div class="sheet">
    <small>Patient files ready</small>
    <h1>Welcome, <?php echo e($shown); ?></h1>
    <p><?php echo e(ucfirst($role)); ?> &middot; opening your dashboard</p>
    <div class="bar"><i></i></div>
</div>

<a class="skip" href="dashboard.php">Skip</a>

<script>
// go to the dashboard when the scene has finished (or at once if the person asked for less motion)
var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
setTimeout(function () { window.location.href = 'dashboard.php'; }, reduce ? 300 : 8100);
</script>
</body>
</html>