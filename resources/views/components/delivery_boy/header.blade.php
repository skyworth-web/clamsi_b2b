<div class="header @@classList bg-white">
    <link rel="stylesheet" href="{{ asset('/assets/boxicons/css/boxicons.css') }}">

    <!-- navbar -->
    <!-- Navbar -->
    @php

        use App\Models\Store;
        use Illuminate\Support\Facades\Auth;

        $user = Auth::user();
        $user_image =
            !empty($user->image) && file_exists(public_path(config('constants.USER_IMG_PATH') . $user->image))
                ? getMediaImageUrl($user->image, 'USER_IMG_PATH')
                : getImageUrl('no-user-img.jpeg', '', '', 'image', 'NO_USER_IMAGE');
        use App\Models\Language;
        $languages = Language::all();

        $stores = Store::where('is_default_store', 1)->where('status', 1)->get();
    @endphp


    <nav class="navbar navbar-main navbar-expand-lg px-0 shadow-none border-radius-xl" id="navbarBlur" data-scroll="false">
        <input type="hidden" id="app_url" data-app-url="{{ config('app.url') }}" />
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-4 d-flex justify-content-start">
                    <a id="nav-toggle" class="mx-2" href="#"><i class='bx bx-bar-chart bx-rotate-90'></i></a>
                </div>
                <div class="d-flex col-md-8 justify-content-end align-items-center">
                    @php
                        $language_code = session()->get('locale');
                        $selected_language = fetchdetails('languages', ['code' => $language_code], 'language');
                        $selected_language =
                            isset($selected_language) && !empty($selected_language)
                                ? $selected_language[0]->language
                                : 'English';
                    @endphp
                    @if (!empty($selected_language))
                        <label for=""class="badge bg-primary mx-3">{{ $selected_language }}</label>
                    @endif

                    <li class="nav-item dropdown  d-flex justify-content-center me-3 notifiationDropDown">
                        <a href="javascript:;" class="nav-link p-0" id="languageDropdown" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="bx bx-globe"></i>
                        </a>
                        <ul class="dropdown-menu  dropdown-menu-end  px-2 py-3 me-sm-n4"
                            aria-labelledby="languageDropdown">
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

                        <div id="delivery_boy_profile" class="input-group-text">
                            <li class="nav-item dropdown pe-2 d-flex align-items-center">
                                <a href="javascript:;" class="nav-link text-white p-0 nav-link-text ms-1"
                                    id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                    <img class="avatar rounded-circle avatar-sm" src="{{ $user_image }}">
                                    {{ $user->username }}
                                    <i class="fas fa-angle-down"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                                    <li>
                                        <a class="dropdown-item text-dark"
                                            href="/delivery_boy/account/{{ auth()->user()->id }}"><i
                                                class='bx bx-user-circle'></i>
                                            {{ labels('admin_labels.profile', 'Profile') }}</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-dark" href="{{ route('delivery_boy.logout') }}"><i
                                                class='bx bx-log-in-circle'></i>{{ labels('admin_labels.logout', 'Logout') }}</a>
                                    </li>
                                </ul>
                            </li>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>




    @php

        $primary_colour =
            isset($stores[0]->primary_color) && !empty($stores[0]->primary_color)
                ? $stores[0]->primary_color
                : '#B52046';
        $background_opacity_color = $primary_colour . '10';
        $secondary_color =
            isset($stores[0]->secondary_color) && !empty($stores[0]->secondary_color)
                ? $stores[0]->secondary_color
                : '#201A1A';
        $hover_color =
            isset($stores[0]->hover_color) && !empty($stores[0]->hover_color) ? $stores[0]->hover_color : '#911A38';
        $active_color =
            isset($stores[0]->active_color) && !empty($stores[0]->active_color) ? $stores[0]->active_color : '#6D132A';

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
