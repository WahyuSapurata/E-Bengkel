<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') . ' | ' . $module }}</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/favicon.ico') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/sweet-alert/sweetalert2.min.css') }}">
</head>

<body>

    <form id="form-kasir">
        <div class="container-fluid">
            <div class="row">
                <!-- Sidebar kiri -->
                <div class="col-md-2 sidebar p-3 d-flex flex-column min-vh-100">
                    <div>
                        <div class="text-center mb-3">
                            <h6 class="fw-bold text-uppercase">{{ $data_outlet->nama_outlet }}</h6>
                            <small>{{ $data_outlet->alamat }}</small>
                        </div>

                        <div class="text-center mb-3">
                            <h4 class="bg-success text-white p-2 rounded fs-2">{{ $nomor_urut }}</h4>
                            <p class="fw-bold">KASIR<br>{{ auth()->user()->nama }}</p>
                        </div>

                        <h6 class="fw-bold">PRODUK TERPILIH</h6>

                        <!-- Scroll area produk -->
                        <div class="produk-terpilih overflow-auto" style="max-height: 350px;">
                            <!-- Produk terpilih akan muncul di sini -->
                        </div>
                    </div>

                    <!-- Tombol tetap di bawah -->
                    <div class="d-grid gap-2 mt-auto">
                        <button onclick="window.location.href = '{{ route('logout') }}'"
                            class="btn btn-outline-danger btn-sm">📤 Keluar</button>
                        <button class="btn btn-outline-secondary btn-sm">📦 Stok Barang</button>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-md-7 p-3 d-flex flex-column min-vh-100">
                    <!-- Judul -->
                    <h5 class="bg-success text-white text-center py-2 rounded">BARANG BELANJA</h5>
                    <!-- Input scan (bisa disembunyikan kalau mau) -->
                    <input type="text" id="scanInput" class="form-control" placeholder="Scan barcode disini"
                        autofocus style="opacity:0; position:absolute; left:-9999px;">
                    <!-- Area scroll untuk tabel + form -->
                    <div class="flex-grow-1 overflow-auto">
                        <div class="table-wrapper mb-2">
                            <table class="table table-bordered table-striped table-sm mb-0" id="cartTable">
                                <thead class="table-success sticky-top">
                                    <tr>
                                        <th>Nama Barang</th>
                                        <th>Qty</th>
                                        <th>Satuan</th>
                                        <th>Harga</th>
                                        <th>Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Form bawah -->
                    <div class="row m-0">
                        <div class="col-md-3 mb-2">
                            <input type="text" class="form-control form-control-sm" name="nama"
                                placeholder="Nama Customer">
                        </div>
                        <div class="col-md-3 mb-2">
                            <input type="text" class="form-control form-control-sm" name="alamat"
                                placeholder="Alamat">
                        </div>
                        <div class="col-md-3 mb-2">
                            <input type="text" class="form-control form-control-sm" name="nomor"
                                placeholder="Nomor Telp">
                        </div>
                        <div class="col-md-3 mb-2">
                            <input type="text" class="form-control form-control-sm" name="plat"
                                placeholder="Plat">
                        </div>
                    </div>

                    <!-- Shortcut tombol selalu di bawah -->
                    <div class="shortcut-bar d-flex justify-content-between flex-wrap py-2 mt-auto">
                        <button id="btn-f1" class="btn btn-outline-primary btn-sm shortcut-btn">F1 Menu</button>
                        <button id="btn-f2" class="btn btn-outline-success btn-sm shortcut-btn">F2 Tambah</button>
                        <button id="btn-f3" class="btn btn-outline-danger btn-sm shortcut-btn">F3 Hapus</button>
                        <button id="btn-f4" class="btn btn-outline-warning btn-sm shortcut-btn">F4 Edit Qty</button>
                        <button id="btn-f5" class="btn btn-outline-info btn-sm shortcut-btn">F5 Member</button>
                        <button id="btn-f6" class="btn btn-outline-dark btn-sm shortcut-btn">F6 Cari</button>
                        <button id="btn-f7" class="btn btn-outline-secondary btn-sm shortcut-btn">F7 Hold</button>
                        <button id="btn-f8" class="btn btn-outline-success btn-sm shortcut-btn">F8 Simpan</button>
                        <button id="btn-f9" class="btn btn-outline-danger btn-sm shortcut-btn">F9 Batal</button>
                        <button id="btn-f10" class="btn btn-outline-danger btn-sm shortcut-btn">F10
                            Fullscreen</button>
                    </div>
                </div>

                <!-- Sidebar kanan -->
                <div class="col-md-3 p-3 d-grid align-content-between">
                    <div class="total-box mb-3">
                        TOTAL BELANJA <br>
                        <span class="fs-1" id="grandTotal">Rp 0</span> <br>
                        <small id="item">0 item</small>
                    </div>

                    <div class="d-grid">
                        <!-- Hidden input untuk menyimpan metode pembayaran (default: Tunai) -->
                        <input type="hidden" name="pembayaran" id="pembayaran" value="Tunai">

                        <!-- Tombol pilihan pembayaran -->
                        <button type="button" class="btn btn-primary w-100 mb-2 pay-btn" data-metode="Tunai">💵
                            PEMBAYARAN TUNAI</button>
                        <button type="button" class="btn btn-outline-dark w-100 mb-2 pay-btn"
                            data-metode="Kartu Debit/Credit">💳 Kartu Debit/Credit</button>
                        <button type="button" class="btn btn-outline-dark w-100 mb-2 pay-btn"
                            data-metode="E-Wallet">📱
                            E-Wallet</button>
                        <button type="button" class="btn btn-outline-dark w-100 pay-btn"
                            data-metode="Transfer Bank">🏦
                            Transfer Bank</button>

                        <div class="mt-5">
                            <button class="btn btn-outline-primary w-100 mb-2">💾 Simpan Transaksi (F8)</button>
                            <button class="btn btn-outline-danger w-100">❌ Batal Transaksi (F9)</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script src="{{ asset('assets/vendors/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/sweet-alert/sweetalert2.min.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // ambil tombol F10 (Simpan / Batal / Fullscreen)
            const btnFullscreen = document.getElementById("btn-f10");

            if (btnFullscreen) {
                btnFullscreen.addEventListener("click", () => {
                    if (document.documentElement.requestFullscreen) {
                        document.documentElement.requestFullscreen();
                    } else if (document.documentElement.webkitRequestFullscreen) { // Safari
                        document.documentElement.webkitRequestFullscreen();
                    } else if (document.documentElement.msRequestFullscreen) { // IE/Edge lama
                        document.documentElement.msRequestFullscreen();
                    }
                });
            }
        });

        document.addEventListener("DOMContentLoaded", function() {
            const buttons = document.querySelectorAll(".pay-btn");
            const inputMetode = document.getElementById("pembayaran");

            buttons.forEach(btn => {
                btn.addEventListener("click", function() {
                    // set value hidden input
                    inputMetode.value = this.dataset.metode;

                    // reset semua tombol ke outline
                    buttons.forEach(b => {
                        b.classList.remove("btn-primary");
                        b.classList.add("btn-outline-dark");
                    });

                    // tombol terpilih jadi biru
                    this.classList.remove("btn-outline-dark");
                    this.classList.add("btn-primary");
                });
            });
        });

        // function bukaLaciKasir() {
        //     // kalau pakai ESC/POS printer:
        //     try {
        //         // contoh: kirim command ESC/POS untuk buka drawer
        //         const ws = new WebSocket("ws://localhost:40213/"); // pakai service ESC/POS JS
        //         ws.onopen = () => {
        //             // perintah ESC/POS buka laci (kick drawer)
        //             ws.send("\x1B\x70\x00\x19\xFA");
        //             ws.close();
        //         };
        //     } catch (e) {
        //         console.error("Gagal membuka laci:", e);
        //     }
        // }

        document.addEventListener("DOMContentLoaded", function() {
            const scanInput = document.getElementById("scanInput");
            const cartTable = document.querySelector("#cartTable tbody");
            const grandTotalEl = document.getElementById("grandTotal");
            const itemTotalEl = document.getElementById("item");

            let formData = new FormData();

            // selalu fokus ke input hidden
            function keepFocus() {
                setTimeout(() => scanInput.focus(), 100);
            }
            document.body.addEventListener("click", keepFocus);
            document.body.addEventListener("keydown", keepFocus);
            keepFocus();

            // Event scanner (scanner biasanya kirim ENTER)
            scanInput.addEventListener("keydown", function(e) {
                if (e.key === "Enter") {
                    e.preventDefault();
                    const kode = scanInput.value.trim();
                    if (kode !== "") {
                        tambahProduk(kode);
                    }
                    scanInput.value = "";
                }
            });

            function tambahProduk(kode) {
                fetch(`/kasir/produk-get?kode=${kode}`)
                    .then(res => {
                        if (!res.ok) throw new Error("Produk tidak ditemukan");
                        return res.json();
                    })
                    .then(res => {
                        if (res.status !== "success") throw new Error(res.message);
                        const data = res.data;
                        const prices = res.prices; // daftar harga per qty

                        let row = cartTable.querySelector(`tr[data-kode='${kode}']`);
                        let qty;

                        if (row) {
                            // sudah ada → tambah qty
                            let qtyCell = row.querySelector(".qty");
                            qty = parseInt(qtyCell.innerText) + 1;
                            qtyCell.innerText = qty;
                        } else {
                            // produk baru
                            qty = 1;
                            let newRow = document.createElement("tr");
                            newRow.setAttribute("data-kode", kode);
                            newRow.setAttribute("data-uuid", data.uuid); // simpan uuid untuk formData
                            newRow.innerHTML = `
                                <td>${data.nama_barang}</td>
                                <td class="qty">1</td>
                                <td>PCS</td>
                                <td class="harga">Rp ${Math.round(data.harga_jual_default).toLocaleString()}</td>
                                <td class="jumlah"></td>
                            `;
                            cartTable.appendChild(newRow);
                            row = newRow;
                        }

                        // ✅ Tentukan harga yg dipakai untuk TOTAL (jumlah)
                        let harga_dipakai = data.harga_jual_default * qty;
                        prices.forEach(p => {
                            if (qty >= p.qty) {
                                harga_dipakai = p.harga_jual; // harga tier × qty
                            }
                        });

                        // update kolom jumlah
                        row.querySelector(".jumlah").innerText =
                            "Rp " + Math.round(harga_dipakai).toLocaleString();

                        // ---- Tambah foto ke div produk-terpilih ----
                        let produkTerpilihDiv = document.querySelector(".produk-terpilih");
                        let existing = produkTerpilihDiv.querySelector(`.produk-item[data-kode='${kode}']`);
                        if (!existing) {
                            let newBox = document.createElement("div");
                            newBox.classList.add("border", "p-2", "mb-2", "text-center", "produk-item");
                            newBox.setAttribute("data-kode", kode);
                            newBox.innerHTML = `
                                <img src="${data.foto ? '/storage/' + data.foto : 'https://via.placeholder.com/100x80'}"
                                    class="img-fluid" alt="${data.nama_barang}">
                                <p class="small mt-1">${data.nama_barang}</p>
                            `;
                            produkTerpilihDiv.prepend(newBox);
                        }

                        hitungTotal();
                    })
                    .catch(err => {
                        console.warn(err.message);
                        alert("❌ Produk tidak ditemukan!");
                    });
            }

            function hitungTotal() {
                let total = 0;
                let item = 0;

                cartTable.querySelectorAll("tr").forEach(row => {
                    let jumlah = row.querySelector(".jumlah").innerText.replace(/Rp\s?|,/g, "");
                    let qty = parseInt(row.querySelector(".qty").innerText) || 0;

                    total += parseInt(jumlah) || 0;
                    item += qty;
                });

                grandTotalEl.innerText = "Rp " + total.toLocaleString();
                itemTotalEl.innerText = item.toLocaleString() +
                    " item";
            }


            // ---- tombol F8 submit ----
            document.addEventListener("keydown", function(event) {
                if (event.key === "F8") {
                    event.preventDefault();
                    event.stopPropagation();

                    let form = document.getElementById("form-kasir");
                    if (!form) return;

                    // buat formData baru dari form + isi tabel cart
                    let formData = new FormData(form);

                    cartTable.querySelectorAll("tr").forEach(row => {
                        let uuid = row.getAttribute("data-uuid");
                        let qty = parseInt(row.querySelector(".qty").innerText) || 0;
                        let jumlah = row.querySelector(".jumlah").innerText.replace(/Rp\s?|,/g, "");
                        jumlah = parseInt(jumlah) || 0;

                        formData.append("uuid_produk[]", uuid);
                        formData.append("qty[]", qty);
                        formData.append("total_harga[]", jumlah);
                    });

                    // ✅ tampilkan loading pakai SweetAlert
                    Swal.fire({
                        title: "Menyimpan Transaksi...",
                        text: "Mohon tunggu sebentar",
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading(); // spinner loading
                        }
                    });

                    fetch("/kasir/penjualan-store", {
                            method: "POST",
                            body: formData,
                            headers: {
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute("content")
                            }
                        })
                        .then(res => res.json())
                        .then(res => {
                            Swal.close(); // tutup loading

                            if (res.status === "success") {
                                Swal.fire({
                                    title: "Transaksi Berhasil ✅",
                                    text: "Apakah Anda ingin mencetak struk?",
                                    icon: "success",
                                    showCancelButton: true,
                                    confirmButtonText: "Cetak Struk",
                                    cancelButtonText: "Tidak",
                                    reverseButtons: true
                                }).then((result) => {
                                    // bukaLaciKasir();

                                    // if (result.isConfirmed) {
                                    //     cetakStruk(res.data); // cetak struk sesuai data
                                    // }

                                    // reset kasir
                                    resetKasir();
                                });
                            } else {
                                Swal.fire({
                                    title: "Gagal!",
                                    text: res.message,
                                    icon: "error",
                                    confirmButtonText: "OK"
                                });
                            }
                        })
                        .catch(err => {
                            Swal.close();
                            console.error(err);
                            Swal.fire({
                                title: "Error!",
                                text: "Terjadi kesalahan server",
                                icon: "error",
                                confirmButtonText: "OK"
                            });
                        });
                }
            });


            // ------------------
            // Fungsi Reset Kasir
            // ------------------
            function resetKasir() {
                formData = new FormData();
                cartTable.innerHTML = "";
                grandTotalEl.innerText = "Rp 0";
                itemTotalEl.innerText = "0 item";
                document.querySelector(".produk-terpilih").innerHTML = "";
                scanInput.value = "";
                scanInput.focus();
            }
        });
    </script>
</body>

</html>
