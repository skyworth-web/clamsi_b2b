<?php

namespace App\Http\Controllers\Admin;

use App\Models\Language;
use App\Models\Promocode;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\HtmlString;

class PromoCodeController extends Controller
{
    public function index()
    {
        $languages = Language::all();
        return view('admin.pages.forms.promo_code', ['languages' => $languages]);
    }

    public function store(Request $request)
    {
        $store_id = getStoreId();
        $rules = [
            'title' => 'required',
            'promo_code' => 'required',
            'message' => 'required',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'no_of_users' => 'required',
            'minimum_order_amount' => 'required',
            'discount' => 'required|numeric|min:0',
            'discount_type' => 'required|in:percentage,amount',
            'max_discount_amount' => 'required',
            'repeat_usage' => 'required',
            'status' => 'required',
            'image' => 'required',
        ];

        if ($request->repeat_usage == '1') {
            $rules['no_of_repeat_usage'] = 'required';
        }

        if (
            $request->discount_type === 'percentage' &&
            ($request->discount < 1 || $request->discount > 100)
        ) {
            return response()->json([
                'error_message' =>
                labels('admin_labels.you_can_set_percentage_between_one_to_hundred', 'You Can Set Percentage Between 1 to 100.')
            ]);
        }

        $afterValidation = function ($validator) use ($request) {
            if ($request->input('discount_type') === 'amount') {
                $minOrder = $request->input('minimum_order_amount');
                $discount = $request->input('discount');
                $maxDiscount = $request->input('max_discount_amount');

                if ($discount >= $minOrder) {
                    $validator->errors()->add('discount', 'Discount must be less than the Minimum Order Amount.');
                }

                if ($maxDiscount >= $minOrder) {
                    $validator->errors()->add('max_discount_amount', 'Max Discount Amount must be less than the Minimum Order Amount.');
                }
            }
        };

        if ($response = validatePanelRequest($request, $rules, [], $afterValidation)) {
            return $response;
        }
        $translations = [
            'en' => $request->title
        ];
        if (!empty($request['translated_promocode_title'])) {
            $translations = array_merge($translations, $request['translated_promocode_title']);
        }
        $translation_message = [
            'en' => $request->message
        ];
        if (!empty($request['translated_promocode_message'])) {
            $translation_message = array_merge($translations, $request['translated_promocode_message']);
        }

        $promocode_data['title'] = json_encode($translations, JSON_UNESCAPED_UNICODE);
        $promocode_data['message'] = json_encode($translation_message, JSON_UNESCAPED_UNICODE);

        $promocode_data['promo_code'] = $request->promo_code;
        $promocode_data['start_date'] = $request->start_date;
        $promocode_data['end_date'] = $request->end_date;
        $promocode_data['image'] = $request->image;
        $promocode_data['no_of_users'] = $request->no_of_users;
        $promocode_data['minimum_order_amount'] = $request->minimum_order_amount;
        $promocode_data['discount'] = $request->discount;
        $promocode_data['discount_type'] = $request->discount_type;
        $promocode_data['max_discount_amount'] = $request->max_discount_amount;
        $promocode_data['repeat_usage'] = $request->repeat_usage;
        $promocode_data['no_of_repeat_usage'] = $request->no_of_repeat_usage;
        $promocode_data['status'] = $request->status;
        $promocode_data['is_cashback'] = ($request->is_cashback != 'null' && $request->is_cashback == "on") ? 1 : 0;
        $promocode_data['list_promocode'] = ($request->list_promocode != 'null' && $request->list_promocode == "on") ? 1 : 0;
        $promocode_data['store_id'] = $store_id;
        unset($request['translated_promocode_message'], $request['translated_promocode_title']);

        PromoCode::create($promocode_data);

        if ($request->ajax()) {
            return response()->json([
                'message' => labels('admin_labels.promo_code_created_successfully', 'Promo Code created successfully')
            ]);
        }
    }

    public function list()
    {
        $store_id = getStoreId(); // Get the current store ID
        $search = trim(request('search')); // Search term
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = request('limit', 10);
        $sort = request('sort', 'id'); // Default sorting field
        $order = request('order', 'DESC'); // Default sorting order
        $status = request('status') ?? ''; // Status filter

        // Build the base query for promo codes
        $promo_codes = PromoCode::where('store_id', $store_id) // Ensure we're filtering by store_id
            ->when($search, function ($query) use ($search) {
                // Apply search filters only if search is provided
                return $query->where('promo_code', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%');
            })
            ->when($status !== '', function ($query) use ($status) {
                // Filter by status if it's provided
                return $query->where('status', $status);
            });

        // Get the total count
        $total = $promo_codes->count();
        $language_code = get_language_code();
        // Get the filtered results with sorting, pagination
        $promo_codes = $promo_codes->orderBy($sort, $order)
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->map(function ($p) use ($language_code) {
                $edit_url = route('promo_codes.edit', $p->id);
                $delete_url = route('promo_codes.destroy', $p->id);
                $action = '<div class="dropdown bootstrap-table-dropdown">
                <a href="#" class="text-dark" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                   <i class="bx bx-dots-horizontal-rounded"></i>
                </a>
                <div class="dropdown-menu table_dropdown offer_action_dropdown" aria-labelledby="dropdownMenuButton">
                    <a class="dropdown-item dropdown_menu_items" href="' . $edit_url . '"><i class="bx bx-pencil mx-2"></i> Edit</a>
                    <a class="dropdown-item delete-data dropdown_menu_items" data-url="' . $delete_url . '"><i class="bx bx-trash mx-2"></i> Delete</a>
                </div>
            </div>';

                $image = route('admin.dynamic_image', [
                    'url' => getMediaImageUrl($p->image),
                    'width' => 60,
                    'quality' => 90
                ]);

                return [
                    'id' => $p->id,
                    'promo_code' => $p->promo_code,
                    'title' => getDynamicTranslation('promo_codes', 'title', $p->id, $language_code),
                    'message' => getDynamicTranslation('promo_codes', 'message', $p->id, $language_code),
                    'start_date' => $p->start_date,
                    'end_date' => $p->end_date,
                    'discount' => $p->discount,
                    'discount_type' => $p->discount_type,
                    'is_cashback' => ($p->is_cashback == '1') ? '<span class="badge bg-info">On</span>' : '<span class="badge bg-danger">Off</span>',
                    'list_promocode' => ($p->list_promocode == '1') ? '<span class="badge bg-success">Show</span>' : '<span class="badge bg-dark">Hide</span>',
                    'no_of_users' => $p->no_of_users,
                    'no_of_repeat_usage' => $p->no_of_repeat_usage,
                    'repeat_usage' => ($p->repeat_usage == '1') ? '<span class="badge bg-info">Allowed</span>' : '<span class="badge bg-danger">Not Allowed</span>',
                    'min_order_amt' => formateCurrency(formatePriceDecimal($p->minimum_order_amount)),
                    'max_discount_amount' => formateCurrency(formatePriceDecimal($p->max_discount_amount)),
                    'operate' => $action,
                    'image' => '<div><a href="' . getMediaImageUrl($p->image)  . '" data-lightbox="image-' . $p->id . '"><img src="' . $image . '" alt="Avatar" class="rounded"/></a></div>',
                    'status' => '<select class="form-select status_dropdown change_toggle_status ' . ($p->status == 1 ? 'active_status' : 'inactive_status') . '" data-id="' . $p->id . '" data-url="/admin/promo_code/update_status/' . $p->id . '" aria-label="">
                  <option value="1" ' . ($p->status == 1 ? 'selected' : '') . '>Active</option>
                  <option value="0" ' . ($p->status == 0 ? 'selected' : '') . '>Deactive</option>
              </select>',
                ];
            });

        return response()->json([
            "rows" => $promo_codes,
            "total" => $total,
        ]);
    }


    public function update_status($id)
    {
        $promo_code = PromoCode::findOrFail($id);
        $promo_code->status = $promo_code->status == '1' ? '0' : '1';
        $promo_code->save();
        return response()->json(['success' => labels('admin_labels.status_updated_successfully', 'Status updated successfully.')]);
    }

    public function destroy($id)
    {
        $promo_code = PromoCode::find($id);

        if ($promo_code->delete()) {
            return response()->json([
                'error' => false,
                'message' => labels('admin_labels.promo_code_deleted_successfully', 'Promo Code deleted successfully!')
            ]);
        } else {
            return response()->json(['error' => labels('admin_labels.something_went_wrong', 'Something went wrong')]);
        }
    }

    public function edit($data)
    {
        $store_id = getStoreId();
        $promo_code = PromoCode::all();
        $languages = Language::all();
        $data = PromoCode::where('store_id', $store_id)
            ->find($data);

        if ($data === null || empty($data)) {
            return view('admin.pages.views.no_data_found');
        } else {
            return view('admin.pages.forms.update_promo_code', [
                'data' => $data,
                'promo_code' => $promo_code,
                'languages' => $languages
            ]);
        }
    }

    public function update(Request $request, $id)
    {

        $promo_code = PromoCode::find($id);
        if (!$promo_code) {
            return response()->json(['error' => true, 'message' => labels('admin_labels.data_not_found', 'Data Not Found')], 404);
        } else {
            $rules = [
                'promo_code' => 'required',
                'title' => 'required',
                'message' => 'required',
                'start_date' => 'required',
                'end_date' => 'required|date|after:start_date',
                'no_of_users' => 'required',
                'minimum_order_amount' => 'required',
                'discount' => 'required',
                'discount_type' => 'required|in:percentage,amount',
                'max_discount_amount' => 'required',
                'repeat_usage' => 'required',
                'status' => 'required',
                'image' => 'required',
            ];
            if ($request->discount_type === 'percentage' && ($request->discount < 1 || $request->discount > 100)) {
                return response()->json([
                    'error_message' => labels(
                        'admin_labels.you_can_set_percentage_between_one_to_hundred',
                        'You Can Set Percentage Between 1 to 100.'
                    )
                ]);
            }
            $after = function ($validator) use ($request) {
                if ($request->discount_type === 'amount') {
                    $minOrder = $request->minimum_order_amount;
                    $discount = $request->discount;
                    $maxDiscount = $request->max_discount_amount;

                    if ($discount >= $minOrder) {
                        $validator->errors()->add('discount', 'Discount must be less than the Minimum Order Amount.');
                    }

                    if ($maxDiscount >= $minOrder) {
                        $validator->errors()->add('max_discount_amount', 'Max Discount Amount must be less than the Minimum Order Amount.');
                    }
                }
            };

            if ($response = validatePanelRequest($request, $rules, [], $after)) {
                return $response;
            }

            $existingTranslations = json_decode($promo_code->title, true) ?? [];
            $existingMessageTranslations = json_decode($promo_code->message, true) ?? [];

            $existingTranslations['en'] = $request->title;
            $existingMessageTranslations['en'] = $request->message;

            if (!empty($request->translated_promocode_title)) {
                $existingTranslations = array_merge($existingTranslations, $request->translated_promocode_title);
            }
            if (!empty($request->translated_promocode_message)) {
                $existingMessageTranslations = array_merge($existingMessageTranslations, $request->translated_promocode_message);
            }

            $promocode_data['title'] = json_encode($existingTranslations, JSON_UNESCAPED_UNICODE);
            $promocode_data['message'] = json_encode($existingMessageTranslations, JSON_UNESCAPED_UNICODE);;

            $promocode_data['promo_code'] = $request->promo_code;
            $promocode_data['start_date'] = $request->start_date;
            $promocode_data['end_date'] = $request->end_date;
            $promocode_data['image'] = $request->image;
            $promocode_data['no_of_users'] = $request->no_of_users;
            $promocode_data['minimum_order_amount'] = $request->minimum_order_amount;
            $promocode_data['discount'] = $request->discount;
            $promocode_data['discount_type'] = $request->discount_type;
            $promocode_data['max_discount_amount'] = $request->max_discount_amount;
            $promocode_data['repeat_usage'] = $request->repeat_usage;
            $promocode_data['no_of_repeat_usage'] = $request->no_of_repeat_usage;
            $promocode_data['status'] = $request->status;
            $promocode_data['is_cashback'] = ($request->is_cashback != 'null' && $request->is_cashback == "on") ? 1 : 0;
            $promocode_data['list_promocode'] = ($request->list_promocode != 'null' && $request->list_promocode == "on") ? 1 : 0;

            $promo_code->update($promocode_data);
            if ($request->ajax()) {
                return response()->json([
                    'message' => labels('admin_labels.promo_code_updated_successfully', 'Promo Code updated successfully'),
                    'location' => route('promo_codes.index')
                ]);
            }
        }
    }

    public function getPromoCodes($limit = null, $offset = null, $sort = 'id', $order = 'DESC', $search = null, $store_id, $language_code = "")
    {
        $query = DB::table('promo_codes as p')
            ->select(
                'p.id',
                DB::raw('DATEDIFF(end_date, start_date) as remaining_days'),
                'p.promo_code',
                'p.title',
                'p.image',
                'p.message',
                'p.start_date',
                'p.end_date',
                'p.discount',
                'p.repeat_usage',
                'p.minimum_order_amount as min_order_amt',
                'p.no_of_users',
                'p.discount_type',
                'p.max_discount_amount as max_discount_amt',
                'p.no_of_repeat_usage',
                'p.status',
                'p.is_cashback',
                'p.list_promocode'
            )
            ->whereRaw('(CURDATE() between start_date AND end_date)')
            ->where('p.status', 1)
            ->where('p.list_promocode', 1);

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->orWhere('p.id', 'LIKE', "%{$search}%")
                    ->orWhere('p.title', 'LIKE', "%{$search}%")
                    ->orWhere('p.promo_code', 'LIKE', "%{$search}%")
                    ->orWhere('p.message', 'LIKE', "%{$search}%")
                    ->orWhere('p.start_date', 'LIKE', "%{$search}%")
                    ->orWhere('p.end_date', 'LIKE', "%{$search}%")
                    ->orWhere('p.discount', 'LIKE', "%{$search}%")
                    ->orWhere('p.repeat_usage', 'LIKE', "%{$search}%")
                    ->orWhere('p.max_discount_amount', 'LIKE', "%{$search}%");
            });
        }

        $query->where('store_id', $store_id);
        $total = $query->count();

        $promoCodes = $query
            ->orderBy($sort, $order)
            ->when($limit, function ($query, $limit) use ($offset) {
                return $query->skip($offset)->take($limit);
            })
            ->get();

        $bulkData = [
            'error' => $promoCodes->isEmpty(),
            'message' => $promoCodes->isEmpty() ? labels('admin_labels.promo_code_not_exist', 'Promo code(s) does not exist')
                :
                labels('admin_labels.promo_codes_retrieved_successfully', 'Promo code(s) retrieved successfully'),
            'total' => $total,
            'data' => $promoCodes->map(function ($row) use ($language_code) {
                return [
                    'id' => $row->id,
                    'promo_code' => $row->promo_code,
                    'title' => getDynamicTranslation('promo_codes', 'title', $row->id, $language_code),
                    'message' => getDynamicTranslation('promo_codes', 'message', $row->id, $language_code),
                    'start_date' => $row->start_date,
                    'end_date' => $row->end_date,
                    'discount' => $row->discount,
                    'repeat_usage' => $row->repeat_usage == '1' ? 'Allowed' : 'Not Allowed',
                    'min_order_amt' => $row->min_order_amt,
                    'no_of_users' => $row->no_of_users,
                    'discount_type' => $row->discount_type,
                    'max_discount_amt' => $row->max_discount_amt,
                    'image' => isset($row->image) && !empty($row->image) ? getImageUrl($row->image) : '',
                    'no_of_repeat_usage' => $row->no_of_repeat_usage,
                    'status' => $row->status,
                    'is_cashback' => $row->is_cashback,
                    'list_promocode' => $row->list_promocode,
                    'remaining_days' => $row->remaining_days,
                ];
            }),
        ];

        return $bulkData;
    }
    public function delete_selected_data(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:promo_codes,id'
        ]);

        foreach ($request->ids as $id) {
            $promo_code = PromoCode::find($id);

            if ($promo_code) {
                PromoCode::where('id', $id)->delete();
            }
        }
        PromoCode::destroy($request->ids);

        return response()->json(['message' => 'Selected data deleted successfully.']);
    }
}
