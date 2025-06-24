<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Models\ProductFaq;
use App\Models\Seller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductFaqController extends Controller
{
    public function index()
    {
        return view('seller.pages.tables.product_faqs');
    }

    public function store(Request $request, $fromApp = false)
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
        if ($fromApp == true) {
            $seller_id = $request->seller_id;
        } else {
            $seller_id = Seller::where('user_id', $user->id)->value('id');
        }

        $faq_data['product_id'] = $request->product_id;
        $faq_data['question'] = $request->question;
        $faq_data['answer'] = $request->answer;
        $faq_data['user_id'] = isset($request->user_id) && !empty($request->user_id) ? $request->user_id : $user->id;
        $faq_data['seller_id'] = isset($seller_id) && !empty($seller_id) ? $seller_id : 0;
        $faq_data['answered_by'] = isset($request->answer) && !empty($request->answer) ? $user->id : 0;

        $data = ProductFaq::create($faq_data);

        if ($request->ajax()) {
            return response()->json(['message' => labels('admin_labels.product_faq_created_successfully', 'Product Faq created successfully')]);
        } else {
            return $data;
        }
    }

    public function list()
    {
        $store_id = getStoreId();
        $search = trim(request('search'));
        $sort = "id";
        $order = (request('order')) ? request('order') : "DESC";
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = (request('limit')) ? request('limit') : 25;
        $user = Auth::user();
        $seller_id = Seller::where('user_id', $user->id)->value('id');
        $language_code = get_language_code();

        $product_faqs = ProductFaq::join('products', 'products.id', '=', 'product_faqs.product_id')
            ->where('product_faqs.seller_id', $seller_id)
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

        $product_faqs = $product_faqs->orderBy('product_faqs.' . $sort, $order)
            ->offset($offset)
            ->limit($limit)
            ->get();

        $product_faqs = $product_faqs->map(function ($p) use ($language_code) {
            $delete_url = route('seller.product_faqs.destroy', $p->id);
            $action = '<div class="dropdown bootstrap-table-dropdown">
                <a href="#" class="text-dark" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="bx bx-dots-horizontal-rounded"></i>
                </a>
                <div class="dropdown-menu table_dropdown product_faq_action_dropdown" aria-labelledby="dropdownMenuButton">
                    <a class="dropdown-item edit-product-faq" data-id="' . $p->id . '" data-bs-toggle="modal" data-bs-target="#edit_1modal"><i class="bx bx-pencil"></i> Edit</a>
                    <a class="dropdown-item delete-data" data-url="' . $delete_url . '"><i class="bx bx-trash"></i> Delete</a>
                </div>
            </div>';

            return [
                'id' => $p->id,
                'question' => $p->question,
                'answer' => $p->answer,
                'answered_by' => $this->getUserName($p->answered_by),
                'product_id' => $p->product_id,
                'product_name' => $this->getProductName($p->product_id, $language_code),
                'username' => $this->getUserName($p->user_id),
                'date_added' => Carbon::parse($p->created_at)->format('d-m-Y'),
                'operate' => $action,
            ];
        });

        return response()->json([
            "rows" => $product_faqs,
            "total" => $total,
        ]);
    }


    private function getProductName($productId, $language_code = "")
    {
        $product = Product::find($productId);
        return $product ? getDynamicTranslation('products', 'name', $productId, $language_code) : null;
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
            return response()->json(['error' => 'Product Faq not found!']);
        }
    }

    public function edit($id)
    {
        $product_faq = ProductFaq::find($id);

        if (!$product_faq) {
            return response()->json([
                'error' => true,
                'message' => labels('admin_labels.data_not_found', 'Data not found')
            ], 200);
        }

        return response()->json($product_faq);
    }

    public function update(Request $request, $id, $fromApp = false)
    {
        $user = Auth::user();
        if ($fromApp == true) {
            $seller_id = $request->seller_id;
        } else {
            $seller_id = Seller::where('user_id', $user->id)->value('id');
        }
        $seller_user_id = Seller::where('id', $seller_id)->value('user_id');
        // Fetch the username for the answered_by field using user_id from seller_data
        $answered_by_user = User::find($seller_user_id);
        $product_faq = ProductFaq::findOrFail($id);
        $product_faq->answer = $request->answer;
        $product_faq->answered_by = $answered_by_user ? $answered_by_user->username : $seller_id;
        $product_faq->save();
        if ($request->ajax()) {
            return response()->json(['message' => labels('admin_labels.product_faq_updated_successfully', 'Product Faq updated successfully')]);
        } else {
            return $product_faq;
        }
    }
}
