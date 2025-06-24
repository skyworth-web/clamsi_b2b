<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ getMediaImageUrl($setting['favicon']) }}">
    <title>Terms & Conditions</title>
</head>

<body>
    <h2>Terms & Conditions</h2>
    @if (isset($delivery_boy_terms_and_conditions['delivery_boy_terms_and_conditions']) &&
            !empty($delivery_boy_terms_and_conditions['delivery_boy_terms_and_conditions']))
        {!! $delivery_boy_terms_and_conditions['delivery_boy_terms_and_conditions'] !!}
    @endif

</body>

</html>
