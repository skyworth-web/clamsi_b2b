<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\CustomMessage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class CustomMessageController extends Controller
{
    public function index()
    {
        return view('admin.pages.forms.custom_message');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => [
                'required',
                // Add a custom rule to check if the type already exists
                function ($attribute, $value, $fail) {
                    $existingType = CustomMessage::where('type', $value)->exists();
                    if ($existingType) {
                        $fail('You have already this type of custom message');
                    }
                }
            ],
            'title' => 'required',
            'message' => 'required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            if ($request->ajax()) {
                return response()->json(['errors' => $errors->all()], 422);
            }
            return redirect()->back()->withErrors($errors)->withInput();
        }

        // Store the zipcodes as a comma-separated string
        $message_data = $validator->validated();

        $message_data['type'] = $request->type;
        $message_data['title'] = $request->title;
        $message_data['message'] = $request->message;

        // Create the CustomMessage record with the updated data
        CustomMessage::create($message_data);

        if ($request->ajax()) {
            return response()->json([
                'message' => labels('admin_labels.custom_message_added_successfully', 'Custom Message added successfully')
            ]);
        }
    }


    public function list(Request $request)
    {
        $search = trim($request->input('search'));
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'DESC');
        // $offset = trim(request()->input('search')) ? 0 : request()->input('offset', 0);
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = $request->input('limit', 10);

        $message_data = CustomMessage::when($search, function ($query) use ($search) {
            return $query->where('message', 'like', '%' . $search . '%')
                ->orWhere('title', 'LIKE', "%$search%");
        });

        $total = $message_data->count();

        // Use Paginator to handle the server-side pagination
        $custom_messages = $message_data
            ->orderBy($sort, $order)
            ->offset($offset)
            ->limit($limit)
            ->get();

        // Prepare the data for the "Actions" field
        $data = $custom_messages->map(function ($m) {
            $delete_url = route('custom_message.destroy', $m->id);
            $edit_url = route('custom_message.edit', $m->id);
            $action = '<div class="dropdown height-100">
                <a href="#" class="text-dark" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="bx bx-dots-horizontal-rounded"></i>
                </a>
                <div class="dropdown-menu table_dropdown custom_message_action_dropdown" aria-labelledby="dropdownMenuButton">
                <a class="dropdown-item dropdown_menu_items" href="' . $edit_url . '"><i class="bx bx-pencil mx-2"></i> Edit</a>
                    <a class="dropdown-item delete-data dropdown_menu_items" data-url="' . $delete_url . '"><i class="bx bx-trash mx-2"></i> Delete</a>
                </div>
            </div>';

            return [
                'id' => $m->id,
                'title' => $m->title,
                'message' => $m->message,
                'type' => $m->type,
                'operate' => $action,
            ];
        });

        return response()->json([
            "rows" => $data, // Return the formatted data for the "Actions" field
            "total" => $total,
        ]);
    }

    public function destroy($id)
    {
        $custom_message = CustomMessage::find($id);

        if ($custom_message) {
            $custom_message->delete();
            return response()->json([
                'error' => false,
                'message' => labels('admin_labels.message_deleted_successfully', 'Message deleted successfully!')
            ]);
        } else {
            return response()->json(['error' => labels('admin_labels.data_not_found', 'Data Not Found')]);
        }
    }

    public function edit($data)
    {

        $data = CustomMessage::find($data);

        return view('admin.pages.forms.update_custom_message', [
            'data' => $data
        ]);
    }

    public function update(Request $request, $id)
    {
        $custom_message = CustomMessage::find($id);
        if (!$custom_message) {
            return response()->json(['error' => true, 'message' => labels('admin_labels.data_not_found', 'Data Not Found')], 404);
        } else {
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'type' => [
                    'required',
                    // Add a custom rule to check if the type already exists, excluding the current message's type
                    function ($attribute, $value, $fail) use ($id) {
                        $existingType = CustomMessage::where('type', $value)->where('id', '!=', $id)->exists();
                        if ($existingType) {
                            $fail('You have already this type of custom message.');
                        }
                    }
                ],
                'message' => 'required',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors();

                if ($request->ajax()) {
                    return response()->json(['errors' => $errors->all()], 422);
                }
                return redirect()->back()->withErrors($errors)->withInput();
            }

            $custom_message->title = $request->input('title');
            $custom_message->type = $request->input('type');
            $custom_message->message = $request->input('message');

            $custom_message->save();

            if ($request->ajax()) {
                return response()->json([
                    'message' => labels('admin_labels.message_updated_successfully', 'Message updated successfully'),
                    'location' => route('admin.custom_message.index')
                ]);
            }
        }
    }
    public function delete_selected_data(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:custom_messages,id'
        ]);

        foreach ($request->ids as $id) {
            $custom_message = CustomMessage::find($id);

            if ($custom_message) {
                CustomMessage::where('id', $id)->delete();
            }
        }

        return response()->json([
            'error' => false,
            'message' => labels('admin_labels.message_deleted_successfully', 'Selected messages deleted successfully!'),
        ]);
    }
}
