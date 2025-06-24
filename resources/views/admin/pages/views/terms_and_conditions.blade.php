<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ getMediaImageUrl($setting['favicon']) }}">
    <title>Terms And Conditions</title>
</head>

<body>
    <h2>Terms And Conditions</h2>
    @if (!empty($terms_and_conditions['terms_and_conditions']))
        {!! $terms_and_conditions['terms_and_conditions'] !!}
    @elseif (!empty($terms_and_conditions['seller_terms_and_conditions']))
        {!! $terms_and_conditions['seller_terms_and_conditions'] !!}
    @elseif (!empty($terms_and_conditions['delivery_boy_terms_and_conditions']))
        {!! $terms_and_conditions['delivery_boy_terms_and_conditions'] !!}
    @endif

</body>

</html>
