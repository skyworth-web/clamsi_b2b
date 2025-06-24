<div
    style="font-family: Arial, sans-serif; background-color: white; padding: 20px; border-radius: 10px; box-shadow: rgba(0, 0, 0, 0.05) 0px 0px 0px 1px; max-width: 600px; margin: 0 auto; text-align: center;">
    <img src="{{ asset('storage/' . $system_settings['logo']) }}" alt="{{ $system_settings['app_name'] }}"
        style="width: 149px;height: 60px;object-fit:contain;">
    <span style="font-size: 24px; animation: bounce 1s infinite;display: inline-block;">📱</span>
    <h3>नया साइन-इन {{ $device ?? 'लिनक्स' }} पर</h3>
    <p>नमस्ते {{ $username ?? 'मिलें' }}👋,</p>
    <p>हमने देखा है कि आपके {{ $system_settings['app_name'] }} अकाउंट में {{ $device ?? 'लिनक्स' }} डिवाइस से एक नया साइन-इन हुआ है {{($currentDateTime ?? "") . ($timeZone ?? "")}}. अगर यह आप थे, तो कृपया इस ईमेल को नजरअंदाज करें। कोई अधिक कार्रवाई की आवश्यकता नहीं है।</p>
    <p>अगर यह आप नहीं थे, तो कृपया {{ $system_settings['app_name'] }} में अपना पासवर्ड बदलें <a
            href="{{ customUrl('password-recovery') }}" style=" text-decoration: underline;">पासवर्ड बदलें</a>।</p>
    <p>धन्यवाद,<br>टीम {{ $system_settings['app_name'] }}</p>
</div>

<style>
    @keyframes bounce {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-10px);
        }
    }
</style>
