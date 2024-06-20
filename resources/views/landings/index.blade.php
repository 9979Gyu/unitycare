@extends('layouts.app')

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
@endpush

@section('title')
    UnityCare
@endsection

@section('content')

    @if (session()->has('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="container mt-5">
        <!-- Tab for program and job -->
        <ul class="nav nav-tabs" id="tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="program-tab" data-bs-toggle="tab" data-bs-target="#program" type="button" role="tab" aria-controls="program" aria-selected="true">Program</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="job-tab" data-bs-toggle="tab" data-bs-target="#job" type="button" role="tab" aria-controls="job" aria-selected="false">Pekerjaan</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="search-tab" data-bs-toggle="tab" data-bs-target="#search" type="button" role="tab" aria-controls="search" aria-selected="false">Carian</button>
            </li>
        </ul>

        <!-- Content for tab -->
        <div class="tab-content m-3" id="tabContent">
            <!-- Calendar -->
            <div class="tab-pane fade show active" id="program" role="tabpanel" aria-labelledby="program-tab">
                <div id='calendar'></div>
            </div>

            <!-- Job offer -->
            <div class="tab-pane fade" id="job" role="tabpanel" aria-labelledby="job-tab">
                <div id="job">
                    <div class="card-container">
                        @if(count($sectors) == 0)
                            Tiada rekod berkenaan
                        @endif
                        <div class="accordion" id="sectorsAccordion">
                            @foreach($sectors as $sector)
                                @php
                                    // Initialize the displayed positions array for each sector
                                    $displayedPositions = [];
                                    $sumOfPositions = $sector->organizations->flatMap->jobOffers->groupBy('job_id')->count();
                                    
                                    // Initialize an array to count the number of offers for each position
                                    $jobOfferCounts = [];
                                    
                                    // First pass: count the number of offers for each job position
                                    foreach($sector->organizations as $organization) {
                                        foreach($organization->jobOffers as $offer) {
                                            $jobId = $offer->job_id;
                                            if (!isset($jobOfferCounts[$jobId])) {
                                                $jobOfferCounts[$jobId] = 0;
                                            }
                                            $jobOfferCounts[$jobId]++;
                                        }
                                    }
                                @endphp
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingSector{{ $sector->sector_id }}">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSector{{ $sector->sector_id }}" aria-expanded="true" aria-controls="collapseSector{{ $sector->sector_id }}">
                                            {{ $sector->name }} ({{ $sumOfPositions }} Jawatan)
                                        </button>
                                    </h2>
                                    <div id="collapseSector{{ $sector->sector_id }}" class="accordion-collapse collapse" aria-labelledby="headingSector{{ $sector->sector_id }}" data-bs-parent="#sectorsAccordion">
                                        <div class="accordion-body">
                                            @foreach($sector->organizations as $organization)
                                                @foreach($organization->jobOffers->groupBy('job_id') as $jobId => $jobOffers)
                                                    @php
                                                        // Retrieve the job associated with the offer
                                                        $job = $jobOffers->first()->job;
                                                    @endphp

                                                    <!-- Check if $job is not null -->
                                                    @if($job)
                                                        @if(!in_array($job->job_id, $displayedPositions))
                                                            <!-- Add the position to the displayed positions array -->
                                                            @php
                                                                $displayedPositions[] = $job->job_id;
                                                            @endphp

                                                            <!-- Display the position -->
                                                            <div class="accordion mb-2" id="positionAccordion{{ $job->job_id }}">
                                                                <div class="accordion-item">
                                                                    <h2 class="accordion-header" id="headingPosition{{ $job->job_id }}">
                                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePosition{{ $job->job_id }}" aria-expanded="false" aria-controls="collapsePosition{{ $job->job_id }}">
                                                                            {{ $job->position }} ({{ $jobOfferCounts[$jobId] }} Tawaran)
                                                                        </button>
                                                                    </h2>
                                                                    <div id="collapsePosition{{ $job->job_id }}" class="accordion-collapse collapse" aria-labelledby="headingPosition{{ $job->job_id }}" data-bs-parent="#positionAccordion{{ $job->job_id }}">
                                                                        <div class="accordion-body" id="organizationList{{ $job->job_id }}">
                                                                            @foreach($jobOffers as $offer)
                                                                                <div>
                                                                                    <a class="viewAnchor text-black" href="/joinoffer/{{$offer->offer_id}}">
                                                                                    <h5 class="card-text">{{ $offer->organization->name }}</h5></a>
                                                                                    <span class="card-text badge badge-primary"> RM {{ $offer->min_salary }}  - RM {{ $offer->max_salary }} sebulan</span>
                                                                                    <span class="card-text badge badge-primary"> {{ $offer->jobType->name }} </span>
                                                                                    <span class="card-text badge badge-primary"> {{ $offer->shiftType->name }} </span>
                                                                                </div>
                                                                                <hr>
                                                                            @endforeach
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @else
                                                            <!-- Append to the existing position's organization list -->
                                                            <script>
                                                                $(document).ready(function() {
                                                                    const list = $('#organizationList{{ $job->job_id }}');
                                                                    var listItem = '';
                                                                    @foreach($jobOffers as $offer)

                                                                        listItem = '<hr><div><a class="viewAnchor text-black" href="/joinoffer/{{$offer->offer_id}}">' +
                                                                                        '<h5 class="card-text">{{ $offer->organization->username }}</h5></a>' +
                                                                                        '<span class="card-text badge badge-primary"> RM {{ $offer->min_salary }}  - RM {{ $offer->max_salary }} sebulan</span>' +
                                                                                        ' <span class="card-text badge badge-primary"> {{ $offer->jobType->name }} </span>' +
                                                                                        ' <span class="card-text badge badge-primary"> {{ $offer->shiftType->name }} </span>' +
                                                                                        '</div>';
                                                                        list.append(listItem);

                                                                    @endforeach
                                                                });
                                                            </script>
                                                        @endif
                                                    @endif
                                                @endforeach
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search position and program -->
            <div class="tab-pane fade" id="search" role="tabpanel" aria-labelledby="search-tab">
                <div class="container mt-4">
                    <div class="row justify-content-center">
                        <div class="col">
                            <div class="tooltip-container m-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle-fill" viewBox="0 0 16 16" data-bs-toggle="tooltip" 
                                    title="Nama Program atau Pekerjaan (Contoh: Kerjaya)
                                    Tarikh Mula (Contoh: 24-06-2023)">  
                                    <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2"/>
                                </svg>
                            </div>
                            <div class="input-group">
                                <input type="text" id="searchInput" class="form-control" placeholder="Cari...">
                                <button type="button" class="btn btn-primary" id="searchBtn">Cari</button>
                            </div>
                        </div>
                    </div>
                    <!-- Display search results -->
                    <div class="row mt-4 justify-content-center">
                        <div class="accordion" id="searchResults">
                            <!-- Display the program and offer result -->
                        </div>
                    </div>
                </div>


            </div>
        </div>

        <br>

        <br>

        <div class="justify-content-center d-flex">
            <div style="width: 40%">
                @include('charts.people')
            </div>
            <div class="flex-grow-1">
                <object data="https://data.gov.my/ms-MY/data-catalogue/embed/hies_district?visual=poverty" width="100%" height="400px"></object>
            </div>
        </div>

        <br>

        <div>
            @if(Auth::check())
                <h3>Tindakan</h3>
                <div class="flex">

                    @if(Auth::user()->roleID == 1)
                        <button type="button" onclick="window.location='/view/2'" class="btn-primary big-button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="currentColor" class="bi bi-person-badge" viewBox="0 0 16 16">
                                <path d="M6.5 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1zM11 8a3 3 0 1 1-6 0 3 3 0 0 1 6 0"></path>
                                <path d="M4.5 0A2.5 2.5 0 0 0 2 2.5V14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2.5A2.5 2.5 0 0 0 11.5 0zM3 2.5A1.5 1.5 0 0 1 4.5 1h7A1.5 1.5 0 0 1 13 2.5v10.795a4.2 4.2 0 0 0-.776-.492C11.392 12.387 10.063 12 8 12s-3.392.387-4.224.803a4.2 4.2 0 0 0-.776.492z"></path>
                            </svg>
                            
                            <p><b>Pekerja</b></p>
                        </button>

                    @endif

                    @if(Auth::user()->roleID == 1 || Auth::user()->roleID == 2)
                
                        <button type="button" onclick="window.location='/view/3'" class="btn-danger big-button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="currentColor" class="bi bi-buildings-fill" viewBox="0 0 16 16">
                                <path d="M15 .5a.5.5 0 0 0-.724-.447l-8 4A.5.5 0 0 0 6 4.5v3.14L.342 9.526A.5.5 0 0 0 0 10v5.5a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5V14h1v1.5a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5zM2 11h1v1H2zm2 0h1v1H4zm-1 2v1H2v-1zm1 0h1v1H4zm9-10v1h-1V3zM8 5h1v1H8zm1 2v1H8V7zM8 9h1v1H8zm2 0h1v1h-1zm-1 2v1H8v-1zm1 0h1v1h-1zm3-2v1h-1V9zm-1 2h1v1h-1zm-2-4h1v1h-1zm3 0v1h-1V7zm-2-2v1h-1V5zm1 0h1v1h-1z"/>
                            </svg>
                            
                            <p><b>Syarikat</b></p>
                        </button>

                        <button type="button" onclick="window.location='/view/4'" class="btn-success big-button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="currentColor" class="bi bi-balloon-heart-fill" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M8.49 10.92C19.412 3.382 11.28-2.387 8 .986 4.719-2.387-3.413 3.382 7.51 10.92l-.234.468a.25.25 0 1 0 .448.224l.04-.08c.009.17.024.315.051.45.068.344.208.622.448 1.102l.013.028c.212.422.182.85.05 1.246-.135.402-.366.751-.534 1.003a.25.25 0 0 0 .416.278l.004-.007c.166-.248.431-.646.588-1.115.16-.479.212-1.051-.076-1.629-.258-.515-.365-.732-.419-1.004a2 2 0 0 1-.037-.289l.008.017a.25.25 0 1 0 .448-.224l-.235-.468ZM6.726 1.269c-1.167-.61-2.8-.142-3.454 1.135-.237.463-.36 1.08-.202 1.85.055.27.467.197.527-.071.285-1.256 1.177-2.462 2.989-2.528.234-.008.348-.278.14-.386"/>
                            </svg>
                            
                            <p><b>Sukarelawan</b></p>
                        </button>

                        <button type="button" onclick="window.location='/view/5'" class="btn-warning text-light big-button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="currentColor" class="bi bi-people-fill" viewBox="0 0 16 16">
                                <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/>
                            </svg>
                            
                            <p><b>B40/OKU</b></p>
                        </button>

                    @endif

                    @if(Auth::user()->roleID != 4)
                    <button type="button" onclick="window.location='/viewoffer'" class="btn-info big-button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="currentColor" class="bi bi-briefcase-fill" viewBox="0 0 16 16">
                            <path d="M6.5 1A1.5 1.5 0 0 0 5 2.5V3H1.5A1.5 1.5 0 0 0 0 4.5v1.384l7.614 2.03a1.5 1.5 0 0 0 .772 0L16 5.884V4.5A1.5 1.5 0 0 0 14.5 3H11v-.5A1.5 1.5 0 0 0 9.5 1zm0 1h3a.5.5 0 0 1 .5.5V3H6v-.5a.5.5 0 0 1 .5-.5"/>
                            <path d="M0 12.5A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5V6.85L8.129 8.947a.5.5 0 0 1-.258 0L0 6.85z"/>
                        </svg>
                        
                        <p><b>Pekerjaan</b></p>
                    </button>
                    @endif

                    <!-- Show to all type of user -->
                    <button type="button" onclick="window.location='/viewallprograms'" class="btn-dark big-button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="currentColor" class="bi bi-tools" viewBox="0 0 16 16">
                            <path d="M1 0 0 1l2.2 3.081a1 1 0 0 0 .815.419h.07a1 1 0 0 1 .708.293l2.675 2.675-2.617 2.654A3.003 3.003 0 0 0 0 13a3 3 0 1 0 5.878-.851l2.654-2.617.968.968-.305.914a1 1 0 0 0 .242 1.023l3.27 3.27a.997.997 0 0 0 1.414 0l1.586-1.586a.997.997 0 0 0 0-1.414l-3.27-3.27a1 1 0 0 0-1.023-.242L10.5 9.5l-.96-.96 2.68-2.643A3.005 3.005 0 0 0 16 3q0-.405-.102-.777l-2.14 2.141L12 4l-.364-1.757L13.777.102a3 3 0 0 0-3.675 3.68L7.462 6.46 4.793 3.793a1 1 0 0 1-.293-.707v-.071a1 1 0 0 0-.419-.814zm9.646 10.646a.5.5 0 0 1 .708 0l2.914 2.915a.5.5 0 0 1-.707.707l-2.915-2.914a.5.5 0 0 1 0-.708M3 11l.471.242.529.026.287.445.445.287.026.529L5 13l-.242.471-.026.529-.445.287-.287.445-.529.026L3 15l-.471-.242L2 14.732l-.287-.445L1.268 14l-.026-.529L1 13l.242-.471.026-.529.445-.287.287-.445.529-.026z"/>
                        </svg>
                        
                        <p><b>Program</b></p>
                    </button>

                </div>
            @endif
        </div>
        
    </div>

    <script src="{{ asset('js/landingScript.js') }}"></script>

@endsection