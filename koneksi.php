<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

// ============================================================
//  KONFIGURASI DATABASE – InfinityFree
// ============================================================
define('DB_HOST', 'sql206.infinityfree.com');
define('DB_NAME', 'if0_42110436_wjpark');
define('DB_USER', 'if0_42110436');
define('DB_PASS', 'Teguh12345678');
// ============================================================

define('APP_NAME', 'WJ-PARK Analytics');

function db() {
    static $pdo = null;
    if (!$pdo) {
        try {
            $pdo = new PDO(
                "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4",
                DB_USER, DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
            );
        } catch (PDOException $e) {
            die('<div style="font-family:sans-serif;padding:40px;max-width:500px;margin:60px auto;background:#fff3f3;border:1px solid #e74c3c;border-radius:12px">
            <h3 style="color:#e74c3c;margin:0 0 10px">Koneksi Gagal</h3>
            <p>Pastikan konfigurasi database di <b>koneksi.php</b> sudah benar (DB_HOST, DB_NAME, DB_USER, DB_PASS).</p>
            <code style="font-size:12px;color:#666">'.$e->getMessage().'</code></div>');
        }
    }
    return $pdo;
}

function cekLogin() {
    if (empty($_SESSION['uid'])) {
        header('Location: index.php'); exit;
    }
}

function cekAdmin() {
    cekLogin();
    if ($_SESSION['role'] !== 'admin') {
        header('Location: masuk.php'); exit;
    }
}

function rupiah($n) {
    return 'Rp '.number_format($n,0,',','.');
}

function hitungBiaya($masuk, $keluar, $id_tarif) {
    $t = db()->prepare("SELECT * FROM tarif WHERE id=?");
    $t->execute([$id_tarif]);
    $tr = $t->fetch();
    if (!$tr) return 0;
    $menit = round((strtotime($keluar) - strtotime($masuk)) / 60);
    $jam   = max(1, ceil($menit / 60));
    if ($jam <= $tr['jam_awal']) {
        $biaya = $tr['tarif_awal'];
    } else {
        $biaya = $tr['tarif_awal'] + (($jam - $tr['jam_awal']) * $tr['tarif_perjam']);
    }
    return min($biaya, $tr['tarif_maks']);
}

function kapasitas() {
    return db()->query("SELECT * FROM kapasitas LIMIT 1")->fetch();
}

function updateSlot($delta) {
    if ($delta > 0)
        db()->exec("UPDATE kapasitas SET terisi = LEAST(terisi+1, total) WHERE id=1");
    else
        db()->exec("UPDATE kapasitas SET terisi = GREATEST(terisi-1, 0) WHERE id=1");
}
