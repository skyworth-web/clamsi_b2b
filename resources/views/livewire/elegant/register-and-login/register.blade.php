@php
    $bread_crumb['page_main_bread_crumb'] = labels('front_messages.register', 'Register');
@endphp

<div>
    <!-- Onboarding Screen -->
    <div class="container {{ $showWizard ? 'hidden' : '' }}">
        <img src="{{ asset('storage/onboard/onboard_image.png') }}" alt="Onboarding Image" class="onboard-image">
        <div class="overlay-text">
            <div class="logo">Clamsi</div>
            <div class="main-text">Private B2B Fashion Platform</div>
            <div class="sub-text">Fashion Access, By Invitation Only</div>
            <button wire:click="startWizard" class="btn-primary">Get Started</button>
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
                    <a href="#" wire:click="startSupplierRegistration" class="wizard-card supplier-card" onclick="openModal('supplier-modal')">
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
                        <button wire:click="goBack" class="modal-close">&times;</button>
                        <button wire:click="goBack" class="modal-back">&lt;</button>
                        <div class="modal-header">
                            <h2>Register as a Supplier</h2>
                            <div class="modal-icons">
                                <img src="{{ asset('storage/onboard/for_supplies.png') }}" alt="Supplier Icons">
                            </div>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <input type="text" wire:model="form.business_name" placeholder="Business Names">
                                @error('form.business_name') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <select wire:model="form.country_id" class="select2">
                                    <option value="" >Select a countrys</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}" {{ $form['country_id'] == $country->id ? 'selected' : '' }}>
                                            {{ $country->name }} ({{ $country->iso2 }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('form.country_id') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <input type="email" wire:model="form.email" placeholder="Email">
                                @error('form.email') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <input type="text" wire:model="form.website" placeholder="Website (optional)">
                                @error('form.website') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <button wire:click="submitStep1" class="submit-btn">Continue</button>
                        </div>
                    </div>
                </div>
                 @elseif($step == 2)
 <div id="supplier-modal" class="modal-overlay" style="display: flex;">
                    <div class="modal-content">
                        <button wire:click="goBack" class="modal-close">&times;</button>
                        <button wire:click="goBack" class="modal-back">&lt;</button>
                                <label for="mobile" class="d-none">{{ labels('front_messages.mobile', 'mobile') }}
                                    <span class="required">*</span></label>
                                <input type="number" name="mobile" placeholder="mobile" id="number"
                                    value="" />
                                <div class="d-flex justify-content-center align-content-center my-2">
                                    <div id="recaptcha-container"></div>
                                </div>
                                <button class="btn btn-primary btn-lg w-100" id="send_otp">
                                    {{ labels('front_messages.send_otp', 'Send OTP') }}</button>
                            </div>
                        </div>
                        <div class="form-row verify-otp-box d-none">
                            <div class="form-group col-12">
                                <label for="verificationCode"
                                    class="d-none">{{ labels('front_messages.verification_code', 'verificationCode') }}
                                    <span class="required">*</span></label>
                                <input type="text" id="verificationCode" class="form-control"
                                    placeholder="Enter Verification Code"><br>
                                <button type="button" class="btn btn-primary btn-lg w-100"
                                    id="verify_otp">{{ labels('front_messages.verify_code', 'Verify Code') }}</button>
                            </div>
                        </div>

            <!-- Supplier Registration Modal (Step 4) - Select Preferences -->
            @elseif($step == 4)
                <div id="supplier-step4-modal" class="modal-overlay" style="display: flex;">
                    <div class="modal-content">
                        <button wire:click="goBack" class="modal-close">&times;</button>
                        <button wire:click="goBack" class="modal-back">&lt;</button>
                        <div class="modal-header">
                            <h2>Select Your Preferences</h2>
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
                        <button wire:click="goBack" class="modal-close">&times;</button>
                        <button wire:click="goBack" class="modal-back">&lt;</button>
                        <div class="modal-header">
                            <h2>Additional Details</h2>
                            <div class="step-indicator">Step 5</div>
                        </div>
                        <div class="modal-body">
                            <!-- Hidden fields for latitude and longitude -->
                            <input type="hidden" wire:model="form.latitude">
                            <input type="hidden" wire:model="form.longitude">
                            <div class="form-group">
                                <input type="text" wire:model="form.name" placeholder="Name">
                                @error('form.name') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <textarea wire:model="form.address" placeholder="Address" rows="3"></textarea>
                                @error('form.address') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <input type="tel" wire:model="form.phone" placeholder="Phone">
                                @error('form.phone') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <select wire:model="form.country" class="select2">
                                    <option value="">Select a country</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}" {{ $form['country'] == $country->id ? 'selected' : '' }}>
                                            {{ $country->name }} ({{ $country->iso2 }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('form.country') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <input type="password" wire:model="form.password" placeholder="Password">
                                @error('form.password') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <input type="password" wire:model="form.password_confirmation" placeholder="Confirm Password">
                                @error('form.password_confirmation') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <div class="terms-checkbox">
                                    <input type="checkbox" wire:model="form.terms">
                                    <span>I agree to the Terms and Conditions</span>
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
                        <button wire:click="goBack" class="modal-close">&times;</button>
                        <div class="modal-header">
                            <h2>Register as a Buyer</h2>
                            <div class="modal-icons">
                                <img src="{{ asset('storage/onboard/for_customers.png') }}" alt="Buyer Icons">
                            </div>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <input type="text" wire:model="form.business_name" placeholder="Store / Business Name">
                                @error('form.business_name') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <input type="email" wire:model="form.email" placeholder="Email">
                                @error('form.email') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <select wire:model="form.country_id" class="select2">
                                    <option value="">Select a country</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}" {{ $form['country_id'] == $country->id ? 'selected' : '' }}>
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
                        <button wire:click="goBack" class="modal-close">&times;</button>
                        <button wire:click="goBack" class="modal-back">&lt;</button>
                        <div class="modal-header">
                            <h2>Enter Shipping Details</h2>
                            <img src="{{ asset('storage/onboard/for_customers.png') }}" alt="For Buyers" class="card-image">
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <input type="text" wire:model="form.address" placeholder="Address">
                                @error('form.address') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <input type="tel" wire:model="form.phone" placeholder="Phone">
                                @error('form.phone') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <input type="text" wire:model="form.zip_code" placeholder="ZIP Code">
                                @error('form.zip_code') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <select wire:model="form.country" class="select2">
                                    <option value="">Select a country</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}" {{ $form['country'] == $country->id ? 'selected' : '' }}>
                                            {{ $country->name }} ({{ $country->iso2 }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('form.country') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <button wire:click="submitStep8" class="submit-btn">Continue</button>
                        </div>
                    </div>
                </div>

            <!-- Buyer Complete Registration Modal (Step 9) -->
            @elseif($step == 9)
                <div id="buyer-step3-modal" class="modal-overlay" style="display: flex;">
                    <div class="modal-content">
                        <button wire:click="goBack" class="modal-close">&times;</button>
                        <button wire:click="goBack" class="modal-back">&lt;</button>
                        <div class="modal-header">
                            <h2>Complete Registration</h2>
                        </div>
                        <div class="modal-body">
                            <!-- Hidden fields for latitude and longitude -->
                            <input type="hidden" wire:model="form.latitude">
                            <input type="hidden" wire:model="form.longitude">
                            <div class="form-group">
                                <input type="text" wire:model="form.name" placeholder="Full Name">
                                @error('form.name') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <input type="text" wire:model="form.address" placeholder="Address">
                                @error('form.address') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <input type="tel" wire:model="form.phone" placeholder="Phone">
                                @error('form.phone') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <input type="text" wire:model="form.zip_code" placeholder="Zip Code">
                                @error('form.zip_code') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <select wire:model="form.country" class="select2">
                                    <option value="">Select a country</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}" {{ $form['country'] == $country->id ? 'selected' : '' }}>
                                            {{ $country->name }} ({{ $country->iso2 }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('form.country') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <input type="password" wire:model="form.password" placeholder="Password">
                                @error('form.password') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <input type="password" wire:model="form.password_confirmation" placeholder="Confirm Password">
                                @error('form.password_confirmation') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <button wire:click="submitStep9" class="submit-btn">Complete Registration</button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- JavaScript for Modal Toggle and Geolocation -->
    <script>
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
            initializeSelect2();
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        document.addEventListener('livewire:init', () => {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const { latitude, longitude } = position.coords;
                        @this.call('processGeolocation', latitude, longitude);
                        console.log('Geolocation obtained:', { latitude, longitude });
                    },
                    (error) => {
                        console.error('Geolocation error:', error.message);
                        @this.call('detectUserLocation');
                    },
                    { timeout: 10000, maximumAge: 600000 }
                );
            } else {
                console.error('Geolocation not supported.');
                @this.call('detectUserLocation');
            }

            console.log('Livewire initialized. Form:', {
                country_id: '{{ $form["country_id"] }}',
                country_code: '{{ $form["country_code"] }}',
                country: '{{ $form["country"] }}',
                latitude: '{{ $form["latitude"] }}',
                longitude: '{{ $form["longitude"] }}'
            });

            @this.on('update-step', () => {
                const countrySelect = document.querySelector('select[wire\\:model="form.country_id"]');
                const countryCodeSelect = document.querySelector('select[wire\\:model="form.country_code"]');
                const countryFinalSelect = document.querySelector('select[wire\\:model="form.country"]');
                console.log('Step updated. Country:', countrySelect ? countrySelect.value : 'N/A');
                console.log('Step updated. Code:', countryCodeSelect ? countryCodeSelect.value : 'N/A');
                console.log('Step updated. Final:', countryFinalSelect ? countryFinalSelect.value : 'N/A');
                console.log('Step updated. Latitude:', '{{ $form["latitude"] }}');
                console.log('Step updated. Longitude:', '{{ $form["longitude"] }}');
                if (countrySelect) {
                    console.log('Selected country:', countrySelect.options[countrySelect.selectedIndex].text);
                }
            });
        });
        
        
    </script>

</div>


