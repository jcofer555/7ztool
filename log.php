<?php
$logFile = '/boot/config/plugins/7ztool/logs/operation.log';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    file_put_contents($logFile, '');
    echo "Log cleared.";
    exit;
}

if (!file_exists($logFile)) {
    echo "No log entries found.";
    exit;
}

$lines = array_slice(file($logFile), -100);
echo htmlspecialchars(implode('', $lines));
