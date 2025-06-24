@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.sms_gateway_setting', 'SMS Gateway Setting') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.sms_gateway_setting', 'SMS Gateway Setting')" :subtitle="labels(
        'admin_labels.seamlessly_integrate_and_leverage_sms_capabilities',
        'Seamlessly Integrate and Leverage SMS Capabilities',
    )" :breadcrumbs="[
        ['label' => labels('admin_labels.settings', 'Settings'), 'url' => route('settings.index')],
        ['label' => labels('admin_labels.sms_gateway_setting', 'SMS Gateway Setting')],
    ]" />

    <div class="card m-2 tab-pane" id="firebase_setting">
        <div class="card-body">
            <h5 class="card-title">
                {{ labels('admin_labels.sms_gateway_setting', 'SMS Gateway Setting') }}
            </h5>

            <nav>
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    <button class="nav-link sms_gateway_nav_link active sms_gateway_tab" id="sms-gateway-tab"
                        data-bs-toggle="tab" data-bs-target="#nav-sms-gateway" type="button" role="tab"
                        aria-controls="nav-sms-gateway"
                        aria-selected="true">{{ labels('admin_labels.sms_gateway_configuration', 'SMS Gateway Configuration') }}</button>

                </div>
            </nav>
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active" id="nav-sms-gateway" role="tabpanel"
                    aria-labelledby="sms-gateway-tab" tabindex="0">
                    <div class="card">
                        <div class="card-body">
                            <form class="form-horizontal form-submit-event smsgateway_setting_form" action=""
                                method="POST" id="smsgateway_setting_form" enctype="multipart/form-data">
                                @csrf
                                <div class="align-items-baseline d-flex mt-4">
                                    <p class="mx-2 text-bold">are you confuse how to do ?? </p>
                                    <a type="button" class="text-danger" data-bs-toggle="modal"
                                        data-bs-target="#sms_instuction_modal">
                                        follow this for reference
                                    </a>
                                </div>
                                <div class="row">
                                    <div class="mb-3 col-md-6">
                                        <label for="base_url" class="--">Base URL </label>
                                        <input type="text" class="form-control" id="base_url" name="base_url"
                                            value="<?= isset($sms_gateway_settings['base_url']) ? $sms_gateway_settings['base_url'] : '' ?>">
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label for="sms_gateway_method" class="">Method</label>
                                        <select id="sms_gateway_method" class="form-select" name="sms_gateway_method"
                                            class="form-control col-md-5">
                                            <option value="POST">POST</option>
                                            <option value="GET">GET</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="py-3">
                                    <h4 class="mb-3">Create Authorization Token</h4>
                                    <div class="row">
                                        <div class="mb-3 col-md-6">
                                            <label for="converterInputAccountSID" class="">Account SID </label>
                                            <input type="text" id="converterInputAccountSID" class="form-control">
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label for="converterInputAuthToken" class="">Auth Token</label>
                                            <input type="text" id="converterInputAuthToken" class="form-control">
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <div class="col-md-4 mb-3">
                                            <button type="button" onClick="createHeader()" class="btn btn-primary">Create
                                                Token</button>
                                        </div>
                                        <div class="col-md-12">
                                            <h4 id="basicToken"></h4>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <ul class="nav nav-tabs mb-4">
                                        <li class="nav-item">
                                            <a class="nav-link sms_gateway_nav_link sms_gateway_tab active"
                                                id="product-header-tab" data-bs-toggle="tab" href="#product-header"
                                                role="tab" aria-controls="product-header"
                                                aria-selected="true">Header</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link sms_gateway_nav_link sms_gateway_tab" id="product-body-tab"
                                                data-bs-toggle="tab" href="#product-body" role="tab"
                                                aria-controls="product-body" aria-selected="false">Body</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link sms_gateway_nav_link sms_gateway_tab" id="product-params-tab"
                                                data-bs-toggle="tab" href="#product-params" role="tab"
                                                aria-controls="product-params" aria-selected="false">Params</a>
                                        </li>
                                    </ul>
                                    <div class="tab-content" id="nav-tabContent">
                                        <!-- header -->
                                        <div class="tab-pane fade show active" id="product-header" role="tabpanel"
                                            aria-labelledby="product-header-tab">
                                            <div>
                                                <div class="d-flex">
                                                    <h5 class="modal-title">Add Header data</h5>
                                                    <a href="#" id="add_sms_header"
                                                        class="btn btn-primary btn-sm mx-5 text-white">
                                                        <i class="bx bx-plus"></i>
                                                    </a>
                                                </div>
                                                <div class="card-body p-0">
                                                    <div id="formdata_header_section" class="col-md-12"> </div>
                                                    <div class="d-flex justify-content-center">
                                                        <div class="form-group" id="error_box_header">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- product body tab -->
                                        <div class="tab-pane fade" id="product-body" role="tabpanel"
                                            aria-labelledby="product-body-tab">
                                            <div class="row">
                                                <ul class="nav nav-tabs">
                                                    <li class="nav-item">
                                                        <a class="nav-link sms_gateway_nav_link sms_gateway_tab active"
                                                            id="product-text-tab" data-bs-toggle="tab"
                                                            href="#product-text" role="tab"
                                                            aria-controls="product-text"
                                                            aria-selected="true">text/JSON</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link sms_gateway_nav_link sms_gateway_tab"
                                                            id="product-formdata-tab" data-bs-toggle="tab"
                                                            href="#product-formdata" role="tab"
                                                            aria-controls="product-formdata"
                                                            aria-selected="false">Formdata</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="product-params" role="tabpanel"
                                            aria-labelledby="product-params-tab">
                                            <div>
                                                <div class="d-flex">
                                                    <h5 class="modal-title">Add Params </h5>
                                                    <a href="#" id="add_sms_params"
                                                        class="btn btn-primary btn-sm mx-5 text-white">
                                                        <i class="bx bx-plus"></i>
                                                    </a>
                                                </div>

                                                <div class="card-body p-0 mt-4">
                                                    <div id="formdata_params_section" class="col-md-12"> </div>
                                                    <div class="d-flex justify-content-center">
                                                        <div class="form-group" id="error_box">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body p-0 mt-4">


                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-content p-3 w-100" id="nav-tabContent">
                                        <!-- product faq tab -->
                                        <div class="tab-pane fade" id="product-text" role="tabpanel"
                                            aria-labelledby="product-text-tab">
                                            <div class="row">

                                                <div class="mb-3 col-md-12 description">
                                                    <textarea name="text_format_data" class="text_format_data form-control" placeholder="Place some text here"></textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="tab-pane fade" id="product-formdata" role="tabpanel"
                                            aria-labelledby="product-formdata-tab">
                                            <div>
                                                <div class="d-flex">
                                                    <h5 class="modal-title">Add Body data Parameter and values </h5>
                                                    <a href="#" id="add_sms_body"
                                                        class="btn btn-primary text-white btn-sm mx-5">
                                                        <i class="bx bx-plus"></i>
                                                    </a>
                                                </div>

                                                <div class="card-body p-0 mt-4">
                                                    <div id="formdata_section" class="col-md-12">

                                                    </div>

                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="card-body d-flex">

                                        <pre class="">{only_mobile_number}</pre>
                                        <pre>{mobile_number_with_country_code}</pre>
                                        <pre>{country_code}</pre>
                                        <pre>{message}</pre>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="reset"
                                            class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                                        <button type="submit" class="btn btn-primary submit_button"
                                            id="sms_gateway_submit">{{ labels('admin_labels.update_settings', 'Update Settings') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div class="modal fade bd-example-modal-lg" id="sms_instuction_modal" tabindex="-1"
        aria-labelledby="sms_instuction_modal_Label" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sms_instuction_modal_Label">Sms Gateway Configuration</h5>
                    <div class="d-flex justify-content-end"><button type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"></button></div>
                </div>
                <div class="modal-body">
                    <ul>
                        <li class="my-4">Read and follow instructions carefully while configuring the SMS gateway
                            setting.</li>

                        <li class="my-4">Firstly, open your SMS gateway account. You can find API keys in your account
                            under "API keys & credentials" and create an API key.</li>
                        <li class="my-4">After creating the key, you can see Account SID and Auth Token.</li>
                        <div class="simplelightbox-gallery">
                            <a href="" target="_blank">
                                <img src="" class="w-100">
                            </a>
                        </div>

                        <li class="my-4">For Base URL Messaging, go to "Send an SMS".</li>
                        <div class="simplelightbox-gallery">
                            <a href="" target="_blank">
                                <img src="" class="w-100">
                            </a>
                        </div>

                        <li class="my-4">Check this for admin panel settings.</li>
                        <div class="simplelightbox-gallery">
                            <a href="" target="_blank">
                                <img src="" class="w-100">
                            </a>
                        </div>
                        <div class="simplelightbox-gallery">
                            <a href="" target="_blank">
                                <img src="" class="w-100">
                            </a>
                        </div>
                        <li class="my-4"><strong>Make sure you enter valid data as per instructions before
                                proceeding.</strong></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
