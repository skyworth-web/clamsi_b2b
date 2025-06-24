<div id="page-content">
    @php
        $store_settings = getStoreSettings();
        $language_code = get_language_code();
    @endphp
    <x-utility.breadcrumbs.breadcrumbOne :$breadcrumb />
    @php
        $component = getCategoryDisplayComponent($store_settings);
    @endphp

    <x-dynamic-component :component="$component" :categories="$categories" :language_code="$language_code" />
</div>
