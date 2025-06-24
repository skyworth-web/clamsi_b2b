@php
    $bread_crumb['page_main_bread_crumb'] = labels('front_messages.wallet', 'Wallet');
@endphp
<div id="page-content" wire:ignore>
    <x-utility.breadcrumbs.breadcrumbTwo :$bread_crumb />
    <div class="container-fluid">
        <div class="row">
            {{-- @dd($user_info) --}}
            <x-utility.my_account_slider.account_slider :$user_info />
            <div class="col-12 col-sm-12 col-md-12 col-lg-9">
                <div class="dashboard-content">
                    <h4>{{ labels('front_messages.amount_in_currency', 'Amount will be in') }} {{ $currency_code }}
                    </h4>
                    <!-- Profile -->
                    <div class="h-100">
                        <div class="wallet">
                            <p class="wallet-price">{{ $currency_symbol }} <span
                                    id="wallet_balance">{{ $user_info['balance'] }}</span></p>
                            <p class="wallet-name">{{ $user_info['username'] }}</p>
                            <p class="wallet-text">{{ labels('front_messages.wallet', 'Wallet') }}</p>
                        </div>
                        <div class="d-flex align-item-center justify-content-start mt-2 gap-2">
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#add_wallet_modal">{{ labels('front_messages.add', 'Add') }}</button>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#withdrawal_modal">{{ labels('front_messages.withdraw', 'Withdraw') }}</button>
                        </div>
                    </div>
                    <!-- End Profile -->
                </div>
                <input type="hidden" name="mobile" id="mobile" value="{{ $user_info['mobile'] }}">
                <input type="hidden" name="app_name" id="app_name" value="{{ $web_settings['site_title'] }}">
                <input type="hidden" name="logo" id="logo" value="{{ getImageUrl($web_settings['logo']) }}">
                <div wire:ignore.self class="modal fade" id="add_wallet_modal" tabindex="-1"
                    aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="exampleModalLabel">
                                    {{ labels('front_messages.wallet_refill', 'Wallet Refill') }}</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <form action="{{ Route('wallet.refill') }}" method="post" id="wallet_refill_form">
                                <input type="hidden" name="razorpay_signature" id="razorpay_signature">
                                <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
                                <input type="hidden" name="transaction_id" id="transaction_id">
                                <input type="hidden" name="user-email" id="user-email"
                                    value="{{ $user_info['email'] ?? '' }}" />
                                <input type="hidden" name="username" id="username"
                                    value="{{ $user_info['username'] ?? '' }}" />
                                <input type="hidden" name="paystack_reference" id="paystack_reference"
                                    value="" />
                                <div class="modal-body px-2 pt-2">
                                    @csrf
                                    <label for="add_amount"
                                        class="fw-500 fs-6">{{ labels('front_messages.add_amount', 'Add Amount') }}</label>
                                    <input type="number" name="add_amount" id="add_amount" min=1
                                        placeholder="Write The Amount You Want to Add">
                                    <div class="payment-accordion mt-3">
                                        @if ($payment_method->phonepe_method == 1)
                                            <div class="form-check mb-2 d-flex align-items-center">
                                                <input class="form-check-input" type="radio" name="payment_method"
                                                    id="phonepe" value="phonepe">
                                                <label class="form-check-label d-flex align-items-center ps-2"
                                                    for="phonepe" value="phonepe">
                                                    <div class="image payment-image">
                                                        <img class="blur-up lazyload"
                                                            data-src="{{ asset('frontend/elegant/images/logo/PhonePe_Logo.png') }}"
                                                            src="{{ asset('frontend/elegant/images/logo/PhonePe_Logo.png') }}"
                                                            alt="PhonePe" />
                                                    </div>
                                                </label>
                                            </div>
                                        @endif
                                        @if ($payment_method->paypal_method == 1)
                                            <div class="form-check mb-2 d-flex align-items-center">
                                                <input class="form-check-input" type="radio" name="payment_method"
                                                    id="paypal-payment" value="paypal">
                                                <label class="form-check-label d-flex align-items-center ps-2"
                                                    for="paypal-payment" value="paypal">
                                                    <div class="image payment-image">
                                                        <img class="blur-up lazyload"
                                                            data-src="{{ asset('frontend/elegant/images/logo/paypal-Logo.png') }}"
                                                            src="{{ asset('frontend/elegant/images/logo/paypal-Logo.png') }}"
                                                            alt="Paypal" />
                                                    </div>
                                                </label>
                                            </div>
                                        @endif
                                        @if ($payment_method->paystack_method == 1)
                                            <div class="form-check mb-2 d-flex align-items-center">
                                                <input class="form-check-input" type="radio" name="payment_method"
                                                    id="paystack-payment" value="paystack">
                                                <label class="form-check-label d-flex align-items-center ps-2"
                                                    for="paystack-payment" value="paystack">
                                                    <div class="image payment-image">
                                                        <img class="blur-up lazyload"
                                                            data-src="{{ asset('frontend/elegant/images/logo/Paystack_Logo.png') }}"
                                                            src="{{ asset('frontend/elegant/images/logo/Paystack_Logo.png') }}"
                                                            alt="paystack" />
                                                    </div>
                                                </label>
                                            </div>
                                            <input type="hidden" name="paystack_public_key" id="paystack_public_key"
                                                value="{{ $payment_method->paystack_key_id ?? '' }}" />
                                        @endif
                                        @if ($payment_method->stripe_method == 1)
                                            <div class="form-check mb-2 d-flex align-items-center">
                                                <input class="form-check-input" type="radio" name="payment_method"
                                                    id="stripe-payment" value="stripe">
                                                <label class="form-check-label d-flex align-items-center ps-2"
                                                    for="stripe-payment" value="stripe" title="Stripe">
                                                    <div class="image payment-image">
                                                        <img class="blur-up lazyload"
                                                            data-src="{{ asset('frontend/elegant/images/logo/stripe_logo.png') }}"
                                                            src="{{ asset('frontend/elegant/images/logo/stripe_logo.png') }}"
                                                            alt="Stripe" />
                                                    </div>
                                                </label>
                                            </div>
                                        @endif
                                        @if ($payment_method->razorpay_method == 1)
                                            <div class="form-check mb-2 d-flex align-items-center">
                                                <input class="form-check-input" type="radio" name="payment_method"
                                                    id="razorpay-payment" value="razorpay">
                                                <label class="form-check-label d-flex align-items-center ps-2"
                                                    for="razorpay-payment" value="razorpay" title="razorpay">
                                                    <div class="image payment-image">
                                                        <img class="blur-up lazyload"
                                                            data-src="{{ asset('frontend/elegant/images/logo/razorpay_logo.png') }}"
                                                            src="{{ asset('frontend/elegant/images/logo/razorpay_logo.png') }}"
                                                            alt="razorpay" />
                                                    </div>
                                                </label>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">{{ labels('front_messages.close', 'Close') }}</button>
                                    <button type="submit"
                                        class="btn btn-primary">{{ labels('front_messages.add', 'Add') }}</button>
                                </div>
                                <div id="paypal-button-container" class="m-3 d-none"></div>
                                <div id="stripe-checkout"></div>
                            </form>
                        </div>
                    </div>
                </div>
                <div wire:ignore.self class="modal fade" id="withdrawal_modal" tabindex="-1"
                    aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="exampleModalLabel">
                                    {{ labels('front_messages.withdrawal', 'Withdrawal') }}</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <form action="{{ Route('wallet.withdrawal') }}" method="post" id="withdrawal_form">
                                <input type="hidden" name="balance" id="balance"
                                    value="{{ $user_info['balance'] }}">
                                <div class="modal-body px-2 pt-2">
                                    @csrf
                                    <label for="withdrawal_amount"
                                        class="fw-500 fs-6">{{ labels('front_messages.withdrawal_amount', 'Withdrawal Amount') }}</label>
                                    <input type="number" name="withdrawal_amount" id="withdrawal_amount"
                                        placeholder="Write The Amount You Want to Withdraw"
                                        max="{{ $user_info['balance'] }}">
                                    <label for="withdrawal_amount"
                                        class="fw-500 fs-6">{{ labels('front_messages.payment_details', 'Payment details') }}</label>
                                    <textarea name="payment_address" id="payment_address" cols="30" rows="4" placeholder="Account Details"></textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">{{ labels('front_messages.close', 'Close') }}</button>
                                    <button type="submit"
                                        class="btn btn-primary">{{ labels('front_messages.withdraw', 'Withdraw') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="tabs-listing section">
                    <ul class="product-tabs style2 list-unstyled d-flex-wrap d-none d-md-flex" wire:ignore>
                        <li rel="Wallet-tab" class="active"><a
                                class="tablink">{{ labels('front_messages.wallet_transaction', 'Wallet Transaction') }}</a>
                        </li>
                        <li rel="Withdraw-tab"><a
                                class="tablink">{{ labels('front_messages.withdraw_request', 'Withdraw Request') }}</a>
                        </li>
                    </ul>

                    <div class="tab-container">
                        <h3 class="tabs-ac-style rounded-5 d-md-none active" rel="Wallet-tab">
                            {{ labels('front_messages.wallet_transaction', 'Wallet Transaction') }}</h3>
                        <div id="Wallet-tab" class="tab-content" wire:ignore.self>
                            <div class="table-responsive">
                                <table class='table' id="user_wallet_transactions" data-toggle="table"
                                    data-loading-template="loadingTemplate"
                                    data-url="{{ route('my-account.user_wallet_transactions') }}"
                                    data-click-to-select="true" data-side-pagination="server" data-pagination="true"
                                    data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                                    data-show-columns="true" data-show-refresh="true" data-trim-on-search="false"
                                    data-search-highlight="true" data-sort-name="id" data-sort-order="desc"
                                    data-mobile-responsive="true" data-toolbar="" data-show-export="false"
                                    data-maintain-selected="true" data-export-types='["txt","excel","csv"]'
                                    data-query-params="user_wallet_credit_params">
                                    <thead>
                                        <tr>
                                            <th data-field="id" data-sortable="true">
                                                {{ labels('front_messages.id', 'ID') }}
                                            </th>
                                            <th data-field="order_id" data-sortable="false">
                                                {{ labels('front_messages.order_id', 'Order ID') }}
                                            </th>
                                            <th data-field="txn_id" data-sortable="false">
                                                {{ labels('front_messages.transaction_id', 'Transaction ID') }}
                                            </th>
                                            <th data-field="type" data-sortable="false">
                                                {{ labels('front_messages.transaction_type', 'Transaction Type') }}
                                            </th>
                                            <th class='d-none' data-field="payu_txn_id" data-sortable="false"
                                                data-visible="false">
                                                {{ labels('front_messages.pay_transaction_id', 'Pay Transaction ID') }}
                                            </th>
                                            <th data-field="amount" data-sortable="false">
                                                {{ labels('front_messages.amount', 'Amount') }}
                                            </th>
                                            <th data-field="status" data-sortable="false">
                                                {{ labels('front_messages.status', 'Status') }}
                                            </th>
                                            <th data-field="message" data-sortable="false" data-visible="true">
                                                {{ labels('front_messages.message', 'Message') }}
                                            </th>
                                            <th data-field="created_at" data-sortable="true">
                                                {{ labels('front_messages.date', 'Date') }}
                                            </th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <h3 class="tabs-ac-style d-md-none" rel="Withdraw-tab">
                            {{ labels('front_messages.withdraw_request', 'Withdraw Request') }}</h3>
                        <div id="Withdraw-tab" class="tab-content" wire:ignore.self>
                            <div class="table-responsive">
                                <table class='table' id="wallet_withdrawal_request" data-toggle="table"
                                    data-loading-template="loadingTemplate"
                                    data-url="{{ route('my-account.wallet_withdrawal_request') }}"
                                    data-click-to-select="true" data-side-pagination="server" data-pagination="true"
                                    data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                                    data-show-columns="true" data-show-refresh="true" data-trim-on-search="false"
                                    data-search-highlight="true" data-sort-name="id" data-sort-order="desc"
                                    data-mobile-responsive="true" data-toolbar="" data-show-export="false"
                                    data-maintain-selected="true" data-export-types='["txt","excel","csv"]'
                                    data-query-params="wallet_withdrawal_request">
                                    <thead>
                                        <tr>
                                            <th data-field="id" data-sortable="true">
                                                {{ labels('front_messages.id', 'ID') }}
                                            </th>
                                            <th data-field="payment_address" data-sortable="false">
                                                {{ labels('front_messages.payment_address', 'Payment Address') }}
                                            </th>
                                            <th data-field="amount_requested" data-sortable="false">
                                                {{ labels('front_messages.amount_requested', 'Amount Requested') }}
                                            </th>
                                            <th data-field="remarks" data-sortable="false" data-visible="false">
                                                {{ labels('front_messages.remarks', 'Remarks') }}
                                            </th>
                                            <th data-field="status" data-sortable="false">
                                                {{ labels('front_messages.status', 'Status') }}
                                            </th>
                                            <th data-field="date_created" data-sortable="false" data-visible="false">
                                                {{ labels('front_messages.message', 'Date Created') }}
                                            </th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@if ($payment_method->razorpay_method == 1)
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
@endif
@if ($payment_method->paypal_method == 1)
    <script
        src="https://www.paypal.com/sdk/js?client-id={{ $payment_method->paypal_client_id }}&currency={{ $payment_method->currency_code }}">
    </script>
@endif
@if ($payment_method->paystack_method == 1)
    <script src="https://js.paystack.co/v1/inline.js"></script>
@endif
@if ($payment_method->stripe_method == 1)
    <script src="https://js.stripe.com/v3/"></script>
@endif
<script>
    function user_wallet_credit_params(p) {
        return {
            limit: p.limit,
            sort: p.sort,
            order: p.order,
            offset: p.offset,
            search: p.search
        }
    }

    function wallet_withdrawal_request(p) {
        return {
            limit: p.limit,
            sort: p.sort,
            order: p.order,
            offset: p.offset,
            search: p.search
        }
    }
</script>
