@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.update_custom_message', 'Update Custom Message') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.custom_message', 'Custom Message')" :subtitle="labels(
        'admin_labels.craft_personalized_messages_with_custom_message_management',
        'Craft Personalized Messages with Custom Message Management',
    )" :breadcrumbs="[['label' => labels('admin_labels.custom_message', 'Custom Message')]]" />


    <div class="card">
        <div class="row">
            <form class="form-horizontal submit_form" action="{{ url('/admin/custom_message/update/' . $data->id) }}"
                method="POST" enctype="multipart/form-data">
                @method('PUT')
                @csrf
                <div class="card-body">
                    <h5 class="mb-3">
                        {{ labels('admin_labels.update_custom_message', 'Update Custom Message') }}
                    </h5>
                    @php
                        $type = [
                            'place_order',
                            'settle_cashback_discount',
                            'settle_seller_commission',
                            'customer_order_received',
                            'customer_order_processed',
                            'customer_order_shipped',
                            'customer_order_delivered',
                            'customer_order_cancelled',
                            'customer_order_returned',
                            'customer_order_returned_request_decline',
                            'customer_order_returned_request_approved',
                            'delivery_boy_order_deliver',
                            'wallet_transaction',
                            'ticket_status',
                            'ticket_message',
                            'bank_transfer_receipt_status',
                            'bank_transfer_proof',
                        ];
                    @endphp

                    <div class="form-group row">
                        <label for="type" class="col-sm-2 form-label">{{ labels('admin_labels.type', 'Type') }}<span
                                class='text-danger text-sm'>
                                *</span></label>
                        <div class="col-sm-12">
                            <select name="type" class="form-control form-select custom_message_type">
                                <option value="">Select Type</option>
                                @foreach ($type as $row)
                                    <option value="{{ $row }}" {{ $row == $data->type ? 'selected' : '' }}>
                                        {{ ucwords(str_replace('_', ' ', $row)) }}
                                    </option>
                                @endforeach

                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="title" class="col-sm-2 form-label">{{ labels('admin_labels.title', 'Title') }}
                            <span class='text-asterisks text-sm'>*</span></label>
                        <div class="col-sm-12">
                            <input type="text" name="title" id="custom_message_title"
                                class="form-control custom_message_title" placeholder="Title"
                                value="{{ isset($data->title) ? $data->title : '' }}" />
                        </div>
                    </div>
                    <div class="form-group row place_order d-none">

                        @php
                            $hashtag = ['< order_id >'];
                        @endphp

                        @foreach ($hashtag as $row)
                            <div class="col">
                                <div class="hashtag_input">{{ $row }}</div>
                            </div>
                        @endforeach

                    </div>
                    <div class="form-group row">
                        <label for="message" class="col-sm-2 form-label">
                            {{ labels('admin_labels.message', 'Message') }}<span
                                class='text-asterisks text-sm'>*</span></label>
                        <div class="col-sm-12">
                            <textarea name="message" id="text-box" class="form-control" placeholder="Place some text here">{{ isset($data->message) ? $data->message : '' }}</textarea>
                        </div>
                    </div>
                    <div class="form-group row place_order d-none">


                        @php
                            $hashtag = ['< application_name >'];
                        @endphp

                        @foreach ($hashtag as $row)
                            <div class="col">
                                <div class="hashtag">{{ $row }}</div>
                            </div>
                        @endforeach

                    </div>
                    <div class="form-group row settle_cashback_discount d-none">

                        @php
                            $hashtag = ['< customer_name >', '< application_name >'];
                        @endphp

                        @foreach ($hashtag as $row)
                            <div class="col">
                                <div class="hashtag">{{ $row }}</div>
                            </div>
                        @endforeach

                    </div>
                    <div class="form-group row settle_seller_commission d-none">

                        @php
                            $hashtag = ['< customer_name >', '< application_name >'];
                        @endphp

                        @foreach ($hashtag as $row)
                            <div class="col">
                                <div class="hashtag">{{ $row }}</div>
                            </div>
                        @endforeach

                    </div>
                    <div class="form-group row customer_order_received d-none">

                        @php
                            $hashtag = ['< customer_name >', '< order_item_id >', '< application_name >'];
                        @endphp

                        @foreach ($hashtag as $row)
                            <div class="col">
                                <div class="hashtag">{{ $row }}</div>
                            </div>
                        @endforeach

                    </div>
                    <div class="form-group row customer_order_processed d-none">

                        @php
                            $hashtag = ['< customer_name >', '< order_item_id >', '< application_name >'];
                        @endphp

                        @foreach ($hashtag as $row)
                            <div class="col">
                                <div class="hashtag">{{ $row }}</div>
                            </div>
                        @endforeach

                    </div>
                    <div class="form-group row customer_order_shipped d-none">

                        @php
                            $hashtag = ['< customer_name >', '< order_item_id >', '< application_name >'];
                        @endphp

                        @foreach ($hashtag as $row)
                            <div class="col">
                                <div class="hashtag">{{ $row }}</div>
                            </div>
                        @endforeach

                    </div>
                    <div class="form-group row customer_order_delivered d-none">

                        @php
                            $hashtag = ['< customer_name >', '< order_item_id >', '< application_name >'];
                        @endphp

                        @foreach ($hashtag as $row)
                            <div class="col">
                                <div class="hashtag">{{ $row }}</div>
                            </div>
                        @endforeach

                    </div>
                    <div class="form-group row customer_order_cancelled d-none">

                        @php
                            $hashtag = ['< customer_name >', '< order_item_id >', '< application_name >'];
                        @endphp

                        @foreach ($hashtag as $row)
                            <div class="col">
                                <div class="hashtag">{{ $row }}</div>
                            </div>
                        @endforeach

                    </div>
                    <div class="form-group row customer_order_returned d-none">

                        @php
                            $hashtag = ['< customer_name >', '< order_item_id >', '< application_name >'];
                        @endphp

                        @foreach ($hashtag as $row)
                            <div class="col">
                                <div class="hashtag">{{ $row }}</div>
                            </div>
                        @endforeach

                    </div>
                    <div class="form-group row customer_order_returned_request_approved d-none">

                        @php
                            $hashtag = ['< customer_name >', '< order_item_id >', '< application_name >'];
                        @endphp

                        @foreach ($hashtag as $row)
                            <div class="col">
                                <div class="hashtag">{{ $row }}</div>
                            </div>
                        @endforeach

                    </div>
                    <div class="form-group row customer_order_returned_request_decline d-none">

                        @php
                            $hashtag = ['< customer_name >', '< order_item_id >', '< application_name >'];
                        @endphp

                        @foreach ($hashtag as $row)
                            <div class="col">
                                <div class="hashtag">{{ $row }}</div>
                            </div>
                        @endforeach

                    </div>
                    <div class="form-group row wallet_transaction d-none">

                        @php
                            $hashtag = ['< currency >', '< returnable_amount >'];
                        @endphp

                        @foreach ($hashtag as $row)
                            <div class="col">
                                <div class="hashtag">{{ $row }}</div>
                            </div>
                        @endforeach

                    </div>
                    <div class="form-group row ticket_status d-none">

                        @php
                            $hashtag = ['< application_name >'];
                        @endphp

                        @foreach ($hashtag as $row)
                            <div class="col">
                                <div class="hashtag">{{ $row }}</div>
                            </div>
                        @endforeach

                    </div>
                    <div class="form-group row ticket_message d-none">

                        @php
                            $hashtag = ['< application_name >'];
                        @endphp

                        @foreach ($hashtag as $row)
                            <div class="col">
                                <div class="hashtag">{{ $row }}</div>
                            </div>
                        @endforeach

                    </div>
                    <div class="form-group row bank_transfer_receipt_status d-none">

                        @php
                            $hashtag = ['< status >', '< order_id >'];
                        @endphp

                        @foreach ($hashtag as $row)
                            <div class="col">
                                <div class="hashtag">{{ $row }}</div>
                            </div>
                        @endforeach

                    </div>
                    <div class="form-group row bank_transfer_proof d-none">

                        @php
                            $hashtag = ['< order_id >', '< application_name >'];
                        @endphp

                        @foreach ($hashtag as $row)
                            <div class="col">
                                <div class="hashtag">{{ $row }}</div>
                            </div>
                        @endforeach

                    </div>
                    <div class="mb-3 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary submit_button"
                            id="">{{ labels('admin_labels.update_custom_message', 'Update Custom Message') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
