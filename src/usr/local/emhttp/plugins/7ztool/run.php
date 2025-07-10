<?php
header('Content-Type: application/json');

$logDir = '/boot/config/plugins/7ztool/logs';
$logFile = "$logDir/operation.log";
$archiveHistory = "$logDir/archive_history.log";
$extractHistory = "$logDir/extract_history.log";

function runCommand($cmd, $logFile) {
    $timestamp = "[" . date('Y-m-d H:i:s') . "]";
    $redacted = preg_replace('/-p[\'\"]?[^\'\"\\s]+[\'\"]?/', '-p*****', $cmd);
    file_put_contents($logFile, "$timestamp $redacted\n", FILE_APPEND);
    exec($cmd . ' 2>&1', $output, $ret);
    foreach ($output as $line) {
        $line = preg_replace('/-p[\'\"]?[^\'\"\\s]+[\'\"]?/', '-p*****', $line);
        file_put_contents($logFile, "$timestamp $line\n", FILE_APPEND);
    }
    return [$ret, $output];
}

$mode = $_POST['mode'] ?? '';
$response = ['success' => false, 'message' => ''];

if ($mode === 'archive') {
    $src = escapeshellarg($_POST['source']);
    $dest = rtrim(stripslashes($_POST['destination']), '/');
    $format = $_POST['format'] ?? '7z';
    $password = trim($_POST['password'] ?? '');
    $archive = escapeshellarg("$dest/" . basename($_POST['source']) . ".$format");
    $pass = $password ? "-p" . escapeshellarg($password) : '';
    $cmd = "7zzs a -t$format $archive $src $pass";
    [$ret, $output] = runCommand($cmd, $logFile);
    $response['success'] = $ret === 0;
    $response['message'] = $ret === 0 ? 'Archive created successfully.' : 'Archive failed: ' . end($output);
	if ($ret === 0) {
    $response['success'] = true;
    $response['message'] = 'Archive created successfully.';
    file_put_contents($archiveHistory, "[" . date('Y-m-d H:i:s') . "] Archived: $archive\n", FILE_APPEND);
}
} elseif ($mode === 'extract') {
    $archive = escapeshellarg($_POST['archive_file']);
    $dest = escapeshellarg($_POST['extract_dest']);
    $password = trim($_POST['extract_password'] ?? '');
    $pass = $password ? "-p" . escapeshellarg($password) : '';
    $cmd = "7zzs x $archive -o$dest $pass -y";
    [$ret, $output] = runCommand($cmd, $logFile);

    if ($ret === 0) {
        $chownCmd = "chown -R nobody:users $dest && chmod -R 755 $dest";
        runCommand($chownCmd, $logFile);
        $response['success'] = true;
        $response['message'] = 'Extracted successfully.';
		$response['message'] = 'Extracted successfully.';
$response['success'] = true;
file_put_contents($extractHistory, "[" . date('Y-m-d H:i:s') . "] Extracted: $archive to $dest\n", FILE_APPEND);
    } else {
        $response['message'] = 'Extraction failed: ' . end($output);
    }
}

echo json_encode($response);
