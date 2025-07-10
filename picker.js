let currentField = '';

function openPicker(field) {
  currentField = field;
  loadPicker('/mnt/user');
  document.getElementById('pickerModal').classList.remove('hidden');
}

function closePicker() {
  document.getElementById('pickerModal').classList.add('hidden');
  document.getElementById('pickerContent').innerHTML = '';
}

function loadPicker(path) {
  fetch('browse.php?path=' + encodeURIComponent(path))
    .then(res => res.text())
    .then(html => {
      document.getElementById('pickerContent').innerHTML = html;
    });
}

function selectPath(path) {
  document.getElementById(currentField + 'Input').value = path;
  closePicker();
}

document.getElementById('mainForm').addEventListener('submit', async function (e) {
  e.preventDefault();
  const formData = new FormData(this);
  const statusEl = document.getElementById('statusMessage');
  statusEl.textContent = 'Processing...';
  statusEl.style.color = 'black';
  showSpinner(true);

  const res = await fetch('run.php', {
    method: 'POST',
    body: formData
  });

  const data = await res.json();
  statusEl.textContent = data.message;
  statusEl.style.color = data.success ? 'green' : 'red';
  showSpinner(false);
  loadLog();
});

function clearLog() {
  fetch('log.php', {
    method: 'POST'
  }).then(() => loadLog());
}

document.getElementById('mainForm').addEventListener('submit', async function (e) {
  e.preventDefault();
  const formData = new FormData(this);
  const statusEl = document.getElementById('statusMessage');
  statusEl.textContent = 'Processing...';

  const res = await fetch('run.php', {
    method: 'POST',
    body: formData
  });

  const data = await res.json();
  statusEl.textContent = data.message;
  statusEl.style.color = data.success ? 'green' : 'red';
  loadLog(); // Refresh log output
});

function showSpinner(show) {
  document.getElementById('spinner').classList.toggle('hidden', !show);
}
