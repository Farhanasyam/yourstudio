@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'System Settings'])
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <h6>Pengaturan Sistem</h6>
                        <p class="text-sm mb-0">
                            Kelola semua pengaturan sistem dari satu tempat
                        </p>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('settings.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="row">
                                <div class="col-md-3">
                                    <!-- Navigation tabs -->
                                    <div class="nav flex-column nav-pills me-3" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                        @foreach($settings as $group => $groupSettings)
                                        <button class="nav-link {{ $loop->first ? 'active' : '' }} text-start mb-2" 
                                                id="v-pills-{{ $group }}-tab"
                                                data-bs-toggle="pill" 
                                                data-bs-target="#v-pills-{{ $group }}" 
                                                type="button" 
                                                role="tab">
                                            <i class="fas fa-{{ $group === 'store' ? 'store' : 
                                                              ($group === 'receipt' ? 'receipt' : 
                                                              ($group === 'inventory' ? 'boxes' : 
                                                              ($group === 'printer' ? 'print' : 
                                                              ($group === 'notification' ? 'bell' : 'cog')))) }} me-2"></i>
                                            {{ ucfirst($group) }}
                                        </button>
                                        @endforeach
                                    </div>
                                </div>
                                
                                <div class="col-md-9">
                                    <!-- Tab content -->
                                    <div class="tab-content" id="v-pills-tabContent">
                                        @foreach($settings as $group => $groupSettings)
                                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" 
                                             id="v-pills-{{ $group }}" 
                                             role="tabpanel">
                                            
                                            <div class="card shadow-none border">
                                                <div class="card-body">
                                                    <h6 class="mb-3">{{ ucfirst($group) }} Settings</h6>
                                                    
                                                    @foreach($groupSettings as $setting)
                                                    <div class="form-group mb-4">
                                                        <label class="form-control-label d-flex align-items-center">
                                                            {{ $setting->label }}
                                                            @if($setting->description)
                                                            <i class="fas fa-info-circle ms-2" 
                                                               data-bs-toggle="tooltip" 
                                                               title="{{ $setting->description }}"></i>
                                                            @endif
                                                        </label>
                                                        
                                                        @switch($setting->type)
                                                            @case('boolean')
                                                                <div class="form-check form-switch">
                                                                    <input class="form-check-input" 
                                                                           type="checkbox" 
                                                                           name="settings[{{ $setting->key }}]" 
                                                                           id="{{ $setting->key }}"
                                                                           {{ $setting->value ? 'checked' : '' }}>
                                                                </div>
                                                                @break
                                                            
                                                            @case('number')
                                                                <input type="number" 
                                                                       class="form-control" 
                                                                       name="settings[{{ $setting->key }}]" 
                                                                       value="{{ $setting->value }}"
                                                                       step="1">
                                                                @break
                                                            
                                                            @default
                                                                <input type="text" 
                                                                       class="form-control" 
                                                                       name="settings[{{ $setting->key }}]" 
                                                                       value="{{ $setting->value }}">
                                                        @endswitch
                                                        
                                                        @error('settings.' . $setting->key)
                                                            <span class="text-danger text-xs">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn bg-gradient-primary mb-0">
                                        <i class="fas fa-save me-2"></i>Simpan Pengaturan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
</script>
@endpush
