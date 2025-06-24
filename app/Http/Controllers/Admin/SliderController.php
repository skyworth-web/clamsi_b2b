<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\HtmlString;

class SliderController extends Controller
{
    public function index()
    {
        $store_id = getStoreId();
        $categories = Category::where('status', 1)->where('store_id', $store_id)->orderBy('id', 'desc')->get();
        return view('admin.pages.forms.sliders', ['categories' => $categories]);
    }

    public function store(Request $request)
    {
        $store_id = getStoreId();
        $rules = [
            'type' => 'required',
            'image' => 'required',
        ];

        if ($request->type === 'categories') {
            $rules['category_id'] = 'required|exists:categories,id';
        }

        if ($request->type === 'slider_url') {
            $rules['link'] = 'required';
        }

        if ($request->type === 'products') {
            $rules['product_id'] = 'required|exists:products,id';
        }

        if ($request->type === 'combo_products') {
            $rules['combo_product_id'] = 'required|exists:combo_products,id';
        }

        $validationResponse = validatePanelRequest($request, $rules);

        if ($validationResponse !== null) {
            return $validationResponse;
        }

        $type_id = '';
        $link = '';
        if (isset($request->type) && $request->type == 'categories' && isset($request->category_id) && !empty($request->category_id)) {
            $type_id = $request->category_id;
        }
        if (isset($request->type) && $request->type == 'products' && isset($request->product_id) && !empty($request->product_id)) {
            $type_id = $request->product_id;
        }
        if (isset($request->type) && $request->type == 'combo_products' && isset($request->combo_product_id) && !empty($request->combo_product_id)) {
            $type_id = $request->combo_product_id;
        }
        if (isset($request->type) && $request->type == 'slider_url' && !empty($request->link)) {
            $link = $request->link;
            $type_id = 0;
        }
        $slider_data['type'] = $request->type;
        $slider_data['link'] = $link;
        $slider_data['image'] = $request->input('image');
        $slider_data['type_id'] = $type_id;
        $slider_data['store_id'] = $store_id;

        Slider::create($slider_data);

        if ($request->ajax()) {
            return response()->json([
                'message' => labels('admin_labels.slider_created_successfully', 'Slider created successfully')
            ]);
        }
    }

    public function list()
    {
        $store_id = getStoreId();
        $search = trim(request('search'));
        $sort = request('sort', 'id');
        $order = request('order', 'DESC');
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = request('limit', 10);

        $sliders = Slider::where('store_id', $store_id);

        if ($search) {
            $sliders->where('type', 'like', '%' . $search . '%');
        }

        $total = $sliders->count();

        // Mapping array for displaying type values
        $slider_type = [
            'default' => 'Default',
            'categories' => 'Category',
            'products' => 'Product',
            'combo_products' => 'Combo Products',
            'slider_url' => 'Slider URL'
        ];

        $sliders = $sliders->orderBy($sort, $order)
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->map(function ($s) use ($slider_type) {
                $edit_url = route('sliders.edit', $s->id);
                $delete_url = route('sliders.destroy', $s->id);
                $action = '<div class="dropdown bootstrap-table-dropdown">
                <a href="#" class="text-dark" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="bx bx-dots-horizontal-rounded"></i>
                </a>
                <div class="dropdown-menu table_dropdown slider_action_dropdown" aria-labelledby="dropdownMenuButton">
                    <a class="dropdown-item dropdown_menu_items" href="' . $edit_url . '"><i class="bx bx-pencil mx-2"></i> Edit</a>
                    <a class="dropdown-item delete-data dropdown_menu_items" data-url="' . $delete_url . '"><i class="bx bx-trash mx-2"></i> Delete</a>
                </div>
            </div>';

                // Use the mapping array to display type
                $type = isset($slider_type[$s->type]) ? $slider_type[$s->type] : $s->type;
                $image = route('admin.dynamic_image', [
                    'url' => getMediaImageUrl($s->image),
                    'width' => 60,
                    'quality' => 90
                ]);
                return [
                    'id' => $s->id,
                    'type' => $type,
                    'operate' => $action,
                    'link' => $s->link,
                    'image' => '<div><a href="' . getMediaImageUrl($s->image)  . '" data-lightbox="image-' . $s->id . '"><img src="' . $image . '" alt="Avatar" class="rounded"/></a></div>',
                ];
            });

        return response()->json([
            "rows" => $sliders,
            "total" => $total,
        ]);
    }



    public function update_status($id)
    {
        $slider = Slider::findOrFail($id);
        $slider->status = $slider->status == '1' ? '0' : '1';
        $slider->save();
        return response()->json(['success' => labels('admin_labels.status_updated_successfully', 'Status updated successfully.')]);
    }

    public function destroy($id)
    {
        $slider = Slider::find($id);

        if ($slider->delete()) {
            return response()->json([
                'error' => false,
                'message' => labels('admin_labels.slider_deleted_successfully', 'Slider deleted successfully!')
            ]);
        } else {
            return response()->json(['error' => labels('admin_labels.something_went_wrong', 'Something went wrong')]);
        }
    }

    public function edit($data)
    {
        $store_id = getStoreId();
        $language_code = get_language_code();
        $data = Slider::where('store_id', $store_id)
            ->find($data);

        if ($data === null || empty($data)) {
            return view('admin.pages.views.no_data_found');
        } else {
            $categories = Category::where('status', 1)->where('store_id', $store_id)->orderBy('id', 'desc')->get();
            return view('admin.pages.forms.update_slider', compact('data', 'categories', 'language_code'));
        }
    }

    public function update(Request $request, $data)
    {
        $rules = [
            'type' => 'required',
            'image' => 'required',
        ];

        $messages = [];

        if ($request->type === 'categories') {
            $rules['category_id'] = 'required|exists:categories,id';
            $messages['category_id.required'] = labels('admin_labels.select_atleast_one_category', 'Please Select Atleast One category.');
        }

        if ($request->type === 'slider_url') {
            $rules['link'] = 'required';
        }

        if ($request->type === 'products') {
            $rules['product_id'] = 'required|exists:products,id';
            $messages['product_id.required'] = labels('admin_labels.select_atleast_one_product', 'Please Select Atleast One Product.');
        }

        if ($request->type === 'combo_products') {
            $rules['combo_product_id'] = 'required|exists:combo_products,id';
            $messages['combo_product_id.required'] = labels('admin_labels.select_atleast_one_product', 'Please Select Atleast One Product.');
        }
        $validationResponse = validatePanelRequest($request, $rules, $messages);
        if ($validationResponse !== null) {
            return $validationResponse;
        }
        $type_id = 0;
        $link = '';
        if (isset($request->type) && $request->type == 'categories' && isset($request->category_id) && !empty($request->category_id)) {
            $type_id = $request->category_id;
        }
        if (isset($request->type) && $request->type == 'products' && isset($request->product_id) && !empty($request->product_id)) {
            $type_id = $request->product_id;
        }
        if (isset($request->type) && $request->type == 'combo_products' && isset($request->combo_product_id) && !empty($request->combo_product_id)) {
            $type_id = $request->combo_product_id;
        }
        if (isset($request->type) && $request->type == 'slider_url' && !empty($request->link)) {
            $link = $request->link;
            $type_id = 0;
        }
        $slider_data['type'] = $request->type;
        $slider_data['link'] = $link;
        $slider_data['image'] = $request->input('image');
        $slider_data['type_id'] = $type_id;

        Slider::where('id', $data)->update($slider_data);

        if ($request->ajax()) {
            return response()->json([
                'message' => labels('admin_labels.slider_updated_successfully', 'Slider Updated successfully'),
                'location' => route('sliders.index')
            ]);
        }
    }
    public function delete_selected_data(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:sliders,id'
        ]);

        foreach ($request->ids as $id) {
            $slider = Slider::find($id);

            if ($slider) {
                Slider::where('id', $id)->delete();
            }
        }
        Slider::destroy($request->ids);

        return response()->json(['message' => 'Selected data deleted successfully.']);
    }
}
