<div>
    <x-utility.breadcrumbs.breadcrumbOne :$breadcrumb />
    <div class="container-fluid">
        @if ($payment_response == 'order_success')
            <div class="success-text checkout-card text-center mb-4 mb-md-5">
                <i class="icon anm 
                anm-shield-check-r"></i>
                <h2 class="mb-0">{{ labels('front_messages.order_processsed_successfully', 'Order Processsed Successfully') }}</h2>
                <p class="m-1 fs-6 fw-400">{{ labels('front_messages.thank_you_for_shopping', 'Thank you for Shopping with Us') }}.</p>
                <div class="d-flex justify-content-center align-item-center">
                    <a wire:navigate href="{{ customUrl('products') }}" class="btn btn-primary">{{ labels('front_messages.continue_shopping', 'Continue Shopping') }}</a>
                </div>
            </div>
        @elseif ($payment_response == 'order_failed')
            <div class="success-text checkout-card text-center mb-4 mb-md-5">
                <i class="icon anm anm-exclamation-tr text-danger"></i>
                <h2>{{ labels('front_messages.failed_to_processed_order', 'Oops... Failed to Processed Order!') }}</h2>
            </div>
        @elseif ($payment_response == 'wallet_success')
            <div class="success-text checkout-card text-center mb-4 mb-md-5">
                <i class="icon anm 
            anm-shield-check-r"></i>
                <h2>{{ labels('front_messages.wallet_refill_successfully', 'Wallet Refill Successfully') }}</h2>
                <div class="d-flex justify-content-center align-item-center">
                    <a wire:navigate href="{{ customUrl('my-account/wallet') }}" class="btn btn-primary">{{ labels('front_messages.back_to_wallet', 'Back To Wallet') }}</a>
                </div>
            </div>
        @else
            <div class="success-text checkout-card text-center mb-4 mb-md-5">
                <i class="icon anm anm-exclamation-tr text-danger"></i>
                <h2>{{ labels('front_messages.payment_failed', 'Oops... Payment Failed!') }}</h2>
            </div>
        @endif
    </div>
</div>
