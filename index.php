<?php
require 'koneksi.php';

if (!empty($_SESSION['uid'])) {
    header('Location: '.($_SESSION['role']==='admin'?'dashboard.php':'masuk.php')); exit;
}

$err = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $u = trim($_POST['username']??'');
    $p = $_POST['password']??'';
    $st = db()->prepare("SELECT * FROM users WHERE username=? AND aktif=1");
    $st->execute([$u]);
    $row = $st->fetch();
    if ($row && password_verify($p, $row['password'])) {
        session_regenerate_id(true);
        $_SESSION['uid']   = $row['id'];
        $_SESSION['nama']  = $row['nama'];
        $_SESSION['role']  = $row['role'];
        $_SESSION['uname'] = $row['username'];
        header('Location: '.($row['role']==='admin'?'dashboard.php':'masuk.php')); exit;
    } else {
        $err = 'Username atau password salah.';
    }
}
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login – WJ-PARK Analytics</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{
  font-family:'Segoe UI',system-ui,sans-serif;
  min-height:100vh;display:flex;align-items:center;justify-content:center;
  background:linear-gradient(135deg,#1a1f2e 0%,#16213e 50%,#0f3460 100%);
  position:relative;overflow:hidden;
}
body::before{
  content:'';position:absolute;inset:0;
  background:radial-gradient(ellipse at 20% 50%,rgba(99,102,241,.15) 0%,transparent 60%),
             radial-gradient(ellipse at 80% 20%,rgba(16,185,129,.08) 0%,transparent 50%);
}
.wrap{position:relative;z-index:1;width:100%;max-width:420px;padding:20px}
.card{
  background:rgba(255,255,255,.07);backdrop-filter:blur(20px);
  border:1px solid rgba(255,255,255,.12);border-radius:20px;
  padding:40px 36px;box-shadow:0 25px 60px rgba(0,0,0,.4);
}
.logo{text-align:center;margin-bottom:28px}
.logo-icon{
  width:64px;height:64px;border-radius:16px;
  background:linear-gradient(135deg,#6366f1,#4f46e5);
  display:inline-flex;align-items:center;justify-content:center;
  font-size:28px;margin-bottom:14px;
  box-shadow:0 8px 24px rgba(99,102,241,.4);
}
.logo h1{color:#fff;font-size:1.5rem;font-weight:700;margin-bottom:4px}
.logo p{color:rgba(255,255,255,.5);font-size:.85rem}
.form-group{margin-bottom:18px}
.form-group label{display:block;color:rgba(255,255,255,.7);font-size:.85rem;margin-bottom:6px;font-weight:500}
.form-group input{
  width:100%;padding:12px 16px;border-radius:10px;
  background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);
  color:#fff;font-size:.95rem;transition:.2s;outline:none;
}
.form-group input:focus{border-color:#6366f1;background:rgba(99,102,241,.12);box-shadow:0 0 0 3px rgba(99,102,241,.2)}
.form-group input::placeholder{color:rgba(255,255,255,.3)}
.btn-login{
  width:100%;padding:13px;border:none;border-radius:10px;
  background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;
  font-size:1rem;font-weight:600;cursor:pointer;transition:.2s;margin-top:8px;
  box-shadow:0 4px 15px rgba(99,102,241,.4);
}
.btn-login:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(99,102,241,.5)}
.btn-login:active{transform:translateY(0)}
.err{background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#fca5a5;
  padding:10px 14px;border-radius:8px;font-size:.85rem;margin-bottom:16px;display:flex;align-items:center;gap:8px}
.hint{
  margin-top:20px;padding:14px;border-radius:10px;
  background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);
  color:rgba(255,255,255,.4);font-size:.78rem;line-height:1.6
}
.hint strong{color:rgba(255,255,255,.6)}
</style>
</head>
<body>
<div class="wrap">
  <div class="card">
    <div class="logo">
      <div class="logo-icon">🅿</div>
      <h1>WJ-PARK Analytics</h1>
      <p>Sistem Pengelolaan Penitipan Motor</p>
    </div>
    <?php if($err):?>
    <div class="err">⚠️ <?=htmlspecialchars($err)?></div>
    <?php endif;?>
    <form method="POST">
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" placeholder="Masukkan username" autofocus
               value="<?=htmlspecialchars($_POST['username']??'')?>">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Masukkan password">
      </div>
      <button type="submit" class="btn-login">Masuk →</button>
    </form>
    <div class="hint">
      <strong>Akun default:</strong><br>
      Admin &nbsp;: <code>admin</code> / <code>admin123</code><br>
      Operator: <code>operator1</code> / <code>admin123</code>
    </div>
  </div>
</div>
</body>
</html>
