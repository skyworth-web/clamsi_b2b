@php
    $bread_crumb['page_main_bread_crumb'] = labels('front_messages.notifications', 'Notifications');
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
                                <table wire:ignore class='table' id="natifications_table" data-toggle="table"
                                    data-loading-template="loadingTemplate"
                                    data-url="{{ route('my-account.get_notifications') }}" data-click-to-select="true"
                                    data-side-pagination="server" data-pagination="true"
                                    data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                                    data-show-columns="false" data-show-refresh="true" data-trim-on-search="false"
                                    data-search-highlight="true" data-sort-name="id" data-sort-order="desc"
                                    data-mobile-responsive="true" data-toolbar="" data-show-export="false"
                                    data-maintain-selected="true" data-export-types='["txt","excel","csv"]'
                                    data-query-params="get_notification">
                                    <thead>
                                        <tr>
                                            <th data-field="id" data-sortable="true">
                                                {{ labels('admin_labels.id', 'ID') }}
                                            </th>
                                            <th data-field="title" data-disabled="1" data-sortable="false">
                                                {{ labels('admin_labels.title', 'Title') }}
                                            </th>
                                            <th data-field="type" data-sortable="false">
                                                {{ labels('admin_labels.type', 'Type') }}
                                            </th>
                                            <th class="d-flex justify-content-center" data-field="image"
                                                data-sortable="false" class="col-md-5">
                                                {{ labels('admin_labels.image', 'Image') }}
                                            </th>
                                            <th data-field="link" data-sortable="false" class="col-md-5">
                                                {{ labels('admin_labels.link', 'Link') }}
                                            </th>
                                            <th data-field="message" data-sortable="false">
                                                {{ labels('admin_labels.message', 'Message') }}
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
    function get_notification(p) {
        return {
            sort: p.sort,
            order: p.order,
            limit: p.limit,
            offset: p.offset,
            search: p.search
        }
    }
</script>
