@extends('layouts.layout')
@section('content')
    <div class="nxl-content">
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10 text-capitalize">Accounting</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
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
                            <div class="d-flex gap-2">
                                <input type="text" class="form-control" id="reportrange">
                            </div>
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
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>PENDAPATAN</th>
                                            <th>BEBAN</th>
                                        </tr>
                                    </thead>
                                    <tbody id="laba-rugi-body">
                                        <tr>
                                            <td colspan="2" class="text-center">Memuat data...</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th id="total-pendapatan"></th>
                                            <th id="total-beban"></th>
                                        </tr>
                                        <tr>
                                            <th colspan="2" class="text-end" id="laba-bersih"></th>
                                        </tr>
                                    </tfoot>
                                </table>
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
        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR'
            }).format(angka);
        }

        function loadLabaRugi() {
            let tanggal = $('#reportrange').val().split(' - ');
            let tanggal_awal = moment(tanggal[0], 'MM/DD/YYYY').format('DD-MM-YYYY') || '';
            let tanggal_akhir = moment(tanggal[1], 'MM/DD/YYYY').format('DD-MM-YYYY') || '';

            $.ajax({
                url: "{{ route('superadmin.get-laba-rugi') }}",
                data: {
                    tanggal_awal,
                    tanggal_akhir
                },
                success: function(res) {
                    let pendapatanRows = '';
                    res.pendapatan.forEach(p => {
                        pendapatanRows +=
                            `<tr><td>${p.nama} (${p.kode})</td><td class="text-end">${formatRupiah(p.total)}</td></tr>`;
                    });
                    pendapatanRows +=
                        `<tr><td><b>Total Pendapatan</b></td><td class="text-end"><b>${formatRupiah(res.total_pendapatan)}</b></td></tr>`;

                    let bebanRows = '';
                    res.beban.forEach(b => {
                        bebanRows +=
                            `<tr><td>${b.nama} (${b.kode})</td><td class="text-end">${formatRupiah(b.total)}</td></tr>`;
                    });
                    bebanRows +=
                        `<tr><td><b>Total Beban</b></td><td class="text-end"><b>${formatRupiah(res.total_beban)}</b></td></tr>`;

                    $("#laba-rugi-body").html(`
                <tr>
                    <td>
                        <table class="table mb-0">${pendapatanRows}</table>
                    </td>
                    <td>
                        <table class="table mb-0">${bebanRows}</table>
                    </td>
                </tr>
            `);

                    $("#laba-bersih").text(`Laba Bersih: ${formatRupiah(res.laba_bersih)}`);
                }
            });
        }

        $(function() {
            loadLabaRugi();
            $('#reportrange').on('apply.daterangepicker', function() {
                loadLabaRugi();
            });
        });
    </script>
@endpush
