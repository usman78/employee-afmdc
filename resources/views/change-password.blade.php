@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Change Password') }}</div>
                <div class="card-body">
                    @if ($passwordChangeRequired ?? false)
                        <div class="alert alert-warning" role="alert">
                            {{ $passwordChangeReason ?? 'Please change your password before continuing.' }}
                        </div>
                    @endif
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    <div class="alert alert-info" role="alert">
                        Password must be at least 8 characters and include uppercase letters, lowercase letters, and numbers.
                    </div>
                    <form method="POST" action="{{ route('update-password') }}">
                        @csrf
                        <div class="form-group row">
                            <label for="current_password" class="col-md-4 col-form-label text-md-right">{{ __('Current Password') }}</label>

                            <div class="col-md-6">
                                <div class="input-group">
                                    <input id="current_password" type="password" class="form-control @error('current_password') is-invalid @enderror" name="current_password" required autocomplete="current-password">
                                    <button type="button" class="btn btn-outline-secondary toggle-password" data-target="current_password" aria-label="Show current password" title="Show password">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                    </button>
                                </div>

                                @error('current_password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="new_password" class="col-md-4 col-form-label text-md-right">{{ __('New Password') }}</label>

                            <div class="col-md-6">
                                <div class="input-group">
                                    <input id="new_password" type="password" class="form-control @error('new_password') is-invalid @enderror" name="new_password" required autocomplete="new-password">
                                    <button type="button" class="btn btn-outline-secondary toggle-password" data-target="new_password" aria-label="Show new password" title="Show password">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                    </button>
                                </div>

                                @error('new_password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="new_password_confirmation" class="col-md-4 col-form-label text-md-right">{{ __('Confirm New Password') }}</label>

                            <div class="col-md-6">
                                <div class="input-group">
                                    <input id="new_password_confirmation" type="password" class="form-control" name="new_password_confirmation" required autocomplete="new-password">
                                    <button type="button" class="btn btn-outline-secondary toggle-password" data-target="new_password_confirmation" aria-label="Show password confirmation" title="Show password">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Change Password') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.querySelectorAll('.toggle-password').forEach(function (button) {
        button.addEventListener('click', function () {
            const input = document.getElementById(button.dataset.target);
            const icon = button.querySelector('i');
            const showing = input.type === 'text';

            input.type = showing ? 'password' : 'text';
            icon.classList.toggle('bi-eye', showing);
            icon.classList.toggle('bi-eye-slash', !showing);
            button.setAttribute('aria-label', showing ? button.dataset.showLabel : button.dataset.hideLabel);
            button.setAttribute('title', showing ? 'Show password' : 'Hide password');
        });

        button.dataset.showLabel = button.getAttribute('aria-label');
        button.dataset.hideLabel = button.getAttribute('aria-label').replace('Show', 'Hide');
    });
</script>
@endsection
