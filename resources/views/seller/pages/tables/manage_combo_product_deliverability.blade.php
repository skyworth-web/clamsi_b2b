@extends('seller/layout')
@section('title')
    {{ labels('admin_labels.manage_product_deliverability', 'Deliverability Manage') }}
@endsection
@section('content')
    <x-seller.breadcrumb :title="labels('admin_labels.manage_product_deliverability', 'Deliverability Manage')" :subtitle="labels(
        'admin_labels.elevate_your_store_with_seamless_deliverability_management',
        'Elevate Your Store with Seamless Product Deliverability Management',
    )" :breadcrumbs="[['label' => labels('admin_labels.manage_product_deliverability', 'Deliverability Manage')]]" />
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-12 col-xl-12 mt-xl-0 mt-md-2">
                <section class="overview-data">
                    <div class="card content-area p-4 ">
                        <div class="row align-items-center d-flex heading mb-5">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <h4>{{ labels('admin_labels.manage_product_deliverability', 'Deliverability Manage') }}
                                        </h4>
                                    </div>

                                    <div class="col-sm-12 d-flex justify-content-end mt-md-0 mt-sm-2">
                                        <div class="input-group me-2 search-input-grp ">
                                            <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                            <input type="text" data-table="seller_combo_deliverability_table"
                                                class="form-control searchInput" placeholder="Search...">
                                            <span
                                                class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                                        </div>
                                        <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                            data-bs-target="#columnFilterOffcanvas"
                                            data-table="seller_combo_deliverability_table" StatusFilter='true'><i
                                                class='bx bx-filter-alt'></i></a>
                                        <a class="btn me-2" id="tableRefresh"
                                            data-table="seller_combo_deliverability_table"><i class='bx bx-refresh'></i></a>
                                        <div class="dropdown">
                                            <a class="btn dropdown-toggle export-btn" type="button"
                                                id="exportOptionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class='bx bx-download'></i>
                                            </a>
                                            <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                                <li><button class="dropdown-item" type="button"
                                                        onclick="exportTableData('seller_combo_deliverability_table','csv')">CSV</button>
                                                </li>
                                                <li><button class="dropdown-item" type="button"
                                                        onclick="exportTableData('seller_combo_deliverability_table','json')">JSON</button>
                                                </li>
                                                <li><button class="dropdown-item" type="button"
                                                        onclick="exportTableData('seller_combo_deliverability_table','sql')">SQL</button>
                                                </li>
                                                <li><button class="dropdown-item" type="button"
                                                        onclick="exportTableData('seller_combo_deliverability_table','excel')">Excel</button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <button type="button"
                                    class="btn btn-outline-primary btn-sm bulk_update_combo_deliverability_data"
                                    data-table-id="seller_combo_deliverability_table"
                                    data-url="{{ route('seller.combo.deliverability.bulk.update') }}">{{ labels('admin_labels.bulk_update', 'Bulk Update') }}</button>
                            </div>
                            <div class="col-md-12">
                                <div class="pt-0">
                                    <div class="table-responsive">
                                        <table class='table' id="seller_combo_deliverability_table" data-toggle="table"
                                            data-loading-template="loadingTemplate"
                                            data-url="{{ route('seller.combo.product.deliverability.list') }}"
                                            data-click-to-select="true" data-side-pagination="server" data-pagination="true"
                                            data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false"
                                            data-show-columns="false" data-show-refresh="false" data-trim-on-search="false"
                                            data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                                            data-toolbar="" data-show-export="false" data-maintain-selected="true"
                                            data-export-types='["txt","excel"]' data-query-params="brand_query_params">
                                            <thead>
                                                <tr>
                                                    <th data-checkbox="true" data-field="delete-checkbox">
                                                        <input name="select_all" type="checkbox">
                                                    </th>
                                                    <th data-field="id" data-sortable="true" data-visible="true">
                                                        {{ labels('admin_labels.id', 'ID') }}
                                                    <th class="d-flex justify-content-center" data-field="image"
                                                        data-sortable="false">
                                                        {{ labels('admin_labels.image', 'Image') }}
                                                    </th>
                                                    <th data-field="name" data-disabled="1" data-sortable="false">
                                                        {{ labels('admin_labels.name', 'Name') }}
                                                    </th>
                                                    <th data-field="operate" data-sortable="false">
                                                        {{ labels('admin_labels.action', 'Action') }}
                                                    </th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
    <!-- Deliverability Modal -->
    <div class="modal fade" id="deliverabilityModal" tabindex="-1" aria-labelledby="deliverabilityModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deliverabilityModalLabel">Manage Deliverability</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="combodeliverabilityForm">
                    <div class="modal-body">
                        <input type="hidden" id="product_id" name="product_id">

                        <div class="mb-3">
                            <label for="deliverable_type" class="form-label">Deliverable Type</label>
                            <select class="form-select" name="deliverable_type" id="deliverable_type">
                                <option value="0">None</option>
                                <option value="1" class="all_deliverable_type">All</option>
                                <option value="2">specific</option>
                                {{-- <option value="3">Excluded</option> --}}
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="deliverable_zones" class="form-label" disabled>Deliverable Zones</label>
                            <select class="form-select search_seller_zone" name="deliverable_zones[]"
                                id="deliverable_zones" multiple>
                            </select>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
