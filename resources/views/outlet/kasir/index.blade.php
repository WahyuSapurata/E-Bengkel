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
                        <div class="d-flex justify-content-center"><img src="{{ asset('logo.png') }}" class="img-fluid"
                                alt=""></div>
                        <div class="text-center mb-3">
                            <h6 class="fw-bold text-uppercase">{{ $data_outlet->nama_outlet }}</h6>
                            <small>{{ $data_outlet->alamat }}</small>
                        </div>

                        <div class="text-center mb-3">
                            <h4 class="bg-custom text-white p-2 rounded fs-2">{{ $nomor_urut }}</h4>
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
                        <button type="button" onclick="window.location.href = '{{ route('logout') }}'"
                            class="btn btn-outline-danger btn-sm">📤 Keluar</button>
                        <button type="button" id="open-stock" class="btn btn-outline-secondary btn-sm">📦 Stok
                            Barang</button>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-md-7 p-3 d-flex flex-column min-vh-100">
                    <!-- Judul -->
                    <h5 class="bg-custom text-white text-center py-2 rounded">BARANG BELANJA</h5>
                    <!-- Input scan (bisa disembunyikan kalau mau) -->
                    {{-- <input type="text" id="scanInput" class="form-control" placeholder="Scan barcode disini"
                        autofocus style="opacity:0; position:absolute; left:-9999px;"> --}}
                    <!-- Area scroll untuk tabel + form -->
                    <div class="flex-grow-1 overflow-auto">
                        <div class="table-wrapper mb-2">
                            <table class="table table-bordered table-striped table-sm mb-0" id="cartTable">
                                <thead class="table-danger sticky-top">
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
                        <button type="button" id="btn-f1" class="btn btn-outline-primary btn-sm shortcut-btn">F1
                            Search</button>
                        <button type="button" id="btn-f2" class="btn btn-outline-success btn-sm shortcut-btn">F2
                            Tambah</button>
                        <button type="button" id="btn-f3" class="btn btn-outline-danger btn-sm shortcut-btn">F3
                            Hapus</button>
                        <button type="button" id="btn-f4" class="btn btn-outline-warning btn-sm shortcut-btn">F4
                            Edit Qty</button>
                        <button type="button" class="btn btn-outline-info btn-sm shortcut-btn">F5 Reload</button>
                        <button type="button" id="btn-f6" class="btn btn-outline-dark btn-sm shortcut-btn">F6 Cetak
                            Ulang</button>
                        {{-- <button type="button" id="btn-f7" class="btn btn-outline-secondary btn-sm shortcut-btn">F7 Hold</button>  --}}
                        <button type="button" id="btn-f8" class="btn btn-outline-success btn-sm shortcut-btn">F8
                            Simpan</button>
                        <button type="button" id="btn-f9" class="btn btn-outline-danger btn-sm shortcut-btn">F9
                            Batal</button>
                        <button type="button" id="btn-f10" class="btn btn-outline-danger btn-sm shortcut-btn">F10
                            Fullscreen</button>
                    </div>
                </div>

                <!-- Sidebar kanan -->
                <div class="col-md-3 p-3 d-grid align-content-between">
                    <div class="bg-custom total-box mb-3">
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
                        <button type="button" class="btn btn-outline-dark w-100 pay-btn"
                            data-metode="Transfer Bank">🏦
                            Transfer Bank</button>
                        <select class="form-select d-none mt-2" id="select-pembayaran" name="nama_bank"
                            aria-label="Default select example">
                            <option selected disabled>Pilih bank</option>
                            @foreach ($aset as $cp)
                                <option value="{{ $cp->nama }}">{{ $cp->nama }}</option>
                            @endforeach
                        </select>

                        <div class="mt-5">
                            <button type="button" class="btn btn-outline-primary w-100 mb-2">💾 Simpan Transaksi
                                (F8)</button>
                            <button type="button" class="btn btn-outline-danger w-100 mb-2">❌ Batal Transaksi
                                (F9)</button>
                            <button type="button" id="btn-closing" class="btn btn-outline-success w-100">📂 Closing
                                Kasir</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Modal -->
    <div class="modal fade" id="exampleModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="exampleModalLabel" aria-hidden="true">
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

    <div class="modal fade" id="stockModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="stockModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="stockModalLabel">List Produk</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="flex-grow-1 overflow-auto">
                        <div class="table-wrapper mb-2">
                            <div class="mb-2">
                                <input type="text" id="searchInput" class="form-control"
                                    placeholder="Cari produk (kode/nama barang)...">
                            </div>

                            <table class="table table-bordered table-striped table-sm mb-0" id="produkTable">
                                <thead class="table-danger sticky-top">
                                    <tr>
                                        <th>Kode</th>
                                        <th>Nama Barang</th>
                                        <th>Total Stock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="SearchModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="SearchModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="SearchModalLabel">List Produk</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="flex-grow-1 overflow-auto">
                        <div class="table-wrapper mb-2">
                            <div class="mb-2">
                                <input type="text" id="searchInputModal" class="form-control"
                                    placeholder="Cari produk (kode/nama barang)...">
                            </div>

                            <table class="table table-bordered table-striped table-sm mb-0" id="produkTableModal">
                                <thead class="table-danger sticky-top">
                                    <tr>
                                        <th>Kode</th>
                                        <th>Nama Barang</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="CetakUlangModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="CetakUlangModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="CetakUlangModalLabel">List Produk</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="flex-grow-1 overflow-auto">
                        <div class="table-wrapper mb-2">
                            <table class="table table-bordered table-striped table-sm mb-0" id="cetakUlangTableModal">
                                <thead class="table-danger sticky-top">
                                    <tr>
                                        <th>No Bukti</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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

            const openStock = document.getElementById("open-stock");
            const modalStock = document.getElementById("stockModal");
            const search = document.getElementById("searchInput");

            const modalSearch = document.getElementById("SearchModal");
            const searchSearch = document.getElementById("searchInputModal");

            const modalCetakUlang = document.getElementById("CetakUlangModal");

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

            openStock.addEventListener("click", (e) => {
                e.preventDefault();
                const modal = bootstrap.Modal.getOrCreateInstance(modalStock);
                if (modalStock.classList.contains("show")) {
                    modal.hide();
                } else {
                    // panggil pertama kali
                    loadStok();
                    modal.show();
                }
            });

            document.addEventListener("keydown", (e) => {
                if (e.key === "F1") {
                    e.preventDefault();
                    const modal = bootstrap.Modal.getOrCreateInstance(modalSearch);
                    if (modalSearch.classList.contains("show")) {
                        modal.hide();
                    } else {
                        loadProduk();
                        modal.show();
                    }
                }
            });

            document.addEventListener("keydown", (e) => {
                if (e.key === "F6") {
                    e.preventDefault();
                    const modal = bootstrap.Modal.getOrCreateInstance(modalCetakUlang);
                    if (modalCetakUlang.classList.contains("show")) {
                        modal.hide();
                    } else {
                        loadPenjualanHariIni();
                        modal.show();
                    }
                }
            });

            // ----------------
            // Fokus otomatis
            // ----------------

            // optional: flag tambahan kalau mau
            let __swalOpen = false;

            function keepFocus(e) {
                // 1) Kalau ada modal bootstrap terbuka → jangan pindah fokus
                if (typeof modalEl !== 'undefined' && modalEl?.classList?.contains("show")) return;
                if (typeof modalStock !== 'undefined' && modalStock?.classList?.contains("show")) return;
                if (typeof modalSearch !== 'undefined' && modalSearch?.classList?.contains("show")) return;

                // 2) Kalau SweetAlert2 sedang tampil → jangan pindah fokus
                //    cek dua cara supaya robust di berbagai versi Swal
                if ((typeof Swal !== 'undefined' && Swal.isVisible && Swal.isVisible()) ||
                    document.body.classList.contains('swal2-shown') ||
                    __swalOpen === true) {
                    return;
                }

                // 3) Kalau user sedang mengetik di input/select/textarea/button/contenteditable → jangan ganggu
                const ae = document.activeElement;
                if (ae &&
                    (ae.tagName === 'INPUT' || ae.tagName === 'TEXTAREA' || ae.tagName === 'SELECT' ||
                        ae.tagName === 'BUTTON' || ae.isContentEditable)) {
                    return;
                }

                // 4) Tidak ada yang perlu diprioritaskan → kembalikan fokus ke scanInput
                if (typeof scanInput !== 'undefined' && scanInput) {
                    scanInput.focus()
                }
            }

            // pakai click saja (tidak keydown supaya tidak bentrok shortcut)
            document.addEventListener("click", keepFocus);

            // panggil sekali di awal
            keepFocus();

            // Modal utama
            if (typeof modalEl !== 'undefined' && modalEl) {
                modalEl.addEventListener("shown.bs.modal", () => {
                    if (typeof kodeProdukInput !== 'undefined' && kodeProdukInput) kodeProdukInput.focus();
                });
                modalEl.addEventListener("hidden.bs.modal", () => {
                    if (typeof scanInput !== 'undefined' && scanInput) scanInput.focus();
                });
            }

            // Modal stok
            if (typeof modalStock !== 'undefined' && modalStock) {
                modalStock.addEventListener("shown.bs.modal", () => {
                    if (typeof search !== 'undefined' && search) search.focus();
                });
                modalStock.addEventListener("hidden.bs.modal", () => {
                    if (typeof scanInput !== 'undefined' && scanInput) scanInput.focus();
                });
            }

            if (typeof modalSearch !== 'undefined' && modalSearch) {
                modalSearch.addEventListener("shown.bs.modal", () => {
                    if (typeof searchSearch !== 'undefined' && searchSearch) searchSearch.focus();
                });
                modalSearch.addEventListener("hidden.bs.modal", () => {
                    if (typeof scanInput !== 'undefined' && scanInput) scanInput.focus();
                });
            }

            // ----------------
            // Tombol save produk manual
            // ----------------
            if (btnSaveProduk) {
                btnSaveProduk.addEventListener("click", () => {
                    const kode = kodeProdukInput.value.trim();
                    const qty = parseInt(document.getElementById("qty-produk").value) || 1;
                    if (!kode) {
                        Swal.fire({
                            title: "Warning!",
                            text: "⚠️ Masukkan kode produk terlebih dahulu!",
                            icon: "warning",
                            showConfirmButton: false,
                            timer: 1000
                        });
                        return;
                    }
                    tambahProduk(kode, qty);

                    // reset dan tutup modal
                    kodeProdukInput.value = "";
                    document.getElementById("qty-produk").value = "1";
                    bootstrap.Modal.getInstance(modalEl).hide();
                });
            }

            let scanBuffer = "";
            let scanTimeout;

            document.addEventListener("keydown", function(e) {

                // Kalau ada inputan manual (user ngetik), biarin aja
                if (e.target.tagName === "INPUT" || e.target.tagName === "TEXTAREA") {
                    return;
                }

                if (e.key === "Enter") {
                    e.preventDefault();
                    const kode = scanBuffer.trim();
                    if (kode !== "") {
                        tambahProduk(kode); // default qty = 1
                    }
                    scanBuffer = ""; // reset buffer
                    return;
                }

                // Tambah ke buffer
                scanBuffer += e.key;

                // Reset kalau delay lebih dari 500ms antar ketikan (anggap bukan scan)
                clearTimeout(scanTimeout);
                scanTimeout = setTimeout(() => {
                    scanBuffer = "";
                }, 500);
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
                    grandTotalEl.innerText = "Rp 0";
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
            const selectBank = document.getElementById("select-pembayaran");

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

                    // kalau Transfer Bank => tampilkan select bank
                    if (this.dataset.metode === "Transfer Bank") {
                        selectBank.classList.remove("d-none");
                        selectBank.required = true;
                    } else {
                        selectBank.classList.add("d-none");
                        selectBank.required = false;
                        selectBank.value = ""; // reset pilihan
                    }
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
                        Swal.fire({
                            title: "Warning!",
                            text: "⚠️ Pilih dulu produk di tabel!",
                            icon: "warning",
                            showConfirmButton: false,
                            timer: 1000
                        });
                        return;
                    }
                    editQty(selectedRow);
                }

                if (e.key === "F3") {
                    e.preventDefault();
                    if (!selectedRow) {
                        Swal.fire({
                            title: "Warning!",
                            text: "⚠️ Pilih dulu produk di tabel!",
                            icon: "warning",
                            showConfirmButton: false,
                            timer: 1000
                        });
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

                // pastikan ps sudah diurutkan ascending berdasarkan qty
                const match = ps
                    .filter(p => newQty >= p.qty) // ambil semua harga yang berlaku untuk qty ini
                    .pop(); // ambil harga terakhir (paling besar qty yg masih memenuhi)

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
                        Swal.fire({
                            title: "Gagal!",
                            text: err.message,
                            icon: "error",
                            confirmButtonText: "OK"
                        });
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

            document.addEventListener("keydown", function(event) {
                if (event.key === "F9") {
                    event.preventDefault();
                    resetKasir();
                }
            });

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
                                        // siapkan data struk
                                        const strukData = {
                                            outlet_nama: "{{ $data_outlet->nama_outlet }}",
                                            outlet_alamat: "{{ $data_outlet->alamat }}",
                                            outlet_telp: "{{ $data_outlet->telepon }}",
                                            no_bukti: res.data.no_bukti,
                                            tanggal: res.data.tanggal,
                                            kasir: res.data.kasir,
                                            pembayaran: res.data.pembayaran,
                                            items: res.data.items.map(i => ({
                                                nama: i.nama,
                                                qty: Number(i
                                                    .qty), // pastikan number
                                                harga: Number(i
                                                    .harga), // pastikan number
                                                subtotal: Number(i
                                                    .subtotal
                                                ) // pastikan number
                                            })),
                                            totalJasa: Number(totalJasa), // pastikan number
                                            totalItem: Number(res.data.totalItem),
                                            grandTotal: Number(res.data.grandTotal) +
                                                Number(totalJasa) // penjumlahan aman
                                        };

                                        // kirim ke backend print
                                        cetakStruk(strukData);
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
                fetch("/kasir/print-struk", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute(
                                "content")
                        },
                        body: JSON.stringify(data)
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === "success") {
                            Swal.fire({
                                title: "Berhasil!",
                                text: "Struk berhasil dicetak ✅",
                                icon: "success",
                                showConfirmButton: false,
                                timer: 1500,
                                timerProgressBar: true
                            });
                        } else {
                            Swal.fire("Gagal!", res.message, "error");
                        }
                    })
                    .catch(err => console.error("❌ Error print struk:", err));
            }

            // function cetakStruk(data) {
            //     // Format item list
            //     let itemsHtml = data.items.map(item => {
            //         return `
        //             <tr>
        //                 <td>${item.nama}</td>
        //                 <td style="text-align:center;">${item.qty}</td>
        //                 <td style="text-align:right;">${parseInt(item.harga).toLocaleString()}</td>
        //                 <td style="text-align:right;">${parseInt(item.subtotal).toLocaleString()}</td>
        //             </tr>
        //         `;
            //     }).join('');

            //     // Template struk
            //     let strukHtml = `
        //         <html>
        //         <head>
        //             <title>Struk</title>
        //             <style>
        //                 body {
        //                     font-family: monospace;
        //                     font-size: 12px;
        //                 }
        //                 .center {
        //                     text-align: center;
        //                 }
        //                 table {
        //                     width: 100%;
        //                     border-collapse: collapse;
        //                 }
        //                 td {
        //                     padding: 2px 0;
        //                 }
        //                 .total td {
        //                     border-top: 1px dashed #000;
        //                     font-weight: bold;
        //                 }
        //             </style>
        //         </head>
        //         <body onload="window.print(); window.close();">
        //             <div class="center">
        //                 <h3>{{ $data_outlet->nama_outlet }}</h3>
        //                 <p>{{ $data_outlet->alamat }}<br/>Telp: {{ $data_outlet->telepon }}</p>
        //             </div>
        //             <p>
        //                 No: ${data.no_bukti}<br/>
        //                 Tgl: ${data.tanggal}<br/>
        //                 Kasir: ${data.kasir}<br/>
        //                 Pembayaran: ${data.pembayaran}
        //             </p>
        //             <table>
        //                 <thead>
        //                     <tr>
        //                         <td>Barang</td>
        //                         <td>Qty</td>
        //                         <td>Harga</td>
        //                         <td>Sub</td>
        //                     </tr>
        //                 </thead>
        //                 <tbody>
        //                     ${itemsHtml}
        //                     <tr class="total">
        //                         <td colspan="3">Total Jasa</td>
        //                         <td style="text-align:right;">${totalJasa}</td>
        //                     </tr>
        //                     <tr class="total">
        //                         <td colspan="3">Total Item</td>
        //                         <td style="text-align:right;">${data.totalItem}</td>
        //                     </tr>
        //                     <tr class="total">
        //                         <td colspan="3">Grand Total</td>
        //                         <td style="text-align:right;">${parseInt(data.grandTotal + totalJasa).toLocaleString()}</td>
        //                     </tr>
        //                 </tbody>
        //             </table>
        //             <div class="center">
        //                 <p>--- Terima Kasih ---<br/>Barang yang sudah dibeli tidak dapat ditukar/dikembalikan</p>
        //             </div>
        //         </body>
        //         </html>
        //     `;

            //     // Buka window popup untuk print
            //     let win = window.open('', 'Struk', 'width=300,height=600');
            //     win.document.write(strukHtml);
            //     win.document.close();
            // }


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
                // scanInput.value = "";
                scanInput.focus();
            }

            // function kurangiStok(kode, qty = 1) {
            //     const tbody = document.querySelector("#produkTable tbody");
            //     const rows = tbody.querySelectorAll("tr");

            //     for (let tr of rows) {
            //         let kodeCell = tr.querySelector("td:first-child");
            //         if (!kodeCell) continue;

            //         if (kodeCell.textContent.trim() === kode) {
            //             let stokCell = tr.querySelector("td:nth-child(3)");
            //             let stokText = stokCell.textContent.trim().split(" ");
            //             let currentStok = parseInt(stokText[0], 10);
            //             let satuan = stokText[1] || "";

            //             if (currentStok >= qty) {
            //                 currentStok -= qty;
            //                 stokCell.textContent = `${currentStok} ${satuan}`;

            //                 if (currentStok <= 0) {
            //                     tr.classList.add("table-secondary"); // tandai habis
            //                 }

            //                 return true; // sukses dikurangi
            //             } else {
            //                 Swal.fire({
            //                     title: "Stok Tidak Cukup!",
            //                     text: `Stok ${kode} hanya ${currentStok}, tidak bisa kurangi ${qty}.`,
            //                     icon: "warning",
            //                     confirmButtonText: "OK"
            //                 });
            //                 return false;
            //             }
            //         }
            //     }

            //     return false; // kode tidak ada di tabel
            // }

            async function loadStok() {
                try {
                    const res = await fetch('/kasir/get-stock');
                    const data = await res.json();

                    const tbody = document.querySelector("#produkTable tbody");
                    tbody.innerHTML = "";

                    if (data.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="3" class="text-center">Belum ada produk</td></tr>`;
                    } else {
                        data.forEach(p => {
                            // buat elemen row
                            const tr = document.createElement("tr");
                            tr.innerHTML = `
                                <td>${p.kode}</td>
                                <td>${p.nama_barang}</td>
                                <td>${p.total_stok} ${p.satuan}</td>
                            `;

                            tbody.appendChild(tr);
                        });

                        // row no-data
                        const noDataRow = document.createElement("tr");
                        noDataRow.id = "no-data";
                        noDataRow.classList.add("d-none");
                        noDataRow.innerHTML =
                            `<td colspan="3" class="text-center">Tidak ada produk ditemukan</td>`;
                        tbody.appendChild(noDataRow);
                    }
                } catch (err) {
                    console.error("Gagal ambil stok:", err);
                }
            }

            async function loadProduk() {
                try {
                    const res = await fetch('/kasir/get-stock');
                    const data = await res.json();

                    const tbody = document.querySelector("#produkTableModal tbody");
                    tbody.innerHTML = "";

                    if (data.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="2" class="text-center">Belum ada produk</td></tr>`;
                    } else {
                        data.forEach(p => {
                            // buat elemen row
                            const tr = document.createElement("tr");
                            tr.innerHTML = `
                                <td>${p.kode}</td>
                                <td>${p.nama_barang}</td>
                            `;

                            // event klik row
                            tr.addEventListener("click", () => {
                                let qty = 1; // default klik = tambah 1
                                tambahProduk(p.kode, qty);

                                // highlight row
                                tr.classList.add("tr-highlight");
                                setTimeout(() => {
                                    tr.classList.remove("tr-highlight");
                                }, 800);
                            });

                            tbody.appendChild(tr);
                        });

                        // row no-data
                        const noDataRow = document.createElement("tr");
                        noDataRow.id = "no-data-modal";
                        noDataRow.classList.add("d-none");
                        noDataRow.innerHTML =
                            `<td colspan="2" class="text-center">Tidak ada produk ditemukan</td>`;
                        tbody.appendChild(noDataRow);
                    }
                } catch (err) {
                    console.error("Gagal ambil stok:", err);
                }
            }

            async function loadPenjualanHariIni() {
                try {
                    const res = await fetch('/kasir/get-penjualan');
                    const data = await res.json();

                    const tbody = document.querySelector("#cetakUlangTableModal tbody");
                    tbody.innerHTML = "";

                    if (data.length === 0) {
                        tbody.innerHTML = `
                <tr><td colspan="1" class="text-center">Belum Riwayat Struk</td></tr>
            `;
                    } else {
                        if (data.status == true) {
                            data.forEach(p => {
                                const tr = document.createElement("tr");
                                tr.innerHTML =
                                    `<td class="text-primary fw-bold cursor-pointer">${p.no_bukti}</td>`;

                                // klik nomor bukti untuk detail
                                tr.querySelector("td").addEventListener("click", async () => {
                                    try {
                                        const detailRes = await fetch(
                                            `/kasir/get-penjualan-detail/${p.uuid}`);
                                        const res = await detailRes.json();

                                        if (res.status === "success") {
                                            const itemsHtml = res.data.items.map(i => `
                                <tr>
                                    <td>${i.nama}</td>
                                    <td>${i.qty}</td>
                                    <td>${i.harga.toLocaleString()}</td>
                                    <td>${i.subtotal.toLocaleString()}</td>
                                </tr>
                            `).join("");

                                            const detailHtml = `
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Nama</th>
                                                <th>Qty</th>
                                                <th>Harga</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${itemsHtml}
                                            <tr>
                                                <td colspan="3" class="text-end fw-bold">Jasa</td>
                                                <td>${res.data.totalJasa.toLocaleString()}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="3" class="text-end fw-bold">Grand Total</td>
                                                <td>${res.data.grandTotal.toLocaleString()}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            `;

                                            Swal.fire({
                                                title: `Detail Struk - ${res.data.no_bukti}`,
                                                html: detailHtml,
                                                width: 600,
                                                showCancelButton: true,
                                                confirmButtonText: "🖨 Cetak Struk",
                                                cancelButtonText: "Tutup"
                                            }).then(result => {
                                                if (result.isConfirmed) {
                                                    const totalJasa = res.data
                                                        .totalJasa ?? 0;
                                                    const strukData = {
                                                        outlet_nama: "{{ $data_outlet->nama_outlet }}",
                                                        outlet_alamat: "{{ $data_outlet->alamat }}",
                                                        outlet_telp: "{{ $data_outlet->telepon }}",
                                                        no_bukti: res.data
                                                            .no_bukti,
                                                        tanggal: res.data
                                                            .tanggal,
                                                        kasir: res.data.kasir,
                                                        pembayaran: res.data
                                                            .pembayaran,
                                                        items: res.data.items
                                                            .map(
                                                                i => ({
                                                                    nama: i
                                                                        .nama,
                                                                    qty: Number(
                                                                        i
                                                                        .qty
                                                                    ),
                                                                    harga: Number(
                                                                        i
                                                                        .harga
                                                                    ),
                                                                    subtotal: Number(
                                                                        i
                                                                        .subtotal
                                                                    )
                                                                })),
                                                        totalJasa: Number(
                                                            totalJasa),
                                                        totalItem: Number(res
                                                            .data
                                                            .totalItem),
                                                        grandTotal: Number(res
                                                                .data
                                                                .grandTotal) +
                                                            Number(totalJasa)
                                                    };

                                                    cetakStruk(strukData);
                                                }
                                            });
                                        }
                                    } catch (err) {
                                        console.error("❌ Gagal ambil detail penjualan:",
                                            err);
                                    }
                                });

                                tbody.appendChild(tr);
                            });
                        } else {
                            const tr = document.createElement("tr");
                            tr.innerHTML = `<td colspan="1" class="text-center">${data.message}</td>`;
                            tbody.appendChild(tr);
                        }
                        // row no-data tersembunyi
                        const noDataRow = document.createElement("tr");
                        noDataRow.id = "no-data-modal";
                        noDataRow.classList.add("d-none");
                        noDataRow.innerHTML =
                            `<td colspan="3" class="text-center">Tidak ada produk ditemukan</td>`;
                        tbody.appendChild(noDataRow);
                    }
                } catch (err) {
                    console.error("Gagal ambil riwayat struk:", err);
                }
            }

            // ---------------------------
            // Search filter
            // ---------------------------
            document.getElementById("searchInput").addEventListener("keyup", function() {
                let value = this.value.toLowerCase();
                let rows = document.querySelectorAll("#produkTable tbody tr:not(#no-data)");
                let noData = document.getElementById("no-data");

                let visibleCount = 0;

                rows.forEach(function(row) {
                    let kode = row.cells[0]?.textContent.toLowerCase() || "";
                    let nama = row.cells[1]?.textContent.toLowerCase() || "";

                    if (kode.includes(value) || nama.includes(value)) {
                        row.style.display = "";
                        visibleCount++;
                    } else {
                        row.style.display = "none";
                    }
                });

                if (noData) {
                    if (visibleCount === 0) {
                        noData.classList.remove("d-none");
                    } else {
                        noData.classList.add("d-none");
                    }
                }
            });

            // ---------------------------
            // Search filter
            // ---------------------------
            document.getElementById("searchInputModal").addEventListener("keyup", function() {
                let value = this.value.toLowerCase();
                let rows = document.querySelectorAll("#produkTableModal tbody tr:not(#no-data-modal)");
                let noData = document.getElementById("no-data-modal");

                let visibleCount = 0;

                rows.forEach(function(row) {
                    let kode = row.cells[0]?.textContent.toLowerCase() || "";
                    let nama = row.cells[1]?.textContent.toLowerCase() || "";

                    if (kode.includes(value) || nama.includes(value)) {
                        row.style.display = "";
                        visibleCount++;
                    } else {
                        row.style.display = "none";
                    }
                });

                if (noData) {
                    if (visibleCount === 0) {
                        noData.classList.remove("d-none");
                    } else {
                        noData.classList.add("d-none");
                    }
                }
            });


            document.getElementById("btn-closing").addEventListener("click", function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Input Uang Fisik',
                    input: 'text', // ganti dari number ke text
                    inputLabel: 'Masukkan jumlah uang fisik di kasir',
                    inputPlaceholder: 'Contoh: 1.500.000',
                    showCancelButton: true,
                    confirmButtonText: 'Simpan Closing',
                    cancelButtonText: 'Batal',
                    willOpen: () => {
                        window.__swalOpen = true;
                    },
                    didOpen: () => {
                        const input = Swal.getInput();
                        if (input) {
                            input.focus();
                            input.select();

                            // Format ke rupiah saat mengetik
                            input.addEventListener("input", () => {
                                let value = input.value.replace(/\D/g,
                                    ""); // hanya angka
                                if (value) {
                                    input.value = new Intl.NumberFormat("id-ID").format(
                                        value);
                                } else {
                                    input.value = "";
                                }
                            });
                        }
                    },
                    willClose: () => {
                        window.__swalOpen = false;
                    },
                    preConfirm: () => {
                        const input = Swal.getInput().value;
                        // Hapus titik/format → jadi angka murni
                        return input.replace(/\D/g, "");
                    }
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    if (result.isConfirmed) {
                        let data = {
                            total_fisik: result.value,
                            uuid_kasir_outlet: "{{ $kasir_login->uuid_outlet ?? '' }}"
                        };

                        fetch("{{ route('kasir.closing') }}", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-TOKEN": document.querySelector(
                                        'meta[name="csrf-token"]').getAttribute("content")
                                },
                                body: JSON.stringify(data)
                            })
                            .then(res => res.json())
                            .then(res => {
                                if (res.status === "success") {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Closing Berhasil',
                                        text: 'Data closing kasir sudah disimpan!',
                                        confirmButtonText: 'OK'
                                    }).then(() => {
                                        localStorage.setItem("closing_done", "1");
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire('Error', res.message ??
                                        'Gagal menyimpan closing.', 'error');
                                }
                            })
                            .catch(err => {
                                console.error(err);
                                Swal.fire('Error', 'Terjadi kesalahan koneksi.', 'error');
                            });
                    }
                });
            });

        });
    </script>

</body>

</html>
