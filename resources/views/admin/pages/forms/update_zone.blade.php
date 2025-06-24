@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.zones', 'Zones') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.zones', 'Zones')" :subtitle="labels(
        'admin_labels.enhance_visual_appeal_with_effortless_zone_integration',
        'Enhance Visual Appeal with Effortless Zone Integration',
    )" :breadcrumbs="[['label' => labels('admin_labels.zones', 'Zones')]]" />

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <form class="form-horizontal form-submit-event submit_form"
                    action="{{ url('/admin/zones/update/' . $zone->id) }}" method="POST" id=""
                    enctype="multipart/form-data">
                    @method('PUT')
                    @csrf
                    <div class="card-body">
                        <h5 class="mb-4">
                            {{ labels('admin_labels.add_zone', 'Manage Zones') }}
                        </h5>

                        <div class="form-group">
                            <ul class="nav nav-tabs" id="brandTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="language-nav-link nav-link active" id="tab-en" data-bs-toggle="tab"
                                        data-bs-target="#content-en" type="button" role="tab"
                                        aria-controls="content-en" aria-selected="true">
                                        {{ labels('admin_labels.default', 'Default') }}
                                    </button>
                                </li>
                                {!! generateLanguageTabsNav($languages) !!}
                            </ul>
                            <div class="tab-content mt-3" id="UpdatebrandTabsContent">
                                <!-- Default 'en' tab content -->
                                <div class="tab-pane fade show active" id="content-en" role="tabpanel"
                                    aria-labelledby="tab-en">
                                    <div class="mb-3">
                                        <label for="brand_name"
                                            class="form-label">{{ labels('admin_labels.name', 'Name') }}<span
                                                class="text-asterisks text-sm">*</span></label>

                                        <input type="text" class="form-control" id="basic-default-fullname"
                                            placeholder="Gucci" name="name"
                                            value="{{ isset($zone->name) ? json_decode($zone->name)->en : '' }}">
                                    </div>
                                </div>
                                {!! generateUpdateableLanguageTabs($languages, $zone->name, 'admin_labels.name', 'Name', 'translated_zone_name') !!}

                            </div>
                        </div>
                        <!-- Zipcodes Repeater -->
                        <label for="name" class="form-label">
                            {{ labels('admin_labels.serviceable_zipcodes', 'Serviceable Zipcodes') }}<span
                                class="text-asterisks text-sm">*</span>
                        </label>
                        <div class="repeater">
                            <div data-repeater-list="zipcode_group">
                                @foreach ($zipcodes as $zipcode)
                                    <div data-repeater-item>
                                        <div class="row">
                                            <div class="col-md-5 mt-2">
                                                <select class="form-select zone_zipcode_list" name="serviceable_zipcode_id">
                                                    <option value="{{ $zipcode->id }}" selected>
                                                        {{ $zipcode->zipcode }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-md-5 mt-2">
                                                <input type="text" name="zipcode_delivery_charge" class="form-control"
                                                    placeholder="Delivery Charge"
                                                    value="{{ $zipcode->delivery_charges }}" />
                                            </div>
                                            <div class="col-md-2">
                                                <input data-repeater-delete type="button" class="btn btn-secondary mt-2"
                                                    value="Delete" />
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <input data-repeater-create type="button" class="btn btn-primary mt-2" value="Add" />
                        </div>

                        <!-- Cities Repeater -->
                        <label for="name" class="form-label mt-4">
                            {{ labels('admin_labels.serviceable_cities', 'Serviceable Cities') }}<span
                                class="text-asterisks text-sm">*</span>
                        </label>
                        <div class="repeater">
                            <div data-repeater-list="city_group">
                                @foreach ($cities as $city)
                                    <div data-repeater-item>
                                        <div class="row city_list_parent">
                                            <div class="col-md-5 mt-2">
                                                <select class="form-select zone_city_list" name="serviceable_city_id">
                                                    <option value="{{ $city->id }}" selected>
                                                        {{ getDynamicTranslation('cities', 'name', $city->id, $language_code) }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-md-5 mt-2">
                                                <input type="text" name="city_delivery_charge" class="form-control"
                                                    placeholder="Delivery Charge" value="{{ $city->delivery_charges }}" />
                                            </div>
                                            <div class="col-md-2">
                                                <input data-repeater-delete type="button" class="btn btn-secondary mt-2"
                                                    value="Delete" />
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <input data-repeater-create type="button" class="btn btn-primary mt-2" value="Add" />
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit"
                                class="btn btn-primary submit_button">{{ labels('admin_labels.update_zone', 'Update Zone') }}</button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
