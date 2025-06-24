<div class="header @@classList bg-white">
    <link rel="stylesheet" href="{{ asset('/assets/boxicons/css/boxicons.css') }}">

    <!-- navbar -->
    <!-- Navbar -->
    @php

        use App\Models\Store;
        use Illuminate\Support\Facades\Auth;
        use Illuminate\Support\Str;

        $language_code = get_language_code();
        $user = Auth::user();

        $isPublicDisk = $user->disk == 'public' ? 1 : 0;
        $user_image = $isPublicDisk
            ? (!empty($user->image) && file_exists(public_path(config('constants.USER_IMG_PATH') . $user->image))
                ? getMediaImageUrl($user->image, 'USER_IMG_PATH')
                : getImageUrl('no-user-img.jpeg', '', '', 'image', 'NO_USER_IMAGE'))
            : $user->image;

        $store_details = fetchDetails('stores', ['status' => 1], '*');
        $store_details = $store_details;

        $default_store_id = '';
        $stores = Store::where('is_default_store', 1)->where('status', 1)->get();
        if ($stores->isNotEmpty()) {
            $default_store_id = $stores[0]->id;
            $default_store_name = getDynamicTranslation('stores', 'name', $stores[0]->id, $language_code);
            $isPublicDisk = $stores[0]->disk == 'public' ? 1 : 0;
            $default_store_image = $isPublicDisk
                ? asset(config('constants.STORE_IMG_PATH') . $stores[0]->image)
                : $stores[0]->image;
        } else {
            $default_store_id = '';
            $default_store_name = '';
            $default_store_image = '';
        }

        if (session('store_id') !== null && !empty(session('store_id'))) {
            $store_id = session('store_id');
        } else {
            $store_id = $default_store_id;
            session(['store_id' => $default_store_id]);
            session(['store_name' => $default_store_name]);
            session(['store_image' => $default_store_image]);
        }

        $store_name = session('store_name') !== null && !empty(session('store_name')) ? session('store_name') : '';
        // dd($store_name);
        if (!empty($stores) && isset($stores[0])) {
            $isPublicDisk = $stores[0]->disk == 'public' ? 1 : 0;
        } else {
            $isPublicDisk = 0;
        }

        $image = $isPublicDisk
            ? asset(config('constants.STORE_IMG_PATH') . session('store_image'))
            : session('store_image');

        $store_image = session('store_image') !== null && !empty(session('store_image')) ? $image : '';

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
                                <img src="{{ getMediaImageUrl($store_image) }}" alt=""
                                    class="avatar rounded-circle avatar-sm">
                                @if (isset($store_details) && !empty($store_details))
                                    <span
                                        class="ms-2 header_store_name">{{ getDynamicTranslation('stores', 'name', $store_id, $language_code) }}</span>
                                @else
                                    <span
                                        class="ms-2 header_store_name">{{ getDynamicTranslation('stores', 'name', $store_id, $language_code) }}</span>
                                @endif
                                <i class="fas fa-angle-down ms-2"></i>
                            </a>

                            <div class="moduleDropDown">
                                <ul class="dropdown-menu stores_dropdown" data-bs-popper="none"
                                    data-bs-placement="bottom-start" id="store-dropdown">
                                    <div class="row ms-2">
                                        <p class="col-12 text-bold">Select Store</p>
                                    </div>
                                    <div class="d-flex flex-row">
                                        @forelse($store_details as $store)
                                            <li class="dropdown-item" data-store-id="{{ $store->id }}"
                                                data-store-name="{{ getDynamicTranslation('stores', 'name', $store->id, $language_code) }}"
                                                data-store-image="{{ $store->image }}">
                                                <div class="align-items-center">
                                                    <div>
                                                        <img alt=""
                                                            src="{{ route('admin.dynamic_image', [
                                                                'url' => getMediaImageUrl($store->image, 'STORE_IMG_PATH'),
                                                                'width' => 110,
                                                                'quality' => 90,
                                                            ]) }}"
                                                            class="">
                                                    </div>
                                                    <span
                                                        class="fw-semibold d-block p-2 text-center">{{ Str::limit(getDynamicTranslation('stores', 'name', $store->id, $language_code), 10, '...') }}
                                                    </span>
                                                </div>
                                            </li>
                                        @empty
                                            <li class="dropdown-item disabled d-flex justify-content-center">
                                                <div class="col-6 text-center">No Stores found.</div>
                                            </li>
                                            <div class="d-flex justify-content-center">
                                                <i class='bx bxs-plus-circle'></i><a href="/admin/store"
                                                    target="_blank">add
                                                    a
                                                    store</a>
                                            </div>
                                        @endforelse
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
                <label for="" class="badge bg-primary mx-3">{{ $selected_language }}</label>
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
                <li class="nav-item dropdown  d-flex justify-content-center me-3 notifiationDropDown d-none">
                    <a href="javascript:;" class="nav-link text-white p-0" id="dropdownMenuButton"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class='bx bx-bell'></i>
                    </a>
                    <ul class="dropdown-menu  dropdown-menu-end  px-2 py-3 me-sm-n4"
                        aria-labelledby="dropdownMenuButton">
                        <li class="mb-2">
                            <a class="dropdown-item border-radius-md" href="javascript:;">
                                <div class="d-flex py-1">
                                    <div class="my-auto">
                                        <img src="" class="avatar avatar-sm  me-3 ">
                                    </div>
                                    <div class="d-flex flex-column justify-content-center">
                                        <h6 class="text-sm font-weight-normal mb-1">
                                            <span class="font-weight-bold">New message</span> from Laur
                                        </h6>
                                        <p class="text-xs text-secondary mb-0">
                                            <i class="fa fa-clock me-1"></i>
                                            13 minutes ago
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </li>
                    </ul>
                </li>
                <div id="profileDropDown" class="input-group-text">
                    <li class="nav-item dropdown pe-2 d-flex align-items-center">
                        <a href="javascript:;" class="nav-link text-white p-0 nav-link-text ms-1"
                            id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            <img class="avatar rounded-circle avatar-sm" src="{{ getMediaImageUrl($user_image) }}">
                            {{ $user->username }}
                            <i class="fas fa-angle-down"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                            <li>
                                <a class="dropdown-item text-dark" href="/admin/account/{{ auth()->user()->id }}"><i
                                        class='bx bx-user-circle'></i>
                                    {{ labels('admin_labels.profile', 'Profile') }}</a>
                            </li>
                            <li>
                                <a class="dropdown-item text-dark" href="{{ route('admin.logout') }}"><i
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
            --primary-theme-color: <?=$primary_colour ?>;
            --background_opacity_color: <?=$background_opacity_color ?>;
            --secondary-theme-color: <?=$secondary_color ?>;
            --hover-color: <?=$hover_color ?>;
            --active-color: <?=$active_color ?>;
        }
    </style>

    <!-- End Navbar -->
</div>
