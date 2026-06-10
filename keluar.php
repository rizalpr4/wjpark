<?php
require 'koneksi.php';
cekLogin();
$pageTitle = 'Kendaraan Keluar';
$db = db();
$msg=$err=''; $kendaraan=null;

// Proses konfirmasi keluar
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['konfirmasi'])){
    $id   = (int)$_POST['id_transaksi'];
    $now  = date('Y-m-d H:i:s');
    $row  = $db->prepare("SELECT * FROM transaksi WHERE id=? AND status='parkir'");
    $row->execute([$id]);
    $trx  = $row->fetch();
    if($trx){
        $menit = round((strtotime($now)-strtotime($trx['waktu_masuk']))/60);
        $biaya = hitungBiaya($trx['waktu_masuk'],$now,$trx['id_tarif']);
        $up = $db->prepare("UPDATE transaksi SET waktu_keluar=?,durasi_menit=?,biaya=?,status='selesai' WHERE id=?");
        $up->execute([$now,$menit,$biaya,$id]);
        updateSlot(-1);
        $msg="✅ <b>{$trx['no_plat']}</b> keluar – Durasi <b>{$menit} mnt</b> – Biaya <b>".rupiah($biaya)."</b>";
        // Untuk struk
        $struk = ['plat'=>$trx['no_plat'],'masuk'=>$trx['waktu_masuk'],'keluar'=>$now,'menit'=>$menit,'biaya'=>$biaya];
    } else { $err='Transaksi tidak ditemukan.'; }
}

// Cari kendaraan
$platCari = $_GET['plat'] ?? ($_POST['cari_plat'] ?? '');
if(!empty($platCari)){
    $platCari = strtoupper(trim($platCari));
    $fc = $db->prepare("SELECT t.*,tr.jenis FROM transaksi t JOIN tarif tr ON t.id_tarif=tr.id WHERE t.no_plat=? AND t.status='parkir'");
    $fc->execute([$platCari]);
    $kendaraan = $fc->fetch();
    if(!$kendaraan && empty($msg)) $err = "Kendaraan plat <b>$platCari</b> tidak ditemukan atau sudah keluar.";
}

include '_layout.php';
?>

<div style="display:grid;grid-template-columns:420px 1fr;gap:20px">

<!-- FORM KELUAR -->
<div>
  <div class="card" style="margin-bottom:16px">
    <div class="card-header">
      <span class="card-title">🟢 Proses Kendaraan Keluar</span>
    </div>
    <?php if($msg):?>
    <div class="alert alert-success"><?=$msg?></div>
    <?php if(isset($struk)):?>
    <div id="struk" style="background:var(--surface2);border:1px dashed var(--border);border-radius:var(--radius-sm);padding:16px;margin-bottom:12px;font-family:monospace;font-size:.82rem;line-height:1.8">
      <div style="text-align:center;font-weight:700;font-size:1rem;margin-bottom:8px">🅿 WJ-PARK Analytics</div>
      <div style="text-align:center;color:var(--text2);margin-bottom:12px;font-size:.75rem">Penitipan Motor W&J – Citayam</div>
      <div>No Plat &nbsp;: <b><?=htmlspecialchars($struk['plat'])?></b></div>
      <div>Masuk &nbsp;&nbsp;&nbsp;: <?=date('d/m/Y H:i',strtotime($struk['masuk']))?></div>
      <div>Keluar &nbsp;&nbsp;: <?=date('d/m/Y H:i',strtotime($struk['keluar']))?></div>
      <div>Durasi &nbsp;&nbsp;: <?=$struk['menit']?> menit</div>
      <div style="border-top:1px dashed var(--border);margin-top:8px;padding-top:8px">
        <b>Total Biaya: <?=rupiah($struk['biaya'])?></b>
      </div>
      <div style="text-align:center;color:var(--text3);margin-top:8px;font-size:.72rem">Terima kasih! 🙏</div>
    </div>
    <button onclick="window.print()" class="btn btn-outline btn-sm" style="width:100%">🖨 Cetak Struk</button>
    <?php endif;?>
    <?php endif;?>
    <?php if($err):?><div class="alert alert-danger"><?=$err?></div><?php endif;?>

    <!-- Form cari -->
    <form method="POST" style="margin-bottom:16px">
      <div class="form-group" style="margin-bottom:10px">
        <label>Cari No Plat</label>
        <div style="display:flex;gap:8px">
          <input type="text" name="cari_plat" class="form-control plat" placeholder="Masukkan nomor plat"
                 value="<?=htmlspecialchars($platCari)?>" style="flex:1">
          <button type="submit" class="btn btn-primary">Cari</button>
        </div>
      </div>
    </form>

    <!-- Hasil cari -->
    <?php if($kendaraan):?>
    <?php
      $now = date('Y-m-d H:i:s');
      $mPreview = round((strtotime($now)-strtotime($kendaraan['waktu_masuk']))/60);
      $jPreview = floor($mPreview/60); $mnPreview = $mPreview%60;
      $bPreview = hitungBiaya($kendaraan['waktu_masuk'],$now,$kendaraan['id_tarif']);
    ?>
    <div style="background:var(--surface2);border:1px solid var(--border);border-radius:var(--radius-sm);padding:16px;margin-bottom:14px">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
        <b style="font-size:1.1rem"><?=htmlspecialchars($kendaraan['no_plat'])?></b>
        <span class="badge badge-parkir">🔵 Sedang Parkir</span>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:.83rem;color:var(--text2)">
        <div>Jenis: <b style="color:var(--text)"><?=htmlspecialchars($kendaraan['jenis'])?></b></div>
        <div>Masuk: <b style="color:var(--text)"><?=date('H:i',strtotime($kendaraan['waktu_masuk']))?></b></div>
        <div>Durasi: <b style="color:var(--warning)" id="durasi-live" data-masuk="<?=$kendaraan['waktu_masuk']?>"><?=$jPreview?>j <?=$mnPreview?>m</b></div>
        <div>Est. Biaya: <b style="color:var(--success)" id="biaya-live"><?=rupiah($bPreview)?></b></div>
      </div>
    </div>
    <form method="POST" id="fKeluar">
      <input type="hidden" name="id_transaksi" value="<?=$kendaraan['id']?>">
      <input type="hidden" name="konfirmasi" value="1">
      <button type="submit" class="btn btn-success btn-block" onclick="return confirm('Konfirmasi kendaraan <?=htmlspecialchars($kendaraan['no_plat'])?> keluar?')">
        ✅ Konfirmasi Keluar &amp; Bayar
      </button>
    </form>
    <?php endif;?>
  </div>
</div>

<!-- DAFTAR MASIH PARKIR -->
<div class="card">
  <div class="card-header">
    <span class="card-title">🏍 Semua Kendaraan Parkir</span>
    <div class="search-box"><span>🔍</span><input type="text" id="tableSearch" placeholder="Cari plat..."></div>
  </div>
  <div class="table-wrap">
    <table id="mainTable">
      <thead><tr><th>No Plat</th><th>Jenis</th><th>Waktu Masuk</th><th>Durasi</th><th>Est. Biaya</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php
        $all = $db->query("SELECT t.*,tr.jenis FROM transaksi t JOIN tarif tr ON t.id_tarif=tr.id WHERE t.status='parkir' ORDER BY t.waktu_masuk ASC")->fetchAll();
        foreach($all as $a):
          $dm = round((time()-strtotime($a['waktu_masuk']))/60);
          $dh = floor($dm/60); $dmn=$dm%60;
          $bEst = hitungBiaya($a['waktu_masuk'],date('Y-m-d H:i:s'),$a['id_tarif']);
        ?>
        <tr>
          <td><b><?=htmlspecialchars($a['no_plat'])?></b></td>
          <td><?=htmlspecialchars($a['jenis'])?></td>
          <td><?=date('H:i',strtotime($a['waktu_masuk']))?></td>
          <td><?=$dh?>j <?=$dmn?>m</td>
          <td style="color:var(--success);font-weight:600"><?=rupiah($bEst)?></td>
          <td>
            <a href="keluar.php?plat=<?=urlencode($a['no_plat'])?>" class="btn btn-success btn-sm">Proses →</a>
          </td>
        </tr>
        <?php endforeach;?>
        <?php if(empty($all)):?><tr><td colspan="6" style="text-align:center;color:var(--text3);padding:32px">Tidak ada kendaraan parkir saat ini</td></tr><?php endif;?>
      </tbody>
    </table>
  </div>
</div>
</div>

<script>
<?php if(isset($kendaraan) && $kendaraan):?>
var wMasuk = new Date('<?=str_replace(' ','T',$kendaraan['waktu_masuk'])?>');
var idTarif = <?=(int)$kendaraan['id_tarif']?>;
var tarifAwal = <?=(int)(($tarifs = db()->query("SELECT * FROM tarif WHERE id=".((int)$kendaraan['id_tarif'])))->fetch()['tarif_awal']??3000)?>;
var jamAwal = <?=(int)(db()->query("SELECT * FROM tarif WHERE id=".((int)$kendaraan['id_tarif']))->fetch()['jam_awal']??3)?>;
var tarifJam = <?=(int)(db()->query("SELECT * FROM tarif WHERE id=".((int)$kendaraan['id_tarif']))->fetch()['tarif_perjam']??1000)?>;
var tarifMaks = <?=(int)(db()->query("SELECT * FROM tarif WHERE id=".((int)$kendaraan['id_tarif']))->fetch()['tarif_maks']??15000)?>;

setInterval(function(){
  var now  = new Date();
  var mnt  = Math.round((now - wMasuk) / 60000);
  var jam  = Math.floor(mnt/60); var mn = mnt%60;
  document.getElementById('durasi-live').textContent = jam+'j '+mn+'m';
  // Hitung biaya
  var jamBulat = Math.max(1, Math.ceil(mnt/60));
  var biaya = jamBulat <= jamAwal ? tarifAwal : Math.min(tarifAwal + (jamBulat-jamAwal)*tarifJam, tarifMaks);
  document.getElementById('biaya-live').textContent = 'Rp '+biaya.toLocaleString('id-ID');
}, 1000);
<?php endif;?>

<?php if($msg):?>showToast('Kendaraan berhasil keluar!','success');<?php endif;?>
<?php if($err && !$kendaraan):?>showToast('<?=addslashes(strip_tags($err))?>','error');<?php endif;?>

// Print only struk
window.onbeforeprint = function(){
  document.querySelectorAll('.sidebar,.main>.topbar,.card:not(#struk)').forEach(function(e){ e.style.display='none'; });
};
window.onafterprint = function(){
  document.querySelectorAll('.sidebar,.main>.topbar,.card:not(#struk)').forEach(function(e){ e.style.display=''; });
};
</script>
<?php include '_layout_end.php';?>
