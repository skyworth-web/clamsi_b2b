@php
    $bread_crumb['page_main_bread_crumb'] = labels('front_messages.support', 'Support');
@endphp
<div id="page-content">
    <x-utility.breadcrumbs.breadcrumbTwo :$bread_crumb />
    <div class="container-fluid">
        <div class="row">
            <x-utility.my_account_slider.account_slider :$user_info />
            <div class="col-12 col-sm-12 col-md-12 col-lg-9">
                <div class="dashboard-content h-100">
                    <div class="h-100" id="profile">
                        <div class="top-sec d-flex-justify-center justify-content-between mb-4">
                            <div class="d-flex-center">
                                <h2 class="mb-0">{{ labels('front_messages.tickets', 'Tickets') }}</h2>
                                <p class="fs-6 m-0 ms-3">
                                    {{ labels('front_messages.total', 'Total') }}:<b>{{ $tickets->total() }}</b></p>
                            </div>
                            <button wire:ignore type="button" class="btn btn-primary btn-sm AddNewTicket"
                                data-bs-toggle="modal" data-bs-target="#AddNewTicket"><ion-icon name="add-outline"
                                    class="me-1 fs-5"></ion-icon>
                                {{ labels('front_messages.edit', 'Add New Ticket') }}</button>
                        </div>
                        <div class="profile-book-section mb-4">
                            <table>
                                <thead>
                                    <th>{{ labels('front_messages.no', 'No') }}.</th>
                                    <th>#id</th>
                                    <th>{{ labels('front_messages.title', 'Title') }}</th>
                                    <th>{{ labels('front_messages.status', 'Status') }}</th>
                                    <th>{{ labels('front_messages.created_date', 'Created Date') }}</th>
                                    <th>{{ labels('front_messages.updated_date', 'Updated Date') }}</th>
                                    <th>{{ labels('front_messages.action', 'Action') }}</th>
                                </thead>
                                <tbody class="ticket_tbody">
                                    @foreach ($tickets as $key => $ticket)
                                        <tr class="ticket_card">
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $ticket->id }}</td>
                                            <td class="fs-6 fw-500">{{ $ticket->subject }}</td>
                                            <td class="d-flex-center">
                                                @if ($ticket->status == 0)
                                                    <div class="circle-status in-review-status"></div>
                                                    <p class="ticket_status">
                                                        {{ labels('front_messages.in_review', 'In Review') }}</p>
                                                @elseif ($ticket->status == 2)
                                                    <div class="circle-status open-status"></div>
                                                    <p class="ticket_status">
                                                        {{ labels('front_messages.opened', 'Opened') }}</p>
                                                @elseif ($ticket->status == 3)
                                                    <div class="circle-status resolved-status"></div>
                                                    <p class="ticket_status">
                                                        {{ labels('front_messages.resolved', 'Resolved') }}</p>
                                                @elseif ($ticket->status == 4)
                                                    <div class="circle-status close-status"></div>
                                                    <p class="ticket_status">
                                                        {{ labels('front_messages.closed', 'Closed') }}</p>
                                                @elseif ($ticket->status == 5)
                                                    <div class="circle-status reopen-status"></div>
                                                    <p class="ticket_status">
                                                        {{ labels('front_messages.reopened', 'Reopened') }}</p>
                                                @endif
                                            </td>
                                            <td>{{ $ticket->created_at }}</td>
                                            <td>{{ $ticket->updated_at }}</td>
                                            <td><ion-icon wire:ignore class="fs-5 AddNewTicket cursor-pointer"
                                                    data-ticket-id='{{ $ticket->id }}' name="pencil-sharp"
                                                    data-bs-toggle="modal" data-bs-target="#AddNewTicket"></ion-icon>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <!--End Product Grid-->
                            <div class="d-flex justify-content-between align-content-center">
                                {{ $tickets->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div wire:ignore.self class="modal fade" id="AddNewTicket" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5 add_new_ticket" id="exampleModalLabel">
                        {{ labels('front_messages.add_new_ticket', 'Add New Ticket') }}</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row gap-2">
                        <label for="ticket_type"
                            class="spr-form-label">{{ labels('front_messages.ticket_type', 'Ticket Type') }}
                            <select name="ticket_type" id="ticket_type">
                                <option value="">{{ labels('front_messages.select_ticket', 'Select Ticket') }}
                                </option>
                                @foreach ($ticket_types as $ticket_type)
                                    <option value="{{ $ticket_type->id }}" title="{{ $ticket_type->title }}">
                                        {{ \Illuminate\Support\Str::limit($ticket_type->title, 80) }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <label for="ticket_email" class="spr-form-label">{{ labels('front_messages.email', 'Email') }}
                            <input type="email" name="ticket_email" id="ticket_email" placeholder="Write Your Email">
                        </label>
                        <label for="ticket_subject"
                            class="spr-form-label">{{ labels('front_messages.subject', 'Subject') }}
                            <input type="text" name="ticket_subject" id="ticket_subject" placeholder="Subject">
                        </label>
                        <label for="ticket_description"
                            class="spr-form-label">{{ labels('front_messages.description', 'Description') }}
                            <textarea type="text" name="ticket_description" id="ticket_description" placeholder="Description"></textarea>
                        </label>
                        <input type="hidden" name="ticket_id" id="ticket_id">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">{{ labels('front_messages.close', 'Close') }}</button>
                    <button type="submit"
                        class="btn btn-primary add_ticket_btn">{{ labels('front_messages.add', 'Add') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
