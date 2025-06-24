@extends('seller/layout')
@section('title')
    <?php echo 'Categories'; ?>
@endsection
@section('content')
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

    <div class="container mt-5">
        <table id='seller_category_table' data-toggle="table" data-loading-template="loadingTemplate" data-url="{{ route('seller_categories.list') }}"
            data-click-to-select="true" data-side-pagination="server" data-pagination="true"
            data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false" data-show-columns="false" data-show-refresh="false"
            data-trim-on-search="false" data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
            data-toolbar="" data-show-export="false" data-maintain-selected="true"
            data-export-types='["txt","excel","pdf","csv"]'
            data-export-options='{
        "fileName": "categories-list",
        "ignoreColumn": ["action"]
    }'
            data-query-params="category_query_params">
            <thead>
                <tr>
                    <th data-field="expand_button" data-formatter="expandButtonFormatter"
                        data-width="30">Expand</th>
                    <th data-field="id" data-sortable="true" data-visible='true'>{{ labels('admin_labels.id', 'ID') }}</th>
                    <th data-field="name" data-sortable="false" data-disabled="1">
                        {{ labels('admin_labels.name', 'Name') }}</th>
                    <th class="d-flex justify-content-center" data-field="image" data-sortable="false" >
                        {{ labels('admin_labels.image', 'Image') }}</th>
                    <th data-field="banner" data-sortable="false" >
                        {{ labels('admin_labels.banner_image', 'Banner Image') }}</th>
                    <th data-field="status" data-sortable="false" >
                        {{ labels('admin_labels.status', 'Status') }}</th>
                </tr>
            </thead>
        </table>
    </div>

    <script>
        $(document).ready(function() {
            $('#seller_category_table').on('click-row.bs.table', function(e, row, $element) {
                // Check if the row has subcategories and if it has not been expanded before
                if (row.subcategories && row.subcategories.length > 0 && !$element.next().hasClass(
                        'subcategories-row')) {
                    var subtable = '<table class="table table-bordered">';
                    subtable += '<tbody>';
                    row.subcategories.forEach(function(subcategory) {
                        subtable += '<tr>';
                        subtable += '<td>' + subcategory.id + '</td>';
                        subtable += '<td>' + subcategory.name + '</td>';
                        subtable += '<td><img src="' + subcategory.image +
                            '" alt="Subcategory Image" class="rounded table-image"/></td>';
                        subtable += '<td><img src="' + subcategory.banner +
                            '" alt="Subcategory Banner" class="rounded table-image"/></td>';
                        subtable += '<td>' + subcategory.status + '</td>';
                        // Add other subcategory fields as needed
                        subtable += '</tr>';
                    });
                    subtable += '</tbody></table>';

                    // Add a class to the clicked row to indicate that it has been expanded
                    $element.addClass('expanded-row');

                    // Add expand/collapse toggle button to the first column of the parent row
                    var expandButton =
                        '<button class="btn btn-sm btn-info expand-btn" data-toggle="tooltip" title="Collapse" data-expanded="true">-</button>';
                    $element.find('td:first').prepend(expandButton);

                    // Update the content of the clicked row
                    $element.after('<tr class="subcategories-row"><td colspan="5">' + subtable +
                        '</td></tr>');

                    // Initialize Bootstrap Table for the subcategories
                    var subTable = $element.next().find('table');
                    subTable.bootstrapTable({
                        data: row.subcategories,
                        columns: [{
                                field: 'id',
                                title: '',
                                sortable: true,
                                visible: false
                            }, // Hide the ID column
                            {
                                field: 'name',
                                title: '',
                                sortable: true
                            },
                            {
                                field: 'image',
                                title: '',
                                sortable: true
                            },
                            {
                                field: 'banner',
                                title: '',
                                sortable: true
                            },
                            {
                                field: 'status',
                                title: '',
                                sortable: true
                            },
                        ]
                    });

                    // Toggle expand/collapse on button click
                    $element.find('.expand-btn').on('click', function() {
                        var isExpanded = $(this).data('expanded');
                        if (isExpanded === true) {
                            // Collapse the row when the button is clicked
                            $element.next('.subcategories-row').hide();
                            $(this).html('+').attr('title', 'Expand').data('expanded', false);
                        } else {
                            // Expand the row when the button is clicked
                            $element.next('.subcategories-row').show();
                            $(this).html('-').attr('title', 'Collapse').data('expanded', true);
                        }
                    });

                    // Tooltip initialization
                    $('[data-toggle="tooltip"]').tooltip();
                }
            });

            // Add data-query-params function for sending additional parameters to the server
            window.category_query_params = function(params) {
                return {
                    limit: params.limit,
                    offset: params.offset,
                    sort: params.sort,
                    order: params.order,
                    search: params.search,
                };
            };
        });
    </script>
@endsection
