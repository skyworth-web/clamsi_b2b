<body style="font-family: Arial, sans-serif; text-align: center; margin: 0; padding: 20px;">
    <div
        style="background-color: white; border-radius: 5px; box-shadow: rgba(0, 0, 0, 0.05) 0px 0px 0px 1px; padding: 20px; max-width: 600px; margin: 0 auto;"> 
        <img src="{{ asset('storage/' . $system_settings['logo']) }}" alt="{{ $system_settings['app_name'] }}"
            style="width: 149px;height: 60px;object-fit:contain;">
        <h2 style="color: #333;">पासवर्ड सफलतापूर्वक बदल दिया गया</h2>
        <p style="color:#666;text-transform: capitalize;">"{{ $username ?? '' }} आपका पासवर्ड सफलतापूर्वक बदल दिया गया है। सुरक्षा कारणों से, कृपया ध्यान दें कि आपका नया पासवर्ड गोपनीय रखें और इसे किसी से साझा न करें।"</p>
        <a href="{{ customUrl('login') }}"
            style="background-color: #1b1919; color: white; border: none; padding: 10px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; border-radius: 4px; cursor: pointer;">साइन इन करें</a>
    </div>
</body>
