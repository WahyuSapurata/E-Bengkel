@php
    $path = explode('/', Request::path());
    $role = auth()->user()->role;
@endphp
<nav class="nxl-navigation">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="index.html" class="b-brand">
                <!-- ========   change your logo hear   ============ -->
                <img src="{{ asset('assets/images/logo-full.png') }}" alt="" class="logo logo-lg" />
                <img src="{{ asset('assets/images/logo-abbr.png') }}" alt="" class="logo logo-sm" />
            </a>
        </div>
        <div class="navbar-content">
            @if ($role === 'superadmin')
                <ul class="nxl-navbar">
                    <li class="nxl-item nxl-caption">
                        <label>Super Admin</label>
                    </li>
                    <li class="nxl-item nxl-hasmenu {{ $path[1] === 'dashboard-superadmin' ? 'active' : '' }}">
                        <a href="{{ route('superadmin.dashboard-superadmin') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-airplay"></i></span>
                            <span class="nxl-mtext">Dashboard</span>
                        </a>
                    </li>
                    <li class="nxl-item nxl-hasmenu {{ $path[1] === 'master-data' ? 'active' : '' }}">
                        <a href="javascript:void(0);" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-cast"></i></span>
                            <span class="nxl-mtext">Master Data</span><span class="nxl-arrow"><i
                                    class="feather-chevron-right"></i></span>
                        </a>
                        <ul class="nxl-submenu">
                            <li class="nxl-item {{ isset($path[2]) && $path[2] === 'kategori' ? 'active' : '' }}"><a
                                    class="nxl-link" href="{{ route('superadmin.kategori') }}">Kategori</a></li>
                            <li class="nxl-item {{ isset($path[2]) && $path[2] === 'subkategori' ? 'active' : '' }}"><a
                                    class="nxl-link" href="{{ route('superadmin.subkategori') }}">Sub Kategori</a></li>
                            <li class="nxl-item {{ isset($path[2]) && $path[2] === 'suplayer' ? 'active' : '' }}"><a
                                    class="nxl-link" href="{{ route('superadmin.suplayer') }}">Suplayer</a></li>
                            <li class="nxl-item {{ isset($path[2]) && $path[2] === 'jasa' ? 'active' : '' }}"><a
                                    class="nxl-link" href="{{ route('superadmin.jasa') }}">Jasa</a></li>
                            <li class="nxl-item {{ isset($path[2]) && $path[2] === 'produk' ? 'active' : '' }}"><a
                                    class="nxl-link" href="{{ route('superadmin.produk') }}">Produk</a></li>
                            <li class="nxl-item {{ isset($path[2]) && $path[2] === 'costumer' ? 'active' : '' }}"><a
                                    class="nxl-link" href="{{ route('superadmin.costumer') }}">Customer</a></li>
                            <li class="nxl-item {{ isset($path[2]) && $path[2] === 'karyawan' ? 'active' : '' }}"><a
                                    class="nxl-link" href="{{ route('superadmin.karyawan') }}">Karyawan</a></li>
                            <li class="nxl-item {{ isset($path[2]) && $path[2] === 'outlet' ? 'active' : '' }}"><a
                                    class="nxl-link" href="{{ route('superadmin.outlet') }}">Outlet</a></li>
                        </ul>
                    </li>
                    <li class="nxl-item nxl-hasmenu {{ $path[1] === 'transaksi' ? 'active' : '' }}">
                        <a href="javascript:void(0);" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-dollar-sign"></i></span>
                            <span class="nxl-mtext">Transaksi</span><span class="nxl-arrow"><i
                                    class="feather-chevron-right"></i></span>
                        </a>
                        <ul class="nxl-submenu">
                            <li class="nxl-item {{ isset($path[2]) && $path[2] === 'pembelian' ? 'active' : '' }}"><a
                                    class="nxl-link" href="{{ route('superadmin.pembelian') }}">Pembelian</a></li>
                            <li class="nxl-item {{ isset($path[2]) && $path[2] === 'hutang' ? 'active' : '' }}"><a
                                    class="nxl-link" href="{{ route('superadmin.hutang') }}">Hutang</a></li>
                            <li class="nxl-item {{ isset($path[2]) && $path[2] === 'po-pusat' ? 'active' : '' }}"><a
                                    class="nxl-link" href="{{ route('superadmin.po-pusat') }}">PO</a></li>
                            <li class="nxl-item {{ isset($path[2]) && $path[2] === 'po-vw-outlet' ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('superadmin.po-vw-outlet') }}">PO Outlet</a>
                            </li>
                            <li class="nxl-item {{ isset($path[2]) && $path[2] === 'pengiriman' ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('superadmin.pengiriman') }}">Pengiriman Barang</a>
                            </li>
                        </ul>
                    </li>
                    <li class="nxl-item nxl-hasmenu {{ $path[1] === 'accounting' ? 'active' : '' }}">
                        <a href="javascript:void(0);" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-trello"></i></span>
                            <span class="nxl-mtext">Accounting</span><span class="nxl-arrow"><i
                                    class="feather-chevron-right"></i></span>
                        </a>
                        <ul class="nxl-submenu">
                            <li class="nxl-item {{ isset($path[2]) && $path[2] === 'akun' ? 'active' : '' }}"><a
                                    class="nxl-link" href="{{ route('superadmin.akun') }}">Daftar Akun</a></li>
                            <li class="nxl-item {{ isset($path[2]) && $path[2] === 'gaji' ? 'active' : '' }}"><a
                                    class="nxl-link" href="{{ route('superadmin.gaji') }}">Gaji Karyawan</a></li>
                            <li class="nxl-item {{ isset($path[2]) && $path[2] === 'biaya' ? 'active' : '' }}"><a
                                    class="nxl-link" href="{{ route('superadmin.biaya') }}">Biaya Lain-lain</a></li>
                            <li
                                class="nxl-item {{ isset($path[2]) && $path[2] === 'vw-jurnal-umum' ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('superadmin.vw-jurnal-umum') }}">Jurnal Umum</a>
                            </li>
                        </ul>
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
