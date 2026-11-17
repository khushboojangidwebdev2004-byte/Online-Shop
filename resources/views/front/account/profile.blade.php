@extends('front.layouts.app')

@section('content')
<section class="section-5 pt-3 pb-3 mb-3 bg-white">
    <div class="container">
        <ol class="breadcrumb primary-color mb-0">
            <li class="breadcrumb-item"><a class="white-text" href="{{ route('front.home') }}">Home</a></li>
            <li class="breadcrumb-item">Settings</li>
        </ol>
    </div>
</section>

<section class=" section-11 ">
    <div class="container  mt-5">
        <div class="row">
            <div class="col-md-3">
                @include('front.account.common.sidebar')
            </div>
            <div class="col-md-9">
                @include('front.account.common.message')
                <div class="card">
                    <div class="card-header">
                        <h2 class="h5 mb-0 pt-2 pb-2">Personal Information</h2>
                    </div>
                    <form action="" name="profileForm" id="profileForm">
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="mb-3">
                                    <label for="name">Name</label>
                                    <input type="text" name="name" id="name" placeholder="Enter Your Name" class="form-control" value="{{ $user->name }}">
                                    <p></p>
                                </div>
                                <div class="mb-3">
                                    <label for="email">Email</label>
                                    <input type="text" name="email" id="email" placeholder="Enter Your Email" class="form-control" value="{{ $user->email }}">
                                    <p></p>
                                </div>
                                <div class="mb-3">
                                    <label for="phone">Phone</label>
                                    <input type="text" name="phone" id="phone" placeholder="Enter Your Phone" class="form-control" value="{{ $user->phone }}">
                                    <p></p>
                                </div>
                                <div class="d-flex">
                                    <button type="submit" class="btn btn-dark">Update</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card mt-5">
                    <div class="card-header">
                        <h2 class="h5 mb-0 pt-2 pb-2">Address</h2>
                    </div>
                    <form action="" name="addressForm" id="addressForm">
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name">First Name</label>
                                    <input type="text" name="first_name" id="first_name" placeholder="Enter Your First Name" class="form-control" value="{{ (!empty($address)) ? $address->first_name : '' }}">
                                    <p></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="last_name">Last Name</label>
                                    <input type="text" name="last_name" id="last_name" placeholder="Enter Your Last Name" class="form-control" value="{{ (!empty($address)) ? $address->last_name : '' }}">
                                    <p></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email">Email</label>
                                    <input type="text" name="email" id="email" placeholder="Enter Your Email" class="form-control" value="{{ (!empty($address)) ? $address->email : '' }}">
                                    <p></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="mobile">Mobile</label>
                                    <input type="text" name="mobile" id="mobile" placeholder="Enter Your Mobile No." class="form-control" value="{{ (!empty($address)) ? $address->mobile : '' }}">
                                    <p></p>
                                </div>
                                <div class="mb-3">
                                    <label for="country_id">Country</label>
                                    <select name="country_id" id="country_id" class="form-control">
                                        <option value="">Select a Country</option>
                                        @if($countries->isNotEmpty())
                                            @foreach($countries as $country)
                                                <option {{ (!empty($address) && $address->country_id == $country->id) ? 'selected' : '' }} value="{{ $country->id }}">{{ $country->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <p></p>
                                </div>
                                <div class="mb-3">
                                    <label for="address">Address</label>
                                    <textarea name="address" id="address" cols="30" rows="5" class="form-control">{{ (!empty($address)) ? $address->address : '' }}</textarea>
                                    <p></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="apartment">Apartment</label>
                                    <input type="text" name="apartment" id="apartment" placeholder="Apartment" class="form-control" value="{{ (!empty($address)) ? $address->apartment : '' }}">
                                    <p></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="city">City</label>
                                    <input type="text" name="city" id="city" placeholder="City" class="form-control" value="{{ (!empty($address)) ? $address->city : '' }}">
                                    <p></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="state">State</label>
                                    <input type="text" name="state" id="state" placeholder="State" class="form-control" value="{{ (!empty($address)) ? $address->state : '' }}">
                                    <p></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="zip">Zip</label>
                                    <input type="text" name="zip" id="zip" placeholder="Zip" class="form-control" value="{{ (!empty($address)) ? $address->zip : '' }}">
                                    <p></p>
                                </div>
                                <div class="d-flex">
                                    <button type="submit" class="btn btn-dark">Update</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('customJs')
<script>
    // PROFILE UPDATE AJAX
    $("#profileForm").submit(function(event){
        event.preventDefault();
        $.ajax({
            url: '{{ route("account.updateProfile") }}',
            type: 'post',
            data: $(this).serializeArray(),
            dataType: 'json',
            success: function(response){
                if (response.status == true) {
                    window.location.href = '{{ route("account.profile") }}';
                } else {
                    var errors = response.errors;
                    if (errors.name) {
                        $("#name").addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors.name);
                    } else {
                        $("#name").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                    }
                    // Same logic for email and phone...
                }
            }
        });
    });

    // ADDRESS UPDATE AJAX
    $("#addressForm").submit(function(event){
        event.preventDefault();
        $.ajax({
            url: '{{ route("account.updateAddress") }}',
            type: 'post',
            data: $(this).serializeArray(),
            dataType: 'json',
            success: function(response){
                if (response.status == true) {
                    window.location.href = '{{ route("account.profile") }}';
                } else {
                    // Error Handling...
                }
            }
        });
    });
</script>
@endsection
