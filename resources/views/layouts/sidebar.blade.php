@php
    $path = explode('/', Request::path());
    $role = auth()->user()->role;
@endphp
<nav class="nxl-navigation">
    <div class="navbar-wrapper">
        <div class="m-header justify-content-center">
            <a class="b-brand">
                <!-- ========   change your logo hear   ============ -->
                <img src="{{ asset('logo.png') }}" style="width: 200px; margin-top: 6px" alt=""
                    class="logo logo-lg" />
                <img src="{{ asset('logo_favicon.png') }}" alt="" class="logo logo-sm" />
            </a>
        </div>
        <div class="navbar-content">
            {{-- <pre>{{ print_r(array_keys(session('hak_akses')->toArray()), true) }}</pre> --}}

            @if ($role === 'superadmin')
                <ul class="nxl-navbar">
                    <li class="nxl-item nxl-caption">
                        <label>Super Admin</label>
                    </li>

                    {{-- Dashboard (global, tidak pakai canView) --}}
                    <li class="nxl-item nxl-hasmenu {{ $path[1] === 'dashboard-superadmin' ? 'active' : '' }}">
                        <a href="{{ route('superadmin.dashboard-superadmin') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-airplay"></i></span>
                            <span class="nxl-mtext">Dashboard</span>
                        </a>
                    </li>

                    {{-- Setup --}}
                    <li class="nxl-item nxl-hasmenu {{ $path[1] === 'setup' ? 'active' : '' }}">
                        <a href="javascript:void(0);" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-sliders"></i></span>
                            <span class="nxl-mtext">Setup</span><span class="nxl-arrow"><i
                                    class="feather-chevron-right"></i></span>
                        </a>
                        <ul class="nxl-submenu">
                            @canView('Data Pengguna')
                            <li class="nxl-item {{ isset($path[2]) && $path[2] === 'data-pengguna' ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('superadmin.data-pengguna') }}">Data Pengguna</a>
                            </li>
                            @endcanView
                            <li
                                class="nxl-item {{ isset($path[2]) && $path[2] === 'target-penjualan' ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('superadmin.target-penjualan') }}">Target
                                    Penjualan</a>
                            </li>
                        </ul>
                    </li>

                    {{-- Master Data --}}
                    <li class="nxl-item nxl-hasmenu {{ $path[1] === 'master-data' ? 'active' : '' }}">
                        <a href="javascript:void(0);" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-cast"></i></span>
                            <span class="nxl-mtext">Master Data</span><span class="nxl-arrow"><i
                                    class="feather-chevron-right"></i></span>
                        </a>
                        <ul class="nxl-submenu">
                            @canView('Kategori')
                            <li class="nxl-item {{ isset($path[2]) && $path[2] === 'kategori' ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('superadmin.kategori') }}">Kategori</a>
                            </li>
                            @endcanView

                            @canView('Suplayer')
                            <li class="nxl-item {{ isset($path[2]) && $path[2] === 'suplayer' ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('superadmin.suplayer') }}">Suplayer</a>
                            </li>
                            @endcanView

                            @canView('Jasa')
                            <li class="nxl-item {{ isset($path[2]) && $path[2] === 'jasa' ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('superadmin.jasa') }}">Jasa</a>
                            </li>
                            @endcanView

                            @canView('Produk')
                            <li class="nxl-item {{ isset($path[2]) && $path[2] === 'produk' ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('superadmin.produk') }}">Produk</a>
                            </li>
                            @endcanView

                            <li class="nxl-item {{ isset($path[2]) && $path[2] === 'paket-hemat' ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('superadmin.paket-hemat') }}">Paket Hemat</a>
                            </li>

                            @canView('Karyawan')
                            <li class="nxl-item {{ isset($path[2]) && $path[2] === 'karyawan' ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('superadmin.karyawan') }}">Karyawan</a>
                            </li>
                            @endcanView

                            @canView('Outlet')
                            <li class="nxl-item {{ isset($path[2]) && $path[2] === 'outlet' ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('superadmin.outlet') }}">Outlet</a>
                            </li>
                            @endcanView
                            <li class="nxl-item {{ isset($path[2]) && $path[2] === 'wirehouse' ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('superadmin.wirehouse') }}">Wirehouse</a>
                            </li>
                        </ul>
                    </li>

                    {{-- Transaksi --}}
                    <li class="nxl-item nxl-hasmenu {{ $path[1] === 'transaksi' ? 'active' : '' }}">
                        <a href="javascript:void(0);" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-dollar-sign"></i></span>
                            <span class="nxl-mtext">Transaksi</span><span class="nxl-arrow"><i
                                    class="feather-chevron-right"></i></span>
                        </a>
                        <ul class="nxl-submenu">
                            @canView('Pembelian')
                            <li class="nxl-item {{ isset($path[2]) && $path[2] === 'pembelian' ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('superadmin.pembelian') }}">Pembelian</a>
                            </li>
                            @endcanView

                            @canView('Hutang')
                            <li class="nxl-item {{ isset($path[2]) && $path[2] === 'hutang' ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('superadmin.hutang') }}">Hutang</a>
                            </li>
                            @endcanView

                            @canView('PO')
                            <li class="nxl-item {{ isset($path[2]) && $path[2] === 'po-pusat' ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('superadmin.po-pusat') }}">PO</a>
                            </li>
                            @endcanView

                            @canView('PO Outlet')
                            <li class="nxl-item {{ isset($path[2]) && $path[2] === 'po-vw-outlet' ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('superadmin.po-vw-outlet') }}">PO Outlet</a>
                            </li>
                            @endcanView

                            @canView('Pengiriman Barang')
                            <li class="nxl-item {{ isset($path[2]) && $path[2] === 'pengiriman' ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('superadmin.pengiriman') }}">Pengiriman Barang</a>
                            </li>
                            @endcanView
                        </ul>
                    </li>

                    {{-- Accounting --}}
                    <li class="nxl-item nxl-hasmenu {{ $path[1] === 'accounting' ? 'active' : '' }}">
                        <a href="javascript:void(0);" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-trello"></i></span>
                            <span class="nxl-mtext">Accounting</span><span class="nxl-arrow"><i
                                    class="feather-chevron-right"></i></span>
                        </a>
                        <ul class="nxl-submenu">
                            @canView('Daftar Akun')
                            <li class="nxl-item {{ isset($path[2]) && $path[2] === 'akun' ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('superadmin.akun') }}">Daftar Akun</a>
                            </li>
                            @endcanView

                            {{-- @canView('Gaji Karyawan')
                            <li class="nxl-item {{ isset($path[2]) && $path[2] === 'gaji' ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('superadmin.gaji') }}">Gaji Karyawan</a>
                            </li>
                            @endcanView

                            @canView('Biaya Lain-lain')
                            <li class="nxl-item {{ isset($path[2]) && $path[2] === 'biaya' ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('superadmin.biaya') }}">Biaya Lain-lain</a>
                            </li>
                            @endcanView --}}

                            <li class="nxl-item {{ isset($path[2]) && $path[2] === 'pengeluaran' ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('superadmin.pengeluaran') }}">Pengeluaran</a>
                            </li>

                            <li
                                class="nxl-item {{ isset($path[2]) && $path[2] === 'pemindahan-dana' ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('superadmin.pemindahan-dana') }}">Pemindahan
                                    Dana</a>
                            </li>

                            @canView('Jurnal Umum')
                            <li
                                class="nxl-item {{ isset($path[2]) && $path[2] === 'vw-jurnal-umum' ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('superadmin.vw-jurnal-umum') }}">Jurnal Umum</a>
                            </li>
                            @endcanView

                            <li
                                class="nxl-item {{ isset($path[2]) && $path[2] === 'vw-lap-transaksi' ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('superadmin.vw-lap-transaksi') }}">Laporan
                                    Transaksi</a>
                            </li>

                            @canView('Buku Besar')
                            <li
                                class="nxl-item {{ isset($path[2]) && $path[2] === 'vw-buku-besar' ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('superadmin.vw-buku-besar') }}">Buku Besar</a>
                            </li>
                            @endcanView

                            @canView('Neraca')
                            <li class="nxl-item {{ isset($path[2]) && $path[2] === 'vw-neraca' ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('superadmin.vw-neraca') }}">Neraca</a>
                            </li>
                            @endcanView

                            @canView('Laba Rugi')
                            <li class="nxl-item {{ isset($path[2]) && $path[2] === 'vw-laba-rugi' ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('superadmin.vw-laba-rugi') }}">Laba Rugi</a>
                            </li>
                            @endcanView
                        </ul>
                    </li>

                    {{-- Cetak Label --}}
                    <li class="nxl-item nxl-hasmenu {{ $path[1] === 'tools' ? 'active' : '' }}">
                        <a href="javascript:void(0);" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-tool"></i></span>
                            <span class="nxl-mtext">Tools</span><span class="nxl-arrow"><i
                                    class="feather-chevron-right"></i></span>
                        </a>
                        <ul class="nxl-submenu">
                            <li
                                class="nxl-item {{ isset($path[2]) && $path[2] === 'cetak-label-rak' ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('superadmin.cetak-label-rak') }}">Cetak Label
                                    Rak</a>
                            </li>
                        </ul>
                    </li>

                    <li class="nxl-item nxl-hasmenu {{ $path[1] === 'sumary-report' ? 'active' : '' }}">
                        <a href="{{ route('superadmin.sumary-report') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-repeat"></i></span>
                            <span class="nxl-mtext">Sumary Report</span>
                        </a>
                    </li>
                </ul>
            @elseif ($role === 'outlet')
                <ul class="nxl-navbar">
                    <li class="nxl-item nxl-caption">
                        <label>Outlet</label>
                    </li>
                    <li class="nxl-item nxl-hasmenu {{ $path[1] === 'dashboard-outlet' ? 'active' : '' }}">
                        <a href="{{ route('outlet.dashboard-outlet') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-airplay"></i></span>
                            <span class="nxl-mtext">Dashboard</span>
                        </a>
                    </li>
                    <li class="nxl-item nxl-hasmenu {{ $path[1] === 'produk' ? 'active' : '' }}">
                        <a href="{{ route('outlet.produk') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-briefcase"></i></span>
                            <span class="nxl-mtext">Produk</span>
                        </a>
                    </li>
                    <li class="nxl-item nxl-hasmenu {{ $path[1] === 'kasir-outlet' ? 'active' : '' }}">
                        <a href="{{ route('outlet.kasir-outlet') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-hard-drive"></i></span>
                            <span class="nxl-mtext">Kasir</span>
                        </a>
                    </li>
                    <li class="nxl-item nxl-hasmenu {{ $path[1] === 'costumer' ? 'active' : '' }}">
                        <a href="{{ route('outlet.costumer') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-users"></i></span>
                            <span class="nxl-mtext">Costumer</span>
                        </a>
                    </li>
                    <li class="nxl-item nxl-hasmenu {{ $path[1] === 'po-outlet' ? 'active' : '' }}">
                        <a href="{{ route('outlet.po-outlet') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-shopping-bag"></i></span>
                            <span class="nxl-mtext">PO</span>
                        </a>
                    </li>
                    <li class="nxl-item nxl-hasmenu {{ $path[1] === 'do-vw-outlet' ? 'active' : '' }}">
                        <a href="{{ route('outlet.do-vw-outlet') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-truck"></i></span>
                            <span class="nxl-mtext">DO</span>
                        </a>
                    </li>
                    <li class="nxl-item nxl-hasmenu {{ $path[1] === 'transfer' ? 'active' : '' }}">
                        <a href="{{ route('outlet.transfer') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-git-merge"></i></span>
                            <span class="nxl-mtext">Transfer Barang</span>
                        </a>
                    </li>

                    <li class="nxl-item nxl-hasmenu {{ $path[1] === 'vw-lap-transaksi' ? 'active' : '' }}">
                        <a href="{{ route('outlet.vw-lap-transaksi') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-book"></i></span>
                            <span class="nxl-mtext">Laporan Transaksi</span>
                        </a>
                    </li>
                </ul>
            @endif
            {{-- <div class="card text-center">
                <div class="card-body">
                    <i class="feather-sunrise fs-4 text-dark"></i>
                    <h6 class="mt-4 text-dark fw-bolder">Downloading Center</h6>
                    <p class="fs-11 my-3 text-dark">Duralux is a production ready CRM to get started up and running
                        easily.</p>
                    <a href="javascript:void(0);" class="btn btn-primary text-dark w-100">Download Now</a>
                </div>
            </div> --}}
        </div>
    </div>
</nav>
