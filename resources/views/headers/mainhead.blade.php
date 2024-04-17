<div class="container-fluid flex">
    <img src="{{ asset('images/applogo2.png') }}" alt="Gambar Aplikasi">
    <div>
        <h1>UNITY CARE</h1>
        <span>PEMBANGUNAN MASYARAKAT</span>
    </div>
</div>
<nav class="navbar navbar-expand-lg navbar-dark navCustom">

    <div class="container-fluid">

        <a class="navbar-brand" href="/"><b>Utama</b></a>

        <!-- for responsive, will show button when screen is down-sized -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="#"><b>Info</b></a>
                </li>
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
                            <li><a class="dropdown-item" href="/createspecial"><b>Kesukaran</b></a></li>
                        </ul>
                    </li>
                @endif
                @if(Auth::check())
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <b>Tindakan</b>
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
                            @endif
                            @if(Auth::user()->roleID != 4)
                            <li><a class="dropdown-item" href="#"><b>Pekerjaan</b></a></li>
                            <li><hr class="dropdown-divider"></li>
                            @endif
                            <li><a class="dropdown-item" href="#"><b>Program</b></a></li>
                        </ul>
                    </li>
                @endif
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <b>Pengguna</b>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        
                        @if(Auth::check())
                            
                            <li><a class="dropdown-item" href="/createstaff"><b>Profile</b></a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><form class="dropdown-item" method="POST" action="/logout">
                                @csrf
                                <button type="submit" style="background: none!important; border: none; cursor: pointer;">
                                    <b>Logout</b>
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
            
            <!-- <form class="d-flex" role="search">
                <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
                <button class="btn btn-outline-light" type="submit">Search</button>
            </form> -->
        </div>
    </div>
</nav>