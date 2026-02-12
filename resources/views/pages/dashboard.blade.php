@extends('main')

@section('content')
<div class="pb-2">
    <h3 id="main_title" class="display">Dashboard</h3>
    <p id="main_subttitle" class="text-muted">Manage your account settings and preferences</p>
</div>
<div id="kpi-cards-container" class="row g-4">
    {{-- This will be populated by JavaScript --}}
    {{-- Skeleton Loader Example (Optional but good UX) --}}
    <div class="col-12 text-center p-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>
<div class="row g-4 mt-1">
    {{-- Other cards like "Financial Health" can be added here --}}

    {{-- Recent Activity Card --}}
    <div class="col-12">
        <div class="card card-dark border-dark bg-dark">
            <div class="card-body p-4 text-dark">
                <div class="row g-3">
                    <div class="col-12">
                        <h3 class="my-0 fw-bold">Recent Activity</h3>
                        <h6 class="my-0 text-muted">Latest confirmed reservations</h6>
                    </div>
                    {{-- Activity items will be injected here --}}
                    <div id="recent-activity-list" class="col-12 vstack gap-3">
                        {{-- Skeleton Loader Example --}}
                        <p class="text-muted">Loading recent activity...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection