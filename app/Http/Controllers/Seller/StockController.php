<?php

namespace App\Http\Controllers\Seller;

use App\Models\Attribute_values;
use App\Models\ComboProduct;
use App\Models\Product_variants;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class StockController extends Controller
{
    public function index()
    {
        $user_id = Auth::user()->id;

        $seller_id = Seller::where('user_id', $user_id)->value('id');

        return view('seller.pages.tables.manage_stock', compact('seller_id'));
    }

    public function get_stock_List()
    {

        $store_id = getStoreId();
        $user_id = Auth::user()->id;
        $seller_id = Seller::where('user_id', $user_id)->value('id');

        $filters['show_only_stock_product'] = true;

        $offset =  request()->query('search') || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = request()->query('limit', 10);
        $sort = request()->query('sort', 'id');
        $order = (request('order')) ? request('order') : "DESC";
        $category_id = request()->query('category_id');
        $filters['search'] = request()->query('search');

        $products = fetchProduct("", $filters, "", $category_id, '', '', $sort, $order, "", "", $seller_id, '', $store_id);

        $total = $products['total'];
        $bulkData = $rows = [];



        foreach ($products['product'] as $product) {
            $category_id = $product->category_id;
            $category_name = fetchDetails('categories', ['id' => $category_id], ['name', 'id']);

            $variants = getVariantsValuesByPid($product->id);

            // Handle the case when the stock type is 2 (multiple variants)
            if ($product->stock_type == 2) {
                foreach ($variants as $variant) {
                    $tempRow = createRow($product, $variant, $category_name, '1');
                    $rows[] = $tempRow;
                }
            } else {
                // Handle the case when the stock type is 0 or 1
                $variant = reset($variants); // Assuming there is at least one variant
                $tempRow = createRow($product, $variant, $category_name, '1');
                $rows[] = $tempRow;
            }
        }

        $pagedRows = array_slice($rows, $offset, $limit);
        $bulkData['rows'] = $pagedRows;
        $bulkData['total'] = count($rows);

        return response()->json($bulkData);
    }

    public function edit($id)
    {

        $store_id = getStoreId();

        $stock = Product_variants::select('stock', 'product_id', 'attribute_value_ids')
            ->where('id', $id)
            ->first();

        if ($stock) {
            $attributeValue = Attribute_values::select('value')
                ->where('id', $stock->attribute_value_ids)
                ->first();

            $productId = $stock->product_id;

            $fetched_data = fetchProduct("", "", $productId, "", "", "", "", "", "", "", "", '', $store_id);
            $fetched = $stock->stock;
            $attribute = isset($attributeValue->value) ? $attributeValue->value : '';
        }


        $variant = Product_variants::find($id);

        $attributeValue = isset($attribute) && !empty($attribute) ? $attribute : "";

        $productName = isset($fetched_data['product'][0]->name) && !empty($fetched_data['product'][0]->name) ? $fetched_data['product'][0]->name : '';

        $stockType = isset($fetched_data['product'][0]->stock_type) && $fetched_data['product'][0]->stock_type != 1 ? $fetched_data['product'][0]->name : '';

        $pro_image = isset($fetched_data['product'][0]->image) && !empty($fetched_data['product'][0]->image) ? $fetched_data['product'][0]->image : '';

        $pname = isset($attributeValue) && !empty($attributeValue) && isset($stockType) && !empty($stockType) ? $productName . " - " . $attributeValue : $productName;

        $stock = isset($fetched_data['product'][0]->stock) && $fetched_data['product'][0]->stock != '' ? $fetched_data['product'][0]->stock : $fetched;

        if (!$variant) {
            return response()->json(['error' => true, 'message' => labels('admin_labels.data_not_found', 'Data Not Found')], 404);
        }

        $data = [
            'product_name' => $pname,
            'stock' => $stock,
            'variant' => $variant,
            'pro_image' => $pro_image,
        ];

        return response()->json($data);
    }

    public function manage_combo_stock()
    {
        return view('seller.pages.tables.manage_combo_stock');
    }

    public function update(Request $request, $id)
    {
        $variant = Product_variants::find($id);
        if (!$variant) {
            return response()->json(['error' => true, 'message' => labels('admin_labels.data_not_found', 'Data Not Found')], 404);
        } else {
            $rules = [
                'stock' => 'required',
                'quantity' => 'required',
                'type' => 'required',
            ];

            if ($response = validatePanelRequest($request, $rules)) {
                return $response;
            }
            if ($request->type == 'add') {

                updateStock($id, (int)$request->quantity, 'plus');
                if ($request->ajax()) {
                    return response()->json(['message' => labels('admin_labels.stock_updated_successfully', 'Stock updated successfully')]);
                }
            } else {
                if ($request->type == 'subtract') {
                    if (
                        $request->quantity > $request->stock
                    ) {
                        return response()->json(['error' => 'true', 'message' => labels('admin_labels.subtracted_stock_greater_than_current_stock', 'Subtracted stock cannot be greater than current stock')]);
                    }
                }

                updateStock($id,  $request->quantity);
                if ($request->ajax()) {
                    return response()->json(['message' => labels('admin_labels.stock_updated_successfully', 'Stock updated successfully')]);
                }
            }
        }
    }

    public function combo_stock_list()
    {
        $store_id = getStoreId();
        $filters['show_only_stock_product'] = true;
        $filters['search'] = request()->query('search');
        $offset =  $filters['search'] || (request('pagination_offset')) ? (request('pagination_offset')) : 0;

        $limit = request()->query('limit', 10);
        $sort = request()->query('sort', 'id');
        $order = request()->query('order', 'ASC');

        $user_id = Auth::user()->id;
        $seller_id = Seller::where('user_id', $user_id)->value('id');

        $products = fetchComboProduct("", $filters, "", $limit, $offset, $sort, $order, "", "", $seller_id, $store_id);
        $total = !empty($products['combo_product']) ? $products['total'] : '0';
        $bulkData = ['total' => $total, 'rows' => []];

        foreach ($products['combo_product'] as $product) {
            $action = '<div class="dropdown bootstrap-table-dropdown">
            <a href="#" class="text-dark" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="bx bx-dots-horizontal-rounded"></i>
            </a>
            <div class="dropdown-menu table_dropdown stock_action_dropdown" aria-labelledby="dropdownMenuButton">
                <a class="dropdown-item edit-combo-stock" title="Edit" data-id="' . $product->id . '" data-bs-toggle="modal" data-bs-target="#edit_modal"><i class="bx bx-pencil"></i> Edit</a>
            </div>
        </div>';
            $image = route('seller.dynamic_image', [
                'url' => getMediaImageUrl($product->image),
                'width' => 60,
                'quality' => 90
            ]);
            $stock_status = $product->availability == 1 ? '<label class="badge bg-success">In Stock</label>' : '<label class="badge bg-danger">Out of Stock</label>';
            $tempRow = [
                'id' => $product->id,
                'price' => $product->special_price . ' ' . '<strike>' . $product->price . '</strike>',
                'stock_count' => $product->stock != 0 ? $product->stock :  $product->stock,
                'stock_status' => $stock_status,
                'name' => '<div class="d-flex"><a href="' . getMediaImageUrl($product->image) . '" data-lightbox="image-' . $product->id . '"><img src=' . $image . ' class="rounded mx-2"></a><div class="ms-2"><p class="m-0">' . $product->title . '</p></div></div>',
                'operate' => $action
            ];

            $bulkData['rows'][] = $tempRow;
        }

        return response()->json($bulkData);
    }

    public function combo_stock_edit($id)
    {
        $product = ComboProduct::find($id);

        if (!$product) {
            return response()->json(['error' => true, 'message' => labels('admin_labels.data_not_found', 'Data Not Found')], 404);
        }

        return response()->json($product);
    }

    public function combo_stock_update(Request $request, $id)
    {

        $product = ComboProduct::find($id);

        if (!$product) {
            return response()->json(['error' => true, 'message' => labels('admin_labels.data_not_found', 'Data Not Found')], 404);
        } else {

            $rules = [
                'stock' => 'required',
                'quantity' => 'required',
                'type' => 'required',
            ];

            if ($response = validatePanelRequest($request, $rules)) {
                return $response;
            }

            if ($request->type == 'add') {

                updateComboStock($id, $request->quantity, 'add');
                if ($request->ajax()) {
                    return response()->json(['message' => labels('admin_labels.stock_updated_successfully', 'Stock updated successfully')]);
                }
            } else {
                if ($request->type == 'subtract') {
                    if (
                        $request->quantity > $request->stock
                    ) {
                        return response()->json(['error_message' => labels('admin_labels.subtracted_stock_greater_than_current_stock', 'Subtracted stock cannot be greater than current stock')]);
                    }
                }
                updateComboStock($id, $request->quantity, 'subtract');
                if ($request->ajax()) {
                    return response()->json(['message' => labels('admin_labels.stock_updated_successfully', 'Stock updated successfully')]);
                }
            }
        }
    }
}
