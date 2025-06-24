document.addEventListener('livewire:initialized', function() {
    // Geolocation
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const { latitude, longitude } = position.coords;
                Livewire.dispatch('call', { method: 'processGeolocation', params: [latitude, longitude] });
                console.log('Geolocation obtained:', { latitude, longitude });
            },
            (error) => {
                console.error('Geolocation error:', error.message);
                Livewire.dispatch('call', { method: 'detectUserLocation' });
            },
            { timeout: 10000, maximumAge: 600000 }
        );
    } else {
        console.error('Geolocation not supported.');
        Livewire.dispatch('call', { method: 'detectUserLocation' });
    }

    // Initial form data logging
    Livewire.on('first', () => {
        console.log('Livewire initialized. Form:', {
            country_id: document.querySelector('select[wire\\:model="form.country_id"]')?.value || '',
            country_code: document.querySelector('select[wire\\:model="form.country_code"]')?.value || '',
            country: document.querySelector('select[wire\\:model="form.country"]')?.value || '',
            latitude: document.querySelector('input[wire\\:model="form.latitude"]')?.value || '',
            longitude: document.querySelector('input[wire\\:model="form"]')?.value || ''
        });
    });

    // Update step listener
    Livewire.on('update-step', function() {
        const countrySelect = document.querySelector('select[wire\\:model]="form.country_id"]');
        const countryCodeSelect = document.querySelector('select[wire\\:model="form.country_code"]');
        const countryFinalSelect = document.querySelector('select[wire\\:model="form.country"]');
        console.log('Step updated. Country:', countrySelect ? countrySelect.value : 'N/A');
        console.log('Step updated. Code:', countryCodeSelect ? countryCodeSelect.value : 'N/A');
        console.log('Step updated. Final:', countryFinalSelect ? countryFinalSelect.value : 'N/A');
        console.log('Step updated. Latitude:', document.querySelector('input[wire\\:model="form.latitude"]')?.value || '');
        console.log('Step updated. Longitude:', document.querySelector('input[wire\\:model="form.longitude"]')?.value || '');
        if (countrySelect) {
            console.log('Selected country:', countrySelect.options[countrySelect.selectedIndex]?.text || '');
        }
    });
});