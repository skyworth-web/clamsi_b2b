<body>
    <div style="margin: 0px auto; max-width: 600px;">
        <div style="width: 100%; display: flex; justify-content: center; align-items: center;" class="img-box mb-3">
            <img src="{{ asset('frontend/elegant/images/Ecommerce-landing-page.png') }}" alt="Welcome" srcset=""
                style="max-width: 100%; max-height: 100%; object-fit: cover;">
        </div>
        <h1 class="heading">आपका {{ $system_settings['app_name'] }} परिवार में स्वागत है!🎉</h1>
        <div class="">
            <h4 style="font-weight: 700;">हेलो, {{$username ?? ""}}👋</h4>
            <h4 style="font-weight: 500;">🛍 {{ $system_settings['app_name'] }} के साथ खरीदारी क्यों करें?</h4>
            <ul style="font-weight: lighter;">
                <li>गुणवत्ता आश्वासन: हम सुनिश्चित करते हैं कि केवल सर्वश्रेष्ठ उत्पाद आपके द्वार पर पहुंचते हैं।</li>
                <li>तेज़ और विश्वसनीय शिपिंग: अपने आदेश जल्दी और सुरक्षित तरीके से प्राप्त करें।</li>
                <li>असाधारण ग्राहक सेवा: हमारी समर्पित सहायता टीम हर कदम पर आपकी मदद के लिए यहां है।</li>
            </ul>
            <p style="font-weight: lighter;">अपनी खरीददारी यात्रा {{ $system_settings['app_name'] }} के साथ आज ही शुरू करें और अंतिम ऑनलाइन खरीदारी अनुभव का आनंद लें। खुश रहें खरीदते रहें!</p>
        </div>
        <div style="width:100%; display:flex; justify-content: center; align-items: center;" class="img-box mb-3">
            <a href="{{ customUrl('') }}">
                <img src="{{ asset('frontend/elegant/images/shopNow.png') }}" alt="Welcome" srcset=""
                    style="max-width: 110px;max-height: 100%;object-fit: cover;cursor: pointer;">
            </a>
        </div>
    </div>
</body>
