@extends('front.layouts.app')

@section('content')
<section class="section-10">
    <div class="container">
        <h1 class="my-3">{{ $page->name }}</h1>

        @if ($page->slug == 'contact-us')
        <div class="row">
            <div class="col-md-6">
                {!! $page->content !!}
            </div>
            <div class="col-md-6">
                @include('front.account.common.message')
                <form class="shake" role="form" method="post" id="contactForm" name="contactForm">
                    <div class="mb-3">
                        <label class="mb-2" for="name">Name</label>
                        <input class="form-control" id="name" type="text" name="name" >
                        <p class="invalid-feedback"></p>
                    </div>
                    <div class="mb-3">
                        <label class="mb-2" for="email">Email</label>
                        <input class="form-control" id="email" type="text" name="email" >
                        <p class="invalid-feedback"></p>
                    </div>
                    <div class="mb-3">
                        <label class="mb-2">Subject</label>
                        <input class="form-control" id="subject" type="text" name="subject" >
                        <p class="invalid-feedback"></p>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="mb-2">Message</label>
                        <textarea class="form-control" rows="3" id="message" name="message"></textarea>
                    </div>
                    <div class="form-submit">
                        <button class="btn btn-dark" type="submit" id="form-submit"><i class="material-icons mdi mdi-message-outline"></i> Send Message</button>
                    </div>
                </form>
            </div>
        </div>
        @else
            {!! $page->content !!}
        @endif
    </div>
</section>
@endsection

@section('customJs')
<script>
    $("#contactForm").submit(function(event){
        event.preventDefault();
        $("#form-submit").prop('disabled', true);

        $.ajax({
            url: '{{ route("front.sendContactEmail") }}',
            type: 'post',
            data: $(this).serializeArray(),
            dataType: 'json',
            success: function(response){
                $("#form-submit").prop('disabled', false);
                if (response.status == true) {
                    window.location.href = "{{ route('front.page', $page->slug) }}";
                } else {
                    var errors = response.errors;
                    if (errors.name) {
                        $("#name").addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors.name);
                    } else {
                        $("#name").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                    }
                    // Email aur Subject ke liye bhi same logic yahan aayega...
                    if (errors.email) {
                        $("#email").addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors.email);
                    } else {
                        $("#email").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                    }
                    if (errors.subject) {
                        $("#subject").addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors.subject);
                    } else {
                        $("#subject").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                    }
                }
            }
        });
    });
</script>
@endsection
