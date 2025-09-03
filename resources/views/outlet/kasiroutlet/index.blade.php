@extends('layouts.layout')
@section('content')
    <div class="nxl-content">
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10 text-capitalize">Outlet</h5>
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
                    <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                        <a href="#" id="openModal" class="btn btn-primary"><svg stroke="currentColor" fill="none"
                                stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"
                                class="me-2" height="16" width="16" xmlns="http://www.w3.org/2000/svg">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg><span>Tambah Data</span></a>
                    </div>
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
                                            <th class="text-capitalize">nama</th>
                                            <th class="text-capitalize">username</th>
                                            <th class="text-capitalize">password</th>
                                            <th class="text-capitalize">alamat</th>
                                            <th class="text-capitalize">telepon</th>
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
            <form id="form">
                <input type="hidden" name="uuid" id="uuid">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Form {{ $module }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="text-capitalize form-label">nama</label>
                            <input type="text" name="nama" id="nama" class="form-control">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-2">
                            <label class="text-capitalize form-label">username</label>
                            <input type="text" name="username" id="username" class="form-control">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-2">
                            <label class="text-capitalize form-label">password</label>
                            <input type="text" name="password_hash" id="password_hash" class="form-control">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-2">
                            <label class="text-capitalize form-label">alamat</label>
                            <input type="text" name="alamat" id="alamat" class="form-control">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-2">
                            <label class="text-capitalize form-label">telepon</label>
                            <input type="text" name="telepon" id="telepon" class="form-control">
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
            // Hapus error lama
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
        });

        // Submit Form (Tambah / Edit)
        $('#form').on('submit', function(e) {
            e.preventDefault();

            let uuid = $('#uuid').val();

            let updateUrl = `{{ route('outlet.kasir-outlet-update', ':uuid') }}`;
            updateUrl = updateUrl.replace(':uuid', uuid);

            let url = uuid ? updateUrl :
                `{{ route('outlet.kasir-outlet-store') }}`;
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
            // Buka modal
            // Hapus error lama
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
            $('#modal').modal('show');
            let uuid = $(this).data('uuid');
            let editUrl = `{{ route('outlet.kasir-outlet-edit', ':uuid') }}`;
            editUrl = editUrl.replace(':uuid', uuid);
            $.get(editUrl, function(res) {
                $.each(res, function(key, value) {
                    $(`[name="${key}"]`).val(value);
                });
            });
        });

        // Hapus
        $('#dataTables').on('click', '.delete', function() {
            let uuid = $(this).data('uuid');
            let deleteUrl = `{{ route('outlet.kasir-outlet-delete', ':uuid') }}`;
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
                ajax: "{{ route('outlet.kasir-outlet-get') }}",
                columns: [{
                        data: null,
                        class: 'mb-kolom-nomor align-content-center',
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'nama',
                        class: 'mb-kolom-text text-left align-content-center'
                    },
                    {
                        data: 'username',
                        class: 'mb-kolom-text text-left align-content-center'
                    },
                    {
                        data: 'password_hash',
                        class: 'mb-kolom-text text-left align-content-center'
                    },
                    {
                        data: 'alamat',
                        class: 'mb-kolom-tanggal text-left align-content-center'
                    },
                    {
                        data: 'telepon',
                        class: 'mb-kolom-text text-left align-content-center'
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
