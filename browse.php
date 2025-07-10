<?php
$path = $_GET['path'] ?? '/mnt/user/';
$path = realpath($path);

// Validate path
if (!$path || !is_dir($path)) {
    echo "<p>Invalid path</p>";
    exit;
}

// Human-readable size function
function humanSize($bytes) {
    if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return round($bytes / 1024, 2) . ' KB';
    return $bytes . ' B';
}

// Breadcrumbs
echo "<div class='breadcrumbs'>";
$parts = explode('/', trim($path, '/'));
$cumulative = '';
echo "<a href='#' onclick=\"loadPicker('/mnt/user')\">root</a> / ";
foreach ($parts as $part) {
    $cumulative .= "/$part";
    echo "<a href='#' onclick=\"loadPicker('$cumulative')\">$part</a> / ";
}
echo "</div>";

// File/folder list
echo "<ul class='picker-list'>";
if ($path !== '/') {
    $parent = dirname($path);
    echo "<li><a href='#' onclick=\"loadPicker('$parent')\">🔼 ..</a></li>";
}

foreach (scandir($path) as $entry) {
    if ($entry === '.' || $entry === '..') continue;
    $full = "$path/$entry";
    $encoded = htmlspecialchars($full);

    if (is_dir($full)) {
        echo "<li><a href='#' onclick=\"loadPicker('$encoded')\">📁 $entry</a></li>";
    } elseif (is_file($full)) {
        $size = humanSize(filesize($full));
        echo "<li><a href='#' onclick=\"selectPath('$encoded')\">📄 $entry <span class='filesize'>($size)</span></a></li>";
    }
}
echo "</ul>";
