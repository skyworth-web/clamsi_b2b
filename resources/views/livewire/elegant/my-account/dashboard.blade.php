<div>
    @php
        $bread_crumb['page_main_bread_crumb'] = labels('front_messages.dashboard', 'Dashboard');
    @endphp
    <div id="page-content">
        <x-utility.breadcrumbs.breadcrumbTwo :$bread_crumb />


        <!--Main Content-->
        <div class="container-fluid">
            <div class="row">
                <x-utility.my_account_slider.account_slider :$user_info />
                <div class="col-12 col-sm-12 col-md-12 col-lg-9">
                    <div class="dashboard-content tab-content h-100" id="top-tabContent">
                        <div class="h-100" id="info">
                            <div class="account-info h-100">
                                <div class="welcome-msg mb-4">
                                    <h2> {{ labels('front_messages.hello', 'Hello') }}, <span
                                            class="text-primary">{{ $user_info->username }}</span></h2>
                                            <p>{{ labels('front_messages.my_account_dashboard', 'From your My Account Dashboard') }} {{ labels('front_messages.view_snapshot', 'you have the ability to view a snapshot of your recent account activity') }} {{ labels('front_messages.update_account_info', 'and update your account information') }}. {{ labels('front_messages.select_link', 'Select a link below to view or edit information') }}.</p>
                                </div>

                                <div class="account-box">
                                    <h3 class="mb-3">
                                        {{ labels('front_messages.account_information', 'Account Information') }}</h3>
                                    <div class="box-info mb-4">
                                        <div class="box-title d-flex-center">
                                            <h4>{{ labels('front_messages.contact_information', 'Contact Information') }}
                                            </h4> <a href="/my-account/profile" wire:navigate
                                                class="btn-link ms-auto">{{ labels('front_messages.edit', 'Edit') }}</a>
                                        </div>
                                        <div class="row row-cols-lg-2 row-cols-md-2 row-cols-sm-1 row-cols-1">
                                            <div class="box-content mt-3">
                                                <h5 class="mb-2">{{ $user_info->username }}</h5>
                                                <p class="mb-2">{{ $user_info->email }}</p>
                                                <p><a href="/my-account/profile" wire:navigate
                                                        class="btn-link">{{ labels('front_messages.change_password', 'Change Password') }}</a>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="box-info mb-4">
                                        <div class="box-title d-flex-center">
                                            <h4>{{ labels('front_messages.address_book', 'Address Book') }}</h4>
                                            <a href="/my-account/addresses" wire:navigate
                                                class="btn-link ms-auto">{{ count($default_address) >= 1 ? labels('front_messages.edit', 'Edit') : labels('front_messages.add', 'Add') }}</a>
                                        </div>
                                        @if (count($default_address) >= 1)
                                            <div class="row row-cols-lg-2 row-cols-md-2 row-cols-sm-1 row-cols-1">
                                                <div class="box-content mt-3">
                                                    <h5>{{ labels('front_messages.default_billing_address', 'Default Billing Address') }}
                                                    </h5>
                                                    <address class="mb-2">
                                                        <b>{{ $default_address[0]->name }}</b><br />
                                                        {{ $default_address[0]->address }},<br />
                                                        {{ $default_address[0]->landmark }} <br />
                                                        {{ $default_address[0]->city }},
                                                        {{ $default_address[0]->state }}
                                                        <br />
                                                        {{ $default_address[0]->pincode }}.
                                                    </address>
                                                </div>
                                            </div>
                                        @else
                                            <div class="d-flex justify-content-center align-items-center">
                                                <p class="my-2">
                                                    {{ labels('front_messages.no_default_address_selected', 'No Default Address Selected') }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--End Main Content-->
    </div>
    <!-- End Body Container -->
</div>
