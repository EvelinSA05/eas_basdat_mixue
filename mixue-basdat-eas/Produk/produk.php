<?php include '../koneksi.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Produk Mixue</title>
    <link rel="stylesheet" href="../style.css">
    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('collapsed');
        }
        function resetForm() {
            document.getElementById('id_produk').value = '';
            document.getElementById('id_kategori').value = '';
            document.getElementById('nama_produk').value = '';
            document.getElementById('harga_satuan').value = '';
            document.getElementById('stok').value = '';
        }
    </script>
</head>

<body>
    <div class="sidebar">
        <button class="toggle-btn" onclick="toggleSidebar()">☰</button>
        <h2 class="logo">Mixue</h2>
        <a href="../Kategori/kategori.php">Kategori</a>
        <a href="produk.php">Produk</a>
        <a href="../Order/order.php">Order</a>
        <a href="../Order/order_detail.php">Order Detail</a>
        <a href="../Atribut/bahan_atribut.php">Bahan Atribut</a>
        <a href="../Atribut/ice_atribut.php">Ice Atribut</a>
        <a href="../Atribut/sugar_atribut.php">Sugar Atribut</a>
        <a href="../Atribut/topping_atribut.php">Topping Atribut</a>
        <a href="../Role/kasir.php">Kasir</a>
    </div>

    <div class="main">
        <h1>Data Produk</h1>

        <div class="form-section">
            <form method="POST">
                <input type="hidden" name="id_produk" id="id_produk">
                <label>Kategori:</label><br>
                <select name="id_kategori" id="id_kategori" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php
                    $kategori = $conn->query("SELECT * FROM kategori");
                    while ($row = $kategori->fetch_assoc()) {
                        echo "<option value='{$row['id_kategori']}'>{$row['nama_kategori']}</option>";
                    }
                    ?>
                </select><br>

                <label>Nama Produk:</label><br>
                <input type="text" name="nama_produk" id="nama_produk" required><br>

                <label>Harga Satuan:</label><br>
                <input type="number" name="harga_satuan" id="harga_satuan" step="0.01" required><br>

                <label>Stok:</label><br>
                <input type="number" name="stok" id="stok" required><br>

                <button type="submit" name="simpan">Simpan</button>
                <button type="button" onclick="resetForm()">Reset</button>
            </form>
        </div>

        <table>
            <tr>
                <th>ID</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th>Harga Satuan</th>
                <th>Stok</th>
                <th>Aksi</th>
            </tr>
            <?php
            // Simpan data
            if (isset($_POST['simpan'])) {
                $id = $_POST['id_produk'];
                $id_kategori = $_POST['id_kategori'];
                $nama = $_POST['nama_produk'];
                $harga = $_POST['harga_satuan'];
                $stok = $_POST['stok'];

                if ($id == '') {
                    $conn->query("INSERT INTO produk (id_kategori, nama_produk, harga_satuan, stok) 
                        VALUES ($id_kategori, '$nama', $harga, $stok)");
                } else {
                    $conn->query("UPDATE produk SET 
                        id_kategori=$id_kategori, 
                        nama_produk='$nama', 
                        harga_satuan=$harga, 
                        stok=$stok 
                        WHERE id_produk=$id");
                }

                header("Location: produk.php");
                exit;
            }

            // Hapus produk
            if (isset($_GET['hapus'])) {
                $conn->query("DELETE FROM produk WHERE id_produk=" . $_GET['hapus']);
                header("Location: produk.php");
                exit;
            }

            // Tampilkan data
            $produk = $conn->query("SELECT p.*, k.nama_kategori 
                                    FROM produk p
                                    JOIN kategori k ON p.id_kategori = k.id_kategori
                                    ORDER BY p.id_produk ASC");
            while ($row = $produk->fetch_assoc()) {
                echo "<tr>
                    <td>{$row['id_produk']}</td>
                    <td>{$row['nama_produk']}</td>
                    <td>{$row['nama_kategori']}</td>
                    <td>Rp " . number_format($row['harga_satuan']) . "</td>
                    <td>{$row['stok']}</td>
                    <td>
                        <a href='?edit={$row['id_produk']}'>Edit</a> |
                        <a href='?hapus={$row['id_produk']}' onclick=\"return confirm('Yakin hapus?')\">Hapus</a>
                    </td>
                </tr>";
            }

            // Edit mode
            if (isset($_GET['edit'])) {
                $id = $_GET['edit'];
                $res = $conn->query("SELECT * FROM produk WHERE id_produk=$id");
                $data = $res->fetch_assoc();
                echo "<script>
                    document.getElementById('id_produk').value = '{$data['id_produk']}';
                    document.getElementById('id_kategori').value = '{$data['id_kategori']}';
                    document.getElementById('nama_produk').value = '{$data['nama_produk']}';
                    document.getElementById('harga_satuan').value = '{$data['harga_satuan']}';
                    document.getElementById('stok').value = '{$data['stok']}';
                </script>";
            }
            ?>
        </table>
    </div>
</body>

</html>
