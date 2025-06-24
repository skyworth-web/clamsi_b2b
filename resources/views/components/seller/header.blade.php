<div class="header @@classList bg-white">
    <link rel="stylesheet" href="{{ asset('/assets/boxicons/css/boxicons.css') }}">

    <!-- navbar -->
    <!-- Navbar -->
    @php

        use App\Models\Seller;
        use App\Models\Store;
        use Illuminate\Support\Facades\Auth;
        use Illuminate\Support\Facades\DB;
        $language_code = get_language_code();
        $user = Auth::user();
        $isPublicDisk = $user->disk == 'public' ? 1 : 0;

        $seller_detail = fetchDetails('seller_store', ['user_id' => $user->id, 'status' => 1], 'store_id');

        $store_ids = [];
        foreach ($seller_detail as $row) {
            $store_ids[] = $row->store_id;
        }

        $store_details = DB::table('stores')->whereIn('id', $store_ids)->where('status', 1)->get();

        if (session('store_id') !== null && in_array(session('store_id'), $store_ids)) {
            // Use the existing session store ID if it's valid
    $store_id = session('store_id');
} else {
    // Use the first store in the list as default
    $store_id = $store_details->isEmpty() ? 0 : $store_details[0]->id;

    // Set session data for the default store
    if ($store_id !== 0) {
        session(['store_id' => $store_id]);
        session(['store_name' => $store_details[0]->name]);
        session(['store_image' => $store_details[0]->image]);
    } else {
        // Handle the case where no valid store is found
        // You might want to set default values or handle this situation differently
    }
}
$user_image = $isPublicDisk
    ? (!empty($user->image) && file_exists(public_path(config('constants.SELLER_IMG_PATH') . $user->image))
        ? getMediaImageUrl($user->image, 'SELLER_IMG_PATH')
        : getImageUrl('no-user-img.jpeg', '', '', 'image', 'NO_USER_IMAGE'))
    : $user->image;

// Retrieve session data for store name and image
$store_name = session('store_name', '');
$store_image = session('store_image', '');

// If store_image is set, generate the asset URL
if (!empty($store_image)) {
    $store_image = asset(config('constants.STORE_IMG_PATH') . $store_image);
        }

        use App\Models\Language;

        $languages = Language::all();
    @endphp
    <nav class="navbar navbar-main navbar-expand-lg px-0 shadow-none border-radius-xl" id="navbarBlur" data-scroll="false">
        <div id="app_url" data-app-url="{{ config('app.url') }}"></div>
        <div class="align-items-center d-flex px-6 py-1 w-100">
            <div class=" mt-2 navbar-collapse justify-content-end" id="navbar">

                <ul class="navbar-nav col">
                    <li class="nav-item dropdown pe-2 d-flex align-items-center dropdown-store">
                        <a id="nav-toggle" href="#"><i class='bx bx-bar-chart bx-rotate-90'></i></a>
                        <div class="module-dropdown-box">
                            <a class="nav-link  dropdown-toggle hide-arrow" href="javascript:void(0);"
                                data-bs-toggle="dropdown">
                                <img src="{{ $store_image }}" class="avatar rounded-circle avatar-sm" alt="">
                                <span
                                    class="ms-2 ">{{ $store_name ? getDynamicTranslation('stores', 'name', $store_id, $language_code) : getDynamicTranslation('stores', 'name', $store_details[0]->id, $language_code) }}</span>
                                <i class="fas fa-angle-down ms-2"></i>
                            </a>

                            <div class="moduleDropDown">
                                <ul class="dropdown-menu stores_dropdown" data-bs-popper="none"
                                    data-bs-placement="bottom-start" id="store-dropdown">
                                    <div class="row ms-2">
                                        <p class="col-12 text-bold">Select Store</p>
                                    </div>
                                    <div class="d-flex flex-row">
                                        @php

                                            foreach ($store_details as $store) {
                                                echo '<li  class="dropdown-item" data-store-id="' .
                                                    $store->id .
                                                    '" data-store-name="' .
                                                    $store->name .
                                                    '" data-store-image="' .
                                                    $store->image .
                                                    '">';
                                                echo '<div class="align-items-center">';
                                                echo '<div >';
                                                echo '<img src="' .
                                                    route('seller.dynamic_image', [
                                                        'url' => getMediaImageUrl($store->image, 'STORE_IMG_PATH'),
                                                        'width' => 110,
                                                        'quality' => 90,
                                                    ]) .
                                                    '" class="" alt="">';
                                                echo '</div>';
                                                echo '<span class="fw-semibold d-block p-2 text-center">' .
                                                    getDynamicTranslation(
                                                        'stores',
                                                        'name',
                                                        $store->id,
                                                        $language_code,
                                                    ) .
                                                    '</span>';
                                                echo '</div>';
                                                echo '</li>';
                                            }
                                        @endphp
                                    </div>
                                </ul>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
            @php
                $language_code = session()->get('locale');
                $selected_language = fetchdetails('languages', ['code' => $language_code], 'language');
                $selected_language =
                    isset($selected_language) && !empty($selected_language) ? $selected_language[0]->language : 'English';
            @endphp
            @if (!empty($selected_language))
                <label for=""class="badge bg-primary mx-3">{{ $selected_language }}</label>
            @endif

            <li class="nav-item dropdown  d-flex justify-content-center me-3 notifiationDropDown">
                <a href="javascript:;" class="nav-link p-0" id="languageDropdown" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="bx bx-globe"></i>
                </a>
                <ul class="dropdown-menu  dropdown-menu-end  px-2 py-3 me-sm-n4" aria-labelledby="languageDropdown">
                    @foreach ($languages as $language)
                        <li>
                            <a class="dropdown-item changeLang" data-lang-code="{{ $language->code }}">
                                {{ ucwords($language->language) }} - {{ strtoupper($language->code) }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </li>
            <div class="d-flex">
                <div id="profileDropDown" class="input-group-text">
                    <li class="nav-item dropdown pe-2 d-flex align-items-center">
                        <a href="javascript:;" class="nav-link text-white p-0 nav-link-text ms-1"
                            id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            <img class="avatar rounded-circle avatar-sm" src="{{ $store_image }}" alt="">
                            {{ $user->username }} <i class="fas fa-angle-down"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                            <li>
                                <a class="dropdown-item text-dark" href="/seller/account/{{ auth()->user()->id }}"><i
                                        class='bx bx-user-circle'></i>
                                    {{ labels('admin_labels.profile', 'Profile') }}</a>
                            </li>
                            <li>
                                <a class="dropdown-item text-dark" href="{{ route('seller.logout') }}"><i
                                        class='bx bx-log-in-circle'></i>{{ labels('admin_labels.logout', 'Logout') }}</a>
                            </li>
                        </ul>
                    </li>
                </div>
            </div>
        </div>
    </nav>

    @php
        $store_id = getStoreId();

        $store_details = fetchDetails(
            'stores',
            ['id' => $store_id],
            ['primary_color', 'secondary_color', 'hover_color', 'active_color'],
        );
        $primary_colour =
            isset($store_details[0]->primary_color) && !empty($store_details[0]->primary_color)
                ? $store_details[0]->primary_color
                : '#B52046';
        $secondary_color =
            isset($store_details[0]->secondary_color) && !empty($store_details[0]->secondary_color)
                ? $store_details[0]->secondary_color
                : '#201A1A';
        $hover_color =
            isset($store_details[0]->hover_color) && !empty($store_details[0]->hover_color)
                ? $store_details[0]->hover_color
                : '#911A38';
        $active_color =
            isset($store_details[0]->active_color) && !empty($store_details[0]->active_color)
                ? $store_details[0]->active_color
                : '#6D132A';
        $background_opacity_color = $primary_colour . '10';
    @endphp

    <style>
        * {
            /* --primary-theme-color: <?= $primary_colour ?>;
            --background_opacity_color: <?= $background_opacity_color ?>;
            --secondary-theme-color: <?= $secondary_color ?>;
            --hover-color: <?= $hover_color ?>;
            --active-color: <?= $active_color ?>; */

            --primary-theme-color: {{ $primary_colour }};
            --background_opacity_color: {{ $background_opacity_color }};
            --secondary-theme-color: {{ $secondary_color }};
            --hover-color: {{ $hover_color }};
            --active-color: {{ $active_color }};
        }
    </style>

    <!-- End Navbar -->
</div>
