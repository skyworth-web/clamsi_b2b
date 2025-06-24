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
    @if (isset($delivery_boy_privacy_policy['delivery_boy_privacy_policy']) &&
            !empty($delivery_boy_privacy_policy['delivery_boy_privacy_policy']))
        {!! $delivery_boy_privacy_policy['delivery_boy_privacy_policy'] !!}
    @endif

</body>

</html>
