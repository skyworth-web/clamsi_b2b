@php
    $bread_crumb['page_main_bread_crumb'] = labels('front_messages.transactions', 'Transactions');
@endphp
<div id="page-content">
    <x-utility.breadcrumbs.breadcrumbTwo :$bread_crumb />
    <div class="container-fluid">
        <div class="row">
            <x-utility.my_account_slider.account_slider :$user_info />
            <div class="col-12 col-sm-12 col-md-12 col-lg-9">
                <div class="row">
                    <div class="col-md-12">
                        <div class="pt-0">
                            <div class="table-responsive" wire:ignore>
                                <table class='table' id="user_transactions" data-toggle="table"
                                    data-loading-template="loadingTemplate"
                                    data-url="{{ route('my-account.get_transaction') }}" data-click-to-select="true"
                                    data-side-pagination="server" data-pagination="true"
                                    data-page-list="[5, 10, 20, 50]" data-search="true" data-show-columns="true"
                                    data-show-refresh="true" data-trim-on-search="false" data-search-highlight="true"
                                    data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                                    data-toolbar="" data-show-export="true" data-maintain-selected="true"
                                    data-export-types='["txt","excel","csv"]' data-query-params="get_transactions">
                                    <thead>
                                        <tr>
                                            <th data-field="id" data-sortable="true">
                                                {{ labels('front_messages.id', 'ID') }}
                                            </th>
                                            <th data-field="order_id" data-sortable="false">
                                                {{ labels('front_messages.order_id', 'Order ID') }}
                                            </th>
                                            <th data-field="txn_id" data-sortable="false">
                                                {{ labels('front_messages.transaction_id', 'Transaction ID') }}
                                            </th>
                                            <th data-field="type" data-sortable="false">
                                                {{ labels('front_messages.transaction_type', 'Transaction Type') }}
                                            </th>
                                            <th data-field="payu_txn_id" data-sortable="false" data-visible="false">
                                                {{ labels('front_messages.pay_transaction_id', 'Pay Transaction ID') }}
                                            </th>
                                            <th data-field="amount" data-sortable="false">
                                                {{ labels('front_messages.amount', 'Amount') }}
                                            </th>
                                            <th data-field="status" data-sortable="false">
                                                {{ labels('front_messages.status', 'Status') }}
                                            </th>
                                            <th data-field="message" data-sortable="false" data-visible="false">
                                                {{ labels('front_messages.message', 'Message') }}
                                            </th>
                                            <th data-field="created_at" data-sortable="true">
                                                {{ labels('front_messages.date', 'Date') }}
                                            </th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function get_transactions(p) {
        return {
            sort: p.sort,
            limit: p.limit,
            order: p.order,
            offset: p.offset,
            search: p.search
        }
    }
</script>
