<body>
    <div style="margin: 0px auto; max-width: 600px;">
        <div style="width: 100%; display: flex; justify-content: center; align-items: center;" class="img-box mb-3">
            <img src="{{ asset('frontend/elegant/images/Ecommerce-landing-page.png') }}" alt="Welcome" srcset=""
                style="max-width: 100%; max-height: 100%; object-fit: cover;">
        </div>
        <h1 class="heading">Welcome to the {{ $system_settings['app_name'] }} family!🎉</h1>
        <div class="">
            <h4 style="font-weight: 700;">Hey, {{$username ?? ""}}👋</h4>
            <h4 style="font-weight: 500;">🛍 Why Shop with {{ $system_settings['app_name'] }}?</h4>
            <ul style="font-weight: lighter;">
                <li>Quality Assurance: We ensure only the best products make it to your doorstep.</li>
                <li>Fast & Reliable Shipping: Get your orders delivered swiftly and securely.</li>
                <li>Exceptional Customer Service: Our dedicated support team is here to assist you every step of the
                    way.</li>
            </ul>
            <p style="font-weight: lighter;">Start your shopping journey with {{ $system_settings['app_name'] }} today and experience the ultimate
                online shopping experience. Happy shopping!</p>
        </div>
        <div style="width:100%; display:flex; justify-content: center; align-items: center;" class="img-box mb-3">
            <a href="{{ customUrl('') }}">
                <img src="{{ asset('frontend/elegant/images/shopNow.png') }}" alt="Welcome" srcset=""
                    style="max-width: 110px;max-height: 100%;object-fit: cover;cursor: pointer;">
            </a>
        </div>
    </div>
</body>
