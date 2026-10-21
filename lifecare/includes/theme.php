<?php // includes/theme.php - all colours, layout and animations for the staff pages ?>
<style>
/* LIFECARE - main stylesheet (modern teal theme) */
:root {
    --brand:#0b6b73; --brand-dark:#084f56; --brand-soft:#e3f2f3; --accent:#9ee8ee;
    --bg:#eef4f5; --line:#e1e8ea; --text:#1b2e31; --muted:#66797c;
    --shadow:0 4px 18px rgba(11,107,115,.10);
}

* { box-sizing:border-box; }
body { margin:0; color:var(--text); font-family: system-ui, "Segoe UI", Roboto, sans-serif;
       background: radial-gradient(circle at 90% 0%, #d9eef0 0%, transparent 40%), var(--bg); }

/* ---------- layout ---------- */
.layout  { display:flex; min-height:100vh; }

.sidebar { width:248px; flex-shrink:0; color:#fff; padding-top:10px;
           background: linear-gradient(180deg, #04262b 0%, #0b4a52 60%, #0e6068 100%);
           box-shadow: 4px 0 24px rgba(4,38,43,.25); position:relative; z-index:2; }
.brand   { display:flex; align-items:center; gap:12px; padding:18px 20px 22px; font-weight:800; font-size:20px;
           letter-spacing:.14em; color:#fff; }
.brand small { display:block; font-weight:400; font-size:11px; color:#a9d3d6; letter-spacing:.04em; margin-top:2px; }
.brand .name { line-height:1.1; }

.sidebar a { display:block; margin:3px 12px; padding:11px 16px; color:#cfeef0; text-decoration:none;
             border-radius:10px; transition: background .2s, transform .2s, color .2s; }
.sidebar a:hover { background:rgba(255,255,255,.12); color:#fff; transform:translateX(4px); }
.sidebar a.active { background:linear-gradient(90deg, rgba(158,232,238,.30), rgba(158,232,238,.10));
                    color:#fff; font-weight:700; box-shadow: inset 3px 0 0 var(--accent); }

.content { flex:1; min-width:0; }
.topbar  { background:rgba(255,255,255,.85); backdrop-filter: blur(8px); border-bottom:1px solid var(--line);
           padding:12px 28px; display:flex; justify-content:space-between; align-items:center;
           position:sticky; top:0; z-index:1; }
.role    { background:var(--brand-soft); color:var(--brand); padding:3px 12px; border-radius:99px;
           font-size:12px; font-style:normal; font-weight:600; margin-left:8px; text-transform:capitalize; }
.logout  { color:#fff; background:var(--brand); padding:8px 16px; border-radius:8px; text-decoration:none;
           font-weight:600; transition: background .2s, transform .2s; }
.logout:hover { background:var(--brand-dark); transform:translateY(-1px); }
.page    { padding:28px; animation: pagein .45s ease both; }
@keyframes pagein { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:none; } }

/* ---------- 3D logo in the sidebar ---------- */
.logo3d { width:38px; height:38px; perspective:300px; flex-shrink:0; }
.logo3d .spin { position:relative; width:38px; height:38px; transform-style:preserve-3d; animation: logospin 8s linear infinite; }
.logo3d .layer { position:absolute; inset:0; background:linear-gradient(145deg, #bff7fa, #4fc3cc);
    clip-path: polygon(35% 0, 65% 0, 65% 35%, 100% 35%, 100% 65%, 65% 65%, 65% 100%, 35% 100%, 35% 65%, 0 65%, 0 35%, 35% 35%); }
.logo3d .l1 { transform: translateZ(-7px); opacity:.6; }
.logo3d .l2 { transform: translateZ(0); }
.logo3d .l3 { transform: translateZ(7px); opacity:.85; }
@keyframes logospin { from { transform: rotateY(0); } to { transform: rotateY(360deg); } }

/* ---------- headings, cards, forms ---------- */
h1 { margin:0 0 18px; font-size:28px; letter-spacing:-.01em; }
h2 { margin:0 0 14px; font-size:18px; }
small { color:var(--muted); }
.card { background:#fff; border:1px solid var(--line); border-radius:14px; padding:22px; margin-bottom:22px;
        box-shadow:var(--shadow); transition: transform .25s, box-shadow .25s; }
.card:hover { transform:translateY(-2px); box-shadow:0 10px 28px rgba(11,107,115,.16); }

label { display:block; font-size:13px; font-weight:600; margin-bottom:5px; color:#33484b; }
input, select, textarea { width:100%; padding:10px 12px; border:1px solid #c9d6d8; border-radius:10px;
        font:inherit; background:#fff; transition: border-color .2s, box-shadow .2s; }
input:focus, select:focus, textarea:focus { outline:none; border-color:var(--brand); box-shadow:0 0 0 3px rgba(11,107,115,.18); }
.form-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(210px, 1fr)); gap:16px; align-items:end; }
.span-2 { grid-column: 1 / -1; }
.form-buttons { display:flex; align-items:center; gap:14px; }

.btn { background:linear-gradient(135deg, #0e8791, var(--brand)); color:#fff; border:0; padding:11px 20px; border-radius:10px;
       font:inherit; font-weight:600; cursor:pointer; box-shadow:0 4px 12px rgba(11,107,115,.28);
       transition: transform .2s, box-shadow .2s; }
.btn:hover { transform:translateY(-2px); box-shadow:0 8px 18px rgba(11,107,115,.35); }
a.btn { text-decoration:none; display:inline-block; }
.btn-secondary { color:#33484b; text-decoration:none; font-weight:600; }
.btn-secondary:hover { text-decoration:underline; }
.toolbar { display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:18px; }
.toolbar h1 { margin:0; }
.search-form input { max-width:380px; }

/* ---------- messages ---------- */
.alert { padding:12px 16px; border-radius:10px; margin-bottom:18px; border-left:4px solid; }
.alert.error   { background:#fdeaea; color:#9b2222; border-color:#d9534f; }
.alert.success { background:#e3f6e8; color:#1d6b34; border-color:#3c9d5b; }
.alert a { color:inherit; font-weight:700; }

/* ---------- tables ---------- */
.table-wrap { overflow-x:auto; background:#fff; border:1px solid var(--line); border-radius:14px; box-shadow:var(--shadow); padding:6px 10px 10px; }
table { width:100%; border-collapse:collapse; }
th, td { text-align:left; padding:12px 14px; border-bottom:1px solid var(--line); vertical-align:middle; }
th { font-size:12px; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); }
tbody tr { transition: background .15s; }
tbody tr:hover { background:#f3fafa; }
tbody tr:last-child td { border-bottom:0; }
td a { color:var(--brand); font-weight:600; text-decoration:none; }
td a:hover { text-decoration:underline; }
td.actions { white-space:nowrap; }
.pill { padding:3px 12px; border-radius:99px; font-size:12px; font-weight:600; display:inline-block; }
.pill.ok, .pill.Completed { background:#dff3e4; color:#1d6b34; }
.pill.off, .pill.Cancelled { background:#fbe3e3; color:#9b2222; }
.pill.Scheduled { background:#fdf0dc; color:#8a4b00; }

/* buttons that look like links */
.inline-form { display:inline; margin-left:14px; }
.link-danger, .link-plain { background:none; border:0; padding:0; font:inherit; font-weight:600; cursor:pointer; width:auto; }
.link-danger { color:#c0392b; }
.link-plain  { color:var(--brand); }
.link-danger:hover, .link-plain:hover { text-decoration:underline; }

/* ---------- medical records ---------- */
.summary-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(170px, 1fr)); gap:16px; margin-bottom:16px; }
.summary-grid span { display:block; font-size:12px; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); }
.summary-grid strong { font-size:16px; }
.history-box { background:var(--brand-soft); border-radius:10px; padding:12px 16px; }
.record-item { border-left:4px solid var(--brand); background:#fff; padding:14px 18px; margin-bottom:14px;
               border-radius:0 12px 12px 0; box-shadow:var(--shadow); }
.record-item .meta { color:var(--muted); font-size:13px; margin-bottom:6px; }
.record-item p { margin:4px 0; white-space:pre-wrap; }

/* ---------- older dashboard classes (kept so nothing breaks) ---------- */
.stats { display:grid; grid-template-columns:repeat(auto-fit, minmax(190px, 1fr)); gap:16px; margin:20px 0; }
.stat-card { background:#fff; border:1px solid var(--line); border-radius:14px; padding:18px 20px; box-shadow:var(--shadow); }
.stat-value { font-size:34px; font-weight:700; color:var(--brand); line-height:1.1; }
.stat-label { margin-top:6px; color:var(--muted); font-size:14px; }

/* ---------- phones ---------- */
@media (max-width: 768px) {
    .layout { flex-direction:column; }
    .sidebar { width:100%; display:flex; flex-wrap:wrap; padding:6px; }
    .brand { width:100%; padding:10px 14px; }
    .sidebar a { margin:2px; padding:8px 12px; }
    .page { padding:18px 14px; }
    .topbar { padding:10px 14px; }
}
@media (prefers-reduced-motion: reduce) {
    .logo3d .spin, .page { animation:none !important; }
    .card, .btn, .sidebar a { transition:none; }
}

/* ---------- soft floating shapes behind every page ---------- */
.bgshapes { position:fixed; inset:0; pointer-events:none; z-index:0; overflow:hidden; }
.bgshapes i { position:absolute; border-radius:50%; background:radial-gradient(circle at 30% 30%, rgba(255,255,255,.9), rgba(11,107,115,.12));
              border:1px solid rgba(11,107,115,.12); animation: drift 16s ease-in-out infinite; }
.bgshapes i:nth-child(1) { width:120px; height:120px; right:6%;  top:18%; }
.bgshapes i:nth-child(2) { width:70px;  height:70px;  right:22%; top:70%; animation-delay:3s; }
.bgshapes i:nth-child(3) { width:50px;  height:50px;  right:4%;  top:80%; animation-delay:6s; }
.bgshapes i:nth-child(4) { width:90px;  height:90px;  left:30%;  top:85%; animation-delay:9s; }
@keyframes drift { 0%,100% { transform:translateY(0) translateX(0); } 50% { transform:translateY(-34px) translateX(14px); } }
.content, .sidebar { position:relative; z-index:1; }
@media (prefers-reduced-motion: reduce) { .bgshapes i { animation:none; } }

/* ---------- spacing fix: keep page content away from the edges ---------- */
.content > main, main.page { display:block; padding:28px !important; max-width:100%; }

/* ---------- animations ---------- */
@keyframes rise    { from { opacity:0; transform:translateY(18px); } to { opacity:1; transform:none; } }
@keyframes slidein { from { opacity:0; transform:translateX(-22px); } to { opacity:1; transform:none; } }
@keyframes rowin   { from { opacity:0; transform:translateX(-16px); } to { opacity:1; transform:none; } }
@keyframes pop     { from { opacity:0; transform:scale(.6); } to { opacity:1; transform:none; } }
@keyframes shake   { 0%,100% { transform:translateX(0); } 20%,60% { transform:translateX(-7px); } 40%,80% { transform:translateX(7px); } }
@keyframes alertin { from { opacity:0; transform:translateY(-12px); } to { opacity:1; transform:none; } }

/* everything on a page rises in, one after another */
.page > * { animation: rise .55s ease backwards; }
.page > *:nth-child(2) { animation-delay:.08s; }
.page > *:nth-child(3) { animation-delay:.16s; }
.page > *:nth-child(4) { animation-delay:.24s; }
.page > *:nth-child(5) { animation-delay:.32s; }
.page > *:nth-child(n+6) { animation-delay:.4s; }

/* cards inside grids pop in one by one */
.dash-stats > *, .rep-stats > *, .stats > *, .summary-grid > *, .dash-columns > * { animation: rise .6s ease backwards; }
.dash-stats > *:nth-child(1), .rep-stats > *:nth-child(1), .stats > *:nth-child(1), .dash-columns > *:nth-child(1) { animation-delay:.15s; }
.dash-stats > *:nth-child(2), .rep-stats > *:nth-child(2), .stats > *:nth-child(2), .dash-columns > *:nth-child(2) { animation-delay:.28s; }
.dash-stats > *:nth-child(3), .rep-stats > *:nth-child(3), .stats > *:nth-child(3) { animation-delay:.41s; }
.dash-stats > *:nth-child(4), .rep-stats > *:nth-child(4), .stats > *:nth-child(4) { animation-delay:.54s; }
.dash-stat, .rep-stat { transition: transform .25s, box-shadow .25s; }
.dash-stat:hover, .rep-stat:hover { transform:translateY(-4px); box-shadow:0 12px 26px rgba(11,107,115,.18); }

/* table rows and list items slide in */
tbody tr { animation: rowin .45s ease backwards; }
tbody tr:nth-child(1) { animation-delay:.20s; } tbody tr:nth-child(2) { animation-delay:.26s; }
tbody tr:nth-child(3) { animation-delay:.32s; } tbody tr:nth-child(4) { animation-delay:.38s; }
tbody tr:nth-child(5) { animation-delay:.44s; } tbody tr:nth-child(6) { animation-delay:.50s; }
tbody tr:nth-child(7) { animation-delay:.56s; } tbody tr:nth-child(8) { animation-delay:.62s; }
tbody tr:nth-child(n+9) { animation-delay:.7s; }
.dash-item, .record-item { animation: rowin .5s ease backwards; animation-delay:.3s; }
.pill { animation: pop .45s ease backwards; animation-delay:.45s; }

/* menu links slide in */
.sidebar a { animation: slidein .5s ease backwards; }
.sidebar a:nth-of-type(1) { animation-delay:.05s; } .sidebar a:nth-of-type(2) { animation-delay:.10s; }
.sidebar a:nth-of-type(3) { animation-delay:.15s; } .sidebar a:nth-of-type(4) { animation-delay:.20s; }
.sidebar a:nth-of-type(5) { animation-delay:.25s; } .sidebar a:nth-of-type(6) { animation-delay:.30s; }
.sidebar a:nth-of-type(7) { animation-delay:.35s; }

/* messages */
.alert { animation: alertin .5s ease backwards; }
.alert.error { animation: alertin .4s ease backwards, shake .5s ease .35s; }

/* buttons feel pressed */
.btn:active, .rep-btn:active, .logout:active { transform:scale(.96) !important; }

/* soft pulse on the active menu item */
.sidebar a.active { animation: slidein .5s ease backwards, glow 3s ease-in-out 1s infinite; }
@keyframes glow { 0%,100% { box-shadow: inset 3px 0 0 var(--accent); } 50% { box-shadow: inset 3px 0 0 var(--accent), 0 0 18px rgba(158,232,238,.35); } }

/* page leaving effect when a menu link is clicked */
body { transition: opacity .18s ease, transform .18s ease; }
body.leaving { opacity:0; transform:translateY(-8px); }

@media (prefers-reduced-motion: reduce) {
    .page > *, .dash-stats > *, .rep-stats > *, .stats > *, .summary-grid > *, .dash-columns > *,
    tbody tr, .dash-item, .record-item, .pill, .sidebar a, .alert, .alert.error { animation:none !important; }
    body { transition:none; }
}
</style>
<script>
// 1) numbers count up from 0
document.addEventListener('DOMContentLoaded', function () {
    var calm = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!calm) {
        document.querySelectorAll('.dash-stat .num, .rep-stat .num, .stat-value').forEach(function (el) {
            var target = parseInt(el.textContent, 10);
            if (isNaN(target) || target <= 0) { return; }
            var start = null;
            el.textContent = '0';
            function step(t) {
                if (start === null) { start = t; }
                var p = Math.min((t - start) / 900, 1);
                el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3)));
                if (p < 1) { requestAnimationFrame(step); }
            }
            setTimeout(function () { requestAnimationFrame(step); }, 350);
        });
    }

    // 2) smooth fade when moving to another page
    document.addEventListener('click', function (ev) {
        var a = ev.target.closest ? ev.target.closest('a[href]') : null;
        if (!a || calm) { return; }
        var href = a.getAttribute('href');
        if (!href || href.charAt(0) === '#' || a.target === '_blank' || ev.ctrlKey || ev.metaKey || ev.shiftKey) { return; }
        if (href.indexOf('export=') !== -1 || href.indexOf('javascript:') === 0) { return; }
        if (a.host !== window.location.host) { return; }
        ev.preventDefault();
        document.body.classList.add('leaving');
        setTimeout(function () { window.location.href = a.href; }, 180);
    });
});
// coming back with the browser Back button: show the page again
window.addEventListener('pageshow', function () { document.body.classList.remove('leaving'); });
</script>