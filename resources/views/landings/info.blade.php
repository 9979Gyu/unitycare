@extends('layouts.app')

@section('title')
    UnityCare
@endsection

@section('content')

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="text-center">
                <img src="{{ asset('images/webicon-512px.png') }}" alt="Web Icon" style="width: 150px;">
                <h1>UNITY CARE</h1>
                <span>PEMBANGUNAN MASYARAKAT</span>
            </div>
        </div>
    </div>
    
    <br>
    <div class="row justify">
        <p>
            UnityCare ialah aplikasi yang direka untuk menangani pelbagai cabaran yang ditimbulkan oleh kemiskinan dan ketidakupayaan. Ia berfungsi sebagai platform komprehensif yang:
        </p>
        
        <div class="container">
            <ol>
                <li>
                    <b>Memudahkan Peluang Pekerjaan Boleh Diakses:</b>
                    <p>
                        Membolehkan perusahaan menyiarkan jawatan kosong yang direka khusus untuk individu kurang upaya, memastikan kesesuaian yang disesuaikan antara keperluan pekerjaan dan kebolehan pemohon. 
                        Meningkatkan keterlihatan dan kebolehcapaian peluang pekerjaan untuk kumpulan yang terdedah, dengan itu meningkatkan peluang mereka untuk mendapatkan pekerjaan dan meningkatkan kualiti hidup mereka.
                    </p>
                </li>

                <li>
                    <b>Galakkan Pembangunan Kemahiran dan Sukarelawan:</b>
                    <p>
                        Libatkan sukarelawan untuk menganjurkan dan mempromosikan program pembangunan kemahiran yang disesuaikan untuk individu kurang upaya, memupuk pertumbuhan peribadi dan meningkatkan kebolehpasaran.
                        Menyediakan platform untuk sukarelawan menyumbang kepada masyarakat dengan menyokong inisiatif pendidikan dan vokasional, seterusnya membina masyarakat yang lebih inklusif dan empati.
                    </p>
                </li>

                <li>
                    <b>Tingkatkan Kesedaran dan Bina Empati:</b>
                    <p>
                        Berkongsi maklumat berkaitan kemiskinan yang komprehensif untuk meningkatkan kesedaran orang ramai tentang cabaran yang dihadapi oleh individu yang hidup dalam kemiskinan dan mereka yang kurang upaya.
                        Memupuk budaya empati dan tanggungjawab kolektif, menggalakkan ahli komuniti mengambil langkah proaktif ke arah pengurangan kemiskinan dan kesaksamaan sosial.
                    </p>
                </li>
            </ol>
        </div>

        <p>
            Melalui UnityCare, kami menyasarkan untuk mewujudkan persekitaran yang menyokong di mana perusahaan, sukarelawan dan individu kurang upaya boleh bekerjasama secara berkesan untuk menggalakkan daya tahan ekonomi dan keterangkuman sosial. Inisiatif ini bukan sahaja bertujuan untuk mengurangkan kesusahan kewangan serta-merta tetapi juga berusaha untuk membina laluan yang mampan ke arah kestabilan ekonomi jangka panjang dan kesejahteraan masyarakat.
        </p>
    </div>
    <br>

    <div id="carouselImage" class="carousel slide" data-bs-ride="carousel">

        <!-- button for control - toleft or toright -->
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#carouselImage" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#carouselImage" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#carouselImage" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
            <img src="{{ asset('images/poverty-unicef.jpeg') }}" class="d-block w-100" alt="unicef">
            <div class="carousel-caption d-none d-md-block">
                <p>COPYRIGHT &copy; UNICEF.org</p>
            </div>
            </div>
            <div class="carousel-item">
            <img src="{{ asset('images/poverty-from-bloomberg.jpg') }}" class="d-block w-100" alt="bloomberg">
            <div class="carousel-caption d-none d-md-block">
                <p>COPYRIGHT &copy; Bloomberg.com</p>
            </div>
            </div>
            <div class="carousel-item">
            <img src="{{ asset('images/poverty-from-dosomething.webp') }}" class="d-block w-100" alt="dosomething">
            <div class="carousel-caption d-none d-md-block">
                <p>COPYRIGHT &copy; DoSomething.org</p>
            </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselImage" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselImage" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

@endsection
