@extends('admin.layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid my-2">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Order: #{{ $order->id }}</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('orders.index') }}" class="btn btn-primary">Back</a>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">

        @if(Session::has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ Session::get('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(Session::has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ Session::get('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        <div class="row">
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header pt-3">
                        <div class="row invoice-info">
                            <div class="col-sm-4 invoice-col">
                                <h1 class="h5 mb-3">Shipping Address</h1>
                                <address>
                                    <strong>{{ $order->first_name }} {{ $order->last_name }}</strong><br>
                                    {{ $order->address }}<br>
                                    {{ $order->city }}, {{ $order->zip }} {{ $order->countryName }}<br>
                                    Phone: {{ $order->mobile }}<br>
                                    Email: {{ $order->email }}
                                </address>

                                @if (!empty($order->shipped_date))
                                    <strong>Shipped Date</strong><br>
                                    <time datetime="{{ $order->shipped_date }}">{{ \Carbon\Carbon::parse($order->shipped_date)->format('d M, Y') }}</time>
                                @endif
                            </div>

                            <div class="col-sm-4 invoice-col">
                                <b>Invoice #00{{ $order->id }}</b><br>
                                <br>
                                <b>Order ID:</b> {{ $order->id }}<br>
                                <b>Total:</b> ${{ number_format($order->grand_total, 2) }}<br>
                                <b>Status:</b>
                                @if ($order->status == 'pending')
                                    <span class="text-danger">Pending</span>
                                @elseif ($order->status == 'shipped')
                                    <span class="text-info">Shipped</span>
                                @elseif ($order->status == 'delivered')
                                    <span class="text-success">Delivered</span>
                                @else
                                    <span class="text-danger">Cancelled</span>
                                @endif
                                <br>
                            </div>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-3">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th width="100">Price</th>
                                    <th width="100">Qty</th>
                                    <th width="100">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orderItems as $item)
                                <tr>
                                    <td>{{ $item->name }}</td>
                                    <td>${{ number_format($item->price, 2) }}</td>
                                    <td>{{ $item->qty }}</td>
                                    <td>${{ number_format($item->total, 2) }}</td>
                                </tr>
                                @endforeach
                                <tr>
                                    <th colspan="3" class="text-right">Subtotal:</th>
                                    <td>${{ number_format($order->subtotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <th colspan="3" class="text-right">Discount: {{ (!empty($order->coupon_code)) ? '('.$order->coupon_code.')' : '' }}</th>
                                    <td>${{ number_format($order->discount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th colspan="3" class="text-right">Shipping:</th>
                                    <td>${{ number_format($order->shipping, 2) }}</td>
                                </tr>
                                <tr>
                                    <th colspan="3" class="text-right">Grand Total:</th>
                                    <td>${{ number_format($order->grand_total, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <form action="" method="post" id="changeOrderStatusForm" name="changeOrderStatusForm">
                        <div class="card-body">
                            <h2 class="h4 mb-3">Order Status</h2>
                            <div class="mb-3">
                                <select name="status" id="status" class="form-control">
                                    <option value="pending" {{ ($order->status == 'pending') ? 'selected' : '' }}>Pending</option>
                                    <option value="shipped" {{ ($order->status == 'shipped') ? 'selected' : '' }}>Shipped</option>
                                    <option value="delivered" {{ ($order->status == 'delivered') ? 'selected' : '' }}>Delivered</option>
                                    <option value="cancelled" {{ ($order->status == 'cancelled') ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="shipped_date">Shipped Date</label>
                                <input type="text" value="{{ $order->shipped_date }}" name="shipped_date" id="shipped_date" class="form-control" placeholder="Shipped Date">
                            </div>

                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card">
                    <form action="" method="post" id="sendInvoiceEmail" name="sendInvoiceEmail">
                        <div class="card-body">
                            <h2 class="h4 mb-3">Send Invoice Email</h2>
                            <div class="mb-3">
                                <select name="user_type" id="user_type" class="form-control">
                                    <option value="customer">Customer</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary">Send</button>
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
    $(document).ready(function(){
        $('#shipped_date').datetimepicker({
            format:'Y-m-d H:i:s',
        });
    });

    $("#changeOrderStatusForm").submit(function(event){
        event.preventDefault();

        if (confirm("Are you sure you want to change status?")) {
            $.ajax({
                url: '{{ route("orders.changeOrderStatus", $order->id) }}',
                type: 'post',
                data: $(this).serializeArray(),
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response){
                    window.location.href = '{{ route("orders.detail", $order->id) }}';
                }
            });
        }
    });

$("#sendInvoiceEmail").submit(function(event){
        event.preventDefault();

        if (confirm("Are you sure you want to send email?")) {
            $.ajax({
                url: '{{ route("orders.sendInvoiceEmail", $order->id) }}',
                type: 'post',
                data: $(this).serializeArray(),
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response){
                    // Agar successfully mail chali gayi
                    window.location.href = '{{ route("orders.detail", $order->id) }}';
                },
                error: function(jqXHR, exception) {
                    // Agar background mein koi error aayi toh ye alert aayega
                    console.log(jqXHR.responseText);
                    alert("Backend Error: Mail nahi gayi. Please check your code.");
                }
            });
        }
    });
</script>
@endsection
