<!-- <div class="container-fluid d-flex align-items-center ">
    <div class="flex-shrink-0">
        <img src="{{ asset('images/webicon-512px.png') }}" alt="Gambar Aplikasi" class="img-fluid">
    </div>
    <div class="flex-grow-1">
        <h1>UNITY CARE</h1>
        <span>PEMBANGUNAN MASYARAKAT</span>
    </div>
</div> -->

<nav class="navbar navbar-expand-lg navbar-dark navCustom">

    <div class="container-fluid">

        <a class="navbar-brand" href="/"><b>UnityCare</b></a>

        <!-- for responsive, will show button when screen is down-sized -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="/info"><b>Info</b></a>
                </li>
                <!-- Not logged in -->
                @if(!Auth::check())
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <b>Jadi Ahli</b>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="/create/3"><b>Syarikat</b></a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/create/4"><b>Sukarelawan</b></a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/createspecial"><b>B40/OKU</b></a></li>
                        </ul>
                    </li>

                <!-- Logged in -->
                @else
                    <!-- Is Admin or Staff -->
                    @if(Auth::user()->roleID == 1 || Auth::user()->roleID == 2)
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <b>Pengguna</b>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                @if(Auth::user()->roleID == 1)
                                    <li><a class="dropdown-item" href="/view/2"><b>Pekerja</b></a></li>
                                    <li><hr class="dropdown-divider"></li>
                                @endif
                                @if(Auth::user()->roleID == 1 || Auth::user()->roleID == 2)
                                    <li><a class="dropdown-item" href="/view/3"><b>Syarikat</b></a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="/view/4"><b>Sukarelawan</b></a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="/view/5"><b>B40/OKU</b></a></li>
                                @endif
                                @if(Auth::user()->roleID == 4)
                                    <li><a class="dropdown-item" href="/createspecial"><b>B40/OKU</b></a></li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    <!-- Is not volunteer -->
                    @if(Auth::user()->roleID != 4)
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <b>Pekerjaan</b>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <!-- Is admin and staff -->
                                @if(Auth::user()->roleID <= 2)
                                    <li><a class="dropdown-item" href="/viewjob"><b>Jenis Pekerjaan</b></a></li>
                                    <li><hr class="dropdown-divider"></li>
                                @endif

                                <li><a class="dropdown-item" href="/viewoffer"><b>Lihat</b></a></li>

                                <!-- Is enterprise -->
                                @if(Auth::user()->roleID == 3)
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="/createoffer"><b>Tambah</b></a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="/viewapplication"><b>Permohonan</b></a></li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <b>Program</b>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="/viewprogram"><b>Lihat</b></a></li>
                            <!-- Is not B40 / OKU -->
                            @if(Auth::user()->roleID != 5)
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/createprogram/{{ Auth::user()->roleID }}"><b>Tambah</b></a></li>
                            @endif
                        </ul>
                    </li>
                @endif
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <b>
                            @if(Auth::check())
                                {{Auth::user()->username}}
                            @else
                                Pengguna
                            @endif
                        </b>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        
                        @if(Auth::check())
                            
                            <li><a class="dropdown-item" href="/viewprofile"><b>Profil</b></a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/login-reset"><b>Tukar Kata Laluan</b></a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><form class="dropdown-item" method="POST" action="/logout">
                                @csrf
                                <button type="submit" style="background: none!important; border: none; cursor: pointer;">
                                    <b>Log Keluar</b>
                                </button>
                            </form></li>
                        @else
                            <li><a class="dropdown-item" href="/login">
                                <b>Log Masuk</b>
                            </a></li>
                        @endif                                
                        
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>