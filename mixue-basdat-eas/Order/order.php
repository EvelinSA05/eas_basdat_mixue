<?php
include '../koneksi.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Order - Mixue</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body>
    <div class="sidebar">
        <button class="toggle-btn" onclick="toggleSidebar()">☰</button>
        <h2 class="logo">Mixue</h2>
        <a href="../Kategori/kategori.php">Kategori</a>
        <a href="../Produk/produk.php">Produk</a>
        <a href="../Order/order.php">Order</a>
        <a href="order_detail.php">Order Detail</a>
        <a href="../Atribut/bahan_atribut.php">Bahan Atribut</a>
        <a href="../Atribut/ice_atribut.php">Ice Atribut</a>
        <a href="../Atribut/sugar_atribut.php">Sugar Atribut</a>
        <a href="../Atribut/topping_atribut.php">Topping Atribut</a>
        <a href="../Role/kasir.php">Kasir</a>
    </div>

    <div class="main">
        <h1>Order - Langkah 1</h1>
        <form method="POST">
            <label>Kasir:</label><br>
            <select name="id_kasir" required>
                <?php
                $kasir = $conn->query("SELECT * FROM kasir");
                while ($k = $kasir->fetch_assoc()) {
                    echo "<option value='{$k['id_kasir']}'>{$k['nama_kasir']}</option>";
                }

                // Produk dan kategori
                $kategoriOptionsHTML = "";
                $kategori = $conn->query("SELECT * FROM kategori");
                while ($k = $kategori->fetch_assoc()) {
                    $kategoriOptionsHTML .= "<option value='{$k['id_kategori']}'>{$k['nama_kategori']}</option>";
                }

                $produkKategoriMap = [];
                $produk = $conn->query("SELECT * FROM produk");
                while ($p = $produk->fetch_assoc()) {
                    $id_kat = $p['id_kategori'];
                    $produkKategoriMap[$id_kat][] = [
                        "id_produk" => $p['id_produk'],
                        "nama_produk" => $p['nama_produk'],
                        "harga_satuan" => $p['harga_satuan']
                    ];
                }

                $toppingOptions = "";
                $toppingResult = $conn->query("SELECT * FROM topping_atribut");
                while ($row = $toppingResult->fetch_assoc()) {
                    $toppingOptions .= "<option value='{$row['id_topping']}' data-harga='{$row['harga_topping']}'>{$row['nama_topping']}</option>";
                }

                $iceOptions = "";
                $iceResult = $conn->query("SELECT * FROM ice_atribut");
                while ($row = $iceResult->fetch_assoc()) {
                    $iceOptions .= "<option value='{$row['id_ice']}'>{$row['level_ice']}</option>";
                }

                $sugarOptions = "";
                $sugarResult = $conn->query("SELECT * FROM sugar_atribut");
                while ($row = $sugarResult->fetch_assoc()) {
                    $sugarOptions .= "<option value='{$row['id_sugar']}'>{$row['level_gula']}</option>";
                }
                ?>
            </select><br>

            <label>Order Type:</label><br>
            <select name="order_type" required>
                <option value="Dine in">Dine in</option>
                <option value="Take away">Take away</option>
            </select><br>

            <label>Tanggal:</label><br>
            <input type="date" name="tanggal" required><br>
            <label>Waktu:</label><br>
            <input type="time" name="waktu" required><br>

            <h3>Produk</h3>
            <div id="produk-container"></div>
            <button type="button" onclick="addProductRow()">+ Tambah Produk</button><br>

            <p>Total Kuantitas: <span id="total_kuantitas">0</span></p>
            <p>Total Pembayaran: <span id="total_pembayaran">Rp0</span></p>

            <button type="submit" name="lanjut">Lanjut ke Pembayaran</button>
        </form>

        <?php
        if (isset($_POST['lanjut'])) {
            $id_kasir = $_POST['id_kasir'];
            $order_type = $_POST['order_type'];
            $tanggal = $_POST['tanggal'];
            $waktu = $_POST['waktu'];
            $produk_ids = $_POST['produk'];
            $kuantitas = $_POST['kuantitas'];
            $toppings = $_POST['topping'];
            $ices = $_POST['ice'];
            $sugars = $_POST['sugar'];

            $total_kuantitas = 0;
            $total_pembayaran = 0;

            foreach ($produk_ids as $i => $id_produk) {
                $q = (int)$kuantitas[$i];
                $harga_produk = (int)$conn->query("SELECT harga_satuan FROM produk WHERE id_produk=$id_produk")->fetch_assoc()['harga_satuan'];

                $harga_topping = 0;
                if (!empty($toppings[$i])) {
                    $harga_topping = (int)$conn->query("SELECT harga_topping FROM topping_atribut WHERE id_topping={$toppings[$i]}")->fetch_assoc()['harga_topping'];
                }

                $subtotal = ($harga_produk * $q) + $harga_topping;
                $total_kuantitas += $q;
                $total_pembayaran += $subtotal;
            }

            $conn->query("INSERT INTO `order` (id_kasir, order_type, tanggal_pemesanan, waktu_pemesanan, total_kuantitas, total_pembayaran, metode_pembayaran, uang_pembayaran, uang_kembalian)
                VALUES ($id_kasir, '$order_type', '$tanggal', '$waktu', $total_kuantitas, $total_pembayaran, '', 0, 0)");
            $id_order = $conn->insert_id;

            foreach ($produk_ids as $i => $id_produk) {
                $q = (int)$kuantitas[$i];
                $harga_produk = (int)$conn->query("SELECT harga_satuan FROM produk WHERE id_produk=$id_produk")->fetch_assoc()['harga_satuan'];

                $harga_topping = 0;
                if (!empty($toppings[$i])) {
                    $harga_topping = (int)$conn->query("SELECT harga_topping FROM topping_atribut WHERE id_topping={$toppings[$i]}")->fetch_assoc()['harga_topping'];
                }

                $subtotal = ($harga_produk * $q) + $harga_topping;

                $conn->query("INSERT INTO order_detail (id_order, id_produk, kuantitas, sub_total)
                    VALUES ($id_order, $id_produk, $q, $subtotal)");
                $id_detail = $conn->insert_id;

                $id_topping = $toppings[$i] ?: 'NULL';
                $id_ice = $ices[$i] ?: 'NULL';
                $id_sugar = $sugars[$i] ?: 'NULL';

                if ($id_topping != 'NULL' || $id_ice != 'NULL' || $id_sugar != 'NULL') {
                    $conn->query("INSERT INTO order_detail_atribut (id_order_detail, id_topping, id_ice, id_sugar)
                        VALUES ($id_detail, $id_topping, $id_ice, $id_sugar)");
                }
            }

            header("Location: order_detail.php?id_order=$id_order");
            exit;
        }
        ?>
    </div>

    <script>
        const produkKategoriMap = <?= json_encode($produkKategoriMap) ?>;
        const kategoriOptionsHTML = `<?= $kategoriOptionsHTML ?>`;
        const toppingOptionsHTML = `<?= $toppingOptions ?>`;
        const iceOptionsHTML = `<?= $iceOptions ?>`;
        const sugarOptionsHTML = `<?= $sugarOptions ?>`;

        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('collapsed');
        }

        function addProductRow() {
            const container = document.getElementById('produk-container');
            const row = document.createElement('div');
            row.classList.add('produk-row');

            const kategoriSelect = document.createElement('select');
            kategoriSelect.innerHTML = `<option value="">-- Pilih Kategori --</option>` + kategoriOptionsHTML;
            kategoriSelect.onchange = function () {
                const selectedKategori = this.value;
                const produkSelect = row.querySelector('.produk-select');
                let optionsHTML = '<option value="">-- Pilih Produk --</option>';
                if (produkKategoriMap[selectedKategori]) {
                    produkKategoriMap[selectedKategori].forEach(p => {
                        optionsHTML += `<option value="${p.id_produk}" data-harga="${p.harga_satuan}">${p.nama_produk}</option>`;
                    });
                }
                produkSelect.innerHTML = optionsHTML;
                updateTotal();
            };

            const produkSelect = document.createElement('select');
            produkSelect.name = "produk[]";
            produkSelect.required = true;
            produkSelect.classList.add("produk-select");
            produkSelect.onchange = updateTotal;

            const qtyInput = document.createElement('input');
            qtyInput.type = 'number';
            qtyInput.name = 'kuantitas[]';
            qtyInput.placeholder = 'Kuantitas';
            qtyInput.required = true;
            qtyInput.oninput = updateTotal;

            const toppingSelect = document.createElement('select');
            toppingSelect.name = 'topping[]';
            toppingSelect.innerHTML = `<option value="">No Topping</option>` + toppingOptionsHTML;
            toppingSelect.onchange = updateTotal;

            const iceSelect = document.createElement('select');
            iceSelect.name = 'ice[]';
            iceSelect.innerHTML = iceOptionsHTML;

            const sugarSelect = document.createElement('select');
            sugarSelect.name = 'sugar[]';
            sugarSelect.innerHTML = sugarOptionsHTML;

            const subtotalSpan = document.createElement('span');
            subtotalSpan.classList.add('subtotal');

            const deleteBtn = document.createElement('button');
            deleteBtn.type = 'button';
            deleteBtn.innerText = 'Hapus';
            deleteBtn.onclick = () => {
                row.remove();
                updateTotal();
            };

            row.appendChild(document.createTextNode('Kategori: '));
            row.appendChild(kategoriSelect);
            row.appendChild(document.createTextNode(' Produk: '));
            row.appendChild(produkSelect);
            row.appendChild(qtyInput);
            row.appendChild(toppingSelect);
            row.appendChild(iceSelect);
            row.appendChild(sugarSelect);
            row.appendChild(subtotalSpan);
            row.appendChild(deleteBtn);
            row.appendChild(document.createElement('br'));

            container.appendChild(row);
        }

        function updateTotal() {
            const rows = document.querySelectorAll('.produk-row');
            let totalKuantitas = 0;
            let totalPembayaran = 0;

            rows.forEach(row => {
                const kuantitasInput = row.querySelector('input[name="kuantitas[]"]');
                const selectProduk = row.querySelector('select[name="produk[]"]');
                const toppingSelect = row.querySelector('select[name="topping[]"]');

                const hargaProduk = parseFloat(selectProduk?.selectedOptions[0]?.dataset.harga || 0);
                const hargaTopping = parseFloat(toppingSelect?.selectedOptions[0]?.dataset.harga || 0);
                const kuantitas = parseInt(kuantitasInput?.value) || 0;

                const subtotal = (hargaProduk * kuantitas) + hargaTopping;
                row.querySelector('.subtotal').innerText = ` Subtotal: Rp${subtotal}`;
                totalKuantitas += kuantitas;
                totalPembayaran += subtotal;
            });

            document.getElementById('total_kuantitas').innerText = totalKuantitas;
            document.getElementById('total_pembayaran').innerText = `Rp${totalPembayaran}`;
        }
    </script>
</body>

</html>
