<?php
require 'koneksi.php';
cekLogin();
$pageTitle = 'Kendaraan Masuk';
$db = db();
$msg = $err = '';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $plat = strtoupper(trim($_POST['no_plat']??''));
    $idt  = (int)($_POST['id_tarif']??1);
    if(empty($plat)){ $err='Nomor plat wajib diisi.'; }
    else {
        $cek = $db->prepare("SELECT id FROM transaksi WHERE no_plat=? AND status='parkir'");
        $cek->execute([$plat]);
        if($cek->fetch()){ $err="Plat <b>$plat</b> sudah tercatat masuk dan belum keluar!"; }
        else {
            $kap = kapasitas();
            if($kap['terisi'] >= $kap['total']){ $err='Kapasitas parkir sudah PENUH!'; }
            else {
                $ins = $db->prepare("INSERT INTO transaksi (no_plat,id_tarif,waktu_masuk,id_operator,status) VALUES(?,?,NOW(),?,'parkir')");
                $ins->execute([$plat,$idt,$_SESSION['uid']]);
                updateSlot(+1);
                $msg = "✅ Kendaraan <b>$plat</b> berhasil dicatat masuk – ".date('H:i:s');
            }
        }
    }
}

$tarifs  = $db->query("SELECT * FROM tarif")->fetchAll();
$kap     = kapasitas();
$pct     = $kap['total']>0 ? round($kap['terisi']/$kap['total']*100) : 0;
$parkir  = $db->query("SELECT t.*,tr.jenis FROM transaksi t JOIN tarif tr ON t.id_tarif=tr.id WHERE t.status='parkir' ORDER BY t.waktu_masuk DESC LIMIT 15")->fetchAll();

include '_layout.php';
?>

<div style="display:grid;grid-template-columns:380px 1fr;gap:20px">

<!-- FORM -->
<div>
  <div class="card" style="margin-bottom:16px">
    <div class="card-header">
      <span class="card-title">🔵 Catat Kendaraan Masuk</span>
    </div>
    <?php if($msg):?><div class="alert alert-success"><?=$msg?></div><?php endif;?>
    <?php if($err):?><div class="alert alert-danger"><?=$err?></div><?php endif;?>
    <form method="POST" id="fMasuk">
      <div class="form-group">
        <label>Nomor Plat *</label>
        <input type="text" name="no_plat" class="form-control plat" placeholder="Contoh: B 1234 ABC"
               required maxlength="15" autofocus id="inputPlat">
      </div>
      <div class="form-group">
        <label>Jenis Kendaraan</label>
        <select name="id_tarif" class="form-control">
          <?php foreach($tarifs as $t):?>
          <option value="<?=$t['id']?>"><?=htmlspecialchars($t['jenis'])?> –
            <?=rupiah($t['tarif_awal'])?>/<?=$t['jam_awal']?>jam</option>
          <?php endforeach;?>
        </select>
      </div>
      <div style="background:var(--surface2);border-radius:var(--radius-sm);padding:12px;margin-bottom:16px;font-size:.82rem;color:var(--text2)">
        ⏱ Waktu masuk: <b id="waktuMasuk" style="color:var(--text)">–</b>
      </div>
      <button type="submit" class="btn btn-success btn-block">🔵 Catat Masuk</button>
    </form>
  </div>

  <!-- Kapasitas -->
  <div class="card">
    <div style="display:flex;justify-content:space-between;margin-bottom:8px">
      <span style="font-weight:600;font-size:.85rem">🅿 Kapasitas</span>
      <span style="font-weight:700;color:<?=$pct>=90?'var(--danger)':($pct>=70?'var(--warning)':'var(--success)')?>;font-size:.85rem">
        <?=$kap['terisi']?>/<?=$kap['total']?> (<?=$pct?>%)
      </span>
    </div>
    <div class="progress">
      <div class="progress-bar <?=$pct>=90?'red':($pct>=70?'yellow':'green')?>" style="width:<?=$pct?>%"></div>
    </div>
    <div style="display:flex;justify-content:space-between;margin-top:6px">
      <small style="color:var(--text2)">Tersedia: <b style="color:var(--success)"><?=$kap['total']-$kap['terisi']?></b></small>
      <?php if($pct>=90):?><small style="color:var(--danger);font-weight:600">⚠️ Hampir penuh!</small><?php endif;?>
    </div>
  </div>
</div>

<!-- DAFTAR SEDANG PARKIR -->
<div class="card">
  <div class="card-header">
    <span class="card-title">🏍 Kendaraan Sedang Parkir <span style="background:var(--primary-dim);color:var(--primary);padding:2px 10px;border-radius:20px;font-size:.78rem"><?=count($parkir)?></span></span>
    <div class="search-box"><span>🔍</span><input type="text" id="tableSearch" placeholder="Cari plat..."></div>
  </div>
  <div class="table-wrap">
    <table id="mainTable">
      <thead><tr><th>No Plat</th><th>Jenis</th><th>Waktu Masuk</th><th>Durasi</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach($parkir as $p):?>
        <?php
          $dm = round((time()-strtotime($p['waktu_masuk']))/60);
          $dh = floor($dm/60); $dmn = $dm%60;
        ?>
        <tr>
          <td><b><?=htmlspecialchars($p['no_plat'])?></b></td>
          <td><?=htmlspecialchars($p['jenis'])?></td>
          <td><?=date('H:i',strtotime($p['waktu_masuk']))?></td>
          <td><span class="badge badge-parkir"><?=$dh?>j <?=$dmn?>m</span></td>
          <td>
            <a href="keluar.php?plat=<?=urlencode($p['no_plat'])?>" class="btn btn-success btn-sm">Proses Keluar →</a>
          </td>
        </tr>
        <?php endforeach;?>
        <?php if(empty($parkir)):?><tr><td colspan="5" style="text-align:center;color:var(--text3);padding:32px">Belum ada kendaraan parkir</td></tr><?php endif;?>
      </tbody>
    </table>
  </div>
</div>

</div>

<script>
// Live waktu masuk
function updWaktu(){
  var n=new Date();
  document.getElementById('waktuMasuk').textContent =
    n.toLocaleDateString('id-ID',{weekday:'short',day:'numeric',month:'short'})+' '+
    String(n.getHours()).padStart(2,'0')+':'+String(n.getMinutes()).padStart(2,'0')+':'+String(n.getSeconds()).padStart(2,'0');
}
setInterval(updWaktu,1000); updWaktu();

// Notif setelah submit
<?php if($msg):?>showToast('Kendaraan berhasil dicatat masuk!','success');<?php endif;?>
<?php if($err):?>showToast('<?=addslashes(strip_tags($err))?>','error');<?php endif;?>
</script>

<?php include '_layout_end.php';?>
