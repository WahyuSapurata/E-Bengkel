@extends('layouts.layout')
<style>
    .table-responsive {
        max-height: calc(5 * 40px);
        /* 5 baris x tinggi baris ~40px */
        overflow-y: auto;
    }

    /* Optional: tetap sticky untuk header */
    .table-responsive thead {
        background: #f8f9fa;
        /* warna header */
        z-index: 1;
    }
</style>
@section('content')
    <div class="nxl-content">
        <!-- [ page-header ] start -->
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">{{ $module }}</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item">{{ $module }}</li>
                </ul>
            </div>
            <div class="page-header-right ms-auto">
                <div class="page-header-right-items">
                    <div class="d-flex d-md-none">
                        <a href="javascript:void(0)" class="page-header-right-close-toggle">
                            <i class="feather-arrow-left me-2"></i>
                            <span>Back</span>
                        </a>
                    </div>
                    <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper" style="width: 250px;">
                        <select name="uuid_user" id="filter-outlet" class="form-select form-select-sm">
                            <option value="">Semua Outlet</option>
                            @foreach ($outlet as $o)
                                <option value="{{ $o->uuid_user }}">{{ $o->nama_outlet }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="d-md-none d-flex align-items-center">
                    <a href="javascript:void(0)" class="page-header-right-open-toggle">
                        <i class="feather-align-right fs-20"></i>
                    </a>
                </div>
            </div>
        </div>
        <!-- [ page-header ] end -->
        <!-- [ Main Content ] start -->
        <div class="main-content">
            <div class="row">
                <!-- [Payment Records] start -->
                <div class="col-12">
                    <div class="card stretch stretch-full">
                        <div class="row">
                            <div class="card-header">
                                <h5 class="card-title">Penjualan</h5>
                                <div class="card-header-action">
                                    <div class="card-header-btn">
                                        <div data-bs-toggle="tooltip" title="Refresh">
                                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-warning"
                                                data-bs-toggle="refresh"> </a>
                                        </div>
                                        <div data-bs-toggle="tooltip" title="Maximize/Minimize">
                                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-success"
                                                data-bs-toggle="expand"> </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="card-header">
                                    <h5 class="card-title">Target Profit Penjualan Harian</h5>
                                </div>
                                <div class="card-body custom-card-action p-0">
                                    {{-- <div id="payment-records-chart"></div> --}}
                                    <div class="table-responsive p-3">
                                        <table id="tabel-penjualan" class="table table-bordered table-striped">
                                            <thead class="text-center">
                                                <tr>
                                                    <th>Tanggal</th>
                                                    <th>Kassa</th>
                                                    <th>Modal</th>
                                                    <th>Penjualan</th>
                                                    <th>Jasa</th>
                                                    <th>Profit</th>
                                                    <th>Tunai</th>
                                                    <th>Non Tunai</th>
                                                    <th>Sub Total</th>
                                                    <th>Total</th>
                                                    <th>Target Profit</th>
                                                    <th>Persentase Target</th>
                                                    <th>Selisih</th>
                                                </tr>
                                            </thead>
                                            <tbody id="target-body">
                                                <!-- Data akan diisi lewat JavaScript -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="card-header">
                                    <h5 class="card-title">Target Profit Penjualan Bulanan</h5>
                                </div>
                                <div class="card-body custom-card-action p-0">
                                    {{-- <div id="payment-records-chart"></div> --}}
                                    <div class="table-responsive p-3">
                                        <table id="tabel-penjualan" class="table table-bordered table-striped">
                                            <thead class="text-center">
                                                <tr>
                                                    <th>Tanggal</th>
                                                    <th>Kassa</th>
                                                    <th>Modal</th>
                                                    <th>Penjualan</th>
                                                    <th>Jasa</th>
                                                    <th>Profit</th>
                                                    <th>Tunai</th>
                                                    <th>Non Tunai</th>
                                                    <th>Sub Total</th>
                                                    <th>Total</th>
                                                    <th>Target Profit</th>
                                                    <th>Persentase Target</th>
                                                    <th>Selisih</th>
                                                </tr>
                                            </thead>
                                            <tbody id="target-bulanan-body">
                                                <!-- Data akan diisi lewat JavaScript -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="card-header">
                                    <h5 class="card-title">Produk Paling Laku</h5>
                                </div>
                                <div class="card-body custom-card-action p-0">
                                    <div class="table-responsive p-3">
                                        <table id="tabel-penjualan-terlaku" class="table table-bordered table-striped">
                                            <thead class="text-center">
                                                <tr>
                                                    <th>Nama Barang</th>
                                                    <th>Total Terjual</th>
                                                </tr>
                                            </thead>
                                            <tbody id="terlaku-body">
                                                <!-- Data akan diisi lewat JavaScript -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="card-header">
                                    <h5 class="card-title">Produk Untung Banyak</h5>
                                </div>
                                <div class="card-body custom-card-action p-0">
                                    <div class="table-responsive p-3">
                                        <table id="tabel-penjualan-untung" class="table table-bordered table-striped">
                                            <thead class="text-center">
                                                <tr>
                                                    <th>Nama Barang</th>
                                                    <th>Total Profit</th>
                                                </tr>
                                            </thead>
                                            <tbody id="untung-body">
                                                <!-- Data akan diisi lewat JavaScript -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="card-header">
                                    <h5 class="card-title">Penjualan Per Kategori</h5>
                                </div>
                                <div class="card-body custom-card-action p-0">
                                    {{-- <div id="payment-records-chart"></div> --}}
                                    <div class="table-responsive p-3">
                                        <table id="tabel-penjualan-kategori" class="table table-bordered table-striped">
                                            <thead class="text-center">
                                                <tr>
                                                    <th>Nama Kategori</th>
                                                    <th>Total Penjualan</th>
                                                    <th>Total Modal</th>
                                                    <th>Profit</th>
                                                    <th>% Profit</th>
                                                </tr>
                                            </thead>
                                            <tbody id="harian-body-kategori">
                                                <!-- Data akan diisi lewat JavaScript -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="card-header">
                                    <h5 class="card-title">Logs</h5>
                                </div>
                                <div class="card-body custom-card-action p-0">
                                    <div class="table-responsive p-3" style="max-height: 250px; overflow-y: auto;">
                                        <table class="table table-bordered table-striped">
                                            <thead class="text-center">
                                                <tr>
                                                    <th>Ref</th>
                                                    <th>Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($log as $lg)
                                                    <tr>
                                                        <td>{{ $lg->ref }}</td>
                                                        <td>{{ $lg->ketarangan }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="2" class="text-center text-danger">Belum ada log
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="row g-4">
                                <div class="col-lg-3">
                                    <div class="p-3 border border-dashed rounded">
                                        <div class="fs-12 text-muted mb-1">Produk</div>
                                        <h6 class="fw-bold text-dark">{{ $produk }}</h6>
                                        <div class="progress mt-2 ht-3">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: 100%">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="p-3 border border-dashed rounded">
                                        <div class="fs-12 text-muted mb-1">Costumer</div>
                                        <h6 class="fw-bold text-dark">{{ $costumer }}</h6>
                                        <div class="progress mt-2 ht-3">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: 100%">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="p-3 border border-dashed rounded">
                                        <div class="fs-12 text-muted mb-1">Outlet</div>
                                        <h6 class="fw-bold text-dark">{{ $outlet->count() }}</h6>
                                        <div class="progress mt-2 ht-3">
                                            <div class="progress-bar bg-danger" role="progressbar" style="width: 100%">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="p-3 border border-dashed rounded">
                                        <div class="fs-12 text-muted mb-1">Laba Bersih</div>
                                        <h6 class="fw-bold text-dark">
                                            {{ 'Rp ' . number_format($laba_bersih, 0, ',', '.') }}
                                        </h6>
                                        <div class="progress mt-2 ht-3">
                                            <div class="progress-bar bg-dark" role="progressbar" style="width: 100%">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- [Payment Records] end -->

                <!--! BEGIN: [Upcoming Schedule] !-->
                <div class="col-12">
                    <div class="card stretch stretch-full">
                        <div class="card-header">
                            <h5 class="card-title">Informasi PO Dari Outlet</h5>
                            <div class="card-header-action">
                                <div class="card-header-btn">
                                    <div data-bs-toggle="tooltip" title="Refresh">
                                        <a href="javascript:void(0);" class="avatar-text avatar-xs bg-warning"
                                            data-bs-toggle="refresh"> </a>
                                    </div>
                                    <div data-bs-toggle="tooltip" title="Maximize/Minimize">
                                        <a href="javascript:void(0);" class="avatar-text avatar-xs bg-success"
                                            data-bs-toggle="expand"> </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <!--! BEGIN: [Events] !-->
                            @forelse ($data as $po)
                                <div class="p-3 border border-dashed rounded-3 mb-3">
                                    <div class="d-flex justify-content-between">
                                        <div class="d-flex align-items-center gap-3">
                                            <div
                                                class="wd-50 ht-50 bg-soft-warning text-warning lh-1 d-flex align-items-center justify-content-center flex-column rounded-2 schedule-date">
                                                <span
                                                    class="fs-18 fw-bold mb-1 d-block">{{ \Carbon\Carbon::createFromFormat('d-m-Y', $po->tanggal_transaksi)->format('d') }}</span>
                                                <span
                                                    class="fs-10 fw-semibold text-uppercase d-block">{{ \Carbon\Carbon::createFromFormat('d-m-Y', $po->tanggal_transaksi)->format('M') }}</span>
                                            </div>
                                            <div class="text-dark">
                                                <a href="javascript:void(0);" class="fw-bold mb-2 text-truncate-1-line">
                                                    {{ $po->no_po }} - {{ $po->keterangan }}
                                                </a>
                                                <span class="fs-11 fw-normal text-muted text-truncate-1-line">
                                                    Total Qty: {{ $po->total_qty }} | Total: Rp
                                                    {{ number_format($po->total_harga, 0, ',', '.') }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="img-group lh-0 ms-3 justify-content-start d-none d-sm-flex">
                                            <a href="{{ route('superadmin.po-vw-outlet') }}"
                                                class="btn btn-primary btn-sm">
                                                Detail PO
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center">
                                    <i class="feather-slash fs-1"></i>
                                    <p class="mt-3 text-muted">Tidak ada data PO dari outlet.</p>
                                </div>
                            @endforelse
                            <!--! END: [Events] !-->
                        </div>
                    </div>
                </div>
                <!--! END: [Upcoming Schedule] !-->
            </div>
        </div>
        <!-- [ Main Content ] end -->
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/dashboard-init.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // let chart; // simpan chart supaya bisa di-destroy

            // function loadChart(uuidOutlet = "") {
            //     $.get("/superadmin/get-penjualan-bulanan", {
            //         uuid_outlet: uuidOutlet
            //     }, function(res) {
            //         if (chart) chart.destroy(); // hapus chart lama

            //         let options = {
            //             chart: {
            //                 height: 380,
            //                 width: "100%",
            //                 stacked: false,
            //                 toolbar: {
            //                     show: false
            //                 }
            //             },
            //             stroke: {
            //                 width: [2],
            //                 curve: "smooth",
            //                 lineCap: "round"
            //             },
            //             colors: ["#3454d1"],
            //             series: [{
            //                 name: "Total Penjualan",
            //                 type: "bar",
            //                 data: res.series
            //             }],
            //             xaxis: {
            //                 categories: ["JAN", "FEB", "MAR", "APR", "MAY", "JUN",
            //                     "JUL", "AUG", "SEP", "OCT", "NOV", "DEC"
            //                 ],
            //                 labels: {
            //                     style: {
            //                         fontSize: "10px",
            //                         colors: "#A0ACBB"
            //                     }
            //                 }
            //             },
            //             yaxis: {
            //                 labels: {
            //                     formatter: function(e) {
            //                         return "Rp " + e.toLocaleString("id-ID");
            //                     },
            //                     style: {
            //                         color: "#A0ACBB"
            //                     }
            //                 }
            //             },
            //             dataLabels: {
            //                 enabled: false
            //             },
            //             tooltip: {
            //                 y: {
            //                     formatter: function(e) {
            //                         return "Rp " + e.toLocaleString("id-ID");
            //                     }
            //                 }
            //             }
            //         };

            //         chart = new ApexCharts(document.querySelector("#payment-records-chart"), options);
            //         chart.render();
            //     });
            // }

            // // pertama kali load (semua outlet)
            // loadChart();

            //     function loadPenjualanHarian(uuidOutlet = "") {
            //         $.get("/superadmin/get-penjualan-harian", {
            //             uuid_user: uuidOutlet
            //         }, function(res) {
            //             let tbody = $("#harian-body");
            //             tbody.empty(); // kosongkan isi tabel

            //             if (!res.data || res.data.length === 0) {
            //                 tbody.append(`
        //         <tr>
        //             <td colspan="5" class="text-center">Tidak ada data</td>
        //         </tr>
        //     `);
            //                 return;
            //             }

            //             res.data.forEach(item => {
            //                 let penjualan = item.total_penjualan ?? 0;
            //                 let modal = item.total_modal ?? 0;
            //                 let profit = item.total_profit ?? 0;
            //                 let persen = item.persen_profit ?? 0;

            //                 tbody.append(`
        //         <tr>
        //             <td class="text-center">${item.tanggal}</td>
        //             <td class="text-end">Rp ${Number(penjualan).toLocaleString("id-ID")}</td>
        //             <td class="text-end">Rp ${Number(modal).toLocaleString("id-ID")}</td>
        //             <td class="text-end">Rp ${Number(profit).toLocaleString("id-ID")}</td>
        //             <td class="text-center">${persen} %</td>
        //         </tr>
        //     `);
            //             });
            //         }).fail(function() {
            //             $("#harian-body").html(`
        //     <tr>
        //         <td colspan="5" class="text-center text-danger">Gagal memuat data</td>
        //     </tr>
        // `);
            //         });
            //     }

            //     loadPenjualanHarian(); // Panggil fungsi untuk load penjualan harian


            //     function loadPenjualanBulanan(uuidOutlet = "") {
            //         $.get("/superadmin/get-penjualan-bulanan", {
            //             uuid_user: uuidOutlet,
            //         }, function(res) {
            //             let tbody = $("#bulanan-body");
            //             tbody.empty(); // kosongkan isi tabel

            //             if (!res.data || res.data.length === 0) {
            //                 tbody.append(`
        //         <tr>
        //             <td colspan="5" class="text-center">Tidak ada data</td>
        //         </tr>
        //     `);
            //                 return;
            //             }

            //             res.data.forEach(item => {
            //                 let penjualan = item.total_penjualan ?? 0;
            //                 let modal = item.total_modal ?? 0;
            //                 let profit = item.total_profit ?? 0;
            //                 let persen = item.persen_profit ?? 0;

            //                 tbody.append(`
        //         <tr>
        //             <td class="text-center">${item.bulan}</td>
        //             <td class="text-end">Rp ${Number(penjualan).toLocaleString("id-ID")}</td>
        //             <td class="text-end">Rp ${Number(modal).toLocaleString("id-ID")}</td>
        //             <td class="text-end">Rp ${Number(profit).toLocaleString("id-ID")}</td>
        //             <td class="text-center">${persen} %</td>
        //         </tr>
        //     `);
            //             });
            //         }).fail(function() {
            //             $("#bulanan-body").html(`
        //     <tr>
        //         <td colspan="5" class="text-center text-danger">Gagal memuat data</td>
        //     </tr>
        // `);
            //         });
            //     }

            //     loadPenjualanBulanan();

            function loadPenjualanTerlaku(uuidOutlet = "") {
                $.get("/superadmin/get-penjualan-terlaku", {
                    uuid_user: uuidOutlet
                }, function(res) {
                    // Kosongkan tbody
                    $('#terlaku-body').empty();
                    $('#untung-body').empty();

                    // Produk paling laku
                    res.top_laku.forEach(item => {
                        $('#terlaku-body').append(`
                <tr>
                    <td>${item.nama_barang}</td>
                    <td class="text-center">${item.total_terjual}</td>
                </tr>
            `);
                    });

                    // Produk untung banyak
                    res.top_untung.forEach(item => {
                        $('#untung-body').append(`
                <tr>
                    <td>${item.nama_barang}</td>
                    <td class="text-end">Rp ${item.total_profit.toLocaleString()}</td>
                </tr>
            `);
                    });
                });
            }


            loadPenjualanTerlaku();

            //     function loadPenjualanHarianJasa(uuidOutlet = "") {
            //         $.get("/superadmin/get-penjualan-harian-jasa", {
            //             uuid_user: uuidOutlet
            //         }, function(res) {
            //             let tbody = $("#harian-body-jasa");
            //             tbody.empty(); // kosongkan isi tabel

            //             if (!res.data || res.data.length === 0) {
            //                 tbody.append(`
        //         <tr>
        //             <td colspan="2" class="text-center">Tidak ada data</td>
        //         </tr>
        //     `);
            //                 return;
            //             }

            //             res.data.forEach(item => {
            //                 let jasa = item.total_jasa ?? 0;

            //                 tbody.append(`
        //         <tr>
        //             <td class="text-center">${item.tanggal}</td>
        //             <td class="text-end">Rp ${Number(jasa).toLocaleString("id-ID")}</td>
        //         </tr>
        //     `);
            //             });
            //         }).fail(function() {
            //             $("#harian-body-jasa").html(`
        //     <tr>
        //         <td colspan="2" class="text-center text-danger">Gagal memuat data</td>
        //     </tr>
        // `);
            //         });
            //     }

            //     loadPenjualanHarianJasa(); // Panggil fungsi untuk load penjualan harian


            //     function loadPenjualanBulananJasa(uuidOutlet = "") {
            //         $.get("/superadmin/get-penjualan-bulanan-jasa", {
            //             uuid_user: uuidOutlet,
            //         }, function(res) {
            //             let tbody = $("#bulanan-body-jasa");
            //             tbody.empty(); // kosongkan isi tabel

            //             if (!res.data || res.data.length === 0) {
            //                 tbody.append(`
        //         <tr>
        //             <td colspan="2" class="text-center">Tidak ada data</td>
        //         </tr>
        //     `);
            //                 return;
            //             }

            //             res.data.forEach(item => {
            //                 let jasa = item.total_jasa ?? 0;

            //                 tbody.append(`
        //         <tr>
        //             <td class="text-center">${item.bulan}</td>
        //             <td class="text-end">Rp ${Number(jasa).toLocaleString("id-ID")}</td>
        //         </tr>
        //     `);
            //             });
            //         }).fail(function() {
            //             $("#bulanan-body-jasa").html(`
        //     <tr>
        //         <td colspan="2" class="text-center text-danger">Gagal memuat data</td>
        //     </tr>
        // `);
            //         });
            //     }

            //     loadPenjualanBulananJasa();

            function loadPenjualanKategori(uuidOutlet = "") {
                $.get("/superadmin/get-penjualan-kategori", {
                    uuid_user: uuidOutlet
                }, function(res) {
                    let tbody = $("#harian-body-kategori");
                    tbody.empty(); // kosongkan isi tabel

                    if (!res.data || res.data.length === 0) {
                        tbody.append(`
                <tr>
                    <td colspan="5" class="text-center">Tidak ada data</td>
                </tr>
            `);
                        return;
                    }

                    res.data.forEach(item => {
                        let penjualan = item.total_penjualan ?? 0;
                        let modal = item.total_modal ?? 0;
                        let profit = item.total_profit ?? 0;
                        let persen = item.persen_profit ?? 0;

                        tbody.append(`
                <tr>
                    <td class="text-center">${item.nama_kategori}</td>
                    <td class="text-end">Rp ${Number(penjualan).toLocaleString("id-ID")}</td>
                    <td class="text-end">Rp ${Number(modal).toLocaleString("id-ID")}</td>
                    <td class="text-end">Rp ${Number(profit).toLocaleString("id-ID")}</td>
                    <td class="text-center">${persen} %</td>
                </tr>
            `);
                    });
                }).fail(function() {
                    $("#harian-body-kategori").html(`
            <tr>
                <td colspan="5" class="text-center text-danger">Gagal memuat data</td>
            </tr>
        `);
                });
            }

            loadPenjualanKategori(); // Panggil fungsi untuk load penjualan harian

            // function loadPenjualanKasir(uuidOutlet = "") {
            //     $.get("/superadmin/get-penjualan-kasir", {
            //         uuid_user: uuidOutlet
            //     }, function(res) {
            //         let tbody = $("#harian-body-kasir");
            //         tbody.empty(); // kosongkan isi tabel

            //         if (!res.data || res.data.length === 0) {
            //             tbody.append(`
        //             <tr>
        //                 <td colspan="5" class="text-center">Tidak ada data</td>
        //             </tr>
        //         `);
            //             return;
            //         }

            //         res.data.forEach(item => {
            //             let totalPenjualan = item.totalPenjualan ?? 0;
            //             let totalJasa = item.totalJasa ?? 0;
            //             let grandTotal = item.grandTotal ?? 0;

            //             tbody.append(`
        //             <tr>
        //                 <td class="text-center">${item.kasir}</td>
        //                 <td class="text-center">${item.totalTransaksi}</td>
        //                 <td class="text-center">${item.totalItem}</td>
        //                 <td class="text-end">Rp ${Number(totalPenjualan).toLocaleString("id-ID")}</td>
        //                 <td class="text-end">Rp ${Number(totalJasa).toLocaleString("id-ID")}</td>
        //                 <td class="text-end">Rp ${Number(grandTotal).toLocaleString("id-ID")}</td>
        //             </tr>
        //         `);
            //         });
            //     }).fail(function() {
            //         $("#harian-body-kasir").html(`
        //         <tr>
        //             <td colspan="5" class="text-center text-danger">Gagal memuat data</td>
        //         </tr>
        //     `);
            //     });
            // }

            // loadPenjualanKasir(); // Panggil fungsi untuk load penjualan harian

            //     function loadPenjualanTarget(uuidOutlet = "") {
            //         $.get("/superadmin/get-penjualan-kasir-harian", {
            //             uuid_user: uuidOutlet
            //         }, function(res) {
            //             let tbody = $("#target-body");
            //             tbody.empty();

            //             if (!res.data || res.data.length === 0) {
            //                 tbody.append(`<tr>
        //         <td colspan="12" class="text-center">Tidak ada data</td>
        //     </tr>`);
            //                 return;
            //             }

            //             res.data.forEach(group => {
            //                 let kasirList = group.kasir;
            //                 kasirList.forEach((item, index) => {
            //                     let row = "<tr>";

            //                     // Kolom tanggal hanya muncul sekali dengan rowspan
            //                     if (index === 0) {
            //                         row +=
            //                             `<td rowspan="${kasirList.length}">${group.tanggal}</td>`;
            //                     }

            //                     row += `
        //             <td>${item.nama}</td>
        //             <td>${(item.modal ?? 0).toLocaleString()}</td>
        //             <td>${(item.penjualan ?? 0).toLocaleString()}</td>
        //             <td>${(item.jasa ?? 0).toLocaleString()}</td>
        //             <td>${(item.profit ?? 0).toLocaleString()}</td>
        //             <td>${(item.tunai ?? 0).toLocaleString()}</td>
        //             <td>${(item.non_tunai ?? 0).toLocaleString()}</td>
        //             <td>${(item.total ?? 0).toLocaleString()}</td>
        //             <td>${(item.target_profit ?? 0).toLocaleString()}</td>
        //             <td>${(item.persentase ?? 0)}%</td>
        //             <td>${(item.selisih ?? 0).toLocaleString()}</td>
        //         </tr>`;
            //                     tbody.append(row);
            //                 });
            //             });
            //         }).fail(function() {
            //             $("#target-body").html(`<tr>
        //     <td colspan="12" class="text-center text-danger">Gagal memuat data</td>
        // </tr>`);
            //         });
            //     }

            function loadPenjualanTarget(uuidOutlet = "") {
                $.get("/superadmin/get-penjualan-kasir-harian", {
                    uuid_user: uuidOutlet
                }, function(res) {
                    let tbody = $("#target-body");
                    tbody.empty();

                    if (!res.data || res.data.length === 0) {
                        tbody.append(`<tr>
                <td colspan="12" class="text-center">Tidak ada data</td>
            </tr>`);
                        return;
                    }

                    res.data.forEach(group => {
                        let kasirList = group.kasir;
                        kasirList.forEach((item, index) => {
                            let row = "<tr>";

                            // Tanggal rowspan
                            if (index === 0) {
                                row +=
                                    `<td rowspan="${kasirList.length}">${group.tanggal}</td>`;
                            }

                            // Data kasir
                            row += `
                    <td>${item.nama}</td>
                    <td>${(item.modal ?? 0).toLocaleString()}</td>
                    <td>${(item.penjualan ?? 0).toLocaleString()}</td>
                    <td>${(item.jasa ?? 0).toLocaleString()}</td>
                    <td>${(item.profit ?? 0).toLocaleString()}</td>
                    <td>${(item.tunai ?? 0).toLocaleString()}</td>
                    <td>${(item.non_tunai ?? 0).toLocaleString()}</td>
                    <td>${(item.sub_total ?? 0).toLocaleString()}</td>
                `;

                            // Target profit, persentase, selisih rowspan
                            if (index === 0) {
                                row += `
                        <td rowspan="${kasirList.length}">${(item.total ?? 0).toLocaleString()}</td>
                        <td rowspan="${kasirList.length}">${(item.target_profit ?? 0).toLocaleString()}</td>
                        <td rowspan="${kasirList.length}">${(item.persentase ?? 0)}%</td>
                        <td rowspan="${kasirList.length}">${(item.selisih ?? 0).toLocaleString()}</td>
                    `;
                            }

                            row += "</tr>";
                            tbody.append(row);
                        });
                    });
                }).fail(function() {
                    $("#target-body").html(`<tr>
            <td colspan="12" class="text-center text-danger">Gagal memuat data</td>
        </tr>`);
                });
            }

            loadPenjualanTarget(); // Panggil fungsi untuk load penjualan harian

            function loadPenjualanTargetBulanan(uuidOutlet = "") {
                $.get("/superadmin/get-penjualan-kasir-bulanan", {
                    uuid_user: uuidOutlet
                }, function(res) {
                    let tbody = $("#target-bulanan-body");
                    tbody.empty();

                    if (!res.data || res.data.length === 0) {
                        tbody.append(`<tr>
                <td colspan="12" class="text-center">Tidak ada data</td>
            </tr>`);
                        return;
                    }

                    res.data.forEach(group => {
                        let kasirList = group.kasir;
                        kasirList.forEach((item, index) => {
                            let row = "<tr>";

                            // Tanggal rowspan
                            if (index === 0) {
                                row +=
                                    `<td rowspan="${kasirList.length}">${group.bulan}</td>`;
                            }

                            // Data kasir
                            row += `
                    <td>${item.nama}</td>
                    <td>${(item.modal ?? 0).toLocaleString()}</td>
                    <td>${(item.penjualan ?? 0).toLocaleString()}</td>
                    <td>${(item.jasa ?? 0).toLocaleString()}</td>
                    <td>${(item.profit ?? 0).toLocaleString()}</td>
                    <td>${(item.tunai ?? 0).toLocaleString()}</td>
                    <td>${(item.non_tunai ?? 0).toLocaleString()}</td>
                    <td>${(item.sub_total ?? 0).toLocaleString()}</td>
                `;

                            // Target profit, persentase, selisih rowspan
                            if (index === 0) {
                                row += `
                                <td rowspan="${kasirList.length}">${(item.total ?? 0).toLocaleString()}</td>
                        <td rowspan="${kasirList.length}">${(item.target_profit ?? 0).toLocaleString()}</td>
                        <td rowspan="${kasirList.length}">${(item.persentase ?? 0)}%</td>
                        <td rowspan="${kasirList.length}">${(item.selisih ?? 0).toLocaleString()}</td>
                    `;
                            }

                            row += "</tr>";
                            tbody.append(row);
                        });
                    });
                }).fail(function() {
                    $("#target-bulanan-body").html(`<tr>
            <td colspan="12" class="text-center text-danger">Gagal memuat data</td>
        </tr>`);
                });
            }

            loadPenjualanTargetBulanan(); // Panggil fungsi untuk load penjualan harian

            // reload saat ganti outlet
            $("#filter-outlet").on("change", function() {
                let uuidOutlet = $(this).val();
                // loadPenjualanHarian(uuidOutlet); // Panggil fungsi dengan outlet yang dipilih
                // loadPenjualanBulanan(uuidOutlet)
                loadPenjualanTerlaku(uuidOutlet);
                // loadPenjualanHarianJasa(uuidOutlet); // Panggil fungsi dengan outlet yang dipilih
                // loadPenjualanBulananJasa(uuidOutlet)
                // loadPenjualanKategori(uuidOutlet)
                // loadPenjualanKasir(uuidOutlet)
                loadPenjualanTarget(uuidOutlet)
                loadPenjualanTargetBulanan(uuidOutlet)
                // loadChart(uuidOutlet);
            });
        });
    </script>
@endpush
