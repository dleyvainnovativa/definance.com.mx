@extends('main')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-2">
    <div>
        <h3 id="main_title" class="display">Dashboard</h3>
        <p class="text-muted pb-0 mb-0">Manage your journal entries</p>
    </div>
</div>
<div class="row g-4 mt-1">
    <div class="col-12 text-dark">
        <div class="row g-4" id="cards-header">
            <!-- @include("components.loading.cards_header") -->
        </div>
    </div>
    <div class="col-12 text-dark">
        <div class="row g-4" id="cards-container">
            <!-- @include("components.loading.cards_body") -->
        </div>
    </div>
</div>

@endsection