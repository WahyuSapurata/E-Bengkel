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
                        {{-- <button id="btn-f1" class="btn btn-outline-primary btn-sm shortcut-btn">F1 Menu</button> --}}
                        <button id="btn-f2" class="btn btn-outline-success btn-sm shortcut-btn">F2 Tambah</button>
                        <button id="btn-f3" class="btn btn-outline-danger btn-sm shortcut-btn">F3 Hapus</button>
                        <button id="btn-f4" class="btn btn-outline-warning btn-sm shortcut-btn">F4 Edit Qty</button>
                        {{-- <button id="btn-f5" class="btn btn-outline-info btn-sm shortcut-btn">F5 Member</button> --}}
                        {{-- <button id="btn-f6" class="btn btn-outline-dark btn-sm shortcut-btn">F6 Cari</button> --}}
                        {{-- <button id="btn-f7" class="btn btn-outline-secondary btn-sm shortcut-btn">F7 Hold</button>  --}}
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

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="check-jasa">
                                <label class="form-check-label" for="check-jasa">
                                    Pilih Jasa Service
                                </label>
                            </div>

                            <select class="form-select d-none" id="select-jasa" name="uuid_jasa"
                                aria-label="Default select example">
                                <option selected disabled>Pilih jasa</option>
                            </select>
                        </div>

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

    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Tambah Produk Manual</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="kode-produk" class="form-label">Kode Produk</label>
                        <input type="text" class="form-control" id="kode-produk"
                            placeholder="Masukkan kode produk">
                    </div>
                    <div class="mb-3">
                        <label for="qty-produk" class="form-label">Qty</label>
                        <input type="number" class="form-control" id="qty-produk" value="1" min="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="btn-save-produk">Tambah</button>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/vendors/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/sweet-alert/sweetalert2.min.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const scanInput = document.getElementById("scanInput");
            const modalEl = document.getElementById("exampleModal");
            const kodeProdukInput = document.getElementById("kode-produk");
            const btnSaveProduk = document.getElementById("btn-save-produk");

            // variabel penting
            const cartTable = document.getElementById("cartTable");
            const grandTotalEl = document.getElementById("grandTotal");
            const itemTotalEl = document.getElementById("item");

            let checkJasa = document.getElementById("check-jasa");
            let selectJasa = document.getElementById("select-jasa");

            let totalJasa = 0;

            // ----------------
            // F10: toggle fullscreen
            // ----------------
            const btnFullscreen = document.getElementById("btn-f10");
            if (btnFullscreen) {
                btnFullscreen.addEventListener("click", toggleFullscreen);
            }
            document.addEventListener("keydown", (e) => {
                if (e.key === "F10") {
                    e.preventDefault();
                    toggleFullscreen();
                }
            });

            // ----------------
            // F2: toggle modal
            // ----------------
            document.addEventListener("keydown", (e) => {
                if (e.key === "F2") {
                    e.preventDefault();
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    if (modalEl.classList.contains("show")) {
                        modal.hide();
                    } else {
                        modal.show();
                    }
                }
            });

            // ----------------
            // Fokus otomatis
            // ----------------
            function keepFocus() {
                if (modalEl.classList.contains("show")) return; // kalau modal terbuka, biarkan
                setTimeout(() => scanInput.focus(), 100);
            }
            document.addEventListener("click", keepFocus);
            // jangan pakai "keydown" untuk keepFocus biar gak bentrok F2/F8
            keepFocus();

            modalEl.addEventListener("shown.bs.modal", () => {
                if (kodeProdukInput) kodeProdukInput.focus();
            });
            modalEl.addEventListener("hidden.bs.modal", () => {
                if (scanInput) scanInput.focus();
            });

            // ----------------
            // Tombol save produk manual
            // ----------------
            if (btnSaveProduk) {
                btnSaveProduk.addEventListener("click", () => {
                    const kode = kodeProdukInput.value.trim();
                    const qty = parseInt(document.getElementById("qty-produk").value) || 1;
                    if (!kode) {
                        alert("⚠️ Masukkan kode produk terlebih dahulu!");
                        return;
                    }
                    tambahProduk(kode, qty);

                    // reset dan tutup modal
                    kodeProdukInput.value = "";
                    document.getElementById("qty-produk").value = "1";
                    bootstrap.Modal.getInstance(modalEl).hide();
                });
            }

            // ----------------
            // Scanner enter
            // ----------------
            scanInput.addEventListener("keydown", (e) => {
                if (e.key === "Enter") {
                    e.preventDefault();
                    const kode = scanInput.value.trim();
                    if (kode !== "") {
                        tambahProduk(kode);
                    }
                    scanInput.value = "";
                }
            });

            // ----------------
            // fungsi toggle fullscreen
            // ----------------
            function toggleFullscreen() {
                if (document.fullscreenElement) {
                    document.exitFullscreen?.();
                    document.webkitExitFullscreen?.();
                    document.msExitFullscreen?.();
                } else {
                    document.documentElement.requestFullscreen?.();
                    document.documentElement.webkitRequestFullscreen?.(); // Safari
                    document.documentElement.msRequestFullscreen?.(); // IE/Edge lama
                }
            }

            // toggle tampil/hidden select jasa
            checkJasa.addEventListener("change", function() {
                if (this.checked) {
                    selectJasa.classList.remove("d-none");
                    loadJasa(); // ambil data jasa dari server
                } else {
                    selectJasa.classList.add("d-none");
                    selectJasa.innerHTML = `<option selected disabled>Pilih jasa</option>`;
                    totalJasa = 0;
                }
            });

            // ketika jasa dipilih → masukkan ke keranjang
            selectJasa.addEventListener("change", function() {
                let selectedOption = this.options[this.selectedIndex];
                let uuid = selectedOption.value;
                let nama = selectedOption.text;
                let harga = parseInt(selectedOption.dataset.harga);

                totalJasa = harga; // update global

                hitungTotal(); // ✅ panggil ulang supaya grandTotal terupdate
            });

            // fungsi ambil jasa dari backend
            function loadJasa() {
                fetch("/kasir/get-jasa")
                    .then(res => res.json())
                    .then(data => {
                        let selectJasa = document.getElementById("select-jasa");
                        selectJasa.innerHTML = `<option selected disabled>Pilih jasa</option>`;
                        data.forEach(jasa => {
                            let option = document.createElement("option");
                            option.value = jasa.uuid;
                            option.text = jasa.nama
                            option.dataset.harga = jasa.harga; // ✅ simpan harga di data attribute
                            selectJasa.appendChild(option);
                        });
                    })
                    .catch(err => {
                        console.error("❌ Error load jasa:", err);
                    });
            }

            const buttons = document.querySelectorAll(".pay-btn");
            const inputMetode = document.getElementById("pembayaran");

            buttons.forEach(btn => {
                btn.addEventListener("click", function() {
                    // set value hidden input
                    inputMetode.value = this.dataset.metode;

                    // reset semua tombol ke outline
                    buttons.forEach(b => b.classList.remove("btn-primary"));
                    buttons.forEach(b => b.classList.add("btn-outline-dark"));

                    // tombol terpilih jadi biru
                    this.classList.remove("btn-outline-dark");
                    this.classList.add("btn-primary");
                });
            });

            // ----------------
            // Pilih row dengan klik
            // ----------------
            cartTable.addEventListener("click", function(e) {
                let row = e.target.closest("tr");
                if (!row) return;

                // hapus selected dari semua
                cartTable.querySelectorAll("tr").forEach(r => r.classList.remove("selected"));

                // tandai baris aktif
                row.classList.add("selected");
            });

            // ----------------
            // Keyboard event
            // ----------------
            document.addEventListener("keydown", function(e) {
                let selectedRow = cartTable.querySelector("tr.selected");
                let rows = [...cartTable.querySelectorAll("tbody tr")];

                if (e.key === "ArrowDown") {
                    e.preventDefault();
                    if (!selectedRow && rows.length) {
                        rows[0].classList.add("selected");
                    } else {
                        let idx = rows.indexOf(selectedRow);
                        if (idx < rows.length - 1) {
                            selectedRow.classList.remove("selected");
                            rows[idx + 1].classList.add("selected");
                        }
                    }
                }

                if (e.key === "ArrowUp") {
                    e.preventDefault();
                    if (selectedRow) {
                        let idx = rows.indexOf(selectedRow);
                        if (idx > 0) {
                            selectedRow.classList.remove("selected");
                            rows[idx - 1].classList.add("selected");
                        }
                    }
                }

                if (e.key === "F4") {
                    e.preventDefault();
                    if (!selectedRow) {
                        alert("Pilih dulu produk di tabel!");
                        return;
                    }
                    editQty(selectedRow);
                }

                if (e.key === "F3") {
                    e.preventDefault();
                    if (!selectedRow) {
                        alert("Pilih dulu produk di tabel!");
                        return;
                    }
                    hapusRow(selectedRow);
                }
            });

            // === EDIT QTY (F4) ===
            // Hitung dan simpan qty + harga tier
            function simpanQty(row, newQty, prices = [], hargaDefault = 0) {
                newQty = parseInt(newQty, 10);
                if (isNaN(newQty) || newQty < 0) newQty = 0;

                const qtyCell = row.querySelector('.qty');
                const qtyInput = row.querySelector('.qty-input');
                const hargaCell = row.querySelector('.harga'); // TIDAK diubah
                const jumlahCell = row.querySelector('.jumlah');

                if (!qtyCell || !jumlahCell) {
                    console.warn('Row tidak lengkap:', row);
                    return;
                }

                // qty = 0 → hapus baris
                if (newQty === 0) {
                    hapusRow(row);
                    return;
                }

                // pastikan harga default terisi (fallback ambil dari DOM)
                let hargaSatuanDefault = Number(hargaDefault) || 0;
                if (!hargaSatuanDefault && hargaCell) {
                    hargaSatuanDefault = parseInt(hargaCell.innerText.replace(/[^\d]/g, ''), 10) || 0;
                }

                // normalisasi prices
                const ps = (prices || []).map(p => ({
                    qty: Number(p.qty) || 0,
                    harga_jual: Number(p.harga_jual) || 0
                }));

                // hitung JUMLAH:
                // default: hargaDefault * qty
                // jika ada tier dengan qty PERSIS SAMA → jumlah = harga_jual (bundling)
                let jumlah = hargaSatuanDefault * newQty;
                const match = ps.find(p => p.qty === newQty);
                if (match) {
                    jumlah = match.harga_jual;
                }

                // simpan ke DOM
                qtyCell.innerText = newQty;
                if (qtyInput) qtyInput.value = newQty;

                // JANGAN ubah hargaCell (biarkan tetap harga/unit default)
                jumlahCell.innerText = 'Rp ' + Math.round(jumlah).toLocaleString();

                hitungTotal();
            }

            // Dialog edit qty (F4)
            function editQty(row) {
                const current = parseInt(row.querySelector('.qty')?.innerText, 10) || 1;
                const hargaDefault = Number(row.dataset.hargaDefault) || 0;
                let prices = [];
                try {
                    prices = JSON.parse(row.dataset.prices || '[]');
                } catch (e) {}

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Ubah Qty',
                        input: 'number',
                        inputValue: current,
                        inputAttributes: {
                            min: 0,
                            step: 1
                        },
                        showCancelButton: true,
                        confirmButtonText: 'Simpan',
                        cancelButtonText: 'Batal',
                        preConfirm: (v) => {
                            const n = parseInt(v, 10);
                            if (isNaN(n)) {
                                Swal.showValidationMessage('Qty tidak valid');
                                return false;
                            }
                            return n;
                        }
                    }).then(r => {
                        if (!r.isConfirmed) return;
                        const newQty = r.value;
                        if (newQty <= 0) {
                            hapusRow(row);
                            return;
                        }
                        simpanQty(row, newQty, prices, hargaDefault);
                    });
                } else {
                    const v = prompt('Qty baru:', current);
                    if (v === null) return;
                    const n = parseInt(v, 10);
                    if (!(n > 0)) {
                        hapusRow(row);
                        return;
                    }
                    simpanQty(row, n, prices, hargaDefault);
                }
            }

            // ----------------
            // Tambah Produk
            // ----------------
            function tambahProduk(kode, qtyManual = 1) {
                fetch(`/kasir/produk-get?kode=${kode}`)
                    .then(r => r.json())
                    .then(r => {
                        if (!r || r.status !== 'success') throw new Error(r.message ||
                            'Produk tidak ditemukan!');
                        const data = r.data;
                        const prices = (r.prices || []).map(p => ({
                            qty: Number(p.qty) || 0,
                            harga_jual: Number(p.harga_jual) || 0
                        }));

                        let row = cartTable.querySelector(`tbody tr[data-kode='${kode}']`);
                        let qty;

                        if (row) {
                            // pastikan dataset ada (dipakai saat F4)
                            row.dataset.hargaDefault = Number(data.harga_jual_default) || 0;
                            row.dataset.prices = JSON.stringify(prices);

                            qty = (parseInt(row.querySelector('.qty')?.innerText, 10) || 0) + parseInt(
                                qtyManual, 10);
                            simpanQty(row, qty, prices, data.harga_jual_default);
                        } else {
                            qty = parseInt(qtyManual, 10) || 1;
                            row = document.createElement('tr');
                            row.setAttribute('data-kode', kode);
                            row.setAttribute('data-uuid', data.uuid);
                            row.dataset.hargaDefault = Number(data.harga_jual_default) || 0;
                            row.dataset.prices = JSON.stringify(prices);

                            row.innerHTML = `
                            <td>${data.nama_barang}
                                <input type="hidden" name="produk_uuid[]" value="${data.uuid}">
                                <input type="hidden" name="qty[]" value="${qty}" class="qty-input">
                            </td>
                            <td class="qty">${qty}</td>
                            <td>${data.satuan || 'PCS'}</td>
                            <td class="harga">Rp ${Math.round(data.harga_jual_default).toLocaleString()}</td>
                            <td class="jumlah">Rp 0</td>
                            `;
                            (cartTable.querySelector('tbody') || cartTable).appendChild(row);

                            simpanQty(row, qty, prices, data.harga_jual_default);
                        }

                        // ---- Tambah foto ke div produk-terpilih ----
                        let produkTerpilihDiv = document.querySelector(".produk-terpilih");
                        if (produkTerpilihDiv) {
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
                        }


                        hitungTotal();
                    })
                    .catch(err => {
                        console.warn('❌ Error fetch produk:', err.message);
                        alert(err.message);
                    });
            }


            // ----------------
            // Hapus Row
            // ----------------
            function hapusRow(row) {
                const kode = row.getAttribute("data-kode");

                // hapus row dari tabel
                row.remove();

                // hapus gambar produk juga
                let produkTerpilihDiv = document.querySelector(".produk-terpilih");
                if (produkTerpilihDiv) {
                    let imgBox = produkTerpilihDiv.querySelector(`.produk-item[data-kode='${kode}']`);
                    if (imgBox) {
                        imgBox.remove();
                    }
                }

                hitungTotal();
            }

            // ----------------
            // Hitung total
            // ----------------
            function hitungTotal() {
                let grandTotal = 0;
                let item = 0;

                // ambil semua row produk
                let rows = cartTable.querySelectorAll("tbody tr");

                rows.forEach(row => {
                    let qtyCell = row.querySelector(".qty");
                    let jumlahCell = row.querySelector(".jumlah");

                    if (!qtyCell || !jumlahCell) {
                        console.warn("⚠️ Row tidak lengkap, dilewati:", row);
                        return; // skip row yang rusak
                    }

                    let qty = parseInt(qtyCell.innerText) || 0;
                    let jumlahText = jumlahCell.innerText.replace(/[^\d]/g, ""); // buang Rp, koma, titik
                    let jumlah = parseInt(jumlahText) || 0;

                    grandTotal += jumlah;
                    item += qty;
                });

                // ✅ tambahkan jasa
                if (totalJasa > 0) {
                    grandTotal += totalJasa;
                }

                // tampilkan total
                let totalCell = document.querySelector("#grandTotal");
                if (totalCell) {
                    totalCell.innerText = "Rp " + grandTotal.toLocaleString();
                    itemTotalEl.innerText = item + " item";
                }
            }

            // ---- tombol F8 submit ----
            document.addEventListener("keydown", function(event) {
                if (event.key === "F8") {
                    event.preventDefault();
                    event.stopPropagation();

                    let form = document.getElementById("form-kasir");
                    if (!form) return;

                    // buat formData dari form
                    let formData = new FormData(form);

                    // ambil semua baris produk dari tbody
                    const tbody = cartTable.querySelector("tbody");
                    tbody.querySelectorAll("tr").forEach(row => {
                        let uuid = row.getAttribute("data-uuid");
                        let qty = parseInt(row.querySelector(".qty")?.innerText) || 0;
                        let jumlah = row.querySelector(".jumlah")?.innerText.replace(/Rp\s?|,/g,
                            "");
                        jumlah = parseInt(jumlah) || 0;

                        formData.append("uuid_produk[]", uuid);
                        formData.append("qty[]", qty);
                        formData.append("total_harga[]", jumlah);
                    });

                    // tampilkan loading pakai SweetAlert
                    Swal.fire({
                        title: "Menyimpan Transaksi...",
                        text: "Mohon tunggu sebentar",
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
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
                                }).then(result => {
                                    if (result.isConfirmed) {
                                        cetakStruk(res.data); // cetak struk
                                    }
                                    // reset kasir & reload jasa
                                    resetKasir();
                                    loadJasa();
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
                            console.error("❌ Error simpan transaksi:", err);
                            Swal.fire({
                                title: "Error!",
                                text: "Terjadi kesalahan server",
                                icon: "error",
                                confirmButtonText: "OK"
                            });
                        });
                }
            });

            // ------------------ // Fungsi Cetak Struk // ------------------
            function cetakStruk(data) {
                // Format item list
                let itemsHtml = data.items.map(item => {
                    return `
                        <tr>
                            <td>${item.nama}</td>
                            <td style="text-align:center;">${item.qty}</td>
                            <td style="text-align:right;">${parseInt(item.harga).toLocaleString()}</td>
                            <td style="text-align:right;">${parseInt(item.subtotal).toLocaleString()}</td>
                        </tr>
                    `;
                }).join('');

                // Template struk
                let strukHtml = `
                    <html>
                    <head>
                        <title>Struk</title>
                        <style>
                            body {
                                font-family: monospace;
                                font-size: 12px;
                            }
                            .center {
                                text-align: center;
                            }
                            table {
                                width: 100%;
                                border-collapse: collapse;
                            }
                            td {
                                padding: 2px 0;
                            }
                            .total td {
                                border-top: 1px dashed #000;
                                font-weight: bold;
                            }
                        </style>
                    </head>
                    <body onload="window.print(); window.close();">
                        <div class="center">
                            <h3>Toko Kita</h3>
                            <p>Jl. Contoh No.123<br/>Telp: 0812-3456-7890</p>
                        </div>
                        <p>
                            No: ${data.no_bukti}<br/>
                            Tgl: ${data.tanggal}<br/>
                            Kasir: ${data.kasir}<br/>
                            Pembayaran: ${data.pembayaran}
                        </p>
                        <table>
                            <thead>
                                <tr>
                                    <td>Barang</td>
                                    <td>Qty</td>
                                    <td>Harga</td>
                                    <td>Sub</td>
                                </tr>
                            </thead>
                            <tbody>
                                ${itemsHtml}
                                <tr class="total">
                                    <td colspan="3">Total Item</td>
                                    <td style="text-align:right;">${data.totalItem}</td>
                                </tr>
                                <tr class="total">
                                    <td colspan="3">Grand Total</td>
                                    <td style="text-align:right;">${parseInt(data.grandTotal + totalJasa).toLocaleString()}</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="center">
                            <p>--- Terima Kasih ---<br/>Barang yang sudah dibeli tidak dapat ditukar/dikembalikan</p>
                        </div>
                    </body>
                    </html>
                `;

                // Buka window popup untuk print
                let win = window.open('', 'Struk', 'width=300,height=600');
                win.document.write(strukHtml);
                win.document.close();
            }


            // ------------------
            // Fungsi Reset Kasir
            // ------------------
            function resetKasir() {
                // kosongkan hanya tbody (isi tabel)
                const tbody = cartTable.querySelector("tbody");
                if (tbody) {
                    tbody.innerHTML = "";
                }

                // reset total & item
                grandTotalEl.innerText = "Rp 0";
                itemTotalEl.innerText = "0 item";

                // kosongkan box produk terpilih
                const produkTerpilihDiv = document.querySelector(
                    ".produk-terpilih");
                if (produkTerpilihDiv) {
                    produkTerpilihDiv.innerHTML = "";
                }

                // reset scan input
                scanInput.value = "";
                scanInput.focus();
            }
        });
    </script>

</body>

</html>
