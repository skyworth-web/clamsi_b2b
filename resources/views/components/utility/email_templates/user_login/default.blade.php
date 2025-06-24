<div
    style="font-family: Arial, sans-serif; background-color: white; padding: 20px; border-radius: 10px; box-shadow: rgba(0, 0, 0, 0.05) 0px 0px 0px 1px; max-width: 600px; margin: 0 auto; text-align: center;">
    <img src="{{ asset('storage/' . $system_settings['logo']) }}" alt="{{ $system_settings['app_name'] }}"
        style="width: 149px;height: 60px;object-fit:contain;">
    <span style="font-size: 24px; animation: bounce 1s infinite;display: inline-block;">📱</span>
    <h3>A new sign-in on {{ $device ?? 'Linux' }}</h3>
    <p>Hi {{ $username ?? 'User' }}👋,</p>
    <p>We noticed a new sign-in to your {{ $system_settings['app_name'] }} Account from a {{ $device ?? 'Linux' }} device on {{($currentDateTime ?? "") . " " . ($timeZone ?? "")}}. If this
        was you, please disregard this email. No further action is needed.</p>
    <p>If this wasn't you, please change your password in the {{ $system_settings['app_name'] }} <a
            href="{{ customUrl('password-recovery') }}" style=" text-decoration: underline;">Change Password</a>.</p>
    <p>Thanks,<br>Team {{ $system_settings['app_name'] }}</p>
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
