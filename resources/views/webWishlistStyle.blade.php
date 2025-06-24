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
    <div id="wishlist_display_style_for_web_1" class="content-section">
        <div class="tab-container">
            <div id="regular-wishlist" class="tab-content" wire:ignore.self="" style="display: block;">
                <table class="table align-middle text-center order-table">
                    <thead>
                        <tr class="table-head text-nowrap">
                            <th scope="col"></th>
                            <th scope="col">
                                Image
                            </th>
                            <th scope="col">
                                Product Details
                            </th>
                            <th scope="col">
                                Price
                            </th>
                            <th scope="col">
                                Rating
                            </th>
                            <th scope="col">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <button class="btn-close remove-favorite" data-product-id="816"></button>
                            </td>
                            <td>
                                <img class="blur-up lazyloaded"
                                    data-src="https://eshop-pro-dev.eshopweb.store/media/image?url=https%3A%2F%2Feshop-pro-dev.eshopweb.store%2Fstorage%2F%2F%2Fmedia%2Ffreepik__the-style-is-candid-image-photography-with-natural__84060-1737015057_4385.jpg&amp;width=50&amp;quality=90"
                                    src="https://eshop-pro-dev.eshopweb.store/media/image?url=https%3A%2F%2Feshop-pro-dev.eshopweb.store%2Fstorage%2F%2F%2Fmedia%2Ffreepik__the-style-is-candid-image-photography-with-natural__84060-1737015057_4385.jpg&amp;width=50&amp;quality=90"
                                    alt="Kidney Beans" title="Kidney Beans">
                            </td>
                            <td><span class="name">Kidney Beans</span>
                            </td>
                            <td>

                                <span class="price fw-500"> $20.00 -
                                    $10.00
                                </span>
                            </td>


                            <td><span class="id">★0</span>
                            </td>
                            <td> <a href="#quickview-modal" data-bs-toggle="modal" data-bs-target="#quickview_modal"
                                    class="btn btn-md text-nowrap add_cart  quickview quick-view-modal"
                                    data-product-id="816" data-product-variant-id="">Add to Cart</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="wishlist_display_style_for_web_2" class="content-section d-none">
        <div id="regular-wishlist" class="tab-content" wire:ignore.self="" style="display: block;">
            <div class="grid-products grid-view-items mt-4">
                <div class="col-row row row-cols-2 row-cols-lg-4 row-cols-md-3 row-cols-xl-5">
                    <div class="item col-item">
                        <div class="product-box position-relative">
                            <button type="button" class="btn remove-icon close-btn remove-favorite"
                                data-bs-toggle="tooltip" data-bs-placement="top" title=""
                                data-bs-original-title="Remove" aria-label="Remove" data-product-id="816"><ion-icon
                                    name="close-outline" role="img" class="md hydrated"></ion-icon></button>
                            <div class="product-image">
                                <a wire:navigate=""
                                    href="https://eshop-pro-dev.eshopweb.store/products/kidney-beans?store=prime-pantry"
                                    class="all-product-img product-img rounded-3 slider-link"
                                    data-link="https://eshop-pro-dev.eshopweb.store/products/kidney-beans?store=prime-pantry">
                                    <img class="blur-up lazyloaded"
                                        src="https://eshop-pro-dev.eshopweb.store/media/image?url=https%3A%2F%2Feshop-pro-dev.eshopweb.store%2Fstorage%2F%2F%2Fmedia%2Ffreepik__the-style-is-candid-image-photography-with-natural__84060-1737015057_4385.jpg&amp;width=800&amp;quality=90"
                                        alt="Product" title="Kidney Beans" width="625" height="808">
                                </a>
                            </div>
                            <div class="product-details text-center">
                                <div class="product-name text-ellipsis">
                                    <a wire:navigate=""
                                        href="https://eshop-pro-dev.eshopweb.store/products/kidney-beans?store=prime-pantry">Kidney
                                        Beans</a>
                                </div>
                                <div class="product-price">
                                    <span class="price old-price"></span>
                                    <span class="price fw-500"><span wire:model="price">$100.00</span></span>
                                </div>
                                <div class="product-review">
                                    <div class="product-review">
                                        <div
                                            class="rating-container theme-krajee-svg rating-xs rating-animate rating-disabled">
                                            <div class="rating-stars" tabindex="0"><span class="empty-stars"><span
                                                        class="star" title="One Star"><span
                                                            class="krajee-icon krajee-icon-star"></span></span><span
                                                        class="star" title="Two Stars"><span
                                                            class="krajee-icon krajee-icon-star"></span></span><span
                                                        class="star" title="Three Stars"><span
                                                            class="krajee-icon krajee-icon-star"></span></span><span
                                                        class="star" title="Four Stars"><span
                                                            class="krajee-icon krajee-icon-star"></span></span><span
                                                        class="star" title="Five Stars"><span
                                                            class="krajee-icon krajee-icon-star"></span></span></span><span
                                                    class="filled-stars" style="width: 0%;"><span class="star"
                                                        title="One Star"><span
                                                            class="krajee-icon krajee-icon-star"></span></span><span
                                                        class="star" title="Two Stars"><span
                                                            class="krajee-icon krajee-icon-star"></span></span><span
                                                        class="star" title="Three Stars"><span
                                                            class="krajee-icon krajee-icon-star"></span></span><span
                                                        class="star" title="Four Stars"><span
                                                            class="krajee-icon krajee-icon-star"></span></span><span
                                                        class="star" title="Five Stars"><span
                                                            class="krajee-icon krajee-icon-star"></span></span></span>
                                            </div>
                                        </div><input id="input-3-ltr-star-md" name="input-3-ltr-star-md"
                                            class="kv-ltr-theme-svg-star d-none rating-input" value="0"
                                            dir="ltr" data-size="xs" data-show-clear="false"
                                            data-show-caption="false" readonly="readonly" tabindex="-1">
                                    </div>
                                </div>
                                <div class="button-action mt-3">
                                    <div class="addtocart-btn">
                                        <a
                                            class="btn btn-md text-nowrap add_cart">{{ labels('front_messages.add_to_cart', 'Add to Cart') }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
