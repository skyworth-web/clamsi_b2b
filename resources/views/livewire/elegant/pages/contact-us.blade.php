@php
    $title = labels('front_messages.contact_us', 'Contact Us');
@endphp
<div id="page-content">
    <x-utility.breadcrumbs.breadcrumbOne :breadcrumb="$title" />
    <div class="container-fluid contact-style1">
        <div class="contact-form-details section pt-0">
            <div class="row">
                <div class="col-12 col-sm-12 col-md-8 col-lg-8">
                    <div class="formFeilds contact-form form-vertical mb-4 mb-md-0">
                        <div class="section-header">
                            <h2>{{ labels('front_messages.lets_get_in_touch', "Let's Get in touch!") }}</h2>
                            <p>{!! nl2br($contact_us['contact_us']) !!}</p>
                        </div>
                        @if ($errors->has('mailError'))
                            <p class="fw-400 text-danger mt-1">{{ $errors->first('mailError') }}</p>
                        @endif
                        <form wire:submit="send_contact_us_email" id="contact-form" class="contact-form">
                            <div class="form-row">
                                <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                                    <div class="form-group">
                                        <input wire:model="name" type="text" class="form-control"
                                            placeholder="{{ labels('front_messages.name', 'Name') }}" />
                                        @error('name')
                                            <p class="fw-400 text-danger mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                                    <div class="form-group">
                                        <input wire:model="email" type="email" class="form-control"
                                            placeholder="{{ labels('front_messages.email', 'Email') }}" />
                                        @error('email')
                                            <p class="fw-400 text-danger mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <input wire:model="subject" type="text" class="form-control"
                                            placeholder="{{ labels('front_messages.subject', 'Subject') }}" />
                                        @error('subject')
                                            <p class="fw-400 text-danger mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                    <div class="form-group">
                                        <textarea wire:model="message" class="form-control" rows="5"
                                            placeholder="{{ labels('front_messages.message', 'Message') }}"></textarea>
                                        @error('message')
                                            <p class="fw-400 text-danger mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                    <div class="form-group mailsendbtn mb-0 w-100">
                                        <button
                                            class="btn btn-lg">{{ labels('front_messages.send_message', 'Send Message') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-12 col-sm-12 col-md-4 col-lg-4">
                    <div class="contact-details bg-block">
                        <h3 class="mb-3 fs-5">{{ labels('front_messages.store_information', 'Store information') }}
                        </h3>
                        <ul class="list-unstyled">
                            <li class="mb-2 address">
                                <strong class="d-block mb-2">{{ labels('front_messages.address', 'Address') }}
                                    :</strong>
                                <p><i class="icon anm anm-map-marker-al me-2 d-none"></i>{{ $web_settings['address'] }}
                                </p>
                            </li>
                            <li class="mb-2 phone"><strong>{{ labels('front_messages.mobile', 'Phone') }} :</strong><i
                                    class="icon anm anm-phone me-2 d-none"></i> <a
                                    href="tel:{{ $web_settings['support_number'] }}">{{ $web_settings['support_number'] }}</a>
                            </li>
                            <li class="mb-0 email"><strong>{{ labels('front_messages.email', 'Email') }} :</strong><i
                                    class="icon anm anm-envelope-l me-2 d-none"></i> <a
                                    href="mailto:{{ $web_settings['support_email'] }}">{{ $web_settings['support_email'] }}</a>
                            </li>
                        </ul>
                        <hr>
                        <div class="follow-us">
                            <label for=""
                                class="d-block mb-3"><strong>{{ labels('front_messages.stay_connected', 'Stay Connected') }}</strong></label>
                            <ul class="list-inline social-icons mt-3">
                                <li class="list-inline-item"><a href="{{ $web_settings['facebook_link'] }}"
                                        target="_blank" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="Facebook"><i class="anm anm-facebook hdr-icon icon"></i></a>
                                </li>
                                <li class="list-inline-item"><a href="{{ $web_settings['twitter_link'] }}"
                                        target="_blank" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="Twitter"><i class="anm anm-twitter hdr-icon icon"></i></a>
                                </li>
                                <li class="list-inline-item"><a href="{{ $web_settings['instagram_link'] }}"
                                        target="_blank" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="Instagram"><i class="anm anm-instagram hdr-icon icon"></i></a>
                                </li>
                                <li class="list-inline-item"><a href="{{ $web_settings['youtube_link'] }}"
                                        target="_blank" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="Youtube"><i class="anm anm-youtube hdr-icon icon"></i></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
