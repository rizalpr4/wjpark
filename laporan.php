<?php
require 'koneksi.php';
cekAdmin();
$pageTitle = 'Laporan Transaksi';
$db = db();
$msg=$err='';

// ─── HAPUS TERPILIH (CHECKBOX) ───────────────────────────────
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['act']??'')==='hapus_terpilih'){
    $ids = $_POST['ids'] ?? [];
    if(empty($ids)){ $err='Tidak ada transaksi yang dipilih.'; }
    else {
        $ids = array_map('intval', $ids);
        $ph  = implode(',', array_fill(0, count($ids), '?'));
        // Cek berapa yg masih parkir
        $cekP = $db->prepare("SELECT COUNT(*) AS n FROM transaksi WHERE id IN ($ph) AND status='parkir'");
        $cekP->execute($ids);
        $jmlParkir = (int)$cekP->fetch()['n'];
        // Hapus
        $del = $db->prepare("DELETE FROM transaksi WHERE id IN ($ph)");
        $del->execute($ids);
        $jml = $del->rowCount();
        if($jmlParkir > 0)
            $db->exec("UPDATE kapasitas SET terisi = GREATEST(terisi-$jmlParkir,0) WHERE id=1");
        $msg="✅ <b>$jml transaksi</b> berhasil dihapus.";
    }
}

// ─── HAPUS SATU BARIS ────────────────────────────────────────
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['act']??'')==='hapus_satu'){
    $id = (int)$_POST['id'];
    $cek = $db->prepare("SELECT status FROM transaksi WHERE id=?");
    $cek->execute([$id]);
    $trx = $cek->fetch();
    if($trx){
        if($trx['status']==='parkir') updateSlot(-1);
        $db->prepare("DELETE FROM transaksi WHERE id=?")->execute([$id]);
        $msg='✅ Transaksi berhasil dihapus.';
    } else { $err='Transaksi tidak ditemukan.'; }
}

// ─── HAPUS RENTANG TANGGAL ───────────────────────────────────
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['act']??'')==='hapus_rentang'){
    $h1=$_POST['hapus_tgl1']??'';
    $h2=$_POST['hapus_tgl2']??'';
    if(empty($h1)||empty($h2)){ $err='Tanggal wajib diisi.'; }
    else {
        $cekP=$db->prepare("SELECT COUNT(*) AS n FROM transaksi WHERE DATE(waktu_masuk) BETWEEN ? AND ? AND status='parkir'");
        $cekP->execute([$h1,$h2]);
        $jmlParkir=(int)$cekP->fetch()['n'];
        $del=$db->prepare("DELETE FROM transaksi WHERE DATE(waktu_masuk) BETWEEN ? AND ?");
        $del->execute([$h1,$h2]);
        $jml=$del->rowCount();
        if($jmlParkir>0)
            $db->exec("UPDATE kapasitas SET terisi=GREATEST(terisi-$jmlParkir,0) WHERE id=1");
        $msg="✅ <b>$jml transaksi</b> dihapus (periode ".date('d/m/Y',strtotime($h1))." – ".date('d/m/Y',strtotime($h2)).").";
    }
}

// ─── FILTER & DATA ───────────────────────────────────────────
$tgl1 = $_GET['tgl1'] ?? date('Y-m-01');
$tgl2 = $_GET['tgl2'] ?? date('Y-m-d');
$mode = $_GET['mode'] ?? 'harian';

$q = $db->prepare("SELECT t.*,tr.jenis,u.nama FROM transaksi t
    JOIN tarif tr ON t.id_tarif=tr.id
    JOIN users u ON t.id_operator=u.id
    WHERE DATE(t.waktu_masuk) BETWEEN ? AND ?
    ORDER BY t.waktu_masuk DESC");
$q->execute([$tgl1,$tgl2]);
$rows = $q->fetchAll();

$sum=$db->prepare("SELECT COUNT(*) AS jml, COALESCE(SUM(biaya),0) AS total,
    COALESCE(AVG(durasi_menit),0) AS avg_dur
    FROM transaksi WHERE DATE(waktu_masuk) BETWEEN ? AND ? AND status='selesai'");
$sum->execute([$tgl1,$tgl2]);
$sumData=$sum->fetch();

$rekap=[];
if($mode==='bulanan'){
    $rq=$db->prepare("SELECT DATE(waktu_masuk) AS tgl, COUNT(*) AS jml,
        COALESCE(SUM(biaya),0) AS total
        FROM transaksi WHERE DATE(waktu_masuk) BETWEEN ? AND ? AND status='selesai'
        GROUP BY DATE(waktu_masuk) ORDER BY tgl DESC");
    $rq->execute([$tgl1,$tgl2]);
    $rekap=$rq->fetchAll();
}

$totalDB=(int)$db->query("SELECT COUNT(*) AS n FROM transaksi")->fetch()['n'];

include '_layout.php';
?>

<style>
/* Toolbar hapus terpilih */
.bulk-toolbar{
    display:none;align-items:center;gap:12px;
    background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);
    border-radius:var(--radius-sm);padding:10px 16px;margin-bottom:14px;
    animation:slideDown .2s ease;
}
.bulk-toolbar.show{display:flex}
@keyframes slideDown{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:translateY(0)}}
.bulk-count{font-weight:700;color:var(--danger);font-size:.88rem}
.bulk-info{color:var(--text2);font-size:.82rem;flex:1}

/* Checkbox style */
.cb-row{width:16px;height:16px;accent-color:var(--danger);cursor:pointer}
tbody tr.selected{background:rgba(239,68,68,.06)!important}

/* Panel hapus rentang */
.hapus-panel{
    background:var(--surface2);border:1px solid rgba(239,68,68,.2);
    border-radius:var(--radius-sm);padding:16px;margin-top:12px;
}
</style>

<?php if($msg):?><div class="alert alert-success"><?=$msg?></div><?php endif;?>
<?php if($err):?><div class="alert alert-danger"><?=$err?></div><?php endif;?>

<!-- FILTER -->
<div class="card" style="margin-bottom:20px">
  <form method="GET" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end">
    <input type="hidden" name="mode" value="<?=$mode?>">
    <div class="form-group" style="margin:0;flex:1;min-width:140px">
      <label>Dari Tanggal</label>
      <input type="date" name="tgl1" class="form-control" value="<?=htmlspecialchars($tgl1)?>">
    </div>
    <div class="form-group" style="margin:0;flex:1;min-width:140px">
      <label>Sampai Tanggal</label>
      <input type="date" name="tgl2" class="form-control" value="<?=htmlspecialchars($tgl2)?>">
    </div>
    <div style="display:flex;gap:8px">
      <a href="?mode=harian&tgl1=<?=date('Y-m-d')?>&tgl2=<?=date('Y-m-d')?>"
         class="btn <?=$mode==='harian'?'btn-primary':'btn-outline'?>">📅 Harian</a>
      <a href="?mode=bulanan&tgl1=<?=date('Y-m-01')?>&tgl2=<?=date('Y-m-d')?>"
         class="btn <?=$mode==='bulanan'?'btn-primary':'btn-outline'?>">📆 Bulanan</a>
    </div>
    <button type="submit" class="btn btn-success">🔍 Tampilkan</button>
  </form>
</div>

<!-- SUMMARY -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px">
  <div class="card" style="text-align:center">
    <div style="font-size:1.4rem;font-weight:800;color:var(--primary)"><?=rupiah($sumData['total'])?></div>
    <div style="font-size:.78rem;color:var(--text2)">Total Pendapatan</div>
  </div>
  <div class="card" style="text-align:center">
    <div style="font-size:1.4rem;font-weight:800;color:var(--success)"><?=(int)$sumData['jml']?></div>
    <div style="font-size:.78rem;color:var(--text2)">Transaksi Selesai</div>
  </div>
  <div class="card" style="text-align:center">
    <div style="font-size:1.4rem;font-weight:800;color:var(--warning)"><?=round($sumData['avg_dur'])?> mnt</div>
    <div style="font-size:.78rem;color:var(--text2)">Rata-rata Durasi</div>
  </div>
  <div class="card" style="text-align:center">
    <div style="font-size:1.4rem;font-weight:800;color:var(--text2)"><?=$totalDB?></div>
    <div style="font-size:.78rem;color:var(--text2)">Total Semua Data</div>
  </div>
</div>

<?php if($mode==='bulanan'&&!empty($rekap)):?>
<div class="card" style="margin-bottom:20px">
  <div class="card-header"><span class="card-title">📆 Rekap Per Hari</span></div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Tanggal</th><th>Jumlah Transaksi</th><th>Total Pendapatan</th></tr></thead>
      <tbody>
        <?php foreach($rekap as $r):?>
        <tr>
          <td><?=date('l, d F Y',strtotime($r['tgl']))?></td>
          <td><?=(int)$r['jml']?> transaksi</td>
          <td style="font-weight:600;color:var(--success)"><?=rupiah($r['total'])?></td>
        </tr>
        <?php endforeach;?>
      </tbody>
    </table>
  </div>
</div>
<?php endif;?>

<!-- HAPUS RENTANG TANGGAL -->
<div class="card" style="margin-bottom:20px">
  <div class="card-header">
    <span class="card-title">🗑 Opsi Hapus Massal</span>
    <button class="btn btn-outline btn-sm" onclick="togglePanel()">📅 Hapus per Rentang Tanggal</button>
  </div>
  <div id="rentangPanel" style="display:none">
    <div class="hapus-panel">
      <div class="alert alert-warning" style="margin-bottom:12px">
        ⚠️ <b>Peringatan:</b> Data yang dihapus tidak dapat dikembalikan!
      </div>
      <form method="POST" onsubmit="return konfirmasiRentang(this)">
        <input type="hidden" name="act" value="hapus_rentang">
        <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end">
          <div class="form-group" style="margin:0;flex:1;min-width:130px">
            <label>Dari Tanggal</label>
            <input type="date" name="hapus_tgl1" class="form-control" required>
          </div>
          <div class="form-group" style="margin:0;flex:1;min-width:130px">
            <label>Sampai Tanggal</label>
            <input type="date" name="hapus_tgl2" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-danger">🗑 Hapus Rentang</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- TOOLBAR HAPUS TERPILIH (muncul saat ada checkbox dipilih) -->
<div class="bulk-toolbar" id="bulkToolbar">
  <span class="bulk-count" id="bulkCount">0 dipilih</span>
  <span class="bulk-info">Transaksi yang dicentang akan dihapus permanen</span>
  <button class="btn btn-outline btn-sm" onclick="pilihSemua(false)">✕ Batal Pilih</button>
  <button class="btn btn-danger btn-sm" onclick="hapusTerpilih()">🗑 Hapus yang Dipilih</button>
</div>

<!-- FORM HAPUS TERPILIH (hidden) -->
<form method="POST" id="formBulk">
  <input type="hidden" name="act" value="hapus_terpilih">
  <div id="hiddenIds"></div>
</form>

<!-- TABEL DETAIL TRANSAKSI -->
<div class="card">
  <div class="card-header">
    <span class="card-title">
      📋 Detail Transaksi
      <span style="background:var(--primary-dim);color:var(--primary);
        padding:2px 10px;border-radius:20px;font-size:.75rem;margin-left:6px">
        <?=count($rows)?> data
      </span>
    </span>
    <div style="display:flex;gap:8px;align-items:center">
      <!-- Pilih semua toggle -->
      <?php if(!empty($rows)):?>
      <button type="button" class="btn btn-outline btn-sm" onclick="pilihSemua(true)" id="btnPilihSemua">
        ☑ Pilih Semua
      </button>
      <?php endif;?>
      <div class="search-box">
        <span>🔍</span>
        <input type="text" id="tableSearch" placeholder="Cari plat, operator...">
      </div>
      <button onclick="cetakLaporan()" class="btn btn-outline btn-sm">🖨 Cetak</button>
    </div>
  </div>
  <div class="table-wrap" id="printArea">
    <table id="mainTable">
      <thead>
        <tr>
          <th style="width:36px">
            <input type="checkbox" class="cb-row" id="cbAll"
                   onchange="toggleAll(this)" title="Pilih/Batal semua">
          </th>
          <th>#</th>
          <th>No Plat</th>
          <th>Jenis</th>
          <th>Masuk</th>
          <th>Keluar</th>
          <th>Durasi</th>
          <th>Biaya</th>
          <th>Status</th>
          <th>Operator</th>
          <th>Hapus</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($rows as $i=>$r):?>
        <tr id="row-<?=$r['id']?>">
          <td>
            <input type="checkbox" class="cb-row cb-item"
                   value="<?=$r['id']?>" onchange="updateBulkToolbar()">
          </td>
          <td style="color:var(--text3)"><?=$i+1?></td>
          <td><b><?=htmlspecialchars($r['no_plat'])?></b></td>
          <td><?=htmlspecialchars($r['jenis'])?></td>
          <td><?=date('d/m H:i',strtotime($r['waktu_masuk']))?></td>
          <td><?=$r['waktu_keluar']?date('d/m H:i',strtotime($r['waktu_keluar'])):'–'?></td>
          <td><?=$r['durasi_menit']?$r['durasi_menit'].' mnt':'–'?></td>
          <td style="font-weight:600;color:var(--success)"><?=$r['biaya']?rupiah($r['biaya']):'–'?></td>
          <td><span class="badge badge-<?=$r['status']?>"><?=$r['status']==='parkir'?'🔵 Parkir':'✅ Selesai'?></span></td>
          <td style="color:var(--text2)"><?=htmlspecialchars($r['nama'])?></td>
          <td>
            <form method="POST" style="display:inline"
                  onsubmit="return confirm('Hapus transaksi <?=htmlspecialchars($r['no_plat'])?>?\nData tidak dapat dikembalikan!')">
              <input type="hidden" name="act" value="hapus_satu">
              <input type="hidden" name="id" value="<?=$r['id']?>">
              <button type="submit" class="btn btn-danger btn-sm" title="Hapus baris ini">🗑</button>
            </form>
          </td>
        </tr>
        <?php endforeach;?>
        <?php if(empty($rows)):?>
        <tr>
          <td colspan="11" style="text-align:center;color:var(--text3);padding:36px">
            Tidak ada data pada rentang tanggal ini
          </td>
        </tr>
        <?php endif;?>
      </tbody>
    </table>
  </div>
</div>

<script>
// ─── CHECKBOX BULK ──────────────────────────────────────────
function getChecked(){
    return Array.from(document.querySelectorAll('.cb-item:checked'));
}

function updateBulkToolbar(){
    var checked = getChecked();
    var toolbar  = document.getElementById('bulkToolbar');
    var countEl  = document.getElementById('bulkCount');
    var cbAll    = document.getElementById('cbAll');
    var total    = document.querySelectorAll('.cb-item').length;

    countEl.textContent = checked.length + ' dipilih';
    toolbar.classList.toggle('show', checked.length > 0);
    cbAll.indeterminate = checked.length > 0 && checked.length < total;
    cbAll.checked       = checked.length === total && total > 0;

    // Highlight baris yang dicentang
    document.querySelectorAll('.cb-item').forEach(function(cb){
        var row = cb.closest('tr');
        row.classList.toggle('selected', cb.checked);
    });
}

function toggleAll(cbAll){
    document.querySelectorAll('.cb-item').forEach(function(cb){
        cb.checked = cbAll.checked;
    });
    updateBulkToolbar();
}

function pilihSemua(state){
    document.querySelectorAll('.cb-item').forEach(function(cb){ cb.checked = state; });
    var cbAll = document.getElementById('cbAll');
    if(cbAll) cbAll.checked = state;
    updateBulkToolbar();
}

function hapusTerpilih(){
    var checked = getChecked();
    if(checked.length === 0){ showToast('Pilih minimal 1 transaksi dulu!','error'); return; }

    if(!confirm('⚠️ KONFIRMASI HAPUS\n\nAnda akan menghapus ' + checked.length + ' transaksi yang dipilih.\n\nData tidak dapat dikembalikan!\n\nLanjutkan?'))
        return;

    // Masukkan ID ke form hidden
    var container = document.getElementById('hiddenIds');
    container.innerHTML = '';
    checked.forEach(function(cb){
        var inp = document.createElement('input');
        inp.type  = 'hidden';
        inp.name  = 'ids[]';
        inp.value = cb.value;
        container.appendChild(inp);
    });
    document.getElementById('formBulk').submit();
}

// ─── TOGGLE PANEL RENTANG ───────────────────────────────────
function togglePanel(){
    var p = document.getElementById('rentangPanel');
    p.style.display = p.style.display==='none' ? 'block' : 'none';
}

function konfirmasiRentang(form){
    var t1 = form.hapus_tgl1.value;
    var t2 = form.hapus_tgl2.value;
    if(!t1||!t2){ alert('Tanggal wajib diisi!'); return false; }
    if(t1>t2){ alert('Tanggal awal tidak boleh lebih besar dari tanggal akhir!'); return false; }
    return confirm('⚠️ KONFIRMASI HAPUS RENTANG\n\nHapus SEMUA transaksi dari\n'+t1+' sampai '+t2+'\n\nData TIDAK DAPAT dikembalikan!\n\nLanjutkan?');
}

// ─── CETAK (tanpa kolom checkbox & hapus) ──────────────────
function cetakLaporan(){
    var w = window.open('','_blank','width=900,height=600');
    var tbl = document.getElementById('mainTable').cloneNode(true);
    // Hapus kolom checkbox (1) dan hapus (terakhir) dari cetak
    tbl.querySelectorAll('th:first-child,td:first-child').forEach(function(e){ e.remove(); });
    tbl.querySelectorAll('th:last-child,td:last-child').forEach(function(e){ e.remove(); });
    w.document.write('<html><head><title>Laporan WJ-PARK</title><style>');
    w.document.write('body{font-family:sans-serif;font-size:12px;padding:20px}');
    w.document.write('h3{margin-bottom:4px}p{color:#666;font-size:11px;margin-bottom:12px}');
    w.document.write('table{width:100%;border-collapse:collapse}');
    w.document.write('th,td{border:1px solid #ddd;padding:6px 8px;text-align:left}');
    w.document.write('thead{background:#f3f4f6}.badge{padding:2px 6px;border-radius:10px;font-size:11px}');
    w.document.write('</style></head><body>');
    w.document.write('<h3>Laporan Transaksi – WJ-PARK Analytics</h3>');
    w.document.write('<p>Periode: <?=date('d/m/Y',strtotime($tgl1))?> s.d <?=date('d/m/Y',strtotime($tgl2))?> &nbsp;|&nbsp; Dicetak: '+new Date().toLocaleString('id-ID')+'</p>');
    w.document.write('<p>Total Pendapatan: <b><?=rupiah($sumData['total'])?></b> &nbsp;|&nbsp; Transaksi: <b><?=(int)$sumData['jml']?></b></p>');
    w.document.write(tbl.outerHTML+'</body></html>');
    w.document.close(); w.print();
}

// ─── NOTIF ──────────────────────────────────────────────────
<?php if($msg):?>showToast('<?=addslashes(strip_tags($msg))?>','success');<?php endif;?>
<?php if($err):?>showToast('<?=addslashes(strip_tags($err))?>','error');<?php endif;?>
</script>

<?php include '_layout_end.php';?>
