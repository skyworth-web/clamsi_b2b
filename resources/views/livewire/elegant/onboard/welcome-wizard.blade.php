<div>
    <!-- Onboarding Screen -->
    <div class="container {{ $showWizard ? 'hidden' : '' }}">
        <img src="{{ asset('storage/onboard/onboard_image.png') }}" alt="Onboarding Image" class="onboard-image">
        <div class="overlay-text">
            <div class="logo">Clamsi</div>
            <div class="main-text">Private B2B Fashion Platform</div>
            <div class="sub-text">Fashion Access, By Invitation Only</div>
            <button wire:click="startWizard" class="btn-primary">Get Started</button>
            <button wire:click="startWizardLogin" class="btn-primary" style="margin-top: 15px; min-width: 120px;">Login</button>
        </div>
    </div>

    <!-- Wizard Screen -->
    <div class="container {{ $showWizard ? '' : 'hidden' }}">
        <div class="wizard-background">
            @if($errorMessage)
                <div class="alert alert-danger">{{ $errorMessage }}</div>
            @endif

            <!-- Step 0: Supplier/Buyer Selection -->
            @if($step == 0)
                <div class="wizard-options">
                    <button wire:click="closeWizard" class="modal-close">×</button>
                    <a href="#" wire:click="startSupplierRegistration" class="wizard-card supplier-card">
                        <img src="{{ asset('storage/onboard/for_supplies.png') }}" alt="For Suppliers" class="card-image">
                        <div class="card-text">For Suppliers</div>
                    </a>
                    <a href="#" wire:click="startBuyerRegistration" class="wizard-card buyer-card" onclick="openModal('buyer-modal')">
                        <img src="{{ asset('storage/onboard/for_customers.png') }}" alt="For Buyers" class="card-image">
                        <div class="card-text">For Buyers</div>
                    </a>
                </div>

            <!-- Supplier Registration Modal (Step 1) -->
            @elseif($step == 1)
                <div id="supplier-modal" class="modal-overlay" style="display: flex;">
                    <div class="modal-content">
                        <button wire:click="startWizard" class="modal-close">×</button>
                        <button wire:click="goBack" class="modal-back"><</button>
                        <div class="modal-header">
                            <h2>Register as a Supplier</h2>
                            <div class="modal-icons">
                                <img src="{{ asset('storage/onboard/for_supplies.png') }}" alt="Supplier Icons">
                            </div>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="business_name">Business Name</label>
                                <input id="business_name" type="text" wire:model="form.business_name" placeholder="Business Name">
                                @error('form.business_name') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label for="country_id">Country</label>
                                <select id="country_id" wire:model="form.country_id" class="select2 form-control">
                                    <option value="">Select a country</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}" {{ $form['country_id'] == $country->id ? 'selected' : '' }} data-iso2="{{ $country->iso2 }}">
                                            {{ $country->name }} ({{ $country->iso2 }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('form.country_id') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input id="email" type="email" wire:model="form.email" placeholder="Email">
                                @error('form.email') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label for="website">Website (optional)</label>
                                <input id="website" type="text" wire:model="form.website" placeholder="Website (optional)">
                                @error('form.website') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <button wire:click="submitStep1" class="submit-btn">Continue</button>
                        </div>
                    </div>
                </div>

            <!-- Supplier Registration Modal (Step 2) -->
            @elseif($step == 2)
                <div id="supplier-step2-modal" class="modal-overlay" style="display: flex;">
                    <div class="modal-content">
                        <button wire:click="startWizard" class="modal-close">×</button>
                        <button wire:click="goBack" class="modal-back"><</button>
                        <div class="modal-header">
                            <h2>Enter Your Mobile Number</h2>
                            <div class="step-indicator">Step 2</div>
                            <div class="modal-icons">
                                <img src="{{ asset('storage/onboard/for_supplies.png') }}" alt="Supplier Icons">
                            </div>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <div class="mobile-input-wrapper">
                                    <select wire:model="form.country_code" class="country-code select2 form-control" id="country_code_select">
                                        <option value="">Select country code</option>
                                        @foreach($countries as $country)
                                            <option value="{{ $country->phonecode }}" {{ $form['country_code'] == $country->phonecode ? 'selected' : '' }}>
                                                {{ $country->phonecode }} ({{ $country->name }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="tel" wire:model="form.mobile_number" placeholder="Mobile Number" class="mobile-number-input">
                                    @error('form.mobile_number') <span class="error">{{ $message }}</span> @enderror
                                </div>
                                @error('form.country_code') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            @if($otpVerified && $otpVerifiedMobile === ('+' . ltrim($form['country_code'], '+') . $form['mobile_number']))
                                <div class="alert alert-success">Mobile already verified!</div>
                                <button wire:click="nextAfterOtpSupplier" class="submit-btn">Next</button>
                            @else
                                <button wire:click="submitStep2" class="submit-btn">Send OTP</button>
                            @endif
                        </div>
                    </div>
                </div>

            <!-- Supplier Registration Modal (Step 3) - OTP Verification -->
            @elseif($step == 3)
                <div id="supplier-step3-modal" class="modal-overlay" style="display: flex;">
                    <div class="modal-content">
                        <button wire:click="startWizard" class="modal-close">×</button>
                        <button wire:click="goBack" class="modal-back"><</button>
                        <div class="modal-header">
                            <h2>Verify Your Mobile</h2>
                            <div class="step-indicator">Step 3</div>
                            <div class="modal-icons">
                                <img src="{{ asset('storage/onboard/for_supplies.png') }}" alt="Supplier Icons">
                            </div>
                        </div>
                        <div class="modal-body">
                            <p class="otp-message">We've sent a 4-digit code to your mobile. Please enter it below.</p>
                            @if($errorMessage)
                                <div class="error-message">{{ $errorMessage }}</div>
                            @endif
                            @if($otpVerified && $otpVerifiedMobile === ('+' . ltrim($form['country_code'], '+') . $form['mobile_number']))
                                <div class="alert alert-success">Mobile verified!</div>
                                <button wire:click="nextAfterOtpSupplier" class="submit-btn">Next</button>
                            @else
                                <div class="otp-inputs" x-data>
                                    @for ($i = 0; $i < 4; $i++)
                                        <input type="text" class="otp-digit"
                                               maxlength="1"
                                               wire:model.debounce.500ms="form.otp_digits.{{ $i }}"
                                               wire:keydown.enter.prevent="submitStep3"
                                               @keydown.tab.prevent="$wire.focusNext({{ $i }})"
                                               @paste="$wire.handlePaste($event, {{ $i }})"
                                               placeholder="0">
                                    @endfor
                                    @error('form.otp') <span class="error">{{ $message }}</span> @endif
                                </div>
                                <div class="d-flex justify-content-center align-content-center my-2">
                                    <div id="recaptcha-container"></div>
                                </div>
                                <button wire:click="submitStep3" class="submit-btn" id="verify_otp">Verify</button>
                                <div x-data="{ resendTimeout: @entangle('otpResendTimeout'), sentAt: @entangle('otpSentAt'), now: Date.now()/1000, timer: 0, interval: null }"
                                     x-init="
                                        if (sentAt) {
                                            timer = resendTimeout - (Math.floor(now) - Math.floor(new Date(sentAt).getTime()/1000));
                                            if (timer > 0) {
                                                interval = setInterval(() => {
                                                    timer--;
                                                    if (timer <= 0) clearInterval(interval);
                                                }, 1000);
                                            } else {
                                                timer = 0;
                                            }
                                        }
                                     "
                                     class="my-2">
                                    <button wire:click="submitStep2" class="resend-btn" :disabled="timer > 0">Resend OTP <span x-show="timer > 0">in <span x-text="timer"></span>s</span></button>
                                </div>
                                <div class="text-danger mt-2" x-show="$wire.otpTries >= 5">
                                    Too many attempts. Please resend OTP.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            <!-- Supplier Registration Modal (Step 4) - Select Preferences -->
            @elseif($step == 4)
                <div id="supplier-step4-modal" class="modal-overlay" style="display: flex;">
                    <div class="modal-content">
                        <button wire:click="startWizard" class="modal-close">×</button>
                        <button wire:click="goBack" class="modal-back"><</button>
                        <div class="modal-header">
                            <h2>Select Your Product Category</h2>
                            <div class="step-indicator">Step 4</div>
                        </div>
                        <div class="modal-body">
                            <form wire:submit.prevent="submitStep4">
                                <div class="preference-list">
                                    @foreach($categories as $category)
                                        <div class="preference-box {{ is_array($form['categories']) && in_array($category->id, $form['categories']) ? 'selected' : '' }}"
                                             wire:click="toggleCategory({{ $category->id }})"
                                             data-value="{{ $category->id }}">
                                            {{ $category->display_name }}
                                        </div>
                                    @endforeach
                                </div>
                                @error('form.categories') <span class="error">{{ $message }}</span> @enderror
                                <button type="submit" class="submit-btn">Continue</button>
                            </form>
                        </div>
                    </div>
                </div>

            <!-- Supplier Registration Modal (Step 5) - Additional Details -->
            @elseif($step == 5)
                <div id="supplier-step5-modal" class="modal-overlay" style="display: flex;">
                    <div class="modal-content">
                        <button wire:click="startWizard" class="modal-close">×</button>
                        <button wire:click="goBack" class="modal-back"><</button>
                        <div class="modal-header">
                            <h2>Additional Details</h2>
                            <div class="step-indicator">Step 5</div>
                        </div>
                        <div class="modal-body">
                            <!-- No summary panel, no country code/mobile number -->
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input id="name" type="text" wire:model="form.name" placeholder="Full Name">
                                @error('form.name') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label for="address">Address</label>
                                <textarea id="address" wire:model="form.address" placeholder="Address" rows="3"></textarea>
                                @error('form.address') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label for="zip_code">ZIP Code</label>
                                <input id="zip_code" type="text" wire:model="form.zip_code" placeholder="ZIP Code">
                                @error('form.zip_code') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label for="website">Website (optional)</label>
                                <input id="website" type="text" wire:model="form.website" placeholder="Website (optional)">
                                @error('form.website') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <div class="terms-checkbox">
                                    <input id="terms" type="checkbox" wire:model="form.terms">
                                    <label for="terms">I agree to the Terms and Conditions</label>
                                </div>
                                @error('form.terms') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <button wire:click="submitStep5" class="submit-btn">Submit</button>
                        </div>
                    </div>
                </div>

            <!-- Buyer Registration Modal (Step 6) -->
            @elseif($step == 6)
                <div id="buyer-modal" class="modal-overlay" style="display: flex;">
                    <div class="modal-content">
                        <button wire:click="startWizard" class="modal-close">×</button>
                        <div class="modal-header">
                            <h2>Register as a Buyer</h2>
                            <div class="modal-icons">
                                <img src="{{ asset('storage/onboard/for_customers.png') }}" alt="Buyer Icons">
                            </div>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="business_name">Store / Business Name (optional)</label>
                                <input id="business_name" type="text" wire:model="form.business_name" placeholder="Store / Business Name (optional)">
                                @error('form.business_name') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label for="email">Email (optional)</label>
                                <input id="email" type="email" wire:model="form.email" placeholder="Email (optional)">
                                @error('form.email') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label for="country_id">Country</label>
                                <select id="country_id" wire:model="form.country_id" class="select2 form-control">
                                    <option value="">Select a country</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}" {{ $form['country_id'] == $country->id ? 'selected' : '' }} data-iso2="{{ $country->iso2 }}">
                                            {{ $country->name }} ({{ $country->iso2 }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('form.country_id') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <button wire:click="submitStep6" class="submit-btn">Continue</button>
                        </div>
                    </div>
                </div>

            <!-- Buyer Shipping Details Modal (Step 8) -->
            @elseif($step == 8)
                <div id="buyer-step2-modal" class="modal-overlay" style="display: flex;">
                    <div class="modal-content">
                        <button wire:click="startWizard" class="modal-close">×</button>
                        <button wire:click="goBack" class="modal-back"><</button>
                        <div class="modal-header">
                            <h2>Enter Shipping Details</h2>
                            <img src="{{ asset('storage/onboard/for_customers.png') }}" alt="For Buyers" class="card-image">
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="address">Address</label>
                                <input id="address" type="text" wire:model="form.address" placeholder="Address">
                                @error('form.address') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="mobile-input-wrapper">
                                <select wire:model="form.country_code" class="country-code select2 form-control" id="country_code_select">
                                    <option value="">Select country code</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->phonecode }}" {{ $form['country_code'] == $country->phonecode ? 'selected' : '' }}>
                                            {{ $country->phonecode }} ({{ $country->name }})
                                        </option>
                                    @endforeach
                                </select>
                                <input type="tel" wire:model="form.mobile_number" placeholder="Mobile Number" class="mobile-number-input">
                                @error('form.mobile_number') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            @error('form.country_code') <span class="error">{{ $message }}</span> @enderror
                            <div class="form-group">
                                <label for="zip_code">ZIP Code</label>
                                <input id="zip_code" type="text" wire:model="form.zip_code" placeholder="ZIP Code">
                                @error('form.zip_code') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <button wire:click="submitStep8" class="submit-btn">Continue</button>
                        </div>
                    </div>
                </div>

            <!-- Buyer OTP Verification Modal (Step 9) -->
            @elseif($step == 9)
                <div id="buyer-step3-otp-modal" class="modal-overlay" style="display: flex;">
                    <div class="modal-content">
                        <button wire:click="startWizard" class="modal-close">×</button>
                        <button wire:click="goBack" class="modal-back"><</button>
                        <div class="modal-header">
                            <h2>Verify Your Mobile</h2>
                            <div class="step-indicator">Step 3</div>
                            <div class="modal-icons">
                                <img src="{{ asset('storage/onboard/for_customers.png') }}" alt="Buyer Icons">
                            </div>
                        </div>
                        <div class="modal-body">
                            <p class="otp-message">We've sent a 4-digit code to your mobile. Please enter it below.</p>
                            @if($errorMessage)
                                <div class="error-message">{{ $errorMessage }}</div>
                            @endif
                            @if($otpVerified && $otpVerifiedMobile === ('+' . ltrim($form['country_code'], '+') . $form['mobile_number']))
                                <div class="alert alert-success">Mobile verified!</div>
                                <button wire:click="nextAfterOtpBuyer" class="submit-btn">Next</button>
                            @else
                                <div class="otp-inputs" x-data>
                                    @for ($i = 0; $i < 4; $i++)
                                        <input type="text" class="otp-digit"
                                               maxlength="1"
                                               wire:model.debounce.500ms="form.otp_digits.{{ $i }}"
                                               wire:keydown.enter.prevent="submitBuyerOtp"
                                               @keydown.tab.prevent="$wire.focusNext({{ $i }})"
                                               @paste="$wire.handlePaste($event, {{ $i }})"
                                               placeholder="0">
                                    @endfor
                                    @error('form.otp') <span class="error">{{ $message }}</span> @endif
                                </div>
                                <div class="d-flex justify-content-center align-content-center my-2">
                                    <div id="recaptcha-container"></div>
                                </div>
                                <button wire:click="submitBuyerOtp" class="submit-btn" id="verify_otp">Verify</button>
                                <div x-data="{ resendTimeout: @entangle('otpResendTimeout'), sentAt: @entangle('otpSentAt'), now: Date.now()/1000, timer: 0, interval: null }"
                                     x-init="
                                        if (sentAt) {
                                            timer = resendTimeout - (Math.floor(now) - Math.floor(new Date(sentAt).getTime()/1000));
                                            if (timer > 0) {
                                                interval = setInterval(() => {
                                                    timer--;
                                                    if (timer <= 0) clearInterval(interval);
                                                }, 1000);
                                            } else {
                                                timer = 0;
                                            }
                                        }
                                     "
                                     class="my-2">
                                    <button wire:click="resendBuyerOtp" class="resend-btn" :disabled="timer > 0">Resend OTP <span x-show="timer > 0">in <span x-text="timer"></span>s</span></button>
                                </div>
                                <div class="text-danger mt-2" x-show="$wire.otpTries >= 5">
                                    Too many attempts. Please resend OTP.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            <!-- Buyer Complete Registration Modal (Step 10) -->
            @elseif($step == 10)
                <div id="buyer-step4-modal" class="modal-overlay" style="display: flex;">
                    <div class="modal-content">
                        <button wire:click="startWizard" class="modal-close">×</button>
                        <button wire:click="goBack" class="modal-back"><</button>
                        <div class="modal-header">
                            <h2>Complete Registration</h2>
                        </div>
                        <div class="modal-body">
                            <!-- No summary panel, no country code/mobile number -->
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input id="name" type="text" wire:model="form.name" placeholder="Full Name">
                                @error('form.name') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label for="company_name">Company Name (optional)</label>
                                <input id="company_name" type="text" wire:model="form.company_name" placeholder="Company Name (optional)">
                                @error('form.company_name') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label for="address">Address (optional)</label>
                                <input id="address" type="text" wire:model="form.address" placeholder="Address (optional)">
                                @error('form.address') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label for="website">Website (optional)</label>
                                <input id="website" type="text" wire:model="form.website" placeholder="Website (optional)">
                                @error('form.website') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label>Product Category</label>
                                <div class="preference-list">
                                    @foreach($categories as $category)
                                        <div class="preference-box {{ is_array($form['categories']) && in_array($category->id, $form['categories']) ? 'selected' : '' }}"
                                             wire:click="toggleCategory({{ $category->id }})"
                                             data-value="{{ $category->id }}">
                                            {{ $category->display_name }}
                                        </div>
                                    @endforeach
                                </div>
                                @error('form.categories') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <div class="terms-checkbox">
                                    <input id="terms" type="checkbox" wire:model="form.terms">
                                    <label for="terms">I agree to the Terms and Conditions</label>
                                </div>
                                @error('form.terms') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <button wire:click="submitStep10" class="submit-btn">Complete Registration</button>
                        </div>
                    </div>
                </div>
                
            @elseif($step == 11)
            <div class="wizard-options">
                    <button wire:click="closeWizard" class="modal-close">×</button>
                    <a href="#" wire:click="startSupplierLogin" class="wizard-card supplier-card">
                        <img src="{{ asset('storage/onboard/for_supplies.png') }}" alt="For Suppliers" class="card-image">
                        <div class="card-text">Login For Suppliers</div>
                    </a>
                    <a href="/login" class="wizard-card buyer-card">
                        <img src="{{ asset('storage/onboard/for_customers.png') }}" alt="For Buyers" class="card-image">
                        <div class="card-text">Login For Buyers</div>
                    </a>
                </div>
                
            @elseif($step == 12)
            <div id="login-supplier-modal" class="modal-overlay" style="display: flex;">
                    <div class="modal-content">
                        <button wire:click="closeWizard" class="modal-close">×</button>
                        <div class="modal-header">
                            <h2>Enter Your Mobile Number to Login</h2>
                            <div class="step-indicator">Step 1</div>
                            <div class="modal-icons">
                                <img src="{{ asset('storage/onboard/for_supplies.png') }}" alt="Supplier Icons">
                            </div>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <div class="mobile-input-wrapper">
                                    <select wire:model="form.country_code" class="country-code select2 form-control" id="country_code_select">
                                        <option value="">Select country code</option>
                                        @foreach($countries as $country)
                                            <option value="{{ $country->phonecode }}" {{ $form['country_code'] == $country->phonecode ? 'selected' : '' }}>
                                                {{ $country->phonecode }} ({{ $country->name }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="tel" wire:model="form.mobile_number" placeholder="Mobile Number" class="mobile-number-input">
                                    @error('form.mobile_number') <span class="error">{{ $message }}</span> @enderror
                                </div>
                                @error('form.country_code') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <button wire:click="submitStep11" class="submit-btn">Send OTP</button>
                        </div>
                    </div>
                </div>
                
            @elseif($step == 13)
                 <div id="login-supplier-step2-modal" class="modal-overlay" style="display: flex;">
                    <div class="modal-content">
                        <button wire:click="startWizardLogin" class="modal-close">×</button>
                        <button wire:click="goBack" class="modal-back"><</button>
                        <div class="modal-header">
                            <h2>Verify Your Mobile to Login</h2>
                            <div class="step-indicator">Step 2</div>
                            <div class="modal-icons">
                                <img src="{{ asset('storage/onboard/for_supplies.png') }}" alt="Supplier Icons">
                            </div>
                        </div>
                        <div class="modal-body">
                            <p class="otp-message">We’ve sent a 4-digit code to your mobile. Please enter it below.</p>
                            @if($errorMessage)
                                <div class="error-message">{{ $errorMessage }}</div>
                            @endif
                            <div class="otp-inputs" x-data>
                                @for ($i = 0; $i < 4; $i++)
                                    <input type="text" class="otp-digit"
                                           maxlength="1"
                                           wire:model.debounce.500ms="form.otp_digits.{{ $i }}"
                                           wire:keydown.enter.prevent="submitStep12"
                                           @keydown.tab.prevent="$wire.focusNext({{ $i }})"
                                           @paste="$wire.handlePaste($event, {{ $i }})"
                                           placeholder="0">
                                @endfor
                                @error('form.otp') <span class="error">{{ $message }}</span> @endif
                            </div>
                            <div class="d-flex justify-content-center align-content-center my-2">
                                <div id="recaptcha-container"></div>
                            </div>
                            <button wire:click="submitStep12" class="submit-btn" id="verify_otp">Verify</button>
                            <div x-data="{ resendTimeout: @entangle('otpResendTimeout'), sentAt: @entangle('otpSentAt'), now: Date.now()/1000, timer: 0, interval: null }"
                                 x-init="
                                    if (sentAt) {
                                        timer = resendTimeout - (Math.floor(now) - Math.floor(new Date(sentAt).getTime()/1000));
                                        if (timer > 0) {
                                            interval = setInterval(() => {
                                                timer--;
                                                if (timer <= 0) clearInterval(interval);
                                            }, 1000);
                                        } else {
                                            timer = 0;
                                        }
                                    }
                                 "
                                 class="my-2">
                                <button wire:click="submitStep11" class="resend-btn" :disabled="timer > 0">Resend OTP <span x-show="timer > 0">in <span x-text="timer"></span>s</span></button>
                            </div>
                            <div class="text-danger mt-2" x-show="$wire.otpTries >= 5">
                                Too many attempts. Please resend OTP.
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
<script defer>
    document.addEventListener("livewire:load", () => {
        // Listen for Livewire event to trigger focus change
        Livewire.on('focusNext', (index) => {
            const otpBoxes = document.querySelectorAll(".otp-inputs .otp-digit");
            if (otpBoxes[index]?.value && index < otpBoxes.length - 1) {
                otpBoxes[index + 1].focus();
            }
        });
    });
</script>