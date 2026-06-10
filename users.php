<?php
require 'koneksi.php';
cekAdmin();
$pageTitle = 'Manajemen User';
$db = db();
$msg=$err='';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $act = $_POST['act']??'';

    if($act==='tambah'){
        $uname = trim($_POST['username']??'');
        $nama  = trim($_POST['nama']??'');
        $pass  = $_POST['password']??'';
        $role  = $_POST['role']??'operator';
        if(empty($uname)||empty($nama)||empty($pass)){
            $err='Semua field wajib diisi.';
        } else {
            $cek = $db->prepare("SELECT id FROM users WHERE username=?");
            $cek->execute([$uname]);
            if($cek->fetch()){ $err='Username sudah digunakan.'; }
            else {
                $hash = password_hash($pass, PASSWORD_BCRYPT);
                $db->prepare("INSERT INTO users(username,password,nama,role) VALUES(?,?,?,?)")
                   ->execute([$uname,$hash,$nama,$role]);
                $msg="✅ User <b>$uname</b> berhasil ditambahkan.";
            }
        }
    }
    elseif($act==='edit'){
        $id   = (int)$_POST['id'];
        $nama = trim($_POST['nama']??'');
        $role = $_POST['role']??'operator';
        $pass = $_POST['password']??'';
        if(empty($nama)){ $err='Nama wajib diisi.'; }
        else {
            if(!empty($pass)){
                $hash = password_hash($pass, PASSWORD_BCRYPT);
                $db->prepare("UPDATE users SET nama=?,role=?,password=? WHERE id=?")
                   ->execute([$nama,$role,$hash,$id]);
            } else {
                $db->prepare("UPDATE users SET nama=?,role=? WHERE id=?")
                   ->execute([$nama,$role,$id]);
            }
            $msg='✅ Data user berhasil diperbarui.';
        }
    }
    elseif($act==='toggle'){
        $id  = (int)$_POST['id'];
        $cur = (int)$_POST['aktif'];
        // Jangan nonaktifkan diri sendiri
        if($id === (int)$_SESSION['uid']){ $err='Tidak dapat menonaktifkan akun sendiri.'; }
        else {
            $db->prepare("UPDATE users SET aktif=? WHERE id=?")
               ->execute([$cur?0:1,$id]);
            $msg='✅ Status user berhasil diubah.';
        }
    }
    elseif($act==='hapus'){
        $id = (int)$_POST['id'];
        if($id === (int)$_SESSION['uid']){ $err='Tidak dapat menghapus akun sendiri.'; }
        else {
            $db->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
            $msg='✅ User berhasil dihapus.';
        }
    }
}

$users = $db->query("SELECT u.*,
    (SELECT COUNT(*) FROM transaksi WHERE id_operator=u.id) AS total_trx
    FROM users u ORDER BY u.role, u.nama")->fetchAll();

include '_layout.php';
?>

<?php if($msg):?><div class="alert alert-success"><?=$msg?></div><?php endif;?>
<?php if($err):?><div class="alert alert-danger"><?=$err?></div><?php endif;?>

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px">

<!-- TABEL USER -->
<div class="card">
  <div class="card-header">
    <span class="card-title">👥 Daftar User (<?=count($users)?>)</span>
    <div class="search-box"><span>🔍</span><input type="text" id="tableSearch" placeholder="Cari user..."></div>
  </div>
  <div class="table-wrap">
    <table id="mainTable">
      <thead>
        <tr><th>Nama</th><th>Username</th><th>Role</th><th>Total Transaksi</th><th>Status</th><th>Aksi</th></tr>
      </thead>
      <tbody>
        <?php foreach($users as $u):?>
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--primary),#4f46e5);
                display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.8rem;flex-shrink:0">
                <?=strtoupper(substr($u['nama'],0,1))?>
              </div>
              <div>
                <div style="font-weight:600;font-size:.88rem"><?=htmlspecialchars($u['nama'])?></div>
                <div style="font-size:.72rem;color:var(--text3)">Bergabung <?=date('d/m/Y',strtotime($u['created_at']))?></div>
              </div>
            </div>
          </td>
          <td><code style="background:var(--surface2);padding:2px 6px;border-radius:4px;font-size:.82rem"><?=htmlspecialchars($u['username'])?></code></td>
          <td><span class="badge badge-<?=$u['role']?>"><?=$u['role']==='admin'?'👑 Admin':'🔧 Operator'?></span></td>
          <td style="font-weight:600"><?=(int)$u['total_trx']?> transaksi</td>
          <td>
            <span class="badge badge-<?=$u['aktif']?'aktif':'nonaktif'?>">
              <?=$u['aktif']?'✅ Aktif':'⛔ Nonaktif'?>
            </span>
          </td>
          <td>
            <div style="display:flex;gap:6px;flex-wrap:wrap">
              <button class="btn btn-outline btn-sm"
                onclick="editUser(<?=htmlspecialchars(json_encode(['id'=>$u['id'],'nama'=>$u['nama'],'role'=>$u['role'],'username'=>$u['username']]))?>)">
                ✏️
              </button>
              <form method="POST" style="display:inline">
                <input type="hidden" name="act" value="toggle">
                <input type="hidden" name="id" value="<?=$u['id']?>">
                <input type="hidden" name="aktif" value="<?=$u['aktif']?>">
                <button type="submit" class="btn btn-sm <?=$u['aktif']?'btn-warning':'btn-success'?>"
                  title="<?=$u['aktif']?'Nonaktifkan':'Aktifkan'?>"
                  <?=$u['id']==$_SESSION['uid']?'disabled':''?>>
                  <?=$u['aktif']?'⛔':'✅'?>
                </button>
              </form>
              <?php if($u['id']!=(int)$_SESSION['uid']):?>
              <form method="POST" style="display:inline" onsubmit="return confirm('Hapus user <?=htmlspecialchars($u['nama'])?>?')">
                <input type="hidden" name="act" value="hapus">
                <input type="hidden" name="id" value="<?=$u['id']?>">
                <button type="submit" class="btn btn-danger btn-sm">🗑</button>
              </form>
              <?php endif;?>
            </div>
          </td>
        </tr>
        <?php endforeach;?>
      </tbody>
    </table>
  </div>
</div>

<!-- FORM TAMBAH -->
<div class="card">
  <div class="card-header"><span class="card-title">➕ Tambah User Baru</span></div>
  <form method="POST">
    <input type="hidden" name="act" value="tambah">
    <div class="form-group">
      <label>Nama Lengkap</label>
      <input type="text" name="nama" class="form-control" placeholder="Nama lengkap" required>
    </div>
    <div class="form-group">
      <label>Username</label>
      <input type="text" name="username" class="form-control" placeholder="Username login"
             required pattern="[a-zA-Z0-9_]+" title="Hanya huruf, angka, underscore">
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" class="form-control" placeholder="Min. 6 karakter"
             required minlength="6">
    </div>
    <div class="form-group">
      <label>Role</label>
      <select name="role" class="form-control">
        <option value="operator">🔧 Operator</option>
        <option value="admin">👑 Admin</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary btn-block">➕ Tambah User</button>
  </form>
</div>

</div>

<!-- Modal Edit User -->
<div class="modal-overlay" id="modalEdit">
  <div class="modal">
    <h3>✏️ Edit User</h3>
    <p>Kosongkan password jika tidak ingin mengubah password.</p>
    <form method="POST">
      <input type="hidden" name="act" value="edit">
      <input type="hidden" name="id" id="eId">
      <div class="form-group">
        <label>Nama Lengkap</label>
        <input type="text" name="nama" id="eNama" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Username</label>
        <input type="text" id="eUname" class="form-control" disabled style="opacity:.6">
      </div>
      <div class="form-group">
        <label>Password Baru (opsional)</label>
        <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diubah" minlength="6">
      </div>
      <div class="form-group">
        <label>Role</label>
        <select name="role" id="eRole" class="form-control">
          <option value="operator">🔧 Operator</option>
          <option value="admin">👑 Admin</option>
        </select>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn-outline" onclick="document.getElementById('modalEdit').classList.remove('show')">Batal</button>
        <button type="submit" class="btn btn-primary">💾 Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
function editUser(u){
  document.getElementById('eId').value = u.id;
  document.getElementById('eNama').value = u.nama;
  document.getElementById('eUname').value = u.username;
  document.getElementById('eRole').value = u.role;
  document.getElementById('modalEdit').classList.add('show');
}
document.getElementById('modalEdit').addEventListener('click',function(e){
  if(e.target===this) this.classList.remove('show');
});
<?php if($msg):?>showToast('<?=addslashes(strip_tags($msg))?>','success');<?php endif;?>
<?php if($err):?>showToast('<?=addslashes(strip_tags($err))?>','error');<?php endif;?>
</script>
<?php include '_layout_end.php';?>
