@extends('layouts.layout')
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
                    <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                        <a href="#" onclick="window.history.back()" class="btn btn-info"><span>Kembali</span></a>
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
                    <div class="card stretch stretch-full widget-tasks-content">
                        <div class="card-header">
                            <h5 class="card-title">{{ $module }}</h5>
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
                        <div class="card-body custom-card-action">
                            <form method="POST" action="{{ route('superadmin.hak-akses-update', $user->uuid) }}">
                                @csrf

                                <div class="mb-3">
                                    <label>
                                        <input type="checkbox" id="selectAllGlobal">
                                        <strong>Pilih Semua</strong>
                                    </label>
                                </div>

                                @foreach ($defaultMenus as $group => $menus)
                                    @php
                                        // ganti spasi dengan underscore supaya aman dipakai di class
                                        $safeGroup = str_replace(' ', '_', $group);
                                    @endphp

                                    <h5 class="mt-3">{{ $group }}</h5>
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Menu</th>
                                                <th>
                                                    <input type="checkbox" class="select-all-group"
                                                        data-col="view-{{ $safeGroup }}">
                                                    Lihat
                                                </th>
                                                <th>
                                                    <input type="checkbox" class="select-all-group"
                                                        data-col="create-{{ $safeGroup }}">
                                                    Tambah
                                                </th>
                                                <th>
                                                    <input type="checkbox" class="select-all-group"
                                                        data-col="edit-{{ $safeGroup }}">
                                                    Edit
                                                </th>
                                                <th>
                                                    <input type="checkbox" class="select-all-group"
                                                        data-col="delete-{{ $safeGroup }}">
                                                    Hapus
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($menus as $menu)
                                                @php
                                                    $akses = $hakAkses[$menu][0] ?? null;
                                                @endphp
                                                <tr>
                                                    <td>{{ $menu }}</td>
                                                    <td>
                                                        <input type="hidden"
                                                            name="menus[{{ $group }}][{{ $menu }}][view]"
                                                            value="0">
                                                        <input type="checkbox"
                                                            class="col-checkbox view-{{ $safeGroup }}"
                                                            name="menus[{{ $group }}][{{ $menu }}][view]"
                                                            value="1" {{ $akses && $akses->view ? 'checked' : '' }}>
                                                    </td>
                                                    <td>
                                                        <input type="hidden"
                                                            name="menus[{{ $group }}][{{ $menu }}][create]"
                                                            value="0">
                                                        <input type="checkbox"
                                                            class="col-checkbox create-{{ $safeGroup }}"
                                                            name="menus[{{ $group }}][{{ $menu }}][create]"
                                                            value="1"
                                                            {{ $akses && $akses->create ? 'checked' : '' }}>
                                                    </td>
                                                    <td>
                                                        <input type="hidden"
                                                            name="menus[{{ $group }}][{{ $menu }}][edit]"
                                                            value="0">
                                                        <input type="checkbox"
                                                            class="col-checkbox edit-{{ $safeGroup }}"
                                                            name="menus[{{ $group }}][{{ $menu }}][edit]"
                                                            value="1" {{ $akses && $akses->edit ? 'checked' : '' }}>
                                                    </td>
                                                    <td>
                                                        <input type="hidden"
                                                            name="menus[{{ $group }}][{{ $menu }}][delete]"
                                                            value="0">
                                                        <input type="checkbox"
                                                            class="col-checkbox delete-{{ $safeGroup }}"
                                                            name="menus[{{ $group }}][{{ $menu }}][delete]"
                                                            value="1"
                                                            {{ $akses && $akses->delete ? 'checked' : '' }}>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endforeach

                                <button type="submit" class="btn btn-success">Simpan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const globalSelect = document.getElementById("selectAllGlobal");

            // update checkbox select-all per group
            function updateGroupSelect(colClass) {
                const groupCheckbox = document.querySelector(`.select-all-group[data-col="${colClass}"]`);
                if (!groupCheckbox) return;

                const checkboxes = document.querySelectorAll("." + colClass);
                groupCheckbox.checked = Array.from(checkboxes).length > 0 &&
                    Array.from(checkboxes).every(cb => cb.checked);
            }

            // update checkbox global select
            function updateGlobalSelect() {
                const allCheckboxes = document.querySelectorAll("tbody .col-checkbox");
                globalSelect.checked = allCheckboxes.length > 0 &&
                    Array.from(allCheckboxes).every(cb => cb.checked);
            }

            // event untuk select-all per group
            document.querySelectorAll(".select-all-group").forEach(groupCheckbox => {
                groupCheckbox.addEventListener("change", function() {
                    const colClass = this.dataset.col;
                    document.querySelectorAll("." + colClass).forEach(cb => {
                        cb.checked = this.checked;
                    });
                    updateGlobalSelect();
                });
            });

            // event untuk tiap checkbox body
            document.querySelectorAll(".col-checkbox").forEach(cb => {
                cb.addEventListener("change", function() {
                    const classList = Array.from(this.classList).filter(c => c.includes("-"));
                    classList.forEach(updateGroupSelect);
                    updateGlobalSelect();
                });
            });

            // event untuk global select
            globalSelect.addEventListener("change", function() {
                const allCheckboxes = document.querySelectorAll(".col-checkbox, .select-all-group");
                allCheckboxes.forEach(cb => cb.checked = this.checked);
            });

            // sinkronisasi awal saat load (biar sesuai database)
            document.querySelectorAll(".select-all-group").forEach(cb => updateGroupSelect(cb.dataset.col));
            updateGlobalSelect();
        });
    </script>
@endpush
