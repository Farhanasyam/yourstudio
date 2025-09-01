@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Profile Settings'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <form role="form" method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        <div class="card-header pb-0">
                            <div class="d-flex align-items-center">
                                <p class="mb-0">Edit Profile</p>
                                <button type="submit" class="btn btn-primary btn-sm ms-auto">Save Changes</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="text-uppercase text-sm">Basic Information</p>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name" class="form-control-label">Full Name</label>
                                        <input class="form-control @error('name') is-invalid @enderror" 
                                               type="text" 
                                               name="name" 
                                               id="name"
                                               value="{{ old('name', auth()->user()->name) }}"
                                               required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email" class="form-control-label">Email Address</label>
                                        <input class="form-control @error('email') is-invalid @enderror" 
                                               type="email" 
                                               name="email" 
                                               id="email"
                                               value="{{ old('email', auth()->user()->email) }}"
                                               required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="horizontal dark">
                            <p class="text-uppercase text-sm">Change Password</p>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="current_password" class="form-control-label">Current Password</label>
                                        <input class="form-control @error('current_password') is-invalid @enderror" 
                                               type="password" 
                                               name="current_password" 
                                               id="current_password">
                                        @error('current_password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="new_password" class="form-control-label">New Password</label>
                                        <input class="form-control @error('new_password') is-invalid @enderror" 
                                               type="password" 
                                               name="new_password" 
                                               id="new_password">
                                        @error('new_password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="new_password_confirmation" class="form-control-label">Confirm New Password</label>
                                        <input class="form-control" 
                                               type="password" 
                                               name="new_password_confirmation" 
                                               id="new_password_confirmation">
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <p class="text-xs text-secondary mb-0">
                                        <i class="ni ni-bell-55 me-1"></i>
                                        Leave password fields empty if you don't want to change your password
                                    </p>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header pb-0">
                        <h6>Profile Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-control-label">Current Name</label>
                            <p class="text-sm font-weight-bold">{{ auth()->user()->name }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-control-label">Current Email</label>
                            <p class="text-sm font-weight-bold">{{ auth()->user()->email }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-control-label">Role</label>
                            <p class="text-sm font-weight-bold">{{ ucfirst(auth()->user()->role) }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-control-label">Member Since</label>
                            <p class="text-sm font-weight-bold">{{ auth()->user()->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.footers.auth.footer')
    </div>
@endsection
