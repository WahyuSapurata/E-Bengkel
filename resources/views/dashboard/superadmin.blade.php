@extends('layouts.layout')
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
                <div class="col-xxl-8">
                    <div class="card stretch stretch-full">
                        <div class="card-header">
                            <h5 class="card-title">Penjualan Bulanan</h5>
                            <div class="card-header-action">
                                <div class="card-header-btn">
                                    <div data-bs-toggle="tooltip" title="Delete">
                                        <a href="javascript:void(0);" class="avatar-text avatar-xs bg-danger"
                                            data-bs-toggle="remove"> </a>
                                    </div>
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
                        <div class="card-body custom-card-action p-0">
                            <div id="payment-records-chart"></div>
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
                                        <h6 class="fw-bold text-dark">{{ 'Rp ' . number_format($laba_bersih, 0, ',', '.') }}
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
                <div class="col-xxl-4">
                    <div class="card stretch stretch-full">
                        <div class="card-header">
                            <h5 class="card-title">Informasi PO Dari Outlet</h5>
                            <div class="card-header-action">
                                <div class="card-header-btn">
                                    <div data-bs-toggle="tooltip" title="Delete">
                                        <a href="javascript:void(0);" class="avatar-text avatar-xs bg-danger"
                                            data-bs-toggle="remove"> </a>
                                    </div>
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
                                            @foreach (json_decode($po->detail_produk, true) as $produk)
                                                <a href="javascript:void(0)" class="avatar-text avatar-md"
                                                    data-bs-toggle="tooltip" data-bs-trigger="hover"
                                                    title="{{ $produk['nama_barang'] }}">
                                                    {{ strtoupper(substr($produk['nama_barang'], 0, 1)) }}
                                                </a>
                                            @endforeach
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
            let chart; // simpan chart supaya bisa di-destroy

            function loadChart(uuidOutlet = "") {
                $.get("/superadmin/get-penjualan-bulanan", {
                    uuid_outlet: uuidOutlet
                }, function(res) {
                    if (chart) chart.destroy(); // hapus chart lama

                    let options = {
                        chart: {
                            height: 380,
                            width: "100%",
                            stacked: false,
                            toolbar: {
                                show: false
                            }
                        },
                        stroke: {
                            width: [2],
                            curve: "smooth",
                            lineCap: "round"
                        },
                        colors: ["#3454d1"],
                        series: [{
                            name: "Total Penjualan",
                            type: "bar",
                            data: res.series
                        }],
                        xaxis: {
                            categories: ["JAN", "FEB", "MAR", "APR", "MAY", "JUN",
                                "JUL", "AUG", "SEP", "OCT", "NOV", "DEC"
                            ],
                            labels: {
                                style: {
                                    fontSize: "10px",
                                    colors: "#A0ACBB"
                                }
                            }
                        },
                        yaxis: {
                            labels: {
                                formatter: function(e) {
                                    return "Rp " + e.toLocaleString("id-ID");
                                },
                                style: {
                                    color: "#A0ACBB"
                                }
                            }
                        },
                        dataLabels: {
                            enabled: false
                        },
                        tooltip: {
                            y: {
                                formatter: function(e) {
                                    return "Rp " + e.toLocaleString("id-ID");
                                }
                            }
                        }
                    };

                    chart = new ApexCharts(document.querySelector("#payment-records-chart"), options);
                    chart.render();
                });
            }

            // pertama kali load (semua outlet)
            loadChart();

            // reload saat ganti outlet
            $("#filter-outlet").on("change", function() {
                let uuidOutlet = $(this).val();
                loadChart(uuidOutlet);
            });
        });
    </script>
@endpush
