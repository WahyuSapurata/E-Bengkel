@extends('layouts.layout')
@section('content')
    <div class="nxl-content">
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10 text-capitalize">Tools</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item text-capitalize">{{ $module }}</li>
                </ul>
            </div>
            <div class="page-header-right ms-auto">
                <div class="page-header-right-items ">
                    <div class="d-flex d-md-none"><a class="page-header-right-close-toggle" href="/widgets/tables"><svg
                                stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24"
                                stroke-linecap="round" stroke-linejoin="round" class="me-2" height="16" width="16"
                                xmlns="http://www.w3.org/2000/svg">
                                <line x1="19" y1="12" x2="5" y2="12"></line>
                                <polyline points="12 19 5 12 12 5"></polyline>
                            </svg><span>Back</span></a></div>
                </div>
                <div class="d-md-none d-flex align-items-center"><a class="page-header-right-open-toggle"
                        href="/widgets/tables"><svg stroke="currentColor" fill="none" stroke-width="2"
                            viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" class="fs-20" height="1em"
                            width="1em" xmlns="http://www.w3.org/2000/svg">
                            <line x1="21" y1="10" x2="7" y2="10"></line>
                            <line x1="21" y1="6" x2="3" y2="6"></line>
                            <line x1="21" y1="14" x2="3" y2="14"></line>
                            <line x1="21" y1="18" x2="7" y2="18"></line>
                        </svg></a></div>
            </div>
        </div>
        <div class="main-content">
            <div class="row">
                <div class="col-xxl-12">
                    <div class="card stretch stretch-full widget-tasks-content  ">
                        <div class="card-header">
                            <h5 class="card-title">Tabel {{ $module }}</h5>
                            <div class="card-header-action">
                                <div class="card-header-btn">
                                    <div data-bs-toggle="tooltip" aria-label="Refresh" data-bs-original-title="Refresh">
                                        <span class="avatar-text avatar-xs bg-warning" data-bs-toggle="refresh"> </span>
                                    </div>
                                    <div data-bs-toggle="tooltip" aria-label="Maximize/Minimize"
                                        data-bs-original-title="Maximize/Minimize"><span
                                            class="avatar-text avatar-xs bg-success" data-bs-toggle="expand"> </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive p-3">
                                <input type="text" id="scanInput" placeholder="Scan produk di sini" autofocus
                                    style="opacity:0; position:absolute; left:-9999px;" />
                                <table id="tabelLabel" class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Kode</th>
                                            <th>Nama Barang</th>
                                            <th>Merek</th>
                                            <th>Satuan</th>
                                            <th>Harga Jual</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                                <button id="btnCetak" class="btn btn-primary mt-3">Cetak Label</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        let produkDipilih = [];

        document.getElementById("scanInput").addEventListener("keypress", async function(e) {
            if (e.key === "Enter") {
                let kode = this.value.trim();
                this.value = "";

                if (!kode) return; // cegah input kosong

                try {
                    let res = await fetch("/superadmin/tools/cetak-label-rak-get/" + kode);

                    // Kalau status bukan 200 → lempar error
                    if (!res.ok) {
                        let errorData = await res.json();
                        throw new Error(errorData.message || "Produk tidak ditemukan");
                    }

                    let json = await res.json();
                    if (json.status === "success") {
                        let produk = json.data;

                        // cek kalau sudah ada di array → jangan ditambahkan lagi
                        let existing = produkDipilih.find(p => p.kode === produk.kode);
                        if (!existing) {
                            produkDipilih.push(produk);
                            renderTable();
                        } else {
                            Swal.fire({
                                title: "Info",
                                text: "Produk sudah ada di daftar!",
                                icon: "info",
                                showConfirmButton: false,
                                timer: 1000
                            });
                        }
                    } else {
                        Swal.fire({
                            title: "Warning!",
                            text: json.message || "Produk tidak ditemukan!",
                            icon: "warning",
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                } catch (err) {
                    Swal.fire({
                        title: "Error!",
                        text: err.message || "Terjadi kesalahan server",
                        icon: "error",
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            }
        });

        function formatRupiah(angka) {
            angka = angka.toString();
            let number_string = angka.replace(/[^,\d]/g, ''),
                split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
            return rupiah ? 'Rp ' + rupiah : '';
        }

        function renderTable() {
            let tbody = document.querySelector("#tabelLabel tbody");
            tbody.innerHTML = "";

            produkDipilih.forEach((p, i) => {
                // Loop harga jual untuk setiap produk
                let hargaJualHtml = p.harga_jual.map(hj => {
                    return `<div>QTY ${hj.qty} : ${formatRupiah(hj.harga_jual)}</div>`;
                }).join('');

                tbody.innerHTML += `
            <tr>
                <td>${p.kode}</td>
                <td>${p.nama_barang}</td>
                <td>${p.merek}</td>
                <td>${p.satuan}</td>
                <td>${hargaJualHtml}</td>
                <td>
                    <button class="btn btn-sm btn-danger" onclick="hapus(${i})">Hapus</button>
                </td>
            </tr>
        `;
            });
        }

        function hapus(index) {
            produkDipilih.splice(index, 1);
            renderTable();
        }

        // Cetak
        document.getElementById("btnCetak").addEventListener("click", () => {
            if (produkDipilih.length === 0) {
                Swal.fire({
                    title: "Warning!",
                    text: "Tidak ada produk untuk dicetak!",
                    icon: "warning",
                    showConfirmButton: false,
                    timer: 1500
                });
                return;
            }

            fetch("/superadmin/tools/cetak-label-rak-store", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        produk: produkDipilih
                    })
                })
                .then(res => {
                    if (!res.ok) throw new Error("Gagal generate label");
                    return res.blob();
                })
                .then(blob => {
                    let url = URL.createObjectURL(blob);
                    window.open(url, "_blank"); // buka PDF di tab baru
                })
                .catch(err => {
                    Swal.fire({
                        title: "Error!",
                        text: err.message || "Terjadi kesalahan saat cetak label",
                        icon: "error",
                        showConfirmButton: true
                    });
                });
        });
    </script>
@endpush
