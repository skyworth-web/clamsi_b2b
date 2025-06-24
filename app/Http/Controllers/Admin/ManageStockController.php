<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Controller;
use App\Models\Attribute_values;
use App\Models\Category;
use App\Models\ComboProduct;
use App\Models\Product;
use App\Models\Product_variants;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ManageStockController extends Controller
{
    public function index()
    {
        $fetched_data = [];
        $fetched = [];
        $attribute = [];
        if (request()->has('edit_id')) {
            $store_id = getStoreId();
            $editId = request('edit_id');

            $stock = Product_variants::select('stock', 'product_id', 'attribute_value_ids')
                ->where('id', $editId)
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
        }

        $categories = Category::all();

        $sellers = User::select('users.username as seller_name', 'users.id as seller_id', 'seller_store.category_ids', 'seller_data.id as seller_data_id')
            ->join('seller_data', 'seller_data.user_id', '=', 'users.id')
            ->join('seller_store', 'seller_data.user_id', '=', 'users.id')
            ->where('users.role_id', 4)
            ->get();


        if (request()->ajax()) {
            return response()->json(['fetched_data' => $fetched_data, 'fetched' => $fetched, 'attribute' => $attribute]);
        }
        return view('admin.pages.tables.manage_stock', compact('fetched_data', 'fetched', 'attribute', 'categories', 'sellers'));
    }
    public function manage_combo_stock()
    {

        $categories = Category::all();

        $sellers = User::select('users.username as seller_name', 'users.id as seller_id', 'seller_store.category_ids', 'seller_data.id as seller_data_id')
            ->join('seller_data', 'seller_data.user_id', '=', 'users.id')
            ->join('seller_store', 'seller_data.user_id', '=', 'users.id')
            ->where('users.role_id', 4)
            ->get();


        return view('admin.pages.tables.manage_combo_stock', compact('categories', 'sellers'));
    }
    public function list()
    {
        $store_id = getStoreId();

        $filters['show_only_stock_product'] = true;
        $filters['search'] = request()->query('search');

        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $offset = $filters['search'] || (request('pagination_offset')) ? (request('pagination_offset')) : 0;

        $limit = (request('limit')) ? request('limit') : "25";
        $category_id = request()->query('category_id');
        $seller_id = request()->query('seller_id');


        $products = fetchProduct("", $filters, "", $category_id, '', '', $sort, $order, "", "", $seller_id, '', $store_id);
        $total = $products['total'];
        $bulkData = $rows = [];

        $bulkData['total'] = $total;

        foreach ($products['product'] as $product) {

            $category_id = $product->category_id;
            $category_name = fetchDetails('categories', ['id' => $category_id], ['name', 'id']);

            $variants = getVariantsValuesByPid($product->id);


            // Handle the case when the stock type is 2 (multiple variants)
            if ($product->stock_type == 2) {
                foreach ($variants as $variant) {
                    $tempRow = createRow($product, $variant, $category_name);
                    $rows[] = $tempRow;
                }
            } else {

                // Handle the case when the stock type is 0 or 1
                $variant = reset($variants); // Assuming there is at least one variant

                $tempRow = createRow($product, $variant, $category_name);
                $rows[] = $tempRow;
            }
        }

        $pagedRows = array_slice($rows, $offset, $limit);
        $bulkData['rows'] = $pagedRows;
        $bulkData['total'] = count($rows);

        return response()->json($bulkData);
    }

    public function combo_stock_list()
    {
        $store_id = getStoreId();
        $filters['show_only_stock_product'] = true;
        $filters['search'] = request()->query('search');
        $offset = $filters['search'] || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = request()->query('limit', 10);
        $sort = request()->query('sort', 'id');
        $order = request()->query('order', 'ASC');
        $seller_id = request()->query('seller_id');


        $products = fetchComboProduct("", $filters, "", $limit, $offset, $sort, $order, "", "", $seller_id, $store_id);
        $total = !empty($products['combo_product']) ? $products['total'] : '0';
        $bulkData = ['total' => $total, 'rows' => []];

        foreach ($products['combo_product'] as $product) {
            $action = '<div class="d-flex align-items-center">
                <a href="#" class="btn edit-combo-stock single_action_button" title="Edit" data-id="' . $product->id . '" data-bs-toggle="modal" data-bs-target="#edit_modal">
                    <i class="bx bx-pencil mx-2"></i>
                </a>
            </div>';
            $product_image = route('admin.dynamic_image', [
                'url' => $product->image,
                'width' => 60,
                'quality' => 90
            ]);
            $stock_status = $product->availability == 1 ? '<label class="badge bg-success">In Stock</label>' : '<label class="badge bg-danger">Out of Stock</label>';
            $tempRow = [
                'id' => $product->id,
                'price' => $product->special_price . ' ' . '<strike>' . $product->price . '</strike>',
                'stock_count' => $product->stock != 0 ? $product->stock :  $product->stock,
                'stock_status' => $stock_status,
                'name' => '<div class="d-flex align-items-center"><a href="' . getMediaImageUrl($product_image) . '" data-lightbox="image-' . $product->id . '"><img src=' . $product_image . ' class="rounded mx-2"></a><div class="ms-2"><p class="m-0">' . $product->title . '</p></div></div>',
                'operate' => $action
            ];

            $bulkData['rows'][] = $tempRow;
        }

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


        $pname = $attributeValue && $stockType ? $productName . " - " . $attributeValue : $productName;


        $stock = isset($fetched_data['product'][0]->stock) && $fetched_data['product'][0]->stock != '' ? $fetched_data['product'][0]->stock : $fetched;

        if (!$variant) {
            return response()->json(['error' => true, 'message' => 'Data not found'], 404);
        }

        $data = [
            'product_name' => $pname,
            'stock' => $stock,
            'variant' => $variant,
            'pro_image' => $pro_image,

        ];

        return response()->json($data);
    }

    public function combo_stock_edit($id)
    {
        $product = ComboProduct::find($id);

        if (!$product) {
            return response()->json(['error' => true, 'message' => labels('admin_labels.data_not_found', 'Data Not Found')], 404);
        }

        return response()->json($product);
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
                    return response()->json([
                        'message' => labels('admin_labels.stock_updated_successfully', 'Stock updated successfully')
                    ]);
                }
            } else {
                if ($request->type == 'subtract') {
                    if (
                        $request->quantity > $request->stock
                    ) {
                        return response()->json([
                            'error_message' => labels('admin_labels.subtracted_stock_greater_than_current_stock', 'Subtracted stock cannot be greater than current stock')
                        ]);
                    }
                }
                updateStock($id, $request->quantity);
                if ($request->ajax()) {
                    return response()->json([
                        'message' => labels('admin_labels.stock_updated_successfully', 'Stock updated successfully')
                    ]);
                }
            }
        }
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
                    return response()->json([
                        'message' => labels('admin_labels.stock_updated_successfully', 'Stock updated successfully')
                    ]);
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
                    return response()->json([
                        'message' => labels('admin_labels.stock_updated_successfully', 'Stock updated successfully')
                    ]);
                }
            }
        }
    }
}
