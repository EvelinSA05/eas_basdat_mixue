<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Produk Mixue</title>
    <link rel="stylesheet" href="../style.css">
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('collapsed');
        }
    </script>
</head>

<body>
    <div class="sidebar">
        <button class="toggle-btn" onclick="toggleSidebar()">☰</button>
        <h2 class="logo">Mixue</h2>
        <a href="../Kategori/kategori.php">Kategori</a>
        <a href="../Produk/produk.php">Produk</a>
        <a href="../Order/order.php">Order</a>
        <a href="../Order/order_detail.php">Order Detail</a>
        <a href="bahan_atribut.php">Bahan Atribut</a>
        <a href="ice_atribut.php">Ice Atribut</a>
        <a href="sugar_atribut.php">Sugar Atribut</a>
        <a href="topping_atribut.php">Topping Atribut</a>
        <a href="../Role/kasir.php">Kasir</a>
    </div>

    <div class="main">
        <h1>Bahan Atribut</h1>

        <div class="form-section">
            <form method="POST">
                <input type="hidden" name="id_bahan_atribut" id="id_bahan_atribut">
                <label>Nama Bahan:</label><br>
                <input type="text" name="nama_bahan" id="nama_bahan" required><br>
                <label>Satuan:</label><br>
                <input type="text" name="satuan" id="satuan" required><br>
                <label>Stok:</label><br>
                <input type="number" name="stok" id="stok" step="0.01" required><br>
                <button type="submit" name="simpan">Simpan</button>
                <button type="button" onclick="resetForm()">Reset</button>
                <script>
                    function resetForm() {
                        document.getElementById('id_bahan_atribut').value = '';
                        document.getElementById('nama_bahan').value = '';
                        document.getElementById('satuan').value = '';
                        document.getElementById('stok').value = '';
                    }
                </script>

            </form>

        </div>

        <table>
            <tr>
                <th>ID</th>
                <th>Nama Bahan Atribut</th>
                <th>Satuan</th>
                <th>Stok</th>
                <th>Aksi</th>
            </tr>
            <?php
            include '../koneksi.php';

            if (isset($_POST['simpan'])) {
                $id = $_POST['id_bahan_atribut'];
                $nama = $_POST['nama_bahan'];
                $satuan = $_POST['satuan'];
                $stok = $_POST['stok'];

                if ($id == '') {
                    $conn->query("INSERT INTO bahan_atribut (nama_bahan, satuan, stok) VALUES ('$nama', '$satuan', $stok)");
                } else {
                    $conn->query("UPDATE bahan_atribut SET nama_bahan='$nama', satuan='$satuan', stok=$stok WHERE id_bahan_atribut=$id");
                }

                // MENGHINDARI DUPLIKASI SAAT REFRESH
                header("Location: bahan_atribut.php");
                exit;
            }


            if (isset($_GET['hapus'])) {
                $conn->query("DELETE FROM bahan_atribut WHERE id_bahan_atribut=" . $_GET['hapus']);
            }

            $produk = $conn->query("SELECT * FROM bahan_atribut ORDER BY id_bahan_atribut ASC");
            while ($row = $produk->fetch_assoc()) {
                echo "<tr>
                <td>{$row['id_bahan_atribut']}</td>
                <td>{$row['nama_bahan']}</td>
                <td>{$row['satuan']}</td>
                <td>{$row['stok']}</td>
                <td>
                  <a href='?edit={$row['id_bahan_atribut']}'>Edit</a> |
                  <a href='?hapus={$row['id_bahan_atribut']}' onclick=\"return confirm('Yakin hapus?')\">Hapus</a>
                </td>
              </tr>";
            }

            if (isset($_GET['edit'])) {
                $id = $_GET['edit'];
                $res = $conn->query("SELECT * FROM bahan_atribut WHERE id_bahan_atribut=$id");
                $data = $res->fetch_assoc();
                echo "<script>
          document.getElementById('id_bahan_atribut').value = '{$data['id_bahan_atribut']}';
          document.getElementById('nama_bahan').value = '{$data['nama_bahan']}';
          document.getElementById('satuan').value = '{$data['satuan']}';
          document.getElementById('stok').value = '{$data['stok']}';
        </script>";
            }
            ?>
        </table>
    </div>
</body>

</html>