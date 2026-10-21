<?php
// whereami.php - temporary helper: shows which files the website really uses (delete it afterwards)
header('Content-Type: text/html; charset=utf-8');
echo '<body style="font-family:Segoe UI,Arial;padding:20px">';
echo '<h2>Where is the website running from?</h2>';
echo '<p><b>This folder:</b> ' . htmlspecialchars(__DIR__) . '</p>';

$items = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__, FilesystemIterator::SKIP_DOTS));
echo '<table border="1" cellpadding="6" style="border-collapse:collapse"><tr><th>File</th><th>Last saved</th><th>Size</th><th>Has the new design?</th></tr>';
foreach ($items as $f) {
    $name = $f->getFilename();
    if (!preg_match('/^(header|style|lifecare|footer|login|welcome|dashboard)\.(php|css)$/', $name)) { continue; }
    $path = str_replace(__DIR__, '', $f->getPathname());
    $text = file_get_contents($f->getPathname());
    $new  = (strpos($text, 'modern teal theme') !== false || strpos($text, 'bgshapes') !== false || strpos($text, 'welcome.php') !== false) ? 'YES' : 'no';
    echo '<tr><td>' . htmlspecialchars($path) . '</td><td>' . date('d M H:i:s', $f->getMTime()) . '</td><td>' . $f->getSize() . '</td><td>' . $new . '</td></tr>';
}
echo '</table><p>Current time: ' . date('d M H:i:s') . '</p></body>';