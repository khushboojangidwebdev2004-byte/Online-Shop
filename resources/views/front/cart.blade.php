@extends('front.layouts.app')

@section('content')
<section class="section-9 pt-4">
    <div class="container">
        <div class="row">
            @if(Session::has('success'))
                <div class="col-md-12">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {!! Session::get('success') !!}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            @endif

            @if(Session::has('error'))
                <div class="col-md-12">
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ Session::get('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            @endif

            @if(Cart::count() > 0)
            <div class="col-md-8">
                <div class="table-responsive">
                    <table class="table" id="cart">
                        <thead>
                            <tr class="bg-warning">
                                <th>Item</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                                <th>Remove</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cartContent as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @php $productImage = $item->options->productImage; @endphp
                                        @if(!empty($productImage))
                                            <img src="{{ asset('uploads/product/small/'.$productImage->image) }}" width="50" class="mr-2">
                                        @endif
                                        <h2 class="h5 text-secondary">{{ $item->name }}</h2>
                                    </div>
                                </td>
                                <td>${{ $item->price }}</td>
                                <td>
                                    <div class="input-group quantity mx-auto" style="width: 100px;">
                                        <div class="input-group-btn">
                                            <button type="button" class="btn btn-sm btn-dark btn-minus p-2 pt-1 pb-1 sub" data-id="{{ $item->rowId }}">
                                                <i class="fa fa-minus"></i>
                                            </button>
                                        </div>
                                        <input type="text" class="form-control form-control-sm border-0 text-center" value="{{ $item->qty }}">
                                        <div class="input-group-btn">
                                            <button type="button" class="btn btn-sm btn-dark btn-plus p-2 pt-1 pb-1 add" data-id="{{ $item->rowId }}">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </td>
                                <td>${{ $item->price * $item->qty }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteItem('{{ $item->rowId }}');">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card cart-summery">
                    <div class="card-body">
                        <h3 class="pb-3 text-uppercase font-weight-bold" style="border-bottom: 2px solid #ffc107; display:inline-block; margin-bottom: 20px;">CART SUMMERY</h3>

                        <div class="sub-total d-flex justify-content-between pb-2">
                            <h2 class="h4">Subtotal</h2>
                            <h2 class="h4">${{ Cart::subtotal() }}</h2>
                        </div>
                        <div class="sub-total d-flex justify-content-between pb-2">
                            <h2 class="h4">Shipping</h2>
                            <h2 class="h4">$0</h2>
                        </div>
                        <div class="sub-total d-flex justify-content-between pb-2">
                            <h2 class="h4">Total</h2>
                            <h2 class="h4">${{ Cart::subtotal() }}</h2>
                        </div>
                <div class="card cart-summery">
    <div class="card-body">
        <div class="sub-total d-flex justify-content-between pb-2 pt-2">
            <h2 class="h4">Subtotal</h2>
            <h2 class="h4">${{ Cart::subtotal() }}</h2>
        </div>
        <div class="pt-2">
            <a href="{{ route('front.checkout') }}" class="btn btn-dark btn-block w-100">Proceed to Checkout</a>
        </div>
    </div>
</div>
                </div>
            </div>
            @else
            <div class="col-md-12 text-center pt-5 pb-5">
                <h4>Your Cart is Empty!</h4>
                <a href="{{ route('front.home') }}" class="btn btn-primary mt-3">Continue Shopping</a>
            </div>
            @endif
        </div>
    </div>
</section>
@endsection

@section('customJs')
<script>
    // Exact Add Button Logic
    $('.add').click(function(e){
        e.preventDefault();
        var qtyElement = $(this).parent().prev();
        var qtyValue = parseInt(qtyElement.val());

        if (qtyValue < 10) {
            qtyElement.val(qtyValue + 1);
            var rowId = $(this).data('id');
            var newQty = qtyElement.val();
            updateCart(rowId, newQty);
        }
    });

    // Exact Sub Button Logic
    $('.sub').click(function(e){
        e.preventDefault();
        var qtyElement = $(this).parent().next();
        var qtyValue = parseInt(qtyElement.val());

        if (qtyValue > 1) {
            qtyElement.val(qtyValue - 1);
            var rowId = $(this).data('id');
            var newQty = qtyElement.val();
            updateCart(rowId, newQty);
        }
    });

    // Update Cart AJAX
    function updateCart(rowId, qty) {
        $.ajax({
            url: '{{ route("front.updateCart") }}',
            type: 'post',
            data: {rowId: rowId, qty: qty},
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                // Success hote hi page reload hoga taaki Messages aur Total dikhe
                window.location.href = '{{ route("front.cart") }}';
            }
        });
    }

    // Delete Item AJAX
    function deleteItem(rowId) {
        if (confirm("Are you sure you want to delete this item?")) {
            $.ajax({
                url: '{{ route("front.deleteItem") }}',
                type: 'post',
                data: {rowId: rowId},
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    window.location.href = '{{ route("front.cart") }}';
                }
            });
        }
    }
</script>
@endsection
