@extends('front.layouts.app')

@section('content')
<section class="section-5 pt-3 pb-3 mb-3 bg-white">
    <div class="container">
        <div class="light-font">
            <ol class="breadcrumb primary-color mb-0">
                <li class="breadcrumb-item"><a class="white-text" href="{{ route('front.home') }}">Home</a></li>
                <li class="breadcrumb-item active">Shop</li>
            </ol>
        </div>
    </div>
</section>

<section class="section-6 pt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-3 sidebar">

                <div class="sub-title">
                    <h2>Categories</h2>
                </div>
                <div class="card">
                    <div class="card-body">
                      <div class="accordion accordion-flush" id="accordionExample">
    @if ($categories->isNotEmpty())
        @foreach ($categories as $key => $category)
            <div class="accordion-item">
                @if ($category->sub_category->isNotEmpty())
                    <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button collapsed {{ ($categorySelected == $category->id) ? 'text-primary' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne-{{ $key }}" aria-expanded="false">
                            {{ $category->name }}
                        </button>
                    </h2>
                @else
                    {{-- Agar sub-category nahi hai, toh sirf category ka link --}}
                    <a href="{{ route('front.shop', $category->slug) }}" class="nav-item nav-link {{ ($categorySelected == $category->id) ? 'text-primary' : '' }}">{{ $category->name }}</a>
                @endif

                @if ($category->sub_category->isNotEmpty())
                    <div id="collapseOne-{{ $key }}" class="accordion-collapse collapse {{ ($categorySelected == $category->id) ? 'show' : '' }}" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <div class="navbar-nav">
                                @foreach ($category->sub_category as $subCategory)
                                    {{-- Loop ke ANDAR hi $subCategory use karein --}}
                                    <a href="{{ route('front.shop', [$category->slug, $subCategory->slug]) }}" class="nav-item nav-link {{ ($subCategorySelected == $subCategory->id) ? 'text-primary' : '' }}">
                                        {{ $subCategory->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    @endif
</div>
                    </div>
                </div>

                <div class="sub-title mt-5">
                    <h2>Brand</h2>
                </div>
                <div class="card">
                    <div class="card-body">
                        @if ($brands->isNotEmpty())
                            @foreach ($brands as $brand)
                                <div class="form-check mb-2">
                                    <input class="form-check-input brand-label" type="checkbox" name="brand[]"
                                    value="{{ $brand->id }}" id="brand-{{ $brand->id }}"
                                    {{ (in_array($brand->id, $brandsArray)) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="brand-{{ $brand->id }}">
                                        {{ $brand->name }}
                                    </label>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                <div class="sub-title mt-5">
                    <h2>Price</h2>
                </div>
                <div class="card">
                    <div class="card-body">
                        <input type="text" class="js-range-slider" name="my_range" value="" />
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <div class="row pb-3">
                    <div class="col-12 pb-1">
                        <div class="d-flex align-items-center justify-content-end mb-4">
                            <div class="ml-2">
                                <select name="sort" id="sort" class="form-control">
                                    <option value="latest" {{ ($sort == 'latest') ? 'selected' : '' }}>Latest</option>
                                    <option value="price_desc" {{ ($sort == 'price_desc') ? 'selected' : '' }}>Price High</option>
                                    <option value="price_asc" {{ ($sort == 'price_asc') ? 'selected' : '' }}>Price Low</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    @if ($products->isNotEmpty())
                        @foreach ($products as $product)
                            @php
                                $productImage = $product->product_images->first();
                            @endphp
                            <div class="col-md-4">
                                   <div class="card product-card">
    <div class="product-image position-relative"> <a href="{{ route('front.product', $product->slug) }}" class="product-img">
            @if (!empty($product->product_images->first()))
                <img class="card-img-top" src="{{ asset('uploads/product/small/'.$product->product_images->first()->image) }}" alt="">
            @else
                <img class="card-img-top" src="{{ asset('admin-assets/img/default-150x150.png') }}" alt="">
            @endif
        </a>

        <a onclick="addToWishlist({{ $product->id }});" class="whishlist" href="javascript:void(0);">
            <i class="far fa-heart"></i>
        </a>

      <div class="product-action">
    @if($product->track_qty == 'Yes')
        @if($product->qty > 0)
            <a class="btn btn-dark" href="javascript:void(0);" onclick="addToCart({{ $product->id }});">
                <i class="fa fa-shopping-cart"></i> Add To Cart
            </a>
        @else
            <a class="btn btn-dark" href="javascript:void(0);">
                Out of Stock
            </a>
        @endif
    @else
        <a class="btn btn-dark" href="javascript:void(0);" onclick="addToCart({{ $product->id }});">
            <i class="fa fa-shopping-cart"></i> Add To Cart
        </a>
    @endif
</div>
    </div>
    <div class="card-body text-center mt-3">
        <a class="h6 link" href="{{ route('front.product', $product->slug) }}">{{ $product->title }}</a>
        <div class="price mt-2">
            <span class="h5"><strong>${{ $product->price }}</strong></span>
            @if($product->compare_price > 0)
                <span class="h6 text-underline"><del>${{ $product->compare_price }}</del></span>
            @endif
        </div>
    </div>
</div>
                            </div>
                        @endforeach
                    @else
                        <div class="col-md-12 text-center mt-5">
                            <h4>No products found.</h4>
                        </div>
                    @endif

                   <div class="col-md-12 pt-5">
    {{ $products->withQueryString()->links() }}
</div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('customJs')
<script>
// 1. Price Slider
$(".js-range-slider").ionRangeSlider({
    type: "double",
    min: 0,
    max: 1000,
    from: {{ $priceMin }},
    to: {{ $priceMax }},
    step: 10,
    skin: "round",
    max_postfix: "+",
    prefix: "$",
    onFinish: function () {
        apply_filters();
    }
});

var slider = $(".js-range-slider").data("ionRangeSlider");

// 2. Events
$(".brand-label").change(function(){
    apply_filters();
});

$("#sort").change(function(){
    apply_filters();
});

// 3. Apply Filters Function
function apply_filters(){
    var brands = [];
    $(".brand-label:checked").each(function(){
        brands.push($(this).val());
    });

    // Current URL (excluding query string)
    var url = window.location.href.split('?')[0];
    var params = new URLSearchParams();

    // Add Price
    params.set('price_min', slider.result.from);
    params.set('price_max', slider.result.to);

    // Add Brand
    if(brands.length > 0){
        params.set('brand', brands.join(','));
    }

    // Add Sort
    var sort = $("#sort").val();
    if(sort !== ''){
        params.set('sort', sort);
    }

    // Redirect to clean URL
    window.location.href = url + '?' + params.toString();
}
/* apply_filters function ke andar ye code update karein */
function apply_filters() {
    var url = '{{ url()->current() }}?';

    // Search keyword logic
    if ($("#search").val().length > 0) {
        url += '&search=' + $("#search").val();
    }

    // Brand filter logic (Pehle se hoga aapke paas)
    var brands = [];
    $(".brand-label:checked").each(function(){
        brands.push($(this).val());
    });
    if (brands.length > 0) {
        url += '&brand=' + brands.toString();
    }

    // Price range logic (Pehle se hoga aapke paas)
    url += '&price_min=' + $(".js-range-slider").data("from") + '&price_max=' + $(".js-range-slider").data("to");

    // Sorting logic
    url += '&sort=' + $("#sort").val();

    window.location.href = url;
}
</script>
@endsection
