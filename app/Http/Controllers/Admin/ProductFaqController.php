<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\ProductFaq;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\HtmlString;

class ProductFaqController extends Controller
{
    public function index()
    {
        return view('admin.pages.forms.product_faqs');
    }

    public function store(Request $request)
    {
        $rules = [
            'product_id' => 'required|exists:products,id',
            'question' => 'required',
            'answer' => 'required',
        ];

        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        }
        $user = Auth::user();
        $faq_data['product_id'] = $request->product_id;
        $faq_data['question'] = $request->question;
        $faq_data['answer'] = $request->answer;
        $faq_data['user_id'] = isset($request->user_id) && !empty($request->user_id) ? $request->user_id : $user->id;
        $faq_data['seller_id'] =  0;
        $faq_data['answered_by'] = isset($request->answer) && !empty($request->answer) ? $user->id : 0;

        ProductFaq::create($faq_data);

        if ($request->ajax()) {
            return response()->json(['message' => 'Product Faq created successfully']);
        }
    }

    public function list(Request $request)
    {
        $store_id = getStoreId();
        $search = trim($request->input('search'));
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'DESC');
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = $request->input('limit', 10);

        $product_faqs = ProductFaq::join('products', 'products.id', '=', 'product_faqs.product_id')
            ->where('products.store_id', $store_id)
            ->select('product_faqs.*', 'products.name as product_name');


        if ($search) {
            $product_faqs->where(function ($query) use ($search) {
                $query->where('product_faqs.question', 'like', '%' . $search . '%')
                    ->orWhere('product_faqs.answer', 'like', '%' . $search . '%')
                    ->orWhere('products.name', 'like', '%' . $search . '%');
            });
        }

        $total = $product_faqs->count();

        $language_code = get_language_code();
        $faqs = $product_faqs->orderBy('product_faqs.' . $sort, $order)
            ->offset($offset)
            ->limit($limit)
            ->get();


        $data = $faqs->map(function ($faq) use ($language_code) {
            $delete_url = route('admin.product_faqs.destroy', $faq->id);
            $action = '<div class="dropdown bootstrap-table-dropdown">
                    <a href="#" class="text-dark" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="bx bx-dots-horizontal-rounded"></i>
                    </a>
                    <div class="dropdown-menu table_dropdown product_faq_action_dropdown" aria-labelledby="dropdownMenuButton">
                        <a class="dropdown-item dropdown_menu_items edit-product-faq" data-id="' . $faq->id . '" data-bs-toggle="modal" data-bs-target="#edit_modal"><i class="bx bx-pencil mx-2"></i> Edit</a>
                        <a class="dropdown-item delete-data dropdown_menu_items" data-url="' . $delete_url . '"><i class="bx bx-trash mx-2"></i> Delete</a>
                    </div>
                </div>';

            return [
                'id' => $faq->id,
                'product_id' => $faq->product_id,
                'product_name' => getDynamicTranslation('products', 'name', $faq->product_id, $language_code),
                'username' => $this->getUserName($faq->user_id),
                'question' => $faq->question,
                'answer' => $faq->answer,
                'answered_by' => $this->getUserName($faq->answered_by),
                'date_added' => $faq->created_at->format('Y-m-d'),
                'operate' => $action,
            ];
        });

        return response()->json([
            "rows" => $data,
            "total" => $total,
        ]);
    }


    private function getProductName($productId)
    {
        $product = Product::find($productId);
        return $product ? $product->name : null;
    }

    private function getUserName($userId)
    {
        $user = User::find($userId);
        return $user ? $user->username : null;
    }


    public function update_status($id)
    {
        $product_faq = ProductFaq::findOrFail($id);
        $product_faq->status = $product_faq->status == '1' ? '0' : '1';
        $product_faq->save();
        return response()->json(['success' => labels('admin_labels.status_updated_successfully', 'Status updated successfully.')]);
    }

    public function destroy($id)
    {
        $product_faq = ProductFaq::find($id);

        if ($product_faq) {
            $product_faq->delete();
            return response()->json(['error' => false, 'message' => labels('admin_labels.product_faq_deleted_successfully', 'Product Faq deleted successfully!')]);
        } else {
            return response()->json(['error' => labels('admin_labels.data_not_found', 'Data Not Found')]);
        }
    }
    public function edit($id)
    {
        $product_faq = ProductFaq::find($id);

        if (!$product_faq) {
            return response()->json(['error' => true, 'message' => labels('admin_labels.data_not_found', 'Data Not Found')], 404);
        }

        return response()->json($product_faq);
    }

    public function update(Request $request, $id)
    {
        $product_faq = ProductFaq::findOrFail($id);
        $user = Auth::user();
        $product_faq->answer = $request->answer;
        $product_faq->answered_by = isset($request->answer) && !empty($request->answer) ? $user->id : 0;
        $product_faq->save();
        if ($request->ajax()) {
            return response()->json(['message' => labels('admin_labels.product_faq_updated_successfully', 'Product Faq updated successfully')]);
        }
    }
    public function delete_selected_data(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:product_faqs,id'
        ]);

        foreach ($request->ids as $id) {
            $product_faq = ProductFaq::find($id);

            if ($product_faq) {
                ProductFaq::where('id', $id)->delete();
            }
        }

        return response()->json([
            'error' => false,
            'message' => labels('admin_labels.product_faq_deleted_successfully', 'Selected faqs deleted successfully!'),
        ]);
    }
}
