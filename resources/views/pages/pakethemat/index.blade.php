@extends('layouts.layout')
<style>
    .rounded-circle {
        display: none;
    }
</style>
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
                    {{-- @canCreate('Pembelian') --}}
                    <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                        <a href="#" id="openModal" class="btn btn-primary"><svg stroke="currentColor" fill="none"
                                stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"
                                class="me-2" height="16" width="16" xmlns="http://www.w3.org/2000/svg">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg><span>Tambah Data</span></a>
                    </div>
                    {{-- @endcanCreate --}}
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
                                            <th class="text-capitalize">nama paket</th>
                                            <th class="text-capitalize">list produk</th>
                                            <th class="text-capitalize">total modal</th>
                                            <th class="text-capitalize">profit</th>
                                            <th class="text-capitalize">harga jual</th>
                                            <th class="text-capitalize">keterangan</th>
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
                            <label class="text-capitalize form-label">nama paket</label>
                            <input type="text" name="nama_paket" id="nama_paket" class="form-control">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-2">
                            <label class="text-capitalize form-label">Suplayer</label>
                            <select name="uuid_suplayer" id="uuid_suplayer" data-placeholder="Pilih suplayer"
                                class="form-select basic-usage">
                                <option value=""></option>
                                @foreach ($suplayers as $s)
                                    <option value="{{ $s->uuid }}">{{ $s->nama }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-2" id="produkData">
                            <label class="text-capitalize form-label">Produk</label>
                            <select class="form-select form-control max-select" id="uuid_produk" name="uuid_produk[]"
                                data-select2-selector="tag" multiple>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-2">
                            <label class="text-capitalize form-label">total modal</label>
                            <input type="text" name="total_modal" id="total_modal" class="form-control formatRupiah">
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

                        <div class="mb-2">
                            <label class="text-capitalize form-label">keterangan</label>
                            <textarea name="keterangan" id="keterangan" class="form-control" cols="30" rows="2"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-success">Simpan</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('assets/vendors/js/select2-active.min.js') }}"></script>
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
        });

        // Fungsi parse angka dari string Rupiah
        function parseRupiah(value) {
            if (!value) return 0;
            value = value.replace(/[^0-9,]/g, ''); // hapus selain angka dan koma
            value = value.replace(',', '.'); // ubah koma jadi titik
            return parseFloat(value) || 0;
        }

        // Fungsi format ke Rupiah
        function formatRupiah(angka) {
            // Konversi ke string tanpa desimal
            let rounded = Math.floor(angka); // gunakan floor agar tidak lebih besar
            let number_string = rounded.toString().replace(/[^,\d]/g, ''),
                split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

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
            $('#import-po-wrapper').show();

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

            let updateUrl = `{{ route('superadmin.paket-update', ':uuid') }}`;
            updateUrl = updateUrl.replace(':uuid', uuid);

            let url = uuid ? updateUrl :
                `{{ route('superadmin.paket-store') }}`;
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

        $('#uuid_suplayer').on('change', function() {
            let uuid = $(this).val();
            let $produkSelect = $('#uuid_produk');

            // Simpan produk yang sudah dipilih
            let selectedValues = $produkSelect.val() || [];
            let selectedOptions = {};

            $produkSelect.find('option:selected').each(function() {
                selectedOptions[$(this).val()] = {
                    text: $(this).text(),
                    harga: $(this).data('harga') // <- simpan harga juga!
                };
            });

            if (uuid) {
                $.getJSON(`/superadmin/master-data/paket-get-produk-by-suplayer/${uuid}`, function(data) {
                    // kosongkan select
                    $produkSelect.html('');

                    // kembalikan produk yang sudah dipilih dengan harga aslinya
                    selectedValues.forEach(function(val) {
                        if (selectedOptions[val]) {
                            $produkSelect.append(
                                `<option value="${val}" data-harga="${selectedOptions[val].harga}" selected>
                            ${selectedOptions[val].text}
                        </option>`
                            );
                        }
                    });

                    // tambahkan produk baru dari supplier
                    $produkSelect.append(produkOptions(data));

                    // restore pilihan
                    $produkSelect.val(selectedValues).trigger('change');

                    // hitung ulang total modal
                    hitungTotalModal();
                });
            }
        });

        // generate opsi produk baru
        function produkOptions(data) {
            let html = '';
            data.forEach(function(p) {
                if ($("#uuid_produk option[value='" + p.uuid + "']").length === 0) {
                    html += `<option value="${p.uuid}" data-harga="${p.hrg_modal}">
                        ${p.nama_barang}
                    </option>`;
                }
            });
            return html;
        }

        // fungsi hitung total modal
        function hitungTotalModal() {
            let total = 0;

            $('#uuid_produk option:selected').each(function() {
                let harga = parseFloat($(this).attr('data-harga')) || 0;
                total += harga;
            });

            $('#total_modal').val(formatRupiah(total.toString()));

            // hitung harga jual selalu, tapi cek profit dulu
            let profitVal = parseFloat($('#profit').val().toString().replace(',', '.'));
            if (!isNaN(profitVal)) {
                hitungHargaJual();
            }

            return total;
        }

        // fungsi hitung harga jual
        function hitungHargaJual() {
            let modal = parseRupiah($('#total_modal').val());
            let profit = parseFloat($('#profit').val().toString().replace(',', '.')) || 0;

            if (modal > 0 && !isNaN(profit)) {
                let harga_jual = modal + (modal * profit / 100);
                $('#hrg_jual').val(formatRupiah(roundToRibuan(harga_jual).toString()));
            }
        }

        // trigger saat produk dipilih
        $(document).on('change', '#uuid_produk', function() {
            hitungTotalModal();
        });

        function roundToRibuan(num) {
            return Math.round(num / 1000) * 1000;
        }

        // Fungsi untuk bind event profit/harga jual
        function bindProfitHargaJual(profitId, jualId) {
            let isUpdating = false; // kunci biar gak saling timpa

            // Saat profit diubah → update harga jual
            $('#' + profitId).on('input', function() {
                if (isUpdating) return;
                isUpdating = true;

                let modal = parseRupiah($('#total_modal').val());
                let persen = parseFloat($(this).val());

                if (modal > 0 && !isNaN(persen)) {
                    let harga_jual = modal + (modal * persen / 100);
                    $('#' + jualId).val(formatRupiah(roundToRibuan(harga_jual)));
                }

                isUpdating = false;
            });

            // Saat harga jual diubah → update profit
            $('#' + jualId).on('input', function() {
                if (isUpdating) return;
                isUpdating = true;

                let modal = parseRupiah($('#total_modal').val());
                let jual = parseRupiah($(this).val());

                if (modal > 0 && jual > 0) {
                    let persen = ((jual - modal) / modal) * 100;
                    $('#' + profitId).val(persen.toFixed(2));
                }

                isUpdating = false;
            });

            // Saat modal diubah → update sesuai nilai terakhir
            $('#total_modal').on('input', function() {
                let modal = parseRupiah($(this).val());
                let jual = parseRupiah($('#' + jualId).val());
                let persen = parseFloat($('#' + profitId).val());

                if (modal > 0) {
                    if (!isNaN(persen)) {
                        let harga_jual = modal + (modal * persen / 100);
                        $('#' + jualId).val(formatRupiah(roundToRibuan(harga_jual)));
                    } else if (jual > 0) {
                        let persenBaru = ((jual - modal) / modal) * 100;
                        $('#' + profitId).val(persenBaru.toFixed(2));
                    }
                }
            });
        }

        // Bind untuk semua set profit/harga jual
        bindProfitHargaJual('profit', 'hrg_jual');

        // function hitungHargaJual() {
        //     let modal = parseRupiah($('#total_modal').val()); // ambil total modal
        //     let profit = parseFloat($('#profit').val().toString().replace(',', '.')) || 0;

        //     if (modal > 0 && !isNaN(profit)) {
        //         let harga_jual = modal + (modal * profit / 100);
        //         $('#hrg_jual').val(formatRupiah(roundToRibuan(harga_jual).toString()));
        //     }
        // }

        // Edit pembelian
        $('#dataTables').on('click', '.edit', function() {
            // Reset modal error
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
            $('#modal').modal('show');
            $('#import-po-wrapper').hide();

            let uuid = $(this).data('uuid');
            let editUrl = `{{ route('superadmin.paket-edit', ':uuid') }}`;
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

                // === Isi produk ===
                let $produkSelect = $('#uuid_produk');
                $produkSelect.html(''); // kosongkan dulu

                if (Array.isArray(res.uuid_produk)) {
                    res.uuid_produk.forEach(function(p) {
                        $produkSelect.append(
                            `<option value="${p.uuid}" data-harga="${p.hrg_modal}" selected>
                                ${p.nama_barang}
                            </option>`
                        );
                    });
                }

                $produkSelect.trigger('change');

                let profit = parseFloat(res.profit.toString().replace(',', '.')) || 0;
                $('#profit').val(profit);

                // hitung ulang total modal dari data produk
                hitungTotalModal();
            });
        });

        // Hapus
        $('#dataTables').on('click', '.delete', function() {
            let uuid = $(this).data('uuid');
            let deleteUrl = `{{ route('superadmin.paket-delete', ':uuid') }}`;
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
                ajax: "{{ route('superadmin.paket-get') }}",
                columns: [{
                        data: null,
                        class: 'mb-kolom-nomor align-content-center',
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'nama_paket',
                        class: 'mb-kolom-text text-left align-content-center'
                    },
                    {
                        data: 'produk_list',
                        class: 'mb-kolom-tanggal text-left align-content-center',
                        render: function(data, type, row) {
                            if (data && data.length > 0) {
                                return data.map(item => {
                                    return `<span class="badge bg-primary">${item.nama_barang}</span>`;
                                }).join(' ');
                            }
                            return '-';
                        }
                    },
                    {
                        data: 'total_modal',
                        class: 'mb-kolom-tanggal text-left align-content-center',
                        render: function(data, type, row) {
                            return formatRupiah(data.toString());
                        }
                    },
                    {
                        data: 'profit',
                        class: 'mb-kolom-tanggal text-left align-content-center'
                    },
                    {
                        data: 'harga_jual',
                        class: 'mb-kolom-tanggal text-left align-content-center',
                        render: function(data, type, row) {
                            return formatRupiah(data.toString());
                        }
                    },
                    {
                        data: 'keterangan',
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
