<?php
require 'koneksi.php';
cekAdmin();
$pageTitle = 'Dashboard';
$db = db();
$today = date('Y-m-d');

// Statistik hari ini
$st = $db->prepare("SELECT COALESCE(SUM(biaya),0) AS pend, COUNT(*) AS jml,
    COALESCE(AVG(durasi_menit),0) AS avg_dur FROM transaksi WHERE DATE(waktu_masuk)=? AND status='selesai'");
$st->execute([$today]);
$stat = $st->fetch();

$stParkir = $db->query("SELECT COUNT(*) AS n FROM transaksi WHERE status='parkir'")->fetch();
$kap = kapasitas();
$pct = $kap['total']>0 ? round($kap['terisi']/$kap['total']*100) : 0;

// Tren 7 hari
$tren = [];
for($i=6;$i>=0;$i--){
    $d = date('Y-m-d',strtotime("-{$i} days"));
    $r = $db->prepare("SELECT COALESCE(SUM(biaya),0) AS tot, COUNT(*) AS jml FROM transaksi WHERE DATE(waktu_masuk)=? AND status='selesai'");
    $r->execute([$d]);
    $row = $r->fetch();
    $tren[] = ['tgl'=>date('d/m',strtotime("-{$i} days")),'tot'=>(int)$row['tot'],'jml'=>(int)$row['jml']];
}

// Distribusi jam hari ini
$jam = array_fill(0,24,0);
$rj = $db->prepare("SELECT HOUR(waktu_masuk) AS h, COUNT(*) AS n FROM transaksi WHERE DATE(waktu_masuk)=? GROUP BY h");
$rj->execute([$today]);
foreach($rj->fetchAll() as $r) $jam[$r['h']] = (int)$r['n'];

// 8 transaksi terbaru
$tbaru = $db->query("SELECT t.*,tr.jenis,u.nama FROM transaksi t
    JOIN tarif tr ON t.id_tarif=tr.id JOIN users u ON t.id_operator=u.id
    ORDER BY t.created_at DESC LIMIT 8")->fetchAll();

include '_layout.php';
?>

<!-- STAT CARDS -->
<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-top">
      <div class="stat-icon" style="background:rgba(99,102,241,.12)">💰</div>
      <span class="stat-badge" style="background:rgba(99,102,241,.1);color:var(--primary)">Hari Ini</span>
    </div>
    <div class="stat-val"><?=rupiah($stat['pend'])?></div>
    <div class="stat-lbl">Total Pendapatan</div>
  </div>
  <div class="stat-card">
    <div class="stat-top">
      <div class="stat-icon" style="background:rgba(16,185,129,.12)">🏍</div>
      <span class="stat-badge" style="background:rgba(16,185,129,.1);color:var(--success)">Hari Ini</span>
    </div>
    <div class="stat-val"><?=(int)$stat['jml']?></div>
    <div class="stat-lbl">Kendaraan Dilayani</div>
  </div>
  <div class="stat-card">
    <div class="stat-top">
      <div class="stat-icon" style="background:rgba(245,158,11,.12)">🅿</div>
      <span class="stat-badge" style="background:rgba(245,158,11,.1);color:var(--warning)">Live</span>
    </div>
    <div class="stat-val"><?=(int)$stParkir['n']?><small style="font-size:.9rem;font-weight:400;color:var(--text2)"> / <?=$kap['total']?></small></div>
    <div class="stat-lbl">Sedang Parkir</div>
  </div>
  <div class="stat-card">
    <div class="stat-top">
      <div class="stat-icon" style="background:rgba(59,130,246,.12)">⏱</div>
      <span class="stat-badge" style="background:rgba(59,130,246,.1);color:var(--info)">Rata-rata</span>
    </div>
    <div class="stat-val"><?=round($stat['avg_dur'])?><small style="font-size:.9rem;font-weight:400;color:var(--text2)"> mnt</small></div>
    <div class="stat-lbl">Durasi Parkir</div>
  </div>
</div>

<!-- KAPASITAS -->
<div class="card" style="margin-bottom:20px">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
    <span style="font-weight:600;font-size:.9rem">🅿 Monitor Kapasitas</span>
    <span style="font-weight:700;font-size:.9rem;color:<?=$pct>=90?'var(--danger)':($pct>=70?'var(--warning)':'var(--success)')?>">
      <?=$kap['terisi']?>/<?=$kap['total']?> slot (<?=$pct?>%)
    </span>
  </div>
  <div class="progress">
    <div class="progress-bar <?=$pct>=90?'red':($pct>=70?'yellow':'green')?>" style="width:<?=$pct?>%"></div>
  </div>
  <div style="display:flex;justify-content:space-between;margin-top:6px">
    <small style="color:var(--text2)">Tersedia: <b style="color:var(--success)"><?=$kap['total']-$kap['terisi']?> slot</b></small>
    <?php if($pct>=90):?><small style="color:var(--danger);font-weight:600">⚠️ Hampir penuh!</small><?php endif;?>
  </div>
</div>

<!-- GRAFIK -->
<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:20px">
  <div class="card">
    <div class="card-header">
      <span class="card-title">📈 Tren Pendapatan 7 Hari</span>
    </div>
    <canvas id="cTren" height="110"></canvas>
  </div>
  <div class="card">
    <div class="card-header">
      <span class="card-title">🕐 Kendaraan per Jam</span>
    </div>
    <canvas id="cJam" height="110"></canvas>
  </div>
</div>

<!-- TRANSAKSI TERBARU -->
<div class="card">
  <div class="card-header">
    <span class="card-title">🕐 Transaksi Terbaru</span>
    <a href="laporan.php" class="btn btn-outline btn-sm">Lihat Semua →</a>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>No Plat</th><th>Jenis</th><th>Masuk</th><th>Keluar</th><th>Durasi</th><th>Biaya</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach($tbaru as $t):?>
        <tr>
          <td><b><?=htmlspecialchars($t['no_plat'])?></b></td>
          <td><?=htmlspecialchars($t['jenis'])?></td>
          <td><?=date('H:i',strtotime($t['waktu_masuk']))?></td>
          <td><?=$t['waktu_keluar']?date('H:i',strtotime($t['waktu_keluar'])):'–'?></td>
          <td><?=$t['durasi_menit']?$t['durasi_menit'].' mnt':'–'?></td>
          <td><?=$t['biaya']?rupiah($t['biaya']):'–'?></td>
          <td><span class="badge badge-<?=$t['status']?>"><?=$t['status']==='parkir'?'🔵 Parkir':'✅ Selesai'?></span></td>
        </tr>
        <?php endforeach;?>
        <?php if(empty($tbaru)):?><tr><td colspan="7" style="text-align:center;color:var(--text3);padding:24px">Belum ada transaksi</td></tr><?php endif;?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
var isDark = document.documentElement.getAttribute('data-theme')==='dark';
var gridC  = isDark?'rgba(255,255,255,.06)':'rgba(0,0,0,.06)';
var textC  = isDark?'#94a3b8':'#64748b';

Chart.defaults.color = textC;
Chart.defaults.borderColor = gridC;

// Tren
new Chart(document.getElementById('cTren'),{
  type:'line',
  data:{
    labels:<?=json_encode(array_column($tren,'tgl'))?>,
    datasets:[{
      label:'Pendapatan',
      data:<?=json_encode(array_column($tren,'tot'))?>,
      borderColor:'#6366f1',backgroundColor:'rgba(99,102,241,.08)',
      tension:.4,fill:true,pointBackgroundColor:'#6366f1',pointRadius:4,borderWidth:2
    }]
  },
  options:{
    responsive:true,plugins:{legend:{display:false}},
    scales:{y:{beginAtZero:true,ticks:{callback:function(v){return 'Rp'+v.toLocaleString('id-ID')}}},
            x:{grid:{display:false}}}
  }
});

// Jam
var jamL=[], jamD=[];
<?php for($i=5;$i<=22;$i++):?>
jamL.push('<?=$i?>h'); jamD.push(<?=$jam[$i]?>);
<?php endfor;?>
new Chart(document.getElementById('cJam'),{
  type:'bar',
  data:{
    labels:jamL,
    datasets:[{
      data:jamD,
      backgroundColor:'rgba(16,185,129,.6)',borderRadius:4,borderSkipped:false
    }]
  },
  options:{
    responsive:true,plugins:{legend:{display:false}},
    scales:{y:{beginAtZero:true,ticks:{stepSize:1}},x:{grid:{display:false}}}
  }
});
</script>

<?php include '_layout_end.php';?>
