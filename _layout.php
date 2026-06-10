<?php
/* _layout.php – shared head, navbar, theme CSS */
$currentFile = basename($_SERVER['PHP_SELF'], '.php');
$user_nama   = $_SESSION['nama']  ?? '';
$user_role   = $_SESSION['role']  ?? '';

function navItem($file, $icon, $label, $current) {
    $active = ($current === $file) ? 'active' : '';
    echo "<a href=\"{$file}.php\" class=\"nav-link {$active}\">{$icon}<span>{$label}</span></a>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $pageTitle ?? APP_NAME ?></title>
<style>
/* ═══════════════════════════════════════════
   CSS VARIABLES – LIGHT & DARK
═══════════════════════════════════════════ */
:root {
  --bg:          #f0f2f7;
  --surface:     #ffffff;
  --surface2:    #f8f9fc;
  --border:      #e2e8f0;
  --text:        #1e293b;
  --text2:       #64748b;
  --text3:       #94a3b8;
  --primary:     #6366f1;
  --primary-dim: rgba(99,102,241,.1);
  --success:     #10b981;
  --warning:     #f59e0b;
  --danger:      #ef4444;
  --info:        #3b82f6;
  --nav-bg:      #1e293b;
  --nav-text:    rgba(255,255,255,.65);
  --nav-active:  #ffffff;
  --nav-hover:   rgba(255,255,255,.1);
  --shadow:      0 2px 12px rgba(0,0,0,.07);
  --shadow-md:   0 4px 24px rgba(0,0,0,.1);
  --radius:      12px;
  --radius-sm:   8px;
  --transition:  all .25s ease;
}
[data-theme="dark"] {
  --bg:          #0f172a;
  --surface:     #1e293b;
  --surface2:    #162032;
  --border:      rgba(255,255,255,.08);
  --text:        #e2e8f0;
  --text2:       #94a3b8;
  --text3:       #475569;
  --primary:     #818cf8;
  --primary-dim: rgba(129,140,248,.12);
  --success:     #34d399;
  --warning:     #fbbf24;
  --danger:      #f87171;
  --info:        #60a5fa;
  --nav-bg:      #0f172a;
  --nav-text:    rgba(255,255,255,.5);
  --nav-active:  #ffffff;
  --nav-hover:   rgba(255,255,255,.06);
  --shadow:      0 2px 12px rgba(0,0,0,.3);
  --shadow-md:   0 4px 24px rgba(0,0,0,.4);
}

/* ═══════════════════════════════════════════
   RESET & BASE
═══════════════════════════════════════════ */
*{margin:0;padding:0;box-sizing:border-box}
body{
  font-family:'Segoe UI',system-ui,sans-serif;
  background:var(--bg);color:var(--text);
  min-height:100vh;display:flex;
  transition:var(--transition);
  font-size:14px;line-height:1.6;
}
a{text-decoration:none;color:inherit}
input,select,textarea,button{font-family:inherit;font-size:14px}

/* ═══════════════════════════════════════════
   SIDEBAR
═══════════════════════════════════════════ */
.sidebar{
  width:220px;min-height:100vh;background:var(--nav-bg);
  display:flex;flex-direction:column;position:fixed;left:0;top:0;bottom:0;
  transition:var(--transition);z-index:100;
  border-right:1px solid rgba(255,255,255,.05);
}
.sidebar-logo{
  padding:22px 20px 18px;
  border-bottom:1px solid rgba(255,255,255,.06);
}
.sidebar-logo .app-icon{
  width:38px;height:38px;border-radius:10px;
  background:linear-gradient(135deg,var(--primary),#4f46e5);
  display:inline-flex;align-items:center;justify-content:center;
  font-size:18px;margin-bottom:8px;
}
.sidebar-logo .app-name{
  color:#fff;font-weight:700;font-size:.95rem;display:block;
}
.sidebar-logo .app-sub{
  color:rgba(255,255,255,.35);font-size:.72rem;
}
.sidebar-nav{flex:1;padding:14px 10px;overflow-y:auto}
.nav-section{
  color:rgba(255,255,255,.25);font-size:.68rem;font-weight:600;
  letter-spacing:.08em;text-transform:uppercase;
  padding:10px 10px 5px;margin-top:6px;
}
.nav-link{
  display:flex;align-items:center;gap:10px;
  padding:9px 12px;border-radius:var(--radius-sm);
  color:var(--nav-text);transition:var(--transition);
  margin-bottom:2px;font-size:.875rem;
}
.nav-link:hover{background:var(--nav-hover);color:var(--nav-active)}
.nav-link.active{
  background:linear-gradient(135deg,rgba(99,102,241,.25),rgba(79,70,229,.15));
  color:var(--nav-active);font-weight:600;
  border-left:3px solid var(--primary);padding-left:9px;
}
.nav-link span{font-size:.875rem}
.sidebar-footer{
  padding:14px;border-top:1px solid rgba(255,255,255,.06);
}
.user-info{
  display:flex;align-items:center;gap:10px;
  padding:10px;border-radius:var(--radius-sm);
  background:rgba(255,255,255,.05);
}
.user-avatar{
  width:34px;height:34px;border-radius:50%;
  background:linear-gradient(135deg,var(--primary),#4f46e5);
  display:flex;align-items:center;justify-content:center;
  color:#fff;font-weight:700;font-size:.85rem;flex-shrink:0;
}
.user-name{color:rgba(255,255,255,.8);font-size:.8rem;font-weight:600;display:block}
.user-role{color:rgba(255,255,255,.35);font-size:.7rem;text-transform:capitalize}
.btn-logout{
  display:flex;align-items:center;gap:6px;margin-top:8px;
  padding:8px 12px;border-radius:var(--radius-sm);width:100%;
  background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);
  color:#f87171;cursor:pointer;font-size:.8rem;
  transition:var(--transition);justify-content:center;
}
.btn-logout:hover{background:rgba(239,68,68,.2)}

/* ═══════════════════════════════════════════
   MAIN CONTENT
═══════════════════════════════════════════ */
.main{
  margin-left:220px;flex:1;display:flex;flex-direction:column;
  min-height:100vh;transition:var(--transition);
}
.topbar{
  background:var(--surface);border-bottom:1px solid var(--border);
  padding:0 24px;height:58px;display:flex;align-items:center;
  justify-content:space-between;position:sticky;top:0;z-index:50;
  box-shadow:var(--shadow);
}
.topbar-left h2{font-size:1rem;font-weight:600;color:var(--text)}
.topbar-left p{font-size:.75rem;color:var(--text2);margin-top:1px}
.topbar-right{display:flex;align-items:center;gap:10px}
.clock-badge{
  background:var(--primary-dim);color:var(--primary);
  padding:5px 12px;border-radius:20px;font-size:.8rem;font-weight:600;
}
.theme-btn{
  width:36px;height:36px;border-radius:50%;border:1px solid var(--border);
  background:var(--surface);cursor:pointer;display:flex;align-items:center;
  justify-content:center;font-size:16px;transition:var(--transition);color:var(--text2);
}
.theme-btn:hover{background:var(--primary-dim);border-color:var(--primary);color:var(--primary)}
.content{padding:24px;flex:1}

/* ═══════════════════════════════════════════
   CARDS
═══════════════════════════════════════════ */
.card{
  background:var(--surface);border:1px solid var(--border);
  border-radius:var(--radius);padding:20px;box-shadow:var(--shadow);
  transition:var(--transition);
}
.card:hover{box-shadow:var(--shadow-md)}
.card-header{
  display:flex;align-items:center;justify-content:space-between;
  margin-bottom:18px;padding-bottom:14px;border-bottom:1px solid var(--border);
}
.card-title{font-size:.9rem;font-weight:600;color:var(--text);display:flex;align-items:center;gap:8px}

/* Stat Cards */
.stat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px}
.stat-card{
  background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);
  padding:18px 20px;transition:var(--transition);cursor:default;
}
.stat-card:hover{transform:translateY(-2px);box-shadow:var(--shadow-md)}
.stat-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
.stat-icon{
  width:40px;height:40px;border-radius:10px;
  display:flex;align-items:center;justify-content:center;font-size:18px;
}
.stat-badge{font-size:.72rem;padding:3px 8px;border-radius:20px;font-weight:600}
.stat-val{font-size:1.5rem;font-weight:700;color:var(--text);margin-bottom:2px}
.stat-lbl{font-size:.75rem;color:var(--text2)}

/* ═══════════════════════════════════════════
   FORMS
═══════════════════════════════════════════ */
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.form-group{margin-bottom:16px}
.form-group label{display:block;font-size:.8rem;font-weight:600;color:var(--text2);margin-bottom:6px;text-transform:uppercase;letter-spacing:.04em}
.form-control{
  width:100%;padding:10px 14px;border-radius:var(--radius-sm);
  border:1px solid var(--border);background:var(--surface2);
  color:var(--text);transition:var(--transition);outline:none;
}
.form-control:focus{border-color:var(--primary);background:var(--surface);box-shadow:0 0 0 3px var(--primary-dim)}
.form-control.plat{text-transform:uppercase;letter-spacing:2px;font-weight:700;font-size:1rem}

/* ═══════════════════════════════════════════
   BUTTONS
═══════════════════════════════════════════ */
.btn{
  padding:9px 18px;border-radius:var(--radius-sm);border:none;
  cursor:pointer;font-weight:600;font-size:.85rem;
  transition:var(--transition);display:inline-flex;align-items:center;gap:6px;
}
.btn:hover{transform:translateY(-1px);opacity:.92}
.btn:active{transform:translateY(0)}
.btn-primary{background:var(--primary);color:#fff;box-shadow:0 3px 10px rgba(99,102,241,.35)}
.btn-success{background:var(--success);color:#fff;box-shadow:0 3px 10px rgba(16,185,129,.35)}
.btn-danger {background:var(--danger);color:#fff}
.btn-warning{background:var(--warning);color:#fff}
.btn-outline{background:transparent;border:1px solid var(--border);color:var(--text2)}
.btn-outline:hover{border-color:var(--primary);color:var(--primary);background:var(--primary-dim)}
.btn-sm{padding:6px 12px;font-size:.78rem}
.btn-block{width:100%;justify-content:center;padding:12px}

/* ═══════════════════════════════════════════
   TABLE
═══════════════════════════════════════════ */
.table-wrap{overflow-x:auto;border-radius:var(--radius);border:1px solid var(--border)}
table{width:100%;border-collapse:collapse}
thead th{
  background:var(--surface2);color:var(--text2);font-size:.75rem;
  font-weight:600;text-transform:uppercase;letter-spacing:.05em;
  padding:10px 14px;white-space:nowrap;border-bottom:1px solid var(--border);
}
tbody td{padding:10px 14px;border-bottom:1px solid var(--border);color:var(--text);vertical-align:middle}
tbody tr:last-child td{border-bottom:none}
tbody tr:hover{background:var(--primary-dim)}

/* ═══════════════════════════════════════════
   BADGES & ALERTS
═══════════════════════════════════════════ */
.badge{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:20px;font-size:.72rem;font-weight:600}
.badge-parkir  {background:rgba(59,130,246,.12);color:var(--info)}
.badge-selesai {background:rgba(16,185,129,.12);color:var(--success)}
.badge-admin   {background:rgba(99,102,241,.12);color:var(--primary)}
.badge-operator{background:rgba(245,158,11,.12);color:var(--warning)}
.badge-aktif   {background:rgba(16,185,129,.12);color:var(--success)}
.badge-nonaktif{background:rgba(239,68,68,.12); color:var(--danger)}

.alert{padding:12px 16px;border-radius:var(--radius-sm);font-size:.85rem;margin-bottom:16px;display:flex;align-items:flex-start;gap:10px}
.alert-success{background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);color:var(--success)}
.alert-danger {background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:var(--danger)}
.alert-warning{background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.25);color:var(--warning)}
.alert-info   {background:rgba(59,130,246,.1);border:1px solid rgba(59,130,246,.25);color:var(--info)}

/* ═══════════════════════════════════════════
   PROGRESS BAR
═══════════════════════════════════════════ */
.progress{height:10px;background:var(--surface2);border-radius:10px;overflow:hidden;margin:6px 0}
.progress-bar{height:100%;border-radius:10px;transition:.6s ease}
.progress-bar.green {background:linear-gradient(90deg,#10b981,#34d399)}
.progress-bar.yellow{background:linear-gradient(90deg,#f59e0b,#fbbf24)}
.progress-bar.red   {background:linear-gradient(90deg,#ef4444,#f87171)}

/* ═══════════════════════════════════════════
   SEARCH
═══════════════════════════════════════════ */
.search-box{
  display:flex;align-items:center;gap:8px;
  padding:8px 14px;border-radius:var(--radius-sm);
  border:1px solid var(--border);background:var(--surface2);
  color:var(--text);font-size:.85rem;
}
.search-box input{border:none;background:transparent;outline:none;color:var(--text);width:200px}
.search-box input::placeholder{color:var(--text3)}

/* ═══════════════════════════════════════════
   MODAL
═══════════════════════════════════════════ */
.modal-overlay{
  position:fixed;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(4px);
  z-index:1000;display:flex;align-items:center;justify-content:center;
  opacity:0;pointer-events:none;transition:.2s;
}
.modal-overlay.show{opacity:1;pointer-events:all}
.modal{
  background:var(--surface);border:1px solid var(--border);
  border-radius:16px;padding:28px;max-width:440px;width:90%;
  box-shadow:0 20px 60px rgba(0,0,0,.3);
  transform:scale(.95);transition:.2s;
}
.modal-overlay.show .modal{transform:scale(1)}
.modal h3{font-size:1rem;font-weight:700;margin-bottom:6px;color:var(--text)}
.modal p{color:var(--text2);font-size:.875rem;margin-bottom:20px}
.modal-actions{display:flex;gap:10px;justify-content:flex-end}

/* ═══════════════════════════════════════════
   NOTIFICATION TOAST
═══════════════════════════════════════════ */
.toast-container{position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:8px}
.toast{
  background:var(--surface);border:1px solid var(--border);border-radius:10px;
  padding:12px 16px;min-width:260px;max-width:340px;
  box-shadow:var(--shadow-md);display:flex;align-items:center;gap:12px;
  transform:translateX(120%);transition:.3s cubic-bezier(.4,0,.2,1);font-size:.85rem;
}
.toast.show{transform:translateX(0)}
.toast-icon{font-size:18px;flex-shrink:0}
.toast-msg{flex:1;color:var(--text)}
.toast.success{border-left:3px solid var(--success)}
.toast.error  {border-left:3px solid var(--danger)}
.toast.info   {border-left:3px solid var(--info)}

/* ═══════════════════════════════════════════
   RESPONSIVE
═══════════════════════════════════════════ */
@media(max-width:768px){
  .sidebar{width:0;overflow:hidden}
  .main{margin-left:0}
  .stat-grid{grid-template-columns:1fr 1fr}
  .form-row{grid-template-columns:1fr}
}

/* ═══════════════════════════════════════════
   SCROLLBAR
═══════════════════════════════════════════ */
::-webkit-scrollbar{width:6px;height:6px}
::-webkit-scrollbar-track{background:transparent}
::-webkit-scrollbar-thumb{background:var(--border);border-radius:3px}
::-webkit-scrollbar-thumb:hover{background:var(--text3)}
</style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar-logo">
    <div class="app-icon">🅿</div>
    <span class="app-name"><?=APP_NAME?></span>
    <span class="app-sub">W&amp;J Citayam</span>
  </div>
  <nav class="sidebar-nav">
    <?php if($user_role==='admin'):?>
    <div class="nav-section">Dashboard</div>
    <?php navItem('dashboard','📊','Dashboard',$currentFile);?>
    <?php endif;?>
    <div class="nav-section">Operasional</div>
    <?php navItem('masuk','🔵','Kendaraan Masuk',$currentFile);?>
    <?php navItem('keluar','🟢','Kendaraan Keluar',$currentFile);?>
    <?php navItem('kapasitas','🅿️','Monitor Kapasitas',$currentFile);?>
    <?php if($user_role==='admin'):?>
    <div class="nav-section">Laporan & Data</div>
    <?php navItem('laporan','📋','Laporan Transaksi',$currentFile);?>
    <div class="nav-section">Pengaturan</div>
    <?php navItem('tarif','💰','Kelola Tarif',$currentFile);?>
    <?php navItem('users','👥','Manajemen User',$currentFile);?>
    <?php endif;?>
  </nav>
  <div class="sidebar-footer">
    <div class="user-info">
      <div class="user-avatar"><?=strtoupper(substr($user_nama,0,1))?></div>
      <div>
        <span class="user-name"><?=htmlspecialchars($user_nama)?></span>
        <span class="user-role"><?=$user_role?></span>
      </div>
    </div>
    <a href="logout.php" class="btn-logout">🚪 Keluar</a>
  </div>
</aside>

<!-- MAIN -->
<div class="main">
  <div class="topbar">
    <div class="topbar-left">
      <h2><?=$pageTitle ?? APP_NAME?></h2>
      <p><?=date('l, d F Y')?></p>
    </div>
    <div class="topbar-right">
      <div class="clock-badge" id="clock">00:00:00</div>
      <button class="theme-btn" id="themeBtn" title="Ganti Tema" onclick="toggleTheme()">🌙</button>
    </div>
  </div>
  <div class="content">

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<script>
// ─── THEME ───────────────────────────────
(function(){
  var t = localStorage.getItem('wjpark_theme') || 'light';
  document.documentElement.setAttribute('data-theme', t);
  document.addEventListener('DOMContentLoaded', function(){
    var btn = document.getElementById('themeBtn');
    if(btn) btn.textContent = t==='dark'?'☀️':'🌙';
  });
})();

function toggleTheme(){
  var cur = document.documentElement.getAttribute('data-theme') || 'light';
  var next = cur==='dark'?'light':'dark';
  document.documentElement.setAttribute('data-theme', next);
  localStorage.setItem('wjpark_theme', next);
  document.getElementById('themeBtn').textContent = next==='dark'?'☀️':'🌙';
}

// ─── CLOCK ───────────────────────────────
function updateClock(){
  var now = new Date();
  var h = String(now.getHours()).padStart(2,'0');
  var m = String(now.getMinutes()).padStart(2,'0');
  var s = String(now.getSeconds()).padStart(2,'0');
  var el = document.getElementById('clock');
  if(el) el.textContent = h+':'+m+':'+s;
}
setInterval(updateClock, 1000);
updateClock();

// ─── TOAST ───────────────────────────────
function showToast(msg, type){
  type = type||'info';
  var icons = {success:'✅',error:'❌',info:'ℹ️',warning:'⚠️'};
  var c = document.getElementById('toastContainer');
  var t = document.createElement('div');
  t.className = 'toast '+type;
  t.innerHTML = '<span class="toast-icon">'+icons[type]+'</span><span class="toast-msg">'+msg+'</span>';
  c.appendChild(t);
  setTimeout(function(){ t.classList.add('show'); }, 50);
  setTimeout(function(){ t.classList.remove('show'); setTimeout(function(){ t.remove(); },300); }, 3500);
}

// ─── PLAT UPPERCASE ──────────────────────
document.addEventListener('DOMContentLoaded', function(){
  document.querySelectorAll('.plat').forEach(function(el){
    el.addEventListener('input', function(){
      var p = this.selectionStart;
      this.value = this.value.toUpperCase().replace(/[^A-Z0-9\s]/g,'');
      this.setSelectionRange(p,p);
    });
  });

  // Auto dismiss alerts
  setTimeout(function(){
    document.querySelectorAll('.alert').forEach(function(el){
      el.style.opacity='0'; el.style.transition='opacity .4s';
      setTimeout(function(){ el.style.display='none'; },400);
    });
  }, 4000);

  // Table search
  var si = document.getElementById('tableSearch');
  if(si){
    si.addEventListener('input', function(){
      var q = this.value.toLowerCase();
      document.querySelectorAll('#mainTable tbody tr').forEach(function(r){
        r.style.display = r.textContent.toLowerCase().includes(q)?'':'none';
      });
    });
  }
});
</script>
