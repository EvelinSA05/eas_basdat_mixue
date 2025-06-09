<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>CRUD Topping Atribut Mixue</title>
    <link rel="stylesheet" href="../style.css" />
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('collapsed');
        }

        function resetForm() {
            document.getElementById('id_topping').value = '';
            document.getElementById('nama_topping').value = '';
            document.getElementById('jumlah_gram').value = '';
            document.getElementById('harga_topping').value = '';
            document.getElementById('id_bahan_atribut').value = '';
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
        <h1>Topping Atribut</h1>

        <div class="form-section">
            <form method="POST">
                <input type="hidden" name="id_topping" id="id_topping" />
                <label>Nama Topping:</label><br />
                <input type="text" name="nama_topping" id="nama_topping" required /><br />

                <label>Jumlah (Gram):</label><br />
                <input type="number" name="jumlah_gram" id="jumlah_gram" required /><br />

                <label>Harga:</label><br />
                <input type="number" name="harga_topping" id="harga_topping" required /><br />

                <label>Bahan Atribut (untuk stok):</label><br />
                <select name="id_bahan_atribut" id="id_bahan_atribut" required>
                    <option value="">-- Pilih Bahan --</option>
                    <?php
                    include '../koneksi.php';
                    $bahan = $conn->query("SELECT * FROM bahan_atribut ORDER BY nama_bahan ASC");
                    while ($rowBahan = $bahan->fetch_assoc()) {
                        echo "<option value='{$rowBahan['id_bahan_atribut']}'>{$rowBahan['nama_bahan']} ({$rowBahan['stok']} {$rowBahan['satuan']})</option>";
                    }
                    ?>
                </select><br /><br />

                <button type="submit" name="simpan">Simpan</button>
                <button type="button" onclick="resetForm()">Reset</button>
            </form>
        </div>

        <table>
            <tr>
                <th>ID</th>
                <th>Nama Topping</th>
                <th>Jumlah Gram</th>
                <th>Harga</th>
                <th>Bahan</th>
                <th>Aksi</th>
            </tr>
            <?php
            if (isset($_POST['simpan'])) {
                $id = $_POST['id_topping'];
                $nama = $_POST['nama_topping'];
                $jumlah = $_POST['jumlah_gram'];
                $harga = $_POST['harga_topping'];
                $id_bahan = $_POST['id_bahan_atribut'];

                if ($id == '') {
                    $conn->query("INSERT INTO topping_atribut (nama_topping, jumlah_gram, harga_topping, id_bahan_atribut) VALUES ('$nama', $jumlah, $harga, $id_bahan)");
                } else {
                    $conn->query("UPDATE topping_atribut SET nama_topping='$nama', jumlah_gram=$jumlah, harga_topping=$harga, id_bahan_atribut=$id_bahan WHERE id_topping=$id");
                }

                header("Location: topping_atribut.php");
                exit;
            }

            if (isset($_GET['hapus'])) {
                $conn->query("DELETE FROM topping_atribut WHERE id_topping=" . $_GET['hapus']);
                header("Location: topping_atribut.php");
                exit;
            }

            $result = $conn->query("SELECT ta.*, ba.nama_bahan FROM topping_atribut ta LEFT JOIN bahan_atribut ba ON ta.id_bahan_atribut = ba.id_bahan_atribut ORDER BY ta.id_topping ASC");
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                <td>{$row['id_topping']}</td>
                <td>{$row['nama_topping']}</td>
                <td>{$row['jumlah_gram']}</td>
                <td>{$row['harga_topping']}</td>
                <td>{$row['nama_bahan']}</td>
                <td>
                  <a href='?edit={$row['id_topping']}'>Edit</a> |
                  <a href='?hapus={$row['id_topping']}' onclick=\"return confirm('Yakin hapus?')\">Hapus</a>
                </td>
              </tr>";
            }

            if (isset($_GET['edit'])) {
                $id = $_GET['edit'];
                $res = $conn->query("SELECT * FROM topping_atribut WHERE id_topping=$id");
                $data = $res->fetch_assoc();

                echo "<script>
                    document.getElementById('id_topping').value = '{$data['id_topping']}';
                    document.getElementById('nama_topping').value = '{$data['nama_topping']}';
                    document.getElementById('jumlah_gram').value = '{$data['jumlah_gram']}';
                    document.getElementById('harga_topping').value = '{$data['harga_topping']}';
                    document.getElementById('id_bahan_atribut').value = '{$data['id_bahan_atribut']}';
                </script>";
            }
            ?>
        </table>
    </div>
</body>

</html>