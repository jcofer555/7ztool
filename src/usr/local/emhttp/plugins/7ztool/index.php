<?php
$logDir = '/boot/config/plugins/7ztool/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
$logFile = $logDir . '/operation.log';

function runCommand($cmd, $logFile) {
    $timestamp = "[" . date('Y-m-d H:i:s') . "]";
    $redactedCmd = preg_replace('/-p[\'\"]?[^\'\"\\s]+[\'\"]?/', '-p*****', $cmd);
file_put_contents($logFile, "$timestamp $redactedCmd\n", FILE_APPEND);
    $output = [];
    exec($cmd . ' 2>&1', $output, $ret);
    foreach ($output as $line) {
        $sanitized = preg_replace('/-p[\'\"]?[^\'\"\\s]+[\'\"]?/', '-p*****', $line);
file_put_contents($logFile, "$timestamp $sanitized\n", FILE_APPEND);
    }
    return $ret;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? '';
    if ($mode === 'archive') {
        $source = escapeshellarg($_POST['source']);
        $dest = escapeshellarg($_POST['destination']);
        $format = $_POST['format'] ?? '7z';
        $password = trim($_POST['password'] ?? '');

        $archiveFile = escapeshellarg(rtrim(stripslashes($_POST['destination']), '/') . '/' . basename($_POST['source']) . '.' . $format);
        $passPart = $password !== '' ? "-p" . escapeshellarg($password) : '';

        $cmd = "7zzs a -t$format $archiveFile $source $passPart";
        runCommand($cmd, $logFile);
    } elseif ($mode === 'extract') {
		$maxSizeBytes = 500 * 1024 * 1024;
		if (filesize($_POST['archive_file'] ?? $_POST['source']) > $maxSizeBytes) {
			die("Selected file exceeds 500MB limit.");
		}
        $archive = escapeshellarg($_POST['archive_file']);
        $dest = escapeshellarg($_POST['extract_dest']);
        $password = trim($_POST['extract_password'] ?? '');
        $passPart = $password !== '' ? "-p" . escapeshellarg($password) : '';

        $cmd = "7zzs x $archive -o$dest $passPart -y";
        runCommand($cmd, $logFile);

        // Set ownership/permissions
        $chownCmd = "chown -R nobody:users $dest && chmod -R 755 $dest";
        runCommand($chownCmd, $logFile);
    }
}
?>

<?php
$logDir = '/boot/config/plugins/7ztool/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
?>
  <link rel="stylesheet" href="style.css">
  <script src="picker.js"></script>
<h1>7zTool: Archive & Extract</h1>

<form id="mainForm">
  <h2>Action</h2>
  <select name="mode" onchange="toggleMode(this.value)">
    <option value="archive">Archive</option>
    <option value="extract">Extract</option>
  </select>

  <div id="archiveFields">
    <label>Source:</label>
    <input type="text" name="source" id="sourceInput" readonly>
    <button type="button" onclick="openPicker('source')">Pick Source</button>

    <label>Destination:</label>
    <input type="text" name="destination" id="destInput" readonly>
    <button type="button" onclick="openPicker('destination')">Pick Destination</button>

    <label>Password (optional):</label>
    <input type="password" name="password">

    <label>Format:</label>
    <select name="format">
      <option value="7z">.7z</option>
      <option value="zip">.zip</option>
      <option value="tar">.tar</option>
      <option value="tar.gz">.tar.gz</option>
    </select>
  </div>

  <div id="extractFields" style="display:none;">
    <label>Archive:</label>
    <input type="text" name="archive_file" id="archiveInput" readonly>
    <button type="button" onclick="openPicker('archive')">Pick Archive</button>

    <label>Extract to:</label>
    <input type="text" name="extract_dest" id="extractDestInput" readonly>
    <button type="button" onclick="openPicker('extract_dest')">Pick Destination</button>

    <label>Password (optional):</label>
    <input type="password" name="extract_password">
  </div>

  <button type="submit">Run</button>
</form>

<div id="statusMessage" style="margin-top:20px; font-weight:bold;"></div>
<div id="spinner" class="hidden" style="margin-top:10px;">
  <img src="/plugins/7ztool/spinner.gif" alt="Loading..." width="24">
</div>

<h2 style="margin-top: 40px;">
  📝 Operation Log
  <button onclick="clearLog()" style="float: right; font-size: 0.8em;">Clear Log</button>
</h2>

<pre id="logViewer" style="max-height: 300px; overflow-y: scroll; background: #f0f0f0; padding: 10px; border-radius: 8px;">
Loading logs...
</pre>

<div id="pickerModal" class="modal hidden">
  <div class="modal-content">
    <span class="close" onclick="closePicker()">&times;</span>
    <div id="pickerContent">Loading...</div>
  </div>
</div>

<script>
function toggleMode(mode) {
  document.getElementById('archiveFields').style.display = (mode === 'archive') ? 'block' : 'none';
  document.getElementById('extractFields').style.display = (mode === 'extract') ? 'block' : 'none';
}
</script>
<script>
function loadLog() {
  fetch('log.php')
    .then(res => res.text())
    .then(data => {
      const logEl = document.getElementById('logViewer');
      const isAtBottom = logEl.scrollHeight - logEl.scrollTop <= logEl.clientHeight + 30;
      logEl.textContent = data;
      if (isAtBottom) logEl.scrollTop = logEl.scrollHeight;
    });
}

setInterval(loadLog, 3000);
window.addEventListener('load', loadLog);
</script>