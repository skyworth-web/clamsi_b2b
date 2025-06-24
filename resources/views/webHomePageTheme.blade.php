<!DOCTYPE html>
<html lang="en" class="overflow-hidden">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iframe Content</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<link rel="stylesheet" href="{{ asset('frontend/elegant/css/plugins.css') }}?v={{ $version }}">
<link rel="stylesheet" href="{{ asset('frontend/elegant/css/vendor/photoswipe.min.css') }}?v={{ $version }}">
<link rel="stylesheet" href="{{ asset('frontend/elegant/css/bootstrap-table.min.css') }}?v={{ $version }}">
<link rel="stylesheet" href="{{ asset('frontend/elegant/css/style.css') }}?v={{ $version }}">
<link rel="stylesheet" href="{{ asset('frontend/elegant/css/theme.min.css') }}?v={{ $version }}">
<link rel="stylesheet" href="{{ asset('frontend/elegant/css/star-rating.css') }}?v={{ $version }}">
<link rel="stylesheet" href="{{ asset('frontend/elegant/css/star-rating.min.css') }}?v={{ $version }}">
<link rel="stylesheet" href="{{ asset('frontend/elegant/css/intlTelInput.css') }}?v={{ $version }}">
<link rel="stylesheet" href="{{ asset('frontend/elegant/css/select2.min.css') }}?v={{ $version }}">
<link rel="stylesheet" href="{{ asset('frontend/elegant/css/iziToast.css') }}?v={{ $version }}">
<link rel="stylesheet" href="{{ asset('frontend/elegant/css/daterangepicker.css') }}?v={{ $version }}">
<link rel="stylesheet" href="{{ asset('frontend/elegant/css/responsive.css') }}?v={{ $version }}">
<link rel="stylesheet" href="{{ asset('frontend/elegant/css/shareon.min.css') }}?v={{ $version }}">
<link rel="stylesheet" href="{{ asset('frontend/elegant/css/app.css') }}?v={{ $version }}">
<link rel="stylesheet" href="{{ asset('frontend/elegant/css/swiper-bundle.min.css') }}?v={{ $version }}">

<body>
    <div id="web_home_page_theme_1" class="content-section">
        <iframe src="https://plus.eshopweb.store/?store=fashion-1" width="100%" height="500px" frameborder="0"></iframe>
    </div>

    <div id="web_home_page_theme_2" class="content-section d-none">
        <iframe src="https://plus.eshopweb.store/?store=prime-pantry" width="100%" height="500px" frameborder="0"></iframe>
    </div>
    <div id="web_home_page_theme_3" class="content-section d-none">
        <iframe src="https://plus.eshopweb.store/?store=luxeline-ecommerce" width="100%" height="500px" frameborder="0"></iframe>
    </div>
    <div id="web_home_page_theme_4" class="content-section d-none">
        <iframe src="https://plus.eshopweb.store/?store=new-pharmacy" width="100%" height="500px" frameborder="0"></iframe>
    </div>
    <div id="web_home_page_theme_5" class="content-section d-none">
        <iframe src="https://plus.eshopweb.store/?store=electronics" width="100%" height="500px" frameborder="0"></iframe>
    </div>


    <script>
        window.addEventListener("message", function(event) {
            var selectedStyle = event.data;
            // console.log("Received Style:", selectedStyle);

            // Log all section IDs to verify they exist
            document.querySelectorAll(".content-section").forEach(function(section) {
                // console.log("Available Section ID:", section.id);
            });

            // Hide all sections
            document.querySelectorAll(".content-section").forEach(function(section) {
                section.classList.add("d-none");
            });

            // Show the selected section
            var selectedElement = document.getElementById(selectedStyle);
            if (selectedElement) {
                selectedElement.classList.remove("d-none");
                // console.log("Showing:", selectedStyle);
            } else {
                console.error("Element not found:", selectedStyle);
            }
        });
    </script>

</body>
<script src="{{ asset('frontend/elegant/js/plugins.js') }}?v={{ $version }}" data - navigate - track="reload">
</script>
<script src="{{ asset('frontend/elegant/js/vendor/jquery.elevatezoom.js') }}?v={{ $version }}"
    data-navigate-track="reload"></script>
<script src="{{ asset('frontend/elegant/js/moment.min.js') }}?v={{ $version }}" data-navigate-track="reload">
</script>
<script src="{{ asset('frontend/elegant/js/sweetalert2.all.min.js') }}?v={{ $version }}"
    data-navigate-track="reload"></script>
<script src="{{ asset('frontend/elegant/js/swiper-bundle.min.js') }}?v={{ $version }}"
    data-navigate-track="reload"></script>
<script src="{{ asset('frontend/elegant/js/shareon.iife.js') }}?v={{ $version }}" data-navigate-track="reload">
</script>

<script type="module" src="{{ asset('frontend/elegant/js/firebase-app.js') }}?v={{ $version }}"
    data-navigate-track="reload"></script>
<script type="module" src="{{ asset('frontend/elegant/js/firebase-auth.js') }}?v={{ $version }}"
    data-navigate-track="reload"></script>
<script type="module" src="{{ asset('frontend/elegant/js/firebase-firestore.js') }}?v={{ $version }}"
    data-navigate-track="reload"></script>
<script type="module" src="{{ asset('frontend/elegant/js/bootstrap-table.min.js') }}?v={{ $version }}"
    data-navigate-track="reload"></script>
<script type="module" src="{{ asset('frontend/elegant/js/bootstrap-table-export.min.js') }}?v={{ $version }}"
    data-navigate-track="reload"></script>
<script type="module" src="{{ asset('frontend/elegant/js/main.js') }}?v={{ $version }}"
    data-navigate-track="reload"></script>
<script type="module" src="{{ asset('frontend/elegant/js/daterangepicker.js') }}?v={{ $version }}"
    data-navigate-track="reload"></script>
<script type="module" src="{{ asset('frontend/elegant/js/ionicons.js') }}?v={{ $version }}"
    data-navigate-track="reload"></script>
<script type="module" src="{{ asset('frontend/elegant/js/star-rating.js') }}?v={{ $version }}"
    data-navigate-track="reload"></script>
<script type="module" src="{{ asset('frontend/elegant/js/intlTelInput.js') }}?v={{ $version }}"
    data-navigate-track="reload"></script>
<script type="module" src="{{ asset('frontend/elegant/js/iziToast.min.js') }}?v={{ $version }}"
    data-navigate-track="reload"></script>
<script type="module" src="{{ asset('frontend/elegant/js/star-rating.min.js') }}?v={{ $version }}"
    data-navigate-track="reload"></script>
<script type="module" src="{{ asset('frontend/elegant/js/select2.min.js') }}?v={{ $version }}"
    data-navigate-track="reload"></script>

<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js?v={{ $version }}"
    data-navigate-track="reload" defer></script>

</script>

</html>
