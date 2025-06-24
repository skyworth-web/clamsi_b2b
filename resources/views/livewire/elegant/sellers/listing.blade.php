<div id="page-content">
    <section class="testimonial-slider style1">
        <x-utility.breadcrumbs.breadcrumbTwo :$bread_crumb />
        @if (isset($Sellers['listing']) && $Sellers['listing'] != [])
            <div class="container-fluid index-demo3 ">

                <div class="testimonial-wraper">
                    <!--Testimonial Slider Items-->
                    <div class="row col-row row-cols-lg-5 row-cols-md-4 row-cols-2">
                        @foreach ($Sellers['listing'] as $seller)
                            <x-utility.seller.cardOne :$seller />
                        @endforeach
                    </div>
                    <!--Testimonial Slider Items-->
                </div>
                <div>{!! $Sellers['links'] !!}</div>
            </div>
        @else
            @php
                $title = labels('front_messages.no_seller_found', 'No Seller Found!');
            @endphp
            <x-utility.others.not-found :$title />
        @endif
    </section>
</div>
