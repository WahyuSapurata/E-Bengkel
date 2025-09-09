@extends('layouts.layout')
@section('content')
    <div class="nxl-content">
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10 text-capitalize">Transaksi</h5>
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
                    @canCreate('PO')
                    <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                        <a href="#" id="openModal" class="btn btn-primary"><svg stroke="currentColor" fill="none"
                                stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"
                                class="me-2" height="16" width="16" xmlns="http://www.w3.org/2000/svg">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg><span>Tambah Data</span></a>
                    </div>
                    @endcanCreate
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
                                            <th class="text-capitalize">no po</th>
                                            <th class="text-capitalize">nama suplayer</th>
                                            <th class="text-capitalize">total harga</th>
                                            <th class="text-capitalize">tanggal transaksi</th>
                                            <th class="text-capitalize">Keterangan</th>
                                            <th class="text-capitalize">created by</th>
                                            <th class="text-capitalize">updated by</th>
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
                            <label class="text-capitalize form-label">suplayer</label>
                            <select name="uuid_suplayer" id="uuid_suplayer" data-placeholder="Pilih inputan"
                                class="form-select basic-usage">
                                <option value=""></option>
                                @foreach ($suplayers as $s)
                                    <option value="{{ $s->uuid }}">{{ $s->nama }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-2">
                            <label class="text-capitalize form-label">tanggal transaksi</label>
                            <input type="text" name="tanggal_transaksi" id="tanggal_transaksi"
                                class="form-control dateofBirth">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-2">
                            <label class="text-capitalize form-label">keterangan</label>
                            <textarea name="keterangan" id="keterangan" class="form-control" cols="30" rows="2"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                        <hr>
                        <label class="text-uppercase form-label">Produk</label>
                        <div id="produk-wrapper">
                            <div class="row mb-2 produk-row">
                                <div class="col-4">
                                    <label class="text-capitalize form-label">Produk</label>
                                    <select name="uuid_produk[]" id="uuid_produk" data-placeholder="Pilih inputan"
                                        class="form-select basic-usage">
                                        <option value=""></option>
                                    </select>
                                </div>
                                <div class="col-4">
                                    <label class="text-capitalize form-label">QTY</label>
                                    <input type="number" name="qty[]" class="form-control">
                                </div>
                                <div class="col-4">
                                    <label class="text-capitalize form-label">Harga</label>
                                    <input type="text" name="harga[]" class="form-control formatRupiah">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-center gap-2 mt-2">
                            <button type="button" id="btn-tambah" class="btn btn-success">Tambah Produk</button>
                            <button type="button" class="btn btn-danger btn-remove">Hapus</button>
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
        $(document).ready(function() {
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

            $('#uuid_suplayer').on('change', function() {
                let uuid = $(this).val();
                let $produkSelect = $('#uuid_produk');

                $produkSelect.html('<option value="">Loading...</option>');

                if (uuid) {
                    $.getJSON(`/superadmin/transaksi/po-pusat-get-produk-by-suplayer/${uuid}`, function(
                        data) {
                        $produkSelect.html(produkOptions(data));
                    });
                } else {
                    $produkSelect.html('<option value=""></option>');
                }
            });

            // Tambah produk
            $("#btn-tambah").click(function() {
                let firstRow = $(".produk-row").first();

                // Hapus instance select2 di row pertama supaya markup jadi select biasa
                firstRow.find('.basic-usage').select2('destroy');

                // Clone row
                let newRow = firstRow.clone();

                // Kosongkan value
                newRow.find("input").val("");
                newRow.find("select").val("");

                // Kembalikan select2 ke row pertama
                initSelect2(firstRow.find('.basic-usage'));

                // Append row baru
                $("#produk-wrapper").append(newRow);

                // Init select2 di row baru
                initSelect2(newRow.find('.basic-usage'));
            });

            // Hapus produk
            $(document).on("click", ".btn-remove", function() {
                let rows = $(".produk-row");
                if (rows.length > 1) {
                    rows.last().remove();
                } else {
                    Swal.fire({
                        title: "Warning",
                        text: "Minimal satu produk harus ada.",
                        icon: "warning",
                        showConfirmButton: false,
                        timer: 1500,
                    });
                }
            });
        });

        // Fungsi parse angka dari string Rupiah
        function parseRupiah(value) {
            return parseInt(value.replace(/[^0-9]/g, ''), 10) || 0;
        }

        // Fungsi format ke Rupiah
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

        // Event delegation untuk semua input formatRupiah (termasuk yang ditambahkan dinamis)
        $(document).on('input', '.formatRupiah', function() {
            let angka = parseRupiah($(this).val());
            $(this).val(formatRupiah(angka));
        });

        // Pasang CSRF token untuk semua request AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#openModal').on('click', function() {
            // Buka modal
            $('#modal').modal('show');

            // Bersihkan form HTML (reset value asli)
            $('#form')[0].reset();

            // Hapus semua produk-row kecuali yang pertama
            $('#produk-wrapper .produk-row').not(':first').remove();

            // Reset semua input dan select di seluruh form
            $('#form').find('input').val('');
            $('#form').find('select').val('');

            // Kalau pakai select2, reset juga semua select2 di form
            $('#form').find('select').each(function() {
                $(this).val('').trigger('change');
            });

            // Bersihkan field hidden UUID
            $('#uuid').val('');

            // Hapus error lama
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
        });

        // Submit Form (Tambah / Edit)
        $('#form').on('submit', function(e) {
            e.preventDefault();

            let uuid = $('#uuid').val();

            let updateUrl = `{{ route('superadmin.po-pusat-update', ':uuid') }}`;
            updateUrl = updateUrl.replace(':uuid', uuid);

            let url = uuid ? updateUrl :
                `{{ route('superadmin.po-pusat-store') }}`;
            let method = uuid ? 'POST' : 'POST';

            $.ajax({
                url: url,
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

        // Fungsi global untuk buat <option> produk
        function produkOptions(data, selectedUuid = null) {
            let html = '<option value=""></option>';
            data.forEach(function(p) {
                html +=
                    `<option value="${p.uuid}" ${p.uuid === selectedUuid ? 'selected' : ''}>${p.nama_barang}</option>`;
            });
            return html;
        }

        // Edit pembelian
        $('#dataTables').on('click', '.edit', function() {
            // Reset modal error
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
            $('#modal').modal('show');

            let uuid = $(this).data('uuid');
            let editUrl = `{{ route('superadmin.po-pusat-edit', ':uuid') }}`;
            editUrl = editUrl.replace(':uuid', uuid);

            $.get(editUrl, function(res) {
                // Isi form utama
                $.each(res, function(key, value) {
                    if (key !== 'details') {
                        $(`[name="${key}"]`).val(value);
                    }
                    if ($(`[name="${key}"]`).hasClass('formatRupiah')) {
                        $(`[name="${key}"]`).val(formatRupiah(value.toString()));
                    }
                });

                // Bersihkan produk-wrapper
                $('#produk-wrapper').empty();

                // Loop produk di detail
                res.detail_produk.forEach(function(item) {
                    // Ambil produk dari supplier yang sesuai
                    $.getJSON(
                        `/superadmin/transaksi/po-pusat-get-produk-by-suplayer/${res.uuid_suplayer}`,
                        function(data) {
                            let row = `
                                        <div class="row mb-2 produk-row">
                                            <div class="col-4">
                                                <select name="uuid_produk[]" class="form-select basic-usage" data-placeholder="Pilih produk">
                                                    ${produkOptions(data, item.uuid_produk)}
                                                </select>
                                            </div>
                                            <div class="col-4">
                                                <input type="number" name="qty[]" class="form-control" value="${item.qty}">
                                            </div>
                                            <div class="col-4">
                                                <input type="text" name="harga[]" class="form-control formatRupiah" value="${formatRupiah(parseRupiah(item.harga))}">
                                            </div>
                                        </div>
                                    `;
                            $('#produk-wrapper').append(row);

                            // Re-init select2 setelah append
                            $('.basic-usage').select2({
                                theme: "bootstrap-5",
                                width: '100%',
                                placeholder: $(this).data('placeholder'),
                                dropdownParent: $('#modal').find('.modal-body')
                            });
                        });
                });
            });
        });

        // Hapus
        $('#dataTables').on('click', '.delete', function() {
            let uuid = $(this).data('uuid');
            let deleteUrl = `{{ route('superadmin.po-pusat-delete', ':uuid') }}`;
            deleteUrl = deleteUrl.replace(':uuid', uuid);

            Swal.fire({
                title: 'Yakin hapus?',
                text: "Data yang dihapus tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: deleteUrl,
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            Swal.fire({
                                title: "Sukses",
                                text: res.message,
                                icon: "success",
                                showConfirmButton: false,
                                timer: 1500,
                            });
                            $('#dataTables').DataTable().ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: "Gagal",
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan.',
                                icon: "error"
                            });
                        }
                    });
                }
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
                ajax: "{{ route('superadmin.po-pusat-get') }}",
                columns: [{
                        data: null,
                        class: 'mb-kolom-nomor align-content-center',
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'no_po',
                        class: 'mb-kolom-text text-left align-content-center'
                    },
                    {
                        data: 'nama_suplayer',
                        class: 'mb-kolom-tanggal text-left align-content-center'
                    },
                    {
                        data: 'total_harga',
                        class: 'mb-kolom-tanggal text-left align-content-center',
                        render: function(data, type, row) {
                            return formatRupiah(data.toString());
                        }
                    },
                    {
                        data: 'tanggal_transaksi',
                        class: 'mb-kolom-tanggal text-left align-content-center'
                    },
                    {
                        data: 'keterangan',
                        class: 'mb-kolom-tanggal text-left align-content-center'
                    },
                    {
                        data: 'created_by',
                        class: 'mb-kolom-tanggal text-left align-content-center'
                    },
                    {
                        data: 'updated_by',
                        class: 'mb-kolom-tanggal text-left align-content-center'
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
                                    @canEdit('PO')
                                    <a href="#" class="avatar-text avatar-md edit" data-uuid="${data}">
                                        <!-- Icon Edit -->
                                        <svg stroke="currentColor" fill="none" stroke-width="2"
                                            viewBox="0 0 24 24" stroke-linecap="round"
                                            stroke-linejoin="round" height="1em" width="1em">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </a>
                                    @endcanEdit
                                    @canDelete('PO')
                                    <a href="#" class="avatar-text avatar-md delete" data-uuid="${data}">
                                        <!-- Icon Delete -->
                                        <svg stroke="currentColor" fill="none" stroke-width="2"
                                            viewBox="0 0 24 24" stroke-linecap="round"
                                            stroke-linejoin="round" height="1em" width="1em">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4
                                                a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            <line x1="10" y1="11" x2="10" y2="17"></line>
                                            <line x1="14" y1="11" x2="14" y2="17"></line>
                                        </svg>
                                    </a>
                                    @endcanDelete
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
