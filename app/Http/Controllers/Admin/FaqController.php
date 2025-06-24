<?php

namespace App\Http\Controllers\Admin;

use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\HtmlString;

class FaqController extends Controller
{
    public function index()
    {
        return view('admin.pages.forms.faqs');
    }

    public function store(Request $request)
    {

        $rules = [
            'question' => 'required',
            'answer' => 'required',
        ];

        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        }

        $faq_data['question'] = $request->question;
        $faq_data['answer'] = $request->answer;
        $faq_data['status'] = 1;

        Faq::create($faq_data);

        if ($request->ajax()) {
            return response()->json([
                'message' => labels('admin_labels.faq_added_successfully', 'Faq added successfully')
            ]);
        }
    }

    public function list()
    {
        $search = trim(request('search'));
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = (request('limit')) ? request('limit') : "10";
        $faqs = Faq::when($search, function ($query) use ($search) {
            return $query->where('question', 'like', '%' . $search . '%')
                ->orWhere('answer', 'like', '%' . $search . '%');
        });

        $total = $faqs->count();
        $faqs = $faqs->orderBy($sort, $order)
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->map(function ($f) {
                $edit_url = route('faqs.edit', $f->id);
                $delete_url = route('faqs.destroy', $f->id);
                $action = '<div class="dropdown bootstrap-table-dropdown">
                <a href="#" class="text-dark" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="bx bx-dots-horizontal-rounded"></i>
                </a>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                <a class="dropdown-item dropdown_menu_items edit-faq" data-id="' . $f->id . '"><i class="bx bx-pencil mx-2"></i> Edit</a>
                <a class="dropdown-item delete-data dropdown_menu_items" data-url="' . $delete_url . '"><i class="bx bx-trash mx-2"></i> Delete</a>
                </div>
            </div>';

                return [
                    'id' => $f->id,
                    'question' => $f->question,
                    'answer' => $f->answer,
                    'operate' => $action,
                ];
            });

        return response()->json([
            "rows" => $faqs,
            "total" => $total,
        ]);
    }

    public function update_status($id)
    {
        $faq = Faq::findOrFail($id);
        $faq->status = $faq->status == '1' ? '0' : '1';
        $faq->save();
        return response()->json(['success' => labels('admin_labels.status_updated_successfully', 'Status updated successfully.')]);
    }

    public function destroy($id)
    {
        $faq = Faq::find($id);

        if ($faq) {
            $faq->delete();
            return response()->json([
                'error' => false,
                'message' => labels('admin_labels.faq_deleted_successfully', 'Faq deleted successfully!')
            ]);
        } else {
            return response()->json(['error' => labels('admin_labels.data_not_found', 'Data Not Found')]);
        }
    }

    public function edit($id)
    {
        $faq = Faq::find($id);

        if (!$faq) {
            return response()->json(['error' => true, 'message' => labels('admin_labels.data_not_found', 'Data Not Found')], 404);
        }

        return response()->json($faq);
    }

    public function update(Request $request, $id)
    {

        $faq = Faq::find($id);
        if (!$faq) {
            return response()->json(['error' => true, 'message' => labels('admin_labels.data_not_found', 'Data Not Found')], 404);
        } else {

            $rules = [
                'edit_question' => 'required',
                'edit_answer' => 'required',
            ];

            if ($response = validatePanelRequest($request, $rules)) {
                return $response;
            }
            $faq->question = $request->input('edit_question');
            $faq->answer = $request->input('edit_answer');

            $faq->save();

            if ($request->ajax()) {
                return response()->json([
                    'message' => labels('admin_labels.faq_updated_successfully', 'Faq updated successfully')
                ]);
            }
        }
    }

    public function getFaqs($offset, $limit, $sort, $order)
    {
        $faqs_data = [];

        $totalCount = Faq::where('status', '1')->count();

        $faqs = Faq::where('status', '1')
            ->orderBy($sort, $order)
            ->skip($offset)
            ->take($limit)
            ->get();

        // Modify each FAQ item
        $faqs = $faqs->map(function ($faq) {
            // Unset created_at and updated_at fields
            unset($faq['created_at']);
            unset($faq['updated_at']);

            // Replace null values with empty strings
            foreach ($faq as $key => $value) {
                if ($value === null) {
                    $faq[$key] = '';
                }
            }

            // Escape HTML characters
            return outputEscaping($faq->toArray());
        });

        $faqs_data['total'] = $totalCount;
        $faqs_data['data'] = $faqs;

        return $faqs_data;
    }
    public function delete_selected_data(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:faqs,id'
        ]);

        foreach ($request->ids as $id) {
            $faq = Faq::find($id);

            if ($faq) {
                Faq::where('id', $id)->delete();
            }
        }

        return response()->json([
            'error' => false,
            'message' => labels('admin_labels.faq_deleted_successfully', 'Selected faqs deleted successfully!'),
        ]);
    }
}
