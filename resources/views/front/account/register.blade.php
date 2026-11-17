@extends('front.layouts.app')

@section('content')

<section class="section-10">
    <div class="container">
        <div class="login-form">

            <form id="registrationForm" method="POST">
                @csrf

                <h4 class="modal-title">Register Now</h4>

                <div class="form-group">
                    <input type="text" class="form-control" name="name" id="name" placeholder="Name">
                    <small class="text-danger"></small>
                </div>

                <div class="form-group">
                    <input type="email" class="form-control" name="email" id="email" placeholder="Email">
                    <small class="text-danger"></small>
                </div>

                <div class="form-group">
                    <input type="text" class="form-control" name="phone" id="phone" placeholder="Phone">
                    <small class="text-danger"></small>
                </div>

                <div class="form-group">
                    <input type="password" class="form-control" name="password" id="password" placeholder="Password">
                    <small class="text-danger"></small>
                </div>

                <div class="form-group">
                    <input type="password" class="form-control" name="password_confirmation" placeholder="Confirm Password">
                </div>

                <button type="submit" class="btn btn-dark btn-lg btn-block">
                    Register
                </button>

            </form>

        </div>
    </div>
</section>

@endsection


@section('customJs')
<script>

$(document).ready(function(){

    $("#registrationForm").submit(function(e){

        e.preventDefault();

        $.ajax({
            url: "{{ route('account.processRegister') }}",
            type: "POST",
            data: $(this).serialize(),
            success: function(response){

                if(response.status === true){

                    window.location.href = "{{ route('account.login') }}?success=1";

                }

            },
            error: function(xhr){
                console.log(xhr.responseText);
            }
        });

    });

});

</script>
@endsection

