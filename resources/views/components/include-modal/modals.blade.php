{{-- popup message --}}
@if (session()->has('message'))
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show">
        <p class="product-notification">
            {{ session('message') }}
        </p>
    </div>
@endif
<div id="product-notification" class="product-notification position-fixed hide"></div>
