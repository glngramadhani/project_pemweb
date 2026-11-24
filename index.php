<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

$last_visit = isset($_COOKIE['last_visit']) ? $_COOKIE['last_visit'] : "Baru pertama kali masuk";
setcookie('last_visit', date('d-m-Y H:i:s'), time() + (86400 * 30), "/");

if (isset($_GET['hapus'])) {
    $id_hapus = (int) $_GET['hapus'];
    $stmt = mysqli_prepare($conn, "DELETE FROM peminjaman WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_hapus);
    mysqli_stmt_execute($stmt);
    $_SESSION['notif'] = "Data peminjaman berhasil dihapus.";
    header("Location: index.php");
    exit;
}

function hitungDenda($tgl_kembali, $status) {
    if ($status === 'Dikembalikan') {
        return [
            'keterangan' => 'Selesai Dikembalikan',
            'denda'      => 0,
            'badge'      => 'badge-success'
        ];
    }

    $hari_ini = strtotime(date('Y-m-d'));
    $jatuh_tempo = strtotime($tgl_kembali);
    $selisih_detik = $hari_ini - $jatuh_tempo;
    $hari_terlambat = floor($selisih_detik / 86400);

    if ($hari_terlambat > 0) {
        $total_denda = $hari_terlambat * 2000;
        return [
            'keterangan' => "Terlambat $hari_terlambat hari",
            'denda'      => $total_denda,
            'badge'      => 'badge-danger'
        ];
    } else {
        return [
            'keterangan' => 'Masa Pinjam Aktif',
            'denda'      => 0,
            'badge'      => 'badge-warning'
        ];
    }
}

$query = mysqli_query($conn, "SELECT * FROM peminjaman ORDER BY id DESC");
$daftar_pinjam = [];
$total_aktif = 0;
$total_telat = 0;
$akumulasi_denda = 0;

while ($row = mysqli_fetch_assoc($query)) {
    $info_denda = hitungDenda($row['tgl_kembali'], $row['status']);
    $row['info_denda'] = $info_denda;

    if ($row['status'] === 'Dipinjam') {
        $total_aktif++;
    }
    if ($info_denda['denda'] > 0) {
        $total_telat++;
        $akumulasi_denda += $info_denda['denda'];
    }

    $daftar_pinjam[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Perpustakaan - PHP Native</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="<?= (isset($_COOKIE['tema']) && $_COOKIE['tema'] === 'dark') ? 'dark-mode' : '' ?>">
    <nav class="navbar">
        <div class="nav-brand">Sistem Peminjaman Perpustakaan</div>
        <div class="nav-actions">
            <span>Petugas: <b><?= htmlspecialchars($_SESSION['admin']) ?></b></span>
            <button id="btnTema" class="btn btn-secondary">Ganti Tema</button>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </nav>

    <div class="container">
        <p class="cookie-info">Cookie Kunjungan Terakhir: <b><?= htmlspecialchars($last_visit) ?></b></p>

        <?php if (isset($_SESSION['notif'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['notif']); unset($_SESSION['notif']); ?>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="card stat-card">
                <h4>Total Transaksi</h4>
                <p><?= count($daftar_pinjam) ?> Data</p>
            </div>
            <div class="card stat-card">
                <h4>Sedang Dipinjam</h4>
                <p><?= $total_aktif ?> Buku</p>
            </div>
            <div class="card stat-card">
                <h4>Peminjam Terlambat</h4>
                <p><?= $total_telat ?> Orang</p>
            </div>
            <div class="card stat-card">
                <h4>Total Denda Berjalan</h4>
                <p>Rp <?= number_format($akumulasi_denda, 0, ',', '.') ?></p>
            </div>
        </div>

        <div class="card">
            <div class="table-header">
                <a href="tambah.php" class="btn btn-primary">+ Tambah Peminjaman</a>
                <input type="text" id="searchInput" placeholder="Cari judul buku atau nama peminjam (DOM)...">
            </div>

            <div class="table-responsive">
                <table id="tabelPinjam">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Judul Buku</th>
                            <th>Peminjam</th>
                            <th>Tgl Pinjam</th>
                            <th>Jatuh Tempo</th>
                            <th>Status Algoritma</th>
                            <th>Denda</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($daftar_pinjam)): ?>
                            <tr><td colspan="9" class="text-center">Belum ada data peminjaman.</td></tr>
                        <?php else: ?>
                            <?php foreach ($daftar_pinjam as $i => $item): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($item['kode_buku']) ?></td>
                                <td><?= htmlspecialchars($item['judul_buku']) ?></td>
                                <td><?= htmlspecialchars($item['nama_peminjam']) ?></td>
                                <td><?= htmlspecialchars($item['tgl_pinjam']) ?></td>
                                <td><?= htmlspecialchars($item['tgl_kembali']) ?></td>
                                <td>
                                    <span class="badge <?= $item['info_denda']['badge'] ?>">
                                        <?= $item['info_denda']['keterangan'] ?>
                                    </span>
                                </td>
                                <td>Rp <?= number_format($item['info_denda']['denda'], 0, ',', '.') ?></td>
                                <td>
                                    <a href="edit.php?id=<?= $item['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="index.php?hapus=<?= $item['id'] ?>" class="btn btn-danger btn-sm btn-hapus">Hapus</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>