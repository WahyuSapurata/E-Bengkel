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
                    <li class="breadcrumb-item"><a href="index.html">Home</a></li>
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
                                <div class="col-lg-6">
                                    <div class="p-3 border border-dashed rounded">
                                        <div class="fs-12 text-muted mb-1">Produk</div>
                                        <h6 class="fw-bold text-dark">{{ $produk }}</h6>
                                        <div class="progress mt-2 ht-3">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: 100%">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
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

            function loadChart() {
                $.get("/outlet/get-penjualan-bulanan", {
                    uuid_outlet: "{{ Auth::user()->uuid }}"
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
        });
    </script>
@endpush
