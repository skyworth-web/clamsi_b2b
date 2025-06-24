<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ getMediaImageUrl($setting['favicon']) }}">
    <title>Privacy Policy</title>
</head>

<body>
    <h2>Privacy Policy</h2>
    @if (!empty($privacy_policy['privacy_policy']))
        {!! $privacy_policy['privacy_policy'] !!}
    @elseif (!empty($privacy_policy['seller_privacy_policy']))
        {!! $privacy_policy['seller_privacy_policy'] !!}
    @elseif (!empty($privacy_policy['delivery_boy_privacy_policy']))
        {!! $privacy_policy['delivery_boy_privacy_policy'] !!}
    @endif

</body>

</html>
