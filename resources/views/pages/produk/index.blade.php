@extends('layouts.layout')
<style>
    .custom-card-action .table-responsive .table tbody tr:last-child .btn {
        border: 1px solid;
    }

    .custom-card-action .table-responsive .table tbody tr:last-child .btn:hover {
        background-color: var(--bs-btn-hover-bg);
        border-color: var(--bs-btn-hover-border-color);
    }
</style>
@section('content')
    <div class="nxl-content">
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10 text-capitalize">Master Data</h5>
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
                    @canCreate('Produk')
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
                            <div class="d-flex gap-2 w-50">
                                <select name="uuid_kategori" id="filter-kategori" data-placeholder="Pilih kategori"
                                    class="form-select">
                                    <option value="">Pilih Kategori</option>
                                    @foreach ($kategoris as $k)
                                        <option value="{{ $k->uuid }}">{{ $k->nama_kategori }}</option>
                                    @endforeach
                                </select>
                                <select name="uuid_suplayer" id="filter-suplayer" data-placeholder="Pilih suplayer"
                                    class="form-select">
                                    <option value="">Pilih Suplayer</option>
                                    @foreach ($suplayers as $s)
                                        <option value="{{ $s->uuid }}">{{ $s->nama }}</option>
                                    @endforeach
                                </select>
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
                            <div class="table-responsive">
                                <table style="width: 100%" id="dataTables" class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-capitalize">No</th>
                                            <th class="text-capitalize">kode</th>
                                            <th class="text-capitalize">nama barang</th>
                                            <th class="text-capitalize">merek</th>
                                            <th class="text-capitalize">hrg modal</th>
                                            <th class="text-capitalize">profit</th>
                                            <th class="text-capitalize">stock</th>
                                            <th class="text-capitalize">minstock</th>
                                            <th class="text-capitalize">maxstock</th>
                                            <th class="text-capitalize">satuan</th>
                                            <th class="text-capitalize">kategori</th>
                                            <th class="text-capitalize">sub kategori</th>
                                            <th class="text-capitalize">suplayer</th>
                                            <th class="text-capitalize">foto</th>
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
        <div class="modal-dialog">
            <form id="form" enctype="multipart/form-data">
                <input type="hidden" name="uuid" id="uuid">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Form {{ $module }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="text-capitalize form-label">kode</label>
                            <input type="text" name="kode" id="kode" class="form-control">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-2">
                            <label class="text-capitalize form-label">nama barang</label>
                            <input type="text" name="nama_barang" id="nama_barang" class="form-control">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-2">
                            <label class="text-capitalize form-label">merek</label>
                            <input type="text" name="merek" id="merek" class="form-control">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-2">
                            <label for="uuid_kategori" class="form-label text-capitalize">Kategori</label>
                            <select name="uuid_kategori" id="uuid_kategori" data-placeholder="Pilih inputan"
                                class="form-select basic-usage">
                                <option value=""></option>
                                @foreach ($kategoris as $k)
                                    <option value="{{ $k->uuid }}">{{ $k->nama_kategori }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-2">
                            <label for="uuid_sub_kategori" class="form-label text-capitalize">sub Kategori</label>
                            <select name="uuid_sub_kategori" id="uuid_sub_kategori" data-placeholder="Pilih inputan"
                                class="form-select basic-usage">
                                <option value=""></option>
                                @foreach ($sub_kategoris as $sk)
                                    <option value="{{ $sk->uuid }}">{{ $sk->nama_sub_kategori }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
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
                            <label class="text-capitalize form-label">hrg modal</label>
                            <input type="text" name="hrg_modal" id="hrg_modal" class="form-control formatRupiah">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6">
                                <label class="text-capitalize form-label">profit</label>
                                <input type="number" step="any" name="profit" id="profit"
                                    class="form-control">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-6">
                                <label class="text-capitalize form-label">hrg jual</label>
                                <input type="text" name="hrg_jual" id="hrg_jual" class="form-control formatRupiah">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6">
                                <label class="text-capitalize form-label">profit a</label>
                                <input type="number" step="any" name="profit_a" id="profit_a"
                                    class="form-control">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-6">
                                <label class="text-capitalize form-label">hrg jual a</label>
                                <input type="text" name="hrg_jual_a" id="hrg_jual_a"
                                    class="form-control formatRupiah">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6">
                                <label class="text-capitalize form-label">profit b</label>
                                <input type="number" step="any" name="profit_b" id="profit_b"
                                    class="form-control">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-6">
                                <label class="text-capitalize form-label">hrg jual b</label>
                                <input type="text" name="hrg_jual_b" id="hrg_jual_b"
                                    class="form-control formatRupiah">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6">
                                <label class="text-capitalize form-label">profit c</label>
                                <input type="number" step="any" name="profit_c" id="profit_c"
                                    class="form-control">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-6">
                                <label class="text-capitalize form-label">hrg jual c</label>
                                <input type="text" name="hrg_jual_c" id="hrg_jual_c"
                                    class="form-control formatRupiah">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6">
                                <label class="text-capitalize form-label">minstock</label>
                                <input type="number" name="minstock" id="minstock" class="form-control">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-6">
                                <label class="text-capitalize form-label">maxstock</label>
                                <input type="number" name="maxstock" id="maxstock" class="form-control">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="text-capitalize form-label">satuan</label>
                            <input type="text" name="satuan" id="satuan" class="form-control">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-2">
                            <label class="text-capitalize form-label">Foto</label>
                            <div class="wd-100 ht-100 position-relative overflow-hidden border border-gray-2 rounded">
                                <img src="{{ asset('assets/images/logo-abbr.png') }}"
                                    class="upload-pic img-fluid rounded h-100 w-100" alt="">
                                <div
                                    class="position-absolute start-50 top-50 end-0 bottom-0 translate-middle h-100 w-100 hstack align-items-center justify-content-center c-pointer upload-button">
                                    <i class="feather feather-camera" aria-hidden="true"></i>
                                </div>
                                <input class="file-upload" type="file" name="foto" accept="image/*">
                            </div>
                            <div class="invalid-feedback"></div>
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

        // Format Rupiah saat diketik
        $('.formatRupiah').on('input', function() {
            let angka = parseRupiah($(this).val());
            $(this).val(formatRupiah(angka));
        });

        // Fungsi untuk bind event profit/harga jual
        function bindProfitHargaJual(profitId, jualId) {
            // Hitung harga jual jika modal dan profit persen terisi
            $('#hrg_modal, #' + profitId).on('input', function() {
                let modal = parseRupiah($('#hrg_modal').val());
                let persen = parseFloat($('#' + profitId).val());

                if (modal > 0 && !isNaN(persen)) {
                    let harga_jual = modal + (modal * persen / 100);
                    $('#' + jualId).val(formatRupiah(Math.round(harga_jual)));
                }
            });

            // Hitung persen profit jika modal dan harga jual terisi
            $('#hrg_modal, #' + jualId).on('input', function() {
                let modal = parseRupiah($('#hrg_modal').val());
                let jual = parseRupiah($('#' + jualId).val());

                if (modal > 0 && jual > 0) {
                    let persen = ((jual - modal) / modal) * 100;
                    $('#' + profitId).val(persen.toFixed(2));
                }
            });
        }

        // Bind untuk semua set profit/harga jual
        bindProfitHargaJual('profit', 'hrg_jual');
        bindProfitHargaJual('profit_a', 'hrg_jual_a');
        bindProfitHargaJual('profit_b', 'hrg_jual_b');
        bindProfitHargaJual('profit_c', 'hrg_jual_c');

        // Pasang CSRF token untuk semua request AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#openModal').on('click', function() {
            // Buka modal
            $('#modal').modal('show');
            // Bersihkan form
            $('#form')[0].reset();
            $('#uuid').val('');

            // Reset semua input dan select di seluruh form
            $('#form').find('input').val('');
            $('#form').find('select').val('');

            // Kalau pakai select2, reset juga semua select2 di form
            $('#form').find('select').each(function() {
                $(this).val('').trigger('change');
            });

            // reset preview & file upload
            $('.upload-pic').attr('src', "{{ asset('assets/images/logo-abbr.png') }}");
            $('.file-upload').val('');

            // Hapus error lama
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
        });

        // Submit Form (Tambah / Edit)
        $('#form').on('submit', function(e) {
            e.preventDefault();

            let uuid = $('#uuid').val();

            let updateUrl = `{{ route('superadmin.produk-update', ':uuid') }}`;
            updateUrl = updateUrl.replace(':uuid', uuid);

            let url = uuid ? updateUrl :
                `{{ route('superadmin.produk-store') }}`;
            let method = uuid ? 'POST' : 'POST';

            let formData = new FormData(this);

            $.ajax({
                url: url,
                method: method,
                data: formData,
                contentType: false,
                processData: false,
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

        // Edit
        $('#dataTables').on('click', '.edit', function() {
            // Hapus error lama
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            $('#modal').modal('show');

            let uuid = $(this).data('uuid');
            let editUrl = `{{ route('superadmin.produk-edit', ':uuid') }}`;
            editUrl = editUrl.replace(':uuid', uuid);

            $.get(editUrl, function(res) {
                let hrg_modal = 0;

                $.each(res, function(key, value) {
                    let $field = $(`[name="${key}"]`);
                    if (!$field.length) return;

                    let type = $field.attr('type');

                    // Simpan harga modal
                    if (key === 'hrg_modal') {
                        hrg_modal = parseFloat(value);
                        $field.val(formatRupiah(value.toString()));
                        return;
                    }

                    // Format untuk semua input harga
                    if ($field.hasClass('formatRupiah')) {
                        $field.val(formatRupiah(value.toString()));
                    }
                    // Select2
                    else if ($field.hasClass('select2-hidden-accessible')) {
                        $field.val(value).trigger('change');
                    }
                    // Datepicker
                    else if ($field.hasClass('datepicker')) {
                        $field.datepicker('update', value);
                    }
                    // Checkbox / Radio
                    else if (type === 'checkbox' || type === 'radio') {
                        $(`[name="${key}"][value="${value}"]`).prop('checked', true);
                    }
                    // File
                    else if (type === 'file') {
                        // Jika ada file, tampilkan preview
                        if (value) {
                            $field.closest('.mb-2').find('.upload-pic')
                                .attr('src', `{{ asset('storage') }}/${value}`);
                        } else {
                            $field.closest('.mb-2').find('.upload-pic')
                                .attr('src', '{{ asset('assets/images/logo-abbr.png') }}');
                        }
                    }
                    // Default
                    else {
                        $field.val(value);
                    }
                });

                // Ambil profit dari response
                let profit = parseFloat(res.profit) || null;
                let profit_a = parseFloat(res.profit_a) || null;
                let profit_b = parseFloat(res.profit_b) || null;
                let profit_c = parseFloat(res.profit_c) || null;

                // Rumus: harga_jual = modal + (modal * profit% / 100)
                if (profit !== null) {
                    $(`[name="hrg_jual"]`).val(formatRupiah((Math.round(hrg_modal + (hrg_modal * profit /
                            100)))
                        .toString()));
                }
                if (profit_a !== null) {
                    $(`[name="hrg_jual_a"]`).val(formatRupiah((Math.round(hrg_modal + (hrg_modal *
                            profit_a / 100)))
                        .toString()));
                }
                if (profit_b !== null) {
                    $(`[name="hrg_jual_b"]`).val(formatRupiah((Math.round(hrg_modal + (hrg_modal *
                            profit_b / 100)))
                        .toString()));
                }
                if (profit_c !== null) {
                    $(`[name="hrg_jual_c"]`).val(formatRupiah((Math.round(hrg_modal + (hrg_modal *
                            profit_c / 100)))
                        .toString()));
                }
            });
        });

        // Hapus
        $('#dataTables').on('click', '.delete', function() {
            let uuid = $(this).data('uuid');
            let deleteUrl = `{{ route('superadmin.produk-delete', ':uuid') }}`;
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

        $('#dataTables').on('click', '.add-price', function() {
            let uuid = $(this).data('uuid');
            window.location.href = `{{ route('superadmin.produk-price', ':uuid') }}`.replace(':uuid', uuid);
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
                ajax: {
                    url: "{{ route('superadmin.produk-get') }}",
                    data: function(d) {
                        d.uuid_kategori = $('#filter-kategori').val();
                        d.uuid_suplayer = $('#filter-suplayer').val();
                    },
                },
                columns: [{
                        data: null,
                        class: 'mb-kolom-nomor align-content-center',
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'kode',
                        class: 'mb-kolom-text text-left align-content-center'
                    },
                    {
                        data: 'nama_barang',
                        class: 'mb-kolom-tanggal text-left align-content-center'
                    },
                    {
                        data: 'merek',
                        class: 'mb-kolom-tanggal text-left align-content-center'
                    },
                    {
                        data: 'hrg_modal',
                        class: 'mb-kolom-tanggal text-left align-content-center',
                        render: function(data, type, row) {
                            // Format harga ke Rupiah
                            return formatRupiah(data.toString());
                        }
                    },
                    {
                        data: 'profit',
                        class: 'mb-kolom-tanggal text-left align-content-center'
                    },
                    {
                        data: 'total_stok',
                        class: 'mb-kolom-tanggal text-left align-content-center'
                    },
                    {
                        data: 'minstock',
                        class: 'mb-kolom-tanggal text-left align-content-center'
                    },
                    {
                        data: 'maxstock',
                        class: 'mb-kolom-tanggal text-left align-content-center'
                    },
                    {
                        data: 'satuan',
                        class: 'mb-kolom-tanggal text-left align-content-center'
                    },
                    {
                        data: 'kategori',
                        class: 'mb-kolom-tanggal text-left align-content-center'
                    },
                    {
                        data: 'sub_kategori',
                        class: 'mb-kolom-tanggal text-left align-content-center'
                    },
                    {
                        data: 'suplayer',
                        class: 'mb-kolom-tanggal text-left align-content-center'
                    },
                    {
                        data: 'foto',
                        render: function(data, type, row) {
                            if (data) {
                                return `<img src="{{ asset('storage') }}/${data}" class="img-fluid rounded" style="max-width: 50px;">`;
                            }
                            return '<span class="text-muted">Tidak ada foto</span>';
                        },
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
                        let urlHostory =
                            "{{ route('superadmin.price-history', ['params' => ':id']) }}";
                        urlHostory = urlHostory.replace(':id', data);

                        let urlOpname =
                            "{{ route('superadmin.opname-stock', ['params' => ':id']) }}";
                        urlOpname = urlOpname.replace(':id', data);

                        let urlKartuSock =
                            "{{ route('superadmin.kartu-stock', ['params' => ':id']) }}";
                        urlKartuSock = urlKartuSock.replace(':id', data);
                        return `
                                <div class="hstack gap-2 justify-content-end">
                                    @canEdit('Produk')
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
                                    @canDelete('Produk')
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
                                    @canEdit('Produk')
                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="#" class="btn btn-outline-primary btn-sm add-price" data-uuid="${data}">
                                        Produk Price
                                    </a>
                                    <a href="${urlHostory}" class="btn btn-outline-secondary btn-sm">
                                        Price History
                                    </a>
                                    <a href="${urlOpname}" class="btn btn-outline-info btn-sm">
                                        Opname Stock
                                    </a>
                                    <a href="${urlKartuSock}" class="btn btn-outline-warning btn-sm">
                                        Kartu Stock
                                    </a>
                                    </div>
                                    @endcanEdit
                                </div>
                    `;
                    }
                }]
            });
        };

        $('#filter-kategori, #filter-suplayer').on('change', function() {
            $('#dataTables').DataTable().ajax.reload();
        });

        $(function() {
            initDatatable();
        });
    </script>
@endpush
