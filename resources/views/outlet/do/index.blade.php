@extends('layouts.layout')
@section('content')
    <div class="nxl-content">
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10 text-capitalize">Transaksi</h5>
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
                            <div class="table-responsive">
                                <table style="width: 100%" id="dataTables" class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-capitalize">No</th>
                                            <th class="text-capitalize">no do</th>
                                            <th class="text-capitalize">tanggal kirim</th>
                                            <th class="text-capitalize">list barang</th>
                                            <th class="text-capitalize">total harga</th>
                                            <th class="text-capitalize">status</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('modals')
    <!-- Modal Form -->
    <div class="modal fade" id="modal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
        aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="form">
                <input type="hidden" name="uuid" id="uuid">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Form {{ $module }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="text-capitalize form-label">status</label>
                            <select name="status" id="status" data-placeholder="Pilih inputan"
                                class="form-select basic-usage">
                                <option value=""></option>
                                <option value="diterima">Diterima</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <hr>

                        <div id="alokasi-container">
                            <!-- ini akan diisi via JS -->
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        function initSelect2(element) {
            element.select2({
                theme: "bootstrap-5",
                width: '100%',
                placeholder: element.data('placeholder'),
                dropdownParent: element.closest('.modal-body')
            });
        }

        // Init select2 pertama kali
        $('.basic-usage').each(function() {
            initSelect2($(this));
        });

        function parseRupiah(value) {
            return parseInt(value.replace(/[^0-9]/g, ''), 10) || 0;
        }

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

        // Pasang CSRF token untuk semua request AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Submit Form (Tambah / Edit)
        $('#form').on('submit', function(e) {
            e.preventDefault();

            let uuid = $('#uuid').val();

            let updateUrl = `{{ route('outlet.aprove-do-outlet', ':uuid') }}`;
            updateUrl = updateUrl.replace(':uuid', uuid);

            let method = 'POST';

            $.ajax({
                url: updateUrl,
                method: method,
                data: $(this).serialize(),
                success: function(res) {
                    Swal.fire({
                        title: "Sukses",
                        text: res.message,
                        icon: "success",
                        showConfirmButton: false,
                        timer: 1500,
                    });
                    // Bersihkan error lama
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').remove();

                    // Reset semua input
                    let form = $('#form'); // jQuery object
                    form[0].reset(); // ✅ ini baru bisa

                    // Hapus semua produk-row kecuali baris pertama
                    $('#produk-wrapper .produk-row').not(':first').remove();

                    // Reset baris pertama
                    $('#produk-wrapper .produk-row:first').find('input, select').val('');

                    // Kalau pakai select2, reset juga
                    $('#produk-wrapper .produk-row:first').find('select').val('').trigger('change');

                    // Tutup modal
                    $('#modal').modal('hide');

                    // Refresh datatable
                    $('#dataTables').DataTable().ajax.reload();
                },
                error: function(xhr) {
                    if (xhr.status === 422) { // Error validasi Laravel
                        let errors = xhr.responseJSON.errors;

                        // Bersihkan error lama
                        $('.is-invalid').removeClass('is-invalid');
                        $('.invalid-feedback').remove();

                        // Loop semua error
                        $.each(errors, function(field, messages) {
                            let input = $(`[name="${field}"]`);
                            input.addClass('is-invalid');

                            // Tambahkan feedback di bawah input
                            input.after(`<div class="invalid-feedback">${messages[0]}</div>`);
                        });
                    } else {
                        Swal.fire({
                            title: "Eror",
                            text: xhr.responseJSON.message || "Terjadi kesalahan",
                            icon: "warning",
                            showConfirmButton: false,
                            timer: 1500,
                        });
                    }
                }
            });
        });

        // Validasi total qty (tidak boleh lebih dari jumlah kirim)
        $(document).on('input', 'input[name^="alokasi"]', function() {
            const parent = $(this).closest('.border');
            const total = parseInt(parent.data('total'));
            const gudang = parseInt(parent.find('input[name*="[qty_gudang]"]').val()) || 0;
            const toko = parseInt(parent.find('input[name*="[qty_toko]"]').val()) || 0;
            const totalInput = gudang + toko;

            if (totalInput > total) {
                parent.find('.warning-text').remove();
                parent.append(
                    `<div class="warning-text text-danger mt-1">Total alokasi (${totalInput}) melebihi jumlah dikirim (${total})!</div>`
                );
            } else {
                parent.find('.warning-text').remove();
            }
        });

        // Edit pembelian
        $('#dataTables').on('click', '.edit', function() {
            $('#modal').modal('show');
            $('#uuid').val($(this).data('uuid'));

            $.get(`/outlet/detail-do-outlet/${$(this).data('uuid')}`, function(res) {
                let html = '';
                res.forEach(item => {
                    html += `
                    <div class="border p-2 mb-2" data-total="${item.qty}">
                        <strong>${item.nama_barang}</strong> (Total dikirim: ${item.qty})
                        <input type="hidden" name="alokasi[${item.uuid_produk}][uuid_produk]" value="${item.uuid_produk}">
                        <div class="row mt-1">
                            <div class="col-md-6">
                                <label>Ke Gudang</label>
                                <input type="number" name="alokasi[${item.uuid_produk}][qty_gudang]" class="form-control" min="0" value="0">
                            </div>
                            <div class="col-md-6">
                                <label>Ke Toko</label>
                                <input type="number" name="alokasi[${item.uuid_produk}][qty_toko]" class="form-control" min="0" value="0">
                            </div>
                        </div>
                    </div>`;
                });
                $('#alokasi-container').html(html);
            });
        });

        const initDatatable = () => {
            // Destroy existing DataTable if it exists
            if ($.fn.DataTable.isDataTable('#dataTables')) {
                $('#dataTables').DataTable().clear().destroy();
            }

            $('#dataTables').DataTable({
                responsive: true,
                pageLength: 10,
                processing: true,
                serverSide: true,
                ajax: "{{ route('outlet.do-vw-outlet-get') }}",
                columns: [{
                        data: null,
                        class: 'mb-kolom-nomor align-content-center',
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'no_do',
                        class: 'mb-kolom-text text-left align-content-center'
                    },
                    {
                        data: 'tanggal_kirim',
                        class: 'mb-kolom-tanggal text-left align-content-center'
                    },
                    {
                        data: 'detail_produk',
                        class: 'mb-kolom-tanggal text-left align-content-center',
                        render: function(data, type, row) {
                            if (data && data.length > 0) {
                                return data.map(item => {
                                    return `<span class="badge bg-primary">${item.nama_barang} (${item.qty})</span>`;
                                }).join(' ');
                            }
                            return '-';
                        }
                    },
                    {
                        data: 'total_harga',
                        class: 'mb-kolom-tanggal text-left align-content-center',
                        render: function(data, type, row) {
                            return formatRupiah(data.toString());
                        }
                    },
                    {
                        data: 'status',
                        class: 'mb-kolom-tanggal text-left align-content-center',
                        render: function(data, type, row) {
                            return `
                                <span class="badge text-uppercase bg-${data === 'diterima' ? 'success' : 'danger'}">
                                    ${data}
                                </span>
                            `;
                        }
                    },
                    {
                        data: 'uuid', // akan diganti di columnDefs
                        orderable: false,
                        searchable: false
                    }
                ],
                columnDefs: [{
                    targets: -1, // kolom terakhir
                    title: 'Aksi',
                    class: 'mb-kolom-aksi text-end',
                    render: function(data, type, row) {
                        return `
                                <div class="hstack gap-2 justify-content-end">
                                    <a href="#" class="avatar-text avatar-md edit" data-uuid="${data}">
                                        <!-- Icon Edit -->
                                        <svg stroke="currentColor" fill="none" stroke-width="2"
                                            viewBox="0 0 24 24" stroke-linecap="round"
                                            stroke-linejoin="round" height="1em" width="1em">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </a>
                                </div>
                    `;
                    }
                }]
            });
        };

        $(function() {
            initDatatable();
        });
    </script>
@endpush
