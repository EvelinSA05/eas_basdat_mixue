<?php
include '../koneksi.php';

if (!isset($_GET['id_order'])) {
    echo "<p>ID Order tidak ditemukan. Silakan kembali ke halaman sebelumnya.</p>";
    exit;
}

$id_order = (int)$_GET['id_order'];

// Ambil data order
$stmtOrder = $conn->prepare("SELECT * FROM `order` WHERE id_order = ?");
$stmtOrder->bind_param("i", $id_order);
$stmtOrder->execute();
$order = $stmtOrder->get_result()->fetch_assoc();
$stmtOrder->close();

if (!$order) {
    echo "<p>Order tidak ditemukan di database.</p>";
    exit;
}

$total_pembayaran = $order['total_pembayaran'];
$error = '';
$metode = '';
$uang_bayar = '';

if (isset($_POST['submit'])) {
    $metode = $_POST['metode_pembayaran'];
    $uang_bayar = trim($_POST['uang_pembayaran']);

    // Validasi input uang pembayaran
    if ($uang_bayar === '') {
        $error = "Uang pembayaran harus diisi.";
    } elseif (!is_numeric($uang_bayar)) {
        $error = "Uang pembayaran harus berupa angka.";
    } elseif ((float)$uang_bayar < $total_pembayaran) {
        $error = "Uang pembayaran kurang dari total pembayaran.";
    } else {
        $uang_bayar = (float)$uang_bayar;
        $kembalian = $uang_bayar - $total_pembayaran;

        // Update order pembayaran
        $stmtUpdateOrder = $conn->prepare("UPDATE `order` SET metode_pembayaran = ?, uang_pembayaran = ?, uang_kembalian = ? WHERE id_order = ?");
        $stmtUpdateOrder->bind_param("sdii", $metode, $uang_bayar, $kembalian, $id_order);
        $stmtUpdateOrder->execute();
        $stmtUpdateOrder->close();

        // Kurangi stok produk dan bahan atribut
        $stmtOrderDetails = $conn->prepare("SELECT id_order_detail, id_produk, kuantitas FROM order_detail WHERE id_order = ?");
        $stmtOrderDetails->bind_param("i", $id_order);
        $stmtOrderDetails->execute();
        $resultOrderDetails = $stmtOrderDetails->get_result();

        while ($detail = $resultOrderDetails->fetch_assoc()) {
            $id_order_detail = (int)$detail['id_order_detail'];
            $id_produk = (int)$detail['id_produk'];
            $qty = (int)$detail['kuantitas'];

            // Kurangi stok produk
            $stmtProd = $conn->prepare("SELECT stok FROM produk WHERE id_produk = ?");
            $stmtProd->bind_param("i", $id_produk);
            $stmtProd->execute();
            $stokProduk = $stmtProd->get_result()->fetch_assoc()['stok'];
            $stmtProd->close();

            $stokBaruProduk = max(0, $stokProduk - $qty);

            $stmtUpdateProd = $conn->prepare("UPDATE produk SET stok = ? WHERE id_produk = ?");
            $stmtUpdateProd->bind_param("ii", $stokBaruProduk, $id_produk);
            $stmtUpdateProd->execute();
            $stmtUpdateProd->close();

            // Kurangi stok bahan atribut berdasarkan topping, sugar, dan ice
            // Ambil id atribut dari order_detail_atribut
            $stmtAttr = $conn->prepare("SELECT id_topping, id_sugar, id_ice FROM order_detail_atribut WHERE id_order_detail = ?");
            $stmtAttr->bind_param("i", $id_order_detail);
            $stmtAttr->execute();
            $atribut = $stmtAttr->get_result()->fetch_assoc();
            $stmtAttr->close();

            // Fungsi update stok bahan atribut
            function updateStokBahanAtribut($conn, $id_atribut, $qty, $tabel_atribut, $kolom_id_atribut) {
                if ($id_atribut === null || $id_atribut == 0) return;

                // Dapatkan id_bahan_atribut dan jumlah_gram dari atribut yang dipilih
                $stmt = $conn->prepare("SELECT id_bahan_atribut, jumlah_gram FROM $tabel_atribut WHERE $kolom_id_atribut = ?");
                $stmt->bind_param("i", $id_atribut);
                $stmt->execute();
                $data = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if (!$data) return;

                $id_bahan_atribut = (int)$data['id_bahan_atribut'];
                $jumlah_gram = (float)$data['jumlah_gram'];

                // Ambil stok bahan atribut saat ini
                $stmtStok = $conn->prepare("SELECT stok FROM bahan_atribut WHERE id_bahan_atribut = ?");
                $stmtStok->bind_param("i", $id_bahan_atribut);
                $stmtStok->execute();
                $stokSekarang = $stmtStok->get_result()->fetch_assoc()['stok'];
                $stmtStok->close();

                // Hitung stok baru
                $stokBaru = $stokSekarang - ($jumlah_gram * $qty);
                if ($stokBaru < 0) $stokBaru = 0;

                // Update stok bahan atribut
                $stmtUpdate = $conn->prepare("UPDATE bahan_atribut SET stok = ? WHERE id_bahan_atribut = ?");
                $stmtUpdate->bind_param("di", $stokBaru, $id_bahan_atribut);
                $stmtUpdate->execute();
                $stmtUpdate->close();
            }

            // Update stok bahan atribut topping
            updateStokBahanAtribut($conn, $atribut['id_topping'], $qty, 'topping_atribut', 'id_topping');
            // Update stok bahan atribut sugar
            updateStokBahanAtribut($conn, $atribut['id_sugar'], $qty, 'sugar_atribut', 'id_sugar');
            // Update stok bahan atribut ice
            updateStokBahanAtribut($conn, $atribut['id_ice'], $qty, 'ice_atribut', 'id_ice');
        }
        $stmtOrderDetails->close();

        // Tampilkan halaman sukses pembayaran
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Pembayaran Berhasil - Mixue</title>
            <link rel="stylesheet" href="../style.css">
        </head>
        <body>
            <div class="sidebar">
                <button class="toggle-btn" onclick="document.querySelector('.sidebar').classList.toggle('collapsed')">☰</button>
                <h2 class="logo">Mixue</h2>
                <a href="../Kategori/kategori.php">Kategori</a>
                <a href="../Produk/produk.php">Produk</a>
                <a href="order.php">Order</a>
                <a href="order_detail.php">Order Detail</a>
                <a href="../Atribut/bahan_atribut.php">Bahan Atribut</a>
                <a href="../Atribut/ice_atribut.php">Ice Atribut</a>
                <a href="../Atribut/sugar_atribut.php">Sugar Atribut</a>
                <a href="../Atribut/topping_atribut.php">Topping Atribut</a>
                <a href="../Role/kasir.php">Kasir</a>
            </div>

            <div class="main">
                <div class="card">
                    <div class="success-icon">✅</div>
                    <h1>Pembayaran Berhasil!</h1>
                    <p class="info"><strong>ID Order:</strong> <?= htmlspecialchars($id_order) ?></p>
                    <p class="info"><strong>Kembalian:</strong> Rp <?= number_format($kembalian, 0, ',', '.') ?></p>

                    <h2>Detail Pesanan</h2>
                    <table border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse; width: 100%; max-width: 800px;">
                        <thead style="background-color: #f0f0f0; text-align: left;">
                            <tr>
                                <th>Nama Produk</th>
                                <th>Kuantitas</th>
                                <th>Subtotal</th>
                                <th>Topping</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmtItems = $conn->prepare("SELECT od.id_order_detail, p.nama_produk, od.kuantitas, od.sub_total
                                FROM order_detail od
                                JOIN produk p ON od.id_produk = p.id_produk
                                WHERE od.id_order = ?");
                            $stmtItems->bind_param("i", $id_order);
                            $stmtItems->execute();
                            $items = $stmtItems->get_result();

                            while ($item = $items->fetch_assoc()):
                                $id_order_detail = (int)$item['id_order_detail'];
                                $stmtToppings = $conn->prepare("SELECT ta.nama_topping, ta.harga_topping 
                                    FROM order_detail_atribut oda
                                    JOIN topping_atribut ta ON oda.id_topping = ta.id_topping
                                    WHERE oda.id_order_detail = ?");
                                $stmtToppings->bind_param("i", $id_order_detail);
                                $stmtToppings->execute();
                                $toppings = $stmtToppings->get_result();
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['nama_produk']) ?></td>
                                    <td><?= (int)$item['kuantitas'] ?></td>
                                    <td>Rp <?= number_format($item['sub_total'], 0, ',', '.') ?></td>
                                    <td>
                                        <?php if ($toppings->num_rows > 0): ?>
                                            <ul style="padding-left: 20px; margin: 0;">
                                                <?php while ($top = $toppings->fetch_assoc()): ?>
                                                    <li><?= htmlspecialchars($top['nama_topping']) ?> (Rp <?= number_format($top['harga_topping'], 0, ',', '.') ?>)</li>
                                                <?php endwhile; ?>
                                            </ul>
                                        <?php else: ?>
                                            Tidak ada
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php 
                                $stmtToppings->close();
                            endwhile; 
                            $stmtItems->close();
                            ?>
                        </tbody>
                    </table>

                    <a href="order.php" class="btn">⬅ Kembali ke Order</a>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Konfirmasi Pembayaran - Mixue</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="sidebar">
        <button class="toggle-btn" onclick="document.querySelector('.sidebar').classList.toggle('collapsed')">☰</button>
        <h2 class="logo">Mixue</h2>
        <a href="../Kategori/kategori.php">Kategori</a>
        <a href="../Produk/produk.php">Produk</a>
        <a href="order.php">Order</a>
        <a href="order_detail.php">Order Detail</a>
        <a href="../Atribut/bahan_atribut.php">Bahan Atribut</a>
        <a href="../Atribut/ice_atribut.php">Ice Atribut</a>
        <a href="../Atribut/sugar_atribut.php">Sugar Atribut</a>
        <a href="../Atribut/topping_atribut.php">Topping Atribut</a>
        <a href="../Role/kasir.php">Kasir</a>
    </div>

    <div class="main">
        <h1>Konfirmasi Pembayaran</h1>
        <?php if ($error): ?>
            <p style="color: red; font-weight: bold;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST">
            <p>Total Pembayaran: <strong>Rp. <?= number_format($total_pembayaran, 0, ',', '.') ?></strong></p>

            <label>Metode Pembayaran:</label><br>
            <select name="metode_pembayaran" required>
                <option value="Cash" <?= ($metode === 'Cash') ? 'selected' : '' ?>>Cash</option>
                <option value="Qris" <?= ($metode === 'Qris') ? 'selected' : '' ?>>Qris</option>
            </select><br><br>

            <label>Uang Pembayaran:</label><br>
            <input type="text" name="uang_pembayaran" value="<?= htmlspecialchars($uang_bayar) ?>" required><br><br>

            <button type="submit" name="submit">Bayar</button>
        </form>
    </div>
</body>
</html>
