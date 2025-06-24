<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ComboProduct;
use App\Models\ComboProductFaq;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\HtmlString;

class ComboProductFaqController extends Controller
{
    public function index()
    {
        return view('admin.pages.forms.combo_product_faqs');
    }
    public function store(Request $request)
    {
        $rules = [
            'product_id' => 'required|exists:combo_products,id',
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

        ComboProductFaq::create($faq_data);

        if ($request->ajax()) {
            return response()->json([
                'message' => labels('admin_labels.product_faq_created_successfully', 'Product Faq created successfully')
            ]);
        }
    }

    public function list(Request $request)
    {
        $store_id = getStoreId();
        $search = trim(request('search'));
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = (request('limit')) ? request('limit') : "10";

        $product_faqs = ComboProductFaq::leftJoin('combo_products', 'combo_product_faqs.product_id', '=', 'combo_products.id') // Join with combo_products table
            ->leftJoin('users', 'combo_product_faqs.user_id', '=', 'users.id')
            ->where('combo_products.store_id', $store_id)
            ->select('combo_product_faqs.*', 'users.username');
        $language_code = get_language_code();


        if (!empty($search)) {
            $product_faqs
                ->when($search, function ($query) use ($search) {
                    return $query->where('combo_product_faqs.question', 'like', '%' . $search . '%')
                        ->orWhere('combo_product_faqs.id', 'like', '%' . $search . '%')
                        ->orWhere('combo_product_faqs.answer', 'like', '%' . $search . '%');
                });
        }

        $total = $product_faqs->count();


        $product_faqs = $product_faqs->orderBy('combo_product_faqs.' . $sort, $order)
            ->offset($offset)
            ->limit($limit)
            ->get();


        $product_faqs = $product_faqs
            ->map(function ($p) use ($language_code) {

                $delete_url = route('admin.combo_product_faqs.destroy', $p->id);
                $action = '<div class="dropdown bootstrap-table-dropdown">
            <a href="#" class="text-dark" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="bx bx-dots-horizontal-rounded"></i>
            </a>
            <div class="dropdown-menu table_dropdown product_faq_action_dropdown" aria-labelledby="dropdownMenuButton">
            <a class="dropdown-item dropdown_menu_items edit-combo-product-faq" data-id="' . $p->id . '" data-bs-toggle="modal" data-bs-target="#edit_modal"><i class="bx bx-pencil mx-2"></i> Edit</a>
            <a class="dropdown-item delete-data dropdown_menu_items" data-url="' . $delete_url . '"><i class="bx bx-trash mx-2"></i> Delete</a>
            </div>
        </div>';

                return [
                    'id' => $p->id,
                    'question' => $p->question,
                    'answer' => $p->answer,
                    'answered_by' => $p->answered_by,
                    'product_name' => $this->getProductName($p->product_id, $language_code),
                    'username' => $this->getUserName($p->user_id),
                    'answered_by_name' => $p->username,
                    'date_added' => Carbon::parse($p->created_at)->format('d-m-Y'),
                    'operate' => $action,
                ];
            });

        return response()->json([
            "rows" => $product_faqs,
            "total" => $total,
        ]);
    }


    private function getProductName($productId, $language_code = '')
    {
        $product = ComboProduct::find($productId);
        return $product ? getDynamicTranslation('combo_products', 'title', $productId, $language_code) : '';
    }
    private function getUserName($userId)
    {
        $user = User::find($userId);
        return $user ? $user->username : '';
    }

    public function update_status($id)
    {
        $product_faq = ComboProductFaq::findOrFail($id);
        $product_faq->status = $product_faq->status == '1' ? '0' : '1';
        $product_faq->save();
        return response()->json(['success' => labels('admin_labels.status_updated_successfully', 'Status updated successfully.')]);
    }

    public function destroy($id)
    {
        $product_faq = ComboProductFaq::find($id);

        if ($product_faq) {
            $product_faq->delete();
            return response()->json([
                'error' => false,
                'message' => labels('admin_labels.product_faq_deleted_successfully', 'Product Faq deleted successfully!')
            ]);
        } else {
            return response()->json(['error' => labels('admin_labels.data_not_found', 'Data Not Found')]);
        }
    }
    public function edit($id)
    {
        $product_faq = ComboProductFaq::find($id);

        if (!$product_faq) {
            return response()->json(['error' => true, 'message' => labels('admin_labels.data_not_found', 'Data Not Found')], 404);
        }

        return response()->json($product_faq);
    }

    public function update(Request $request, $id)
    {
        $product_faq = ComboProductFaq::findOrFail($id);
        $product_faq->answer = $request->answer;
        $product_faq->save();
        if ($request->ajax()) {
            return response()->json([
                'message' => labels('admin_labels.product_faq_updated_successfully', 'Product Faq updated successfully')
            ]);
        }
    }
    public function delete_selected_data(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:combo_product_faqs,id'
        ]);

        foreach ($request->ids as $id) {
            $product_faq = ComboProductFaq::find($id);

            if ($product_faq) {
                ComboProductFaq::where('id', $id)->delete();
            }
        }

        return response()->json([
            'error' => false,
            'message' => labels('admin_labels.product_faq_deleted_successfully', 'Selected faqs deleted successfully!'),
        ]);
    }
}
