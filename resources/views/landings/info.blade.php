@extends('layouts.app')

@section('title')
    UnityCare
@endsection

@section('content')

    <h3>Misi</h3>
    <p>
        Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
    </p>

    <h3>Visi</h3>
    <p>
        Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
    </p>
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
