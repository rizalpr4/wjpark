<?php
require 'koneksi.php';
cekAdmin();
$pageTitle = 'Kelola Tarif';
$db = db();
$msg=$err='';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $act = $_POST['act']??'';
    if($act==='edit'){
        $id = (int)$_POST['id'];
        $jenis = trim($_POST['jenis']??'');
        $ta = (int)$_POST['tarif_awal'];
        $ja = (int)$_POST['jam_awal'];
        $tp = (int)$_POST['tarif_perjam'];
        $tm = (int)$_POST['tarif_maks'];
        if(empty($jenis)||$ta<=0||$ja<=0||$tp<=0||$tm<=0){ $err='Semua field wajib diisi dengan benar.'; }
        else {
            $db->prepare("UPDATE tarif SET jenis=?,tarif_awal=?,jam_awal=?,tarif_perjam=?,tarif_maks=? WHERE id=?")
               ->execute([$jenis,$ta,$ja,$tp,$tm,$id]);
            $msg='✅ Tarif berhasil diperbarui.';
        }
    } elseif($act==='tambah'){
        $jenis = trim($_POST['jenis_baru']??'');
        $ta = (int)$_POST['tarif_awal_baru'];
        $ja = (int)$_POST['jam_awal_baru'];
        $tp = (int)$_POST['tarif_perjam_baru'];
        $tm = (int)$_POST['tarif_maks_baru'];
        if(empty($jenis)){ $err='Nama jenis kendaraan wajib diisi.'; }
        else {
            $db->prepare("INSERT INTO tarif(jenis,tarif_awal,jam_awal,tarif_perjam,tarif_maks) VALUES(?,?,?,?,?)")
               ->execute([$jenis,$ta,$ja,$tp,$tm]);
            $msg='✅ Jenis kendaraan baru berhasil ditambahkan.';
        }
    } elseif($act==='hapus'){
        $id=(int)$_POST['id'];
        $db->prepare("DELETE FROM tarif WHERE id=?")->execute([$id]);
        $msg='✅ Tarif berhasil dihapus.';
    }
    // Update kapasitas
    if($act==='kapasitas'){
        $tot = (int)$_POST['kapasitas_total'];
        if($tot>0){
            $db->prepare("UPDATE kapasitas SET total=? WHERE id=1")->execute([$tot]);
            $msg='✅ Kapasitas berhasil diperbarui.';
        }
    }
}

$tarifs = $db->query("SELECT * FROM tarif ORDER BY id")->fetchAll();
$kap    = kapasitas();
include '_layout.php';
?>

<?php if($msg):?><div class="alert alert-success"><?=$msg?></div><?php endif;?>
<?php if($err):?><div class="alert alert-danger"><?=$err?></div><?php endif;?>

<div style="display:grid;grid-template-columns:1fr 320px;gap:20px">

<!-- TABEL TARIF -->
<div class="card">
  <div class="card-header">
    <span class="card-title">💰 Daftar Tarif Parkir</span>
  </div>
  <div class="table-wrap" style="margin-bottom:20px">
    <table>
      <thead><tr><th>Jenis</th><th>Tarif Awal</th><th>Jam Awal</th><th>Per Jam</th><th>Maks/Hari</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach($tarifs as $t):?>
        <tr>
          <td><b><?=htmlspecialchars($t['jenis'])?></b></td>
          <td><?=rupiah($t['tarif_awal'])?></td>
          <td><?=$t['jam_awal']?> jam</td>
          <td><?=rupiah($t['tarif_perjam'])?>/jam</td>
          <td><?=rupiah($t['tarif_maks'])?></td>
          <td>
            <button class="btn btn-outline btn-sm" onclick="editTarif(<?=htmlspecialchars(json_encode($t))?>)">✏️ Edit</button>
            <form method="POST" style="display:inline" onsubmit="return confirm('Hapus tarif ini?')">
              <input type="hidden" name="act" value="hapus">
              <input type="hidden" name="id" value="<?=$t['id']?>">
              <button type="submit" class="btn btn-danger btn-sm">🗑</button>
            </form>
          </td>
        </tr>
        <?php endforeach;?>
      </tbody>
    </table>
  </div>

  <!-- Tambah tarif baru -->
  <div style="border-top:1px solid var(--border);padding-top:16px">
    <div style="font-weight:600;font-size:.85rem;margin-bottom:14px;color:var(--text)">➕ Tambah Jenis Kendaraan</div>
    <form method="POST">
      <input type="hidden" name="act" value="tambah">
      <div class="form-row">
        <div class="form-group"><label>Jenis Kendaraan</label>
          <input type="text" name="jenis_baru" class="form-control" placeholder="Misal: Motor Besar" required></div>
        <div class="form-group"><label>Tarif Awal (Rp)</label>
          <input type="number" name="tarif_awal_baru" class="form-control" value="3000" min="0" required></div>
        <div class="form-group"><label>Jam Awal (jam)</label>
          <input type="number" name="jam_awal_baru" class="form-control" value="3" min="1" required></div>
        <div class="form-group"><label>Tarif Per Jam (Rp)</label>
          <input type="number" name="tarif_perjam_baru" class="form-control" value="1000" min="0" required></div>
        <div class="form-group"><label>Tarif Maks/Hari (Rp)</label>
          <input type="number" name="tarif_maks_baru" class="form-control" value="15000" min="0" required></div>
      </div>
      <button type="submit" class="btn btn-primary">➕ Tambah</button>
    </form>
  </div>
</div>

<!-- KAPASITAS -->
<div>
  <div class="card">
    <div class="card-header"><span class="card-title">🅿 Atur Kapasitas</span></div>
    <div style="text-align:center;margin-bottom:16px">
      <div style="font-size:2.5rem;font-weight:800;color:var(--primary)"><?=$kap['total']?></div>
      <div style="color:var(--text2);font-size:.82rem">Total slot saat ini</div>
      <div style="font-size:.82rem;color:var(--text3);margin-top:4px">Terisi: <?=$kap['terisi']?> slot</div>
    </div>
    <form method="POST">
      <input type="hidden" name="act" value="kapasitas">
      <div class="form-group">
        <label>Jumlah Slot Baru</label>
        <input type="number" name="kapasitas_total" class="form-control" value="<?=$kap['total']?>" min="1" max="500" required>
      </div>
      <button type="submit" class="btn btn-success btn-block">💾 Simpan Kapasitas</button>
    </form>
  </div>

  <!-- Simulasi biaya -->
  <div class="card" style="margin-top:16px">
    <div class="card-header"><span class="card-title">🧮 Simulasi Biaya</span></div>
    <div class="form-group">
      <label>Durasi (jam)</label>
      <input type="number" id="simJam" class="form-control" value="3" min="1" max="24" oninput="simulasi()">
    </div>
    <div class="form-group">
      <label>Jenis Kendaraan</label>
      <select id="simTarif" class="form-control" onchange="simulasi()">
        <?php foreach($tarifs as $t):?>
        <option value="<?=$t['tarif_awal']?>,<?=$t['jam_awal']?>,<?=$t['tarif_perjam']?>,<?=$t['tarif_maks']?>">
          <?=htmlspecialchars($t['jenis'])?>
        </option>
        <?php endforeach;?>
      </select>
    </div>
    <div style="background:var(--primary-dim);border-radius:var(--radius-sm);padding:14px;text-align:center">
      <div style="font-size:.75rem;color:var(--text2);margin-bottom:4px">Estimasi Biaya</div>
      <div style="font-size:1.4rem;font-weight:800;color:var(--primary)" id="simHasil">Rp 3.000</div>
    </div>
  </div>
</div>
</div>

<!-- Modal edit tarif -->
<div class="modal-overlay" id="modalEdit">
  <div class="modal">
    <h3>✏️ Edit Tarif</h3>
    <p>Ubah pengaturan tarif parkir</p>
    <form method="POST" id="fEdit">
      <input type="hidden" name="act" value="edit">
      <input type="hidden" name="id" id="eId">
      <div class="form-group"><label>Jenis Kendaraan</label>
        <input type="text" name="jenis" id="eJenis" class="form-control" required></div>
      <div class="form-row">
        <div class="form-group"><label>Tarif Awal (Rp)</label>
          <input type="number" name="tarif_awal" id="eTa" class="form-control" required></div>
        <div class="form-group"><label>Jam Awal</label>
          <input type="number" name="jam_awal" id="eJa" class="form-control" required></div>
        <div class="form-group"><label>Per Jam (Rp)</label>
          <input type="number" name="tarif_perjam" id="eTp" class="form-control" required></div>
        <div class="form-group"><label>Maks/Hari (Rp)</label>
          <input type="number" name="tarif_maks" id="eTm" class="form-control" required></div>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn-outline" onclick="tutupModal()">Batal</button>
        <button type="submit" class="btn btn-primary">💾 Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
function editTarif(t){
  document.getElementById('eId').value=t.id;
  document.getElementById('eJenis').value=t.jenis;
  document.getElementById('eTa').value=t.tarif_awal;
  document.getElementById('eJa').value=t.jam_awal;
  document.getElementById('eTp').value=t.tarif_perjam;
  document.getElementById('eTm').value=t.tarif_maks;
  document.getElementById('modalEdit').classList.add('show');
}
function tutupModal(){ document.getElementById('modalEdit').classList.remove('show'); }
document.getElementById('modalEdit').addEventListener('click',function(e){ if(e.target===this) tutupModal(); });

function simulasi(){
  var jam = parseInt(document.getElementById('simJam').value)||1;
  var vals = document.getElementById('simTarif').value.split(',');
  var ta=parseInt(vals[0]),ja=parseInt(vals[1]),tp=parseInt(vals[2]),tm=parseInt(vals[3]);
  var b = jam<=ja ? ta : Math.min(ta+(jam-ja)*tp, tm);
  document.getElementById('simHasil').textContent='Rp '+b.toLocaleString('id-ID');
}
simulasi();

<?php if($msg):?>showToast('<?=addslashes(strip_tags($msg))?>','success');<?php endif;?>
<?php if($err):?>showToast('<?=addslashes(strip_tags($err))?>','error');<?php endif;?>
</script>
<?php include '_layout_end.php';?>
