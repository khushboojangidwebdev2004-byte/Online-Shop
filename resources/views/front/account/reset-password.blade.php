@extends('front.layouts.app')

@section('content')
<section class="section-10">
    <div class="container">
        @if (Session::has('success'))
            <div class="alert alert-success">{{ Session::get('success') }}</div>
        @endif
        @if (Session::has('error'))
            <div class="alert alert-danger">{{ Session::get('error') }}</div>
        @endif

        <div class="login-form">
            <form action="{{ route('front.processResetPassword') }}" method="post">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}"> <h4 class="modal-title">Reset Password</h4>

                <div class="form-group">
                    <input type="password" class="form-control @error('new_password') is-invalid @enderror" placeholder="New Password" name="new_password">
                    @error('new_password')
                        <p class="invalid-feedback">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <input type="password" class="form-control @error('confirm_password') is-invalid @enderror" placeholder="Confirm Password" name="confirm_password">
                    @error('confirm_password')
                        <p class="invalid-feedback">{{ $message }}</p>
                    @enderror
                </div>

                <input type="submit" class="btn btn-dark btn-block btn-lg" value="Update Password">
            </form>
            <div class="text-center small"><a href="{{ route('account.login') }}">Click here to Login</a></div>
        </div>
    </div>
</section>
@endsection
