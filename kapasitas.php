<?php
require 'koneksi.php';
cekLogin();
$pageTitle = 'Monitor Kapasitas';
$db = db();

$kap = kapasitas();
$pct = $kap['total']>0 ? round($kap['terisi']/$kap['total']*100) : 0;
$tersedia = $kap['total'] - $kap['terisi'];

// Slot visual
$slots = [];
$parkir = $db->query("SELECT no_plat, waktu_masuk FROM transaksi WHERE status='parkir' ORDER BY waktu_masuk ASC")->fetchAll();
for($i=1;$i<=$kap['total'];$i++){
    $slots[$i] = isset($parkir[$i-1]) ? $parkir[$i-1] : null;
}

include '_layout.php';
?>
<style>
.slot-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(88px,1fr));gap:8px;margin-top:16px}
.slot{
  aspect-ratio:1;border-radius:10px;display:flex;flex-direction:column;
  align-items:center;justify-content:center;font-size:.7rem;
  transition:var(--transition);cursor:default;padding:6px;text-align:center;
  border:1px solid var(--border);
}
.slot.terisi{background:rgba(239,68,68,.1);border-color:rgba(239,68,68,.3);color:var(--danger)}
.slot.kosong{background:rgba(16,185,129,.08);border-color:rgba(16,185,129,.2);color:var(--success)}
.slot:hover{transform:scale(1.04)}
.slot .slot-num{font-size:.65rem;color:var(--text3);margin-bottom:2px}
.slot .slot-icon{font-size:1.3rem;margin-bottom:2px}
.slot .slot-plat{font-weight:700;font-size:.72rem;word-break:break-all}
</style>

<div style="display:grid;grid-template-columns:300px 1fr;gap:20px">

<!-- INFO KAPASITAS -->
<div>
  <div class="card" style="margin-bottom:16px">
    <div class="card-header"><span class="card-title">🅿 Status Kapasitas</span></div>
    <div style="text-align:center;padding:12px 0">
      <div style="font-size:3rem;font-weight:800;color:<?=$pct>=90?'var(--danger)':($pct>=70?'var(--warning)':'var(--success)')?>">
        <?=$pct?>%
      </div>
      <div style="color:var(--text2);font-size:.85rem">Terisi</div>
    </div>
    <div class="progress" style="margin-bottom:16px">
      <div class="progress-bar <?=$pct>=90?'red':($pct>=70?'yellow':'green')?>" style="width:<?=$pct?>%"></div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;text-align:center;gap:8px">
      <div style="background:var(--surface2);border-radius:8px;padding:10px">
        <div style="font-size:1.3rem;font-weight:700;color:var(--text)"><?=$kap['total']?></div>
        <div style="font-size:.72rem;color:var(--text3)">Total</div>
      </div>
      <div style="background:rgba(239,68,68,.08);border-radius:8px;padding:10px">
        <div style="font-size:1.3rem;font-weight:700;color:var(--danger)"><?=$kap['terisi']?></div>
        <div style="font-size:.72rem;color:var(--text3)">Terisi</div>
      </div>
      <div style="background:rgba(16,185,129,.08);border-radius:8px;padding:10px">
        <div style="font-size:1.3rem;font-weight:700;color:var(--success)"><?=$tersedia?></div>
        <div style="font-size:.72rem;color:var(--text3)">Tersedia</div>
      </div>
    </div>
    <?php if($pct>=90):?>
    <div class="alert alert-danger" style="margin-top:14px;margin-bottom:0">⚠️ Kapasitas hampir penuh!</div>
    <?php elseif($pct>=70):?>
    <div class="alert alert-warning" style="margin-top:14px;margin-bottom:0">🟡 Kapasitas mulai padat.</div>
    <?php else:?>
    <div class="alert alert-success" style="margin-top:14px;margin-bottom:0">✅ Kapasitas masih aman.</div>
    <?php endif;?>
  </div>

  <div class="card">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px">
      <div style="width:12px;height:12px;border-radius:3px;background:rgba(239,68,68,.4)"></div>
      <span style="font-size:.82rem;color:var(--text2)">Slot Terisi</span>
    </div>
    <div style="display:flex;align-items:center;gap:8px">
      <div style="width:12px;height:12px;border-radius:3px;background:rgba(16,185,129,.4)"></div>
      <span style="font-size:.82rem;color:var(--text2)">Slot Kosong</span>
    </div>
    <div style="margin-top:12px;padding-top:12px;border-top:1px solid var(--border)">
      <small style="color:var(--text3)">Diperbarui otomatis setiap menit.</small><br>
      <button onclick="location.reload()" class="btn btn-outline btn-sm" style="margin-top:8px;width:100%">🔄 Refresh</button>
    </div>
  </div>
</div>

<!-- SLOT VISUAL -->
<div class="card">
  <div class="card-header">
    <span class="card-title">🏍 Peta Slot Parkir</span>
    <span style="font-size:.78rem;color:var(--text2)"><?=$kap['total']?> total slot</span>
  </div>
  <div class="slot-grid">
    <?php foreach($slots as $num=>$data):?>
    <?php if($data):
      $dm = round((time()-strtotime($data['waktu_masuk']))/60);
      $dh=floor($dm/60); $dmn=$dm%60;
    ?>
    <div class="slot terisi" title="<?=htmlspecialchars($data['no_plat'])?> – <?=$dh?>j<?=$dmn?>m">
      <div class="slot-num">#<?=$num?></div>
      <div class="slot-icon">🏍</div>
      <div class="slot-plat"><?=htmlspecialchars($data['no_plat'])?></div>
      <div style="font-size:.62rem;color:var(--danger);margin-top:2px"><?=$dh?>j<?=$dmn?>m</div>
    </div>
    <?php else:?>
    <div class="slot kosong" title="Slot #<?=$num?> kosong">
      <div class="slot-num">#<?=$num?></div>
      <div class="slot-icon">○</div>
      <div style="font-size:.7rem;color:var(--success)">Kosong</div>
    </div>
    <?php endif;?>
    <?php endforeach;?>
  </div>
</div>
</div>

<script>
// Auto refresh kapasitas setiap 60 detik
setTimeout(function(){ location.reload(); }, 60000);
</script>
<?php include '_layout_end.php';?>
