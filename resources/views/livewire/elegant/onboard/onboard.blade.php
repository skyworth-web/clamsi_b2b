<div>
    <!-- Styles and content previously in <head> -->
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow: hidden;
        }
        .container {
            position: relative;
            width: 100%;
            height: 100vh;
            overflow: hidden;
        }
        .onboard-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .overlay-text {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
        }
        .logo {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 24px;
            font-weight: bold;
        }
        .main-text {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .sub-text {
            font-size: 24px;
            margin-bottom: 20px;
        }
        .error-message {
            background-color: rgba(255, 92, 141, 0.1);
            color: #ff0000;
            padding: 10px;
            Number: 5px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 14px;
            font-weight: bold;
        }
        .btn-primary {
            background: #6b48ff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            font-size: 16px;
            cursor: pointer;
            border-radius: 25px;
        }
        .wizard-background {
            width: 100%;
            height: 100vh;
            background-color: #6b48ff;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .wizard-options {
            display: flex;
            gap: 20px;
            justify-content: center;
            align-items: center;
        }
        .wizard-card {
            text-decoration: none;
            border-radius: 15px;
            overflow: hidden;
            width: 200px;
            height: 250px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            transition: transform 0.3s;
        }
        .wizard-card:hover {
            transform: scale(1.05);
        }
        .supplier-card {
            background-color: #ff5c8d;
        }
        .buyer-card {
            background-color: #ff8c00;
        }
        .card-image {
            width: 150px;
            height: 150px;
            object-fit: contain;
        }
        .card-text {
            color: white;
            font-size: 20px;
            font-weight: bold;
            margin-top: 10px;
            text-align: center;
        }
        .hidden {
            display: none;
        }
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background-color: #6b48ff;
            border-radius: 15px;
            width: 400px;
            padding: 20px;
            position: relative;
            color: white;
            text-align: center;
        }
        .modal-close {
            position: absolute;
            top: 10px;
            right: 15px;
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
        }
        .modal-back {
            position: absolute;
            top: 10px;
            left: 15px;
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
        }
        .modal-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .modal-header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        .step-indicator {
            font-size: 16px;
            color: #ffffff;
            margin-top: 5px;
        }
        .modal-icons {
            background-color: #ff5c8d;
            border-radius: 10px;
            padding: 10px;
            margin-top: 10px;
            display: inline-block;
        }
        .modal-icons img {
            width: 100px;
            height: 100px;
            object-fit: contain;
        }
        .modal-body {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            color: #333;
            height: 40px;
            box-sizing: border-box;
        }
        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: #666;
        }
        .form-group select:invalid {
            color: #666;
        }
        .form-group textarea {
            resize: vertical;
            height: auto;
        }
        .mobile-input-wrapper {
            display: flex;
            gap: 10px;
        }
        .country-code {
            width: 100px;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px !important;
            color: #333;
            height: 40px;
            box-sizing: border-box;
        }
        label {
            text-align: left;
        }
        label.center {
            text-align: center;
        }
        .mobile-number-input {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            color: #333;
            height: 40px;
            box-sizing: border-box;
        }
        .otp-message {
            font-size: 14px;
            text-align: center;
            color: #ffffff;
        }
        .otp-inputs {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 15px 0;
        }
        .otp-digit {
            width: 50px;
            height: 50px;
            text-align: center;
            font-size: 24px !important;
            border: 2px solid #ffffff;
            border-radius: 8px;
            background-color: #ffffff;
            color: #6b48ff;
            font-weight: bold;
            transition: border-color 0.3s ease;
        }
        .otp-digit:focus {
            outline: none;
            border-color: #ff5c8d;
            box-shadow: 0 0 5px rgba(255, 92, 141, 0.5);
        }
        .otp-digit::placeholder {
            color: #999;
            font-size: 24px;
        }
        .otp-digit:not(:placeholder-shown) {
            border-color: #6b48ff;
        }
        .preference-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }
        .preference-box {
            min-width: 100px;
            height: 40px;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            background-color: #ffffff;
            color: #6b48ff;
            border: 2px solid #6b48ff;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
            cursor: pointer;
            padding: 0 16px;
            transition: all 0.3s ease;
            user-select: none;
        }
        .preference-box:hover {
            background-color: #f0f0f0;
        }
        .preference-box.selected {
            background-color: #ff5c8d;
            color: #ffffff;
            border: 2px solid #ff5c8d;
        }
        .terms-checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .terms-checkbox input[type="checkbox"] {
            width: 20px;
            height: 20px;
        }
        .terms-checkbox span {
            font-size: 14px;
        }
        .submit-btn {
            background-color: #ffffff;
            color: #6b48ff;
            border: none;
            padding: 10px;
            border-radius: 25px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
        }
        .submit-btn:hover {
            background-color: #f0f0f0;
        }
        .select2-container--default .select2-selection--single {
            height: 40px !important;
            border: none;
            border-radius: 5px;
            background-color: #fff;
            color: #333;
            display: flex !important;
            align-items: center;
            box-sizing: border-box;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 40px;
            padding-left: 10px;
            color: #333;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }
        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #666;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
            right: 10px;
        }
        .select2-container {
            width: 100% !important;
            max-width: 100%;
        }
        .select2-container--default .select2-results__option {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }
        .select2-container--default .select2-dropdown {
            width: 100% !important;
            max-width: 300px;
            min-width: 100px;
            border-radius: 5px;
        }
    </style>
    @livewireStyles
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Main Livewire wizard component -->
    <livewire:wizard.welcome-wizard />

    <!-- Scripts and modal -->
    @livewireScripts
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Livewire.on('registration-success', function (data) {
                window._redirectUrl = data.redirectUrl || '/';
                showRememberDeviceModal();
            });
        });
        function showRememberDeviceModal() {
            document.getElementById('remember-device-modal').style.display = 'flex';
        }
        function rememberDeviceYes() {
            // Set a cookie for 30 days
            var d = new Date();
            d.setTime(d.getTime() + (30*24*60*60*1000));
            var expires = 'expires=' + d.toUTCString();
            document.cookie = 'remember_device=1;' + expires + ';path=/';
            redirectToMainPage();
        }
        function redirectToMainPage() {
            document.getElementById('remember-device-modal').style.display = 'none';
            var url = window._redirectUrl || '/';
            window.location.href = url;
        }
    </script>
    <script>
        $(document).ready(function() {
            let initializedModals = {};

            function initializeSelect2($select, model) {
                if (!$select.hasClass('select2-hidden-accessible')) {
                    $select.select2({
                        placeholder: 'Select an option',
                        allowClear: true,
                        width: '100%',
                        dropdownAutoWidth: false,
                        minimumResultsForSearch: 10,
                        templateResult: function(data) {
                            if (!data.text) return null;
                            return $('<span>').text(data.text).css({
                                'white-space': 'nowrap',
                                'overflow': 'hidden',
                                'text-overflow': 'ellipsis',
                                'max-width': '280px'
                            });
                        },
                        templateSelection: function(data) {
                            if (!data.text) return data.text;
                            return $('<span>').text(data.text).css({
                                'white-space': 'nowrap',
                                'overflow': 'hidden',
                                'text-overflow': 'ellipsis',
                                'max-width': '280px'
                            });
                        }
                    });

                    $select.on('change', function() {
                        Livewire.dispatch('updateFormField', { field: model, value: $(this).val() });
                    });
                }
            }

            function initializeModalSelects(modalId, selectIds) {
                if (!initializedModals[modalId]) {
                    selectIds.forEach(function(selectId) {
                        let $select = $('#' + selectId);
                        let model = $select.attr('wire:model') || $select.attr('wire:model.debounce.500ms');
                        if ($select.length && model) {
                            initializeSelect2($select, model.replace('form.', '')); // Remove 'form.' prefix
                        }
                    });
                    initializedModals[modalId] = true;
                }
            }

            $(document).on('focus', '.modal-overlay', function() {
                let modalId = $(this).attr('id');
                let selectIds = [];
                switch (modalId) {
                    case 'supplier-modal':
                        selectIds = ['country_id_select'];
                        break;
                    case 'supplier-step2-modal':
                        selectIds = ['country_code_select'];
                        break;
                    case 'supplier-step5-modal':
                        selectIds = ['country_select'];
                        break;
                    case 'buyer-modal':
                        selectIds = ['buyer_country_id_select'];
                        break;
                    case 'buyer-step2-modal':
                        selectIds = ['buyer_country_select'];
                        break;
                    case 'buyer-step3-modal':
                        selectIds = ['buyer_final_country_select'];
                        break;
                }
                if (selectIds.length > 0) {
                    initializeModalSelects(modalId, selectIds);
                }
            });

            Livewire.on('refresh', function() {
                initializedModals = {};
                $('.modal-overlay').each(function() {
                    if ($(this).is(':visible')) {
                        $(this).trigger('focus');
                    }
                });
            });

            // Handle OTP focus
            $(document).on('input', '.otp-digit', function() {
                const $this = $(this);
                const index = $('.otp-digit').index(this);
                const value = $this.val();

                // Check if the input is a single digit (0-9)
                if (/^[0-9]$/.test(value) && index < $('.otp-digit').length - 1) {
                    // Emit Livewire event to update the form
                    Livewire.dispatch('updateFormField', {
                        field: `form.otp_digits.${index}`,
                        value: value
                    });

                    // Focus the next input
                    $('.otp-digit').eq(index + 1).focus();
                } else if (value.length > 1) {
                    // Clear invalid input
                    $this.val('');
                }
            });

            // Handle backspace to move to previous input
            $(document).on('keydown', '.otp-digit', function(e) {
                const $this = $(this);
                const index = $('.otp-digit').index(this);

                if (e.key === 'Backspace' && !$(this).val() && index > 0) {
                    $('.otp-digit').eq(index - 1).focus();
                }
            });

            // Handle paste event for OTP
            $(document).on('paste', '.otp-digit', function(e) {
                e.preventDefault();
                const pastedData = (e.originalEvent.clipboardData || window.clipboardData).getData('text');
                if (/^[0-9]{1,6}$/.test(pastedData)) {
                    const digits = pastedData.split('').slice(0, 6);
                    $('.otp-digit').each(function(i) {
                        if (i < digits.length) {
                            $(this).val(digits[i]);
                            Livewire.dispatch('updateFormField', {
                                field: `form.otp_digits.${i}`,
                                value: digits[i]
                            });
                        }
                    });
                    // Focus the last filled input or the next empty one
                    const nextIndex = Math.min(digits.length, $('.otp-digit').length - 1);
                    $('.otp-digit').eq(nextIndex).focus();
                }
            });

            // Existing Livewire focusNext event listener
            document.addEventListener('livewire:load', () => {
                Livewire.on('focusNext', (data) => {
                    const otpBoxes = document.querySelectorAll('.otp-inputs .otp-digit');
                    const index = data.index;
                    if (otpBoxes[index]?.value && index < otpBoxes.length - 1) {
                        otpBoxes[index + 1].focus();
                    }
                });
            });
        });
    </script>
    <!-- Remember Device Modal: Always present in DOM -->
    <div id="remember-device-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <button class="modal-close" onclick="redirectToMainPage()">×</button>
            <div class="modal-header">
                <h2>Remember Device for 30 days</h2>
                <p class="step-indicator">Would you like to remember this device for 30 days?</p>
            </div>
            <div class="modal-body">
                <button class="submit-btn" onclick="rememberDeviceYes()">Yes</button>
                <button class="submit-btn" onclick="redirectToMainPage()">No</button>
            </div>
        </div>
    </div>
</div>