<?php

namespace App\Http\Controllers\Admin;

use App\Models\Role;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\File;
use App\Models\Store;
use App\Models\Category;
use App\Models\Media;
use Exception;

class UserController extends Controller
{
    public function login()
    {
        return view('admin/pages/forms/login');
    }
    public function seller_login()
    {
        return view('seller/pages/forms/login');
    }
    public function delivery_boy_login()
    {
        return view('delivery_boy/pages/forms/login');
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login')->withHeaders([
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    public function seller_logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/onboard')->withHeaders([
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
    public function delivery_boy_logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/delivery_boy/login')->withHeaders([
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    public function authenticate(Request $request)
    {
        $formFields = $request->validate([
            'password' => 'required',
            'mobile' => 'required',
        ]);

        if (auth()->attempt($formFields)) {

            $user = User::with('role')
                ->where('active', 1)
                ->find(Auth::user()->id);

            if ($user) {
                if ($user->role->name == 'delivery_boy') {
                    if ($user->status != 1) {
                        $response = ['errors' => ['status' => ['Your account is not active. Please contact super admin.']]];
                        return response()->json($response, 422);
                    }
                    $response = ['message' => 'Login successful', 'location' => '/delivery_boy/home'];
                    return response()->json($response);
                }
                if ($user->role->name == 'seller') {
                    $response = ['message' => 'Login successful', 'location' => '/seller/home'];
                    return response()->json($response);
                } elseif ($user->role->name == 'super_admin' || $user->role->name == 'admin' || $user->role->name == 'editor') {
                    $response = ['message' => 'Login successful', 'location' => '/admin/home'];
                    return response()->json($response);
                } else {
                    $response = ['errors' => ['role' => ['You do not have access to this panel']]];
                    return response()->json($response, 422);
                }
            } else {
                $response = ['errors' => ['account' => ['Your account is not activated yet. Please wait for activation.']]];
                return response()->json($response, 422);
            }
        } else {
            $response = ['errors' => ['email' => ['Invalid credentials']]];
            return response()->json($response, 422);
        }
    }


    public function edit(User $user)
    {
        return view('admin.pages.forms.account', ['user' => $user]);
    }


    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'username' => ['required'],
            'email' => ['required'],
            'mobile' => 'required',
        ]);

        if (!empty($request->input('old_password')) || !empty($request->input('new_password'))) {
            $validator = Validator::make($request->all(), [
                'old_password' => 'required',
                'new_password' => ['required', 'confirmed'],
                'image' => 'image|mimes:jpeg,gif,jpg,png',
            ]);
        }

        if ($validator->fails()) {
            if ($request->ajax()) {
                throw ValidationException::withMessages($validator->errors()->all());
            }

            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = User::find($id);

        // Check if the old password matches the one in the database
        if (!empty($request->input('old_password'))) {
            if (!Hash::check($request->old_password, $user->password)) {
                if ($request->ajax()) {
                    return response()->json([
                        'message' => labels('admin_labels.incorrect_old_password', 'The old password is incorrect.')
                    ], 422);
                }
                return redirect()->back()->withErrors([
                    'old_password' => labels('admin_labels.incorrect_old_password', 'The old password is incorrect.')
                ])->withInput();
            }
        }

        $userImgPath = public_path(config('constants.USER_IMG_PATH'));

        if (!File::exists($userImgPath)) {
            File::makeDirectory($userImgPath, 0755, true);
        }


        if ($validator->fails()) {
            return response()->json(['error' => true, 'message' => $validator->errors()], 400);
        }

        //----------------- image upload code ----------------------------

        $mediaItem = [];
        try {


            $media_storage_settings = fetchDetails('storage_types', ['is_default' => 1], '*');

            $disk = isset($media_storage_settings) && !empty($media_storage_settings) ? $media_storage_settings[0]->name : 'public';
            if ($request->hasFile('image')) {

                // Specify the path and disk from which you want to delete the file
                if ($disk == 's3') {
                    $path = $request->input('edit_image');
                } else {
                    $path = 'store_images/' . $request->input('edit_image'); // Example path to the file you want to delete
                }

                //Call the removeFile method to delete the file
                removeMediaFile($path, $disk);

                $mediaFile = $request->file('image');

                $mediaItem = $user->addMedia($mediaFile)
                    ->sanitizingFileName(function ($fileName) use ($user) {
                        // Replace special characters and spaces with hyphens
                        $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));

                        // Generate a unique identifier based on timestamp and random component
                        $uniqueId = time() . '_' . mt_rand(1000, 9999);

                        $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                        $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                        return "{$baseName}-{$uniqueId}.{$extension}";
                    })
                    ->toMediaCollection('user_image', $disk);

                $media_list = $user->getMedia('user_image');
                $media_url = $media_list[0]->getUrl();
            }
            if (isset($mediaItem->file_name)) {
                $image = $disk == 's3' ? (isset($media_url) ? $media_url : '') : (isset($mediaItem->file_name) ? '/' . $mediaItem->file_name : '');
            } else {
                $image = $user->image;
            }
        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ]);
        }


        // Update the user's other details
        $formFields = [
            'username' => $request->username,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'address' => $request->address,
            'image' => $image,
            'disk' => $disk,
        ];
        $user->update($formFields);
        if (!empty($mediaItem)) {

            Media::destroy($mediaItem->id);
        }

        // Update the password if a new password is provided
        if ($request->new_password) {
            $user->password = Hash::make($request->new_password);
            $user->save();
        }

        if ($request->ajax()) {
            return response()->json(['message' => labels('admin_labels.profile_details_updated_successfully', 'Profile details updated successfully!')]);
        }

        return back()->with('message', labels('admin_labels.profile_details_updated_successfully', 'Profile details updated successfully!'));
    }


    public function updatePhoto(Request $request, $id)
    {

        if ($request->hasFile('upload')) {
            $formFields['photo'] = $request->file('upload')->store('photos', 'public');
            User::find($id)->update($formFields);
            session()->flash('success', 'Image Upload successfully');
            return back()->with('message', labels('admin_labels.profile_picture_updated_successfully', 'Profile picture update Successfully!'));
        }
    }
    public function destroy($id)
    {
        $user = User::find($id);

        if ($user) {
            $user->delete();
            return response()->json([
                'error' => false,
                'message' => labels('admin_labels.user_deleted_successfully', 'User deleted successfully!')
            ]);
        } else {
            return response()->json(['error' => labels('admin_labels.data_not_found', 'Data Not Found')]);
        }
    }


    public function store(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => ['required'],
            'last_name' => ['required'],
            'email' => ['required', 'email'],
            'password' => 'required|confirmed|min:6'
        ]);
        if ($validator->fails()) {
            // Return the validation errors as a JSON response
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $formFields['first_name'] = $request->first_name;
        $formFields['last_name'] = $request->last_name;
        $formFields['email'] = $request->email;
        $formFields['password'] = bcrypt($request->password);
        $formFields['photo'] = "photos/no-image.png";
        $formFields['role_id'] = 2;
        $formFields['status'] = 1;

        $user = User::create($formFields);

        auth()->login($user);

        if ($request->ajax()) {
            return response()->json(['message' => labels('admin_labels.registered_successfully', 'Registered Successfully!')]);
        } else {
            return redirect('/login')->with('message', labels('admin_labels.registered_successfully', 'Registered Successfully!'));
        }
    }

    public function searchUser(Request $request)
    {
        $search_term = trim($request->input('search'));

        $users = User::select('id', 'username', 'active')
            ->where('username', 'like', '%' . $search_term . '%')
            ->where('active', '1')
            ->where('role_id', '!=', '4')
            ->get();

        $data = [];
        foreach ($users as $user) {
            $data[] = [
                "id" => $user->id,
                "text" => $user->username,
            ];
        }

        return response()->json($data);
    }
    public function searchSeller(Request $request)
    {
        $search_term = trim($request->input('search'));

        $users = User::select('users.id', 'users.username')
            ->join('seller_data', 'seller_data.user_id', '=', 'users.id')
            ->where('users.username', 'like', '%' . $search_term . '%')
            ->where('users.active', '1')
            ->where('users.role_id', '4')
            ->where('seller_data.status', '1')
            ->get();

        $data = [];
        foreach ($users as $user) {
            $data[] = [
                "id" => $user->id,
                "text" => $user->username,
            ];
        }

        return response()->json($data);
    }
    public function customers()
    {
        $customers = User::where('role_id', 2)->get();

        return view('admin.pages.tables.customers', ['customers' => $customers]);
    }

    public function getCustomersList()
    {
        $search = trim(request('search'));
        // $offset = trim(request()->input('search')) ? 0 : request()->input('offset', 0);
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = request('limit', 10);
        $sort = request('sort', 'id');
        $order = request('order', 'asc');
        $status = request('status', '');
        // dd($status);
        $query = User::where('role_id', 2);

        if ($search) {
            $query->where(function ($subquery) use ($search) {
                $subquery->orWhere('id', $search)
                    ->orWhere('username', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('mobile', 'like', '%' . $search . '%');
            });
        }

        if ($status != "") {
            $query->where('active', $status);
        }


        $total = $query->count();


        $customers = $query->orderBy($sort, $order)->offset($offset)
            ->limit($limit)
            ->get();

        $bulkData = [];
        $bulkData['total'] = $total;
        $rows = [];

        foreach ($customers as $row) {
            $viewOrderUrl = route('admin.orders.index', ['user_id' => $row->id]);
            $viewTransactionUrl = route('admin.customers.viewTransactions', ['user_id' => $row->id]);

            $delete_url = route('customers.destroy', $row->id);

            $action = '<div class="dropdown bootstrap-table-dropdown">
                <a href="#" class="text-dark" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="bx bx-dots-horizontal-rounded"></i>
                </a>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                <a class="dropdown-item dropdown_menu_items" href="' . $viewOrderUrl . '"><i class="bx bxs-show mx-2"></i> View Orders</a>
                <a class="dropdown-item dropdown_menu_items" href="' . $viewTransactionUrl . '"><i class="bx bxs-show mx-2"></i>View Transaction</a>
                <a class="dropdown-item dropdown_menu_items" href="" data-id="' . $row->id . '" data-bs-toggle="modal" data-bs-target="#customer-address-modal"><i class="bx bxs-show mx-2"></i> View Address</a>
                <a class="dropdown-item delete-data dropdown_menu_items" data-url="' . $delete_url . '"><i class="bx bx-trash mx-2"></i> Delete</a>
                </div>
            </div>';

            $rows[] = [
                'id' => $row->id,
                'name' => $row->username,
                'email' => $row->email,
                'mobile' => $row->mobile,
                'balance' => $row->balance,
                'operate' => $action,
                'status' => '<select class="form-select status_dropdown change_toggle_status ' . ($row->active == 1 ? 'active_status' : 'inactive_status') . '" data-id="' . $row->id . '" data-url="/admin/customers/update_status/' . $row->id . '" aria-label="">
                  <option value="1" ' . ($row->active == 1 ? 'selected' : '') . '>Active</option>
                  <option value="0" ' . ($row->active == 0 ? 'selected' : '') . '>Deactive</option>
              </select>',

            ];
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }
    public function update_status($id)
    {
        $user = User::findOrFail($id);
        $user->active = $user->active == '1' ? '0' : '1';

        $user->save();
        return response()->json(['success' => labels('admin_labels.status_updated_successfully', 'Status updated successfully.')]);
    }

    public function getCustomersAddresses(Request $request)
    {
        $view_id = $request['user_id'];
        return view('admin.pages.tables.manage_address', compact('view_id'));
    }
    public function getCustomersAddressesList($user_id = '')
    {

        // $offset = trim(request()->input('search')) ? 0 : request()->input('offset', 0);
        $offset = request()->input('search') || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = request()->input('limit', 10);
        $sort = request()->input('sort', 'id');
        $order = request()->input('order', 'ASC');
        $user_id = !empty($user_id) && $user_id != '' ? $user_id : request()->input('user_id');
        $multipleWhere = [];

        if (request()->has('user_id') && !empty(request()->input('user_id'))) {
            $where['user_id'] = request()->input('user_id');
        }

        if (!empty($user_id)) {
            $where['user_id'] = $user_id;
        }

        if (request()->has('search') && trim(request()->input('search')) != '') {
            $search = trim(request()->input('search'));
            $multipleWhere = [
                'addr.name' => $search,
                'addr.address' => $search,
                'mobile' => $search,
                'area' => $search,
                'city' => $search,
                'state' => $search,
                'country' => $search,
                'pincode' => $search
            ];
        }

        $countQuery = DB::table('addresses as addr')
            ->select(DB::raw('COUNT(addr.id) as total'), 'addr.*');

        if (!empty($multipleWhere)) {
            $countQuery->orWhere(function ($query) use ($multipleWhere) {
                foreach ($multipleWhere as $field => $value) {
                    $query->orWhere($field, 'like', '%' . $value . '%');
                }
            });
        }

        if (!empty($where)) {
            $countQuery->where($where);
        }

        $addressCount = $countQuery->first();
        $total = $addressCount->total;

        $searchQuery = DB::table('addresses as addr')
            ->select('addr.*');

        if (!empty($multipleWhere)) {
            $searchQuery->orWhere(function ($query) use ($multipleWhere) {
                foreach ($multipleWhere as $field => $value) {
                    $query->orWhere($field, 'like', '%' . $value . '%');
                }
            });
        }

        if (!empty($where)) {
            $searchQuery->where($where);
        }

        $addressSearchResult = $searchQuery->orderBy($sort, $order)
            ->limit($limit)
            ->offset($offset)
            ->get();

        $bulkData = [
            'total' => $total,
            'rows' => []
        ];

        foreach ($addressSearchResult as $row) {

            $tempRow = [
                'id' => $row->id,
                'name' => $row->name,
                'type' => $row->type,
                'mobile' => $row->mobile,
                'alternate_mobile' => $row->alternate_mobile,
                'address' => $row->address,
                'landmark' => $row->landmark,
                'area' => $row->area,
                'area_id' => $row->area_id,
                'city' => $row->city,
                'city_id' => $row->city_id,
                'state' => $row->state,
                'pincode' => $row->pincode,
                'system_pincode' => $row->system_pincode,
                'pincode_name' => $row->pincode,
                'country' => $row->country,

            ];


            $bulkData['rows'][] = $tempRow;
        }

        return response()->json($bulkData);
    }

    public function viewTransactions(Request $request)
    {
        $user_id = $request['user_id'];
        return view('admin.pages.tables.manage_transactions', compact('user_id'));
    }

    public function walletTransaction()
    {
        return view('admin.pages.tables.manage_customer_wallet');
    }

    public function getTransactionList(SellerController $sellerController)
    {
        $res = $sellerController->wallet_transactions_list();
        return $res;
    }

    public function updateCustomerWallet(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'type' => 'required',
            'amount' => 'required|numeric',
            'message' => 'required'
        ]);
        if ($validator->fails()) {
            // Return the validation errors as a JSON response
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request['type'] == 'debit' || $request['type'] == 'credit') {
            $message = (isset($request['message']) && !empty($request['message'])) ? $request['message'] : "Balance " . $request['type'] . "ed.";
            $response = updateWalletBalance($request['type'], $request['user_id'], $request['amount'], $message);

            return response()->json($response);
        }
    }

    public function getCategories($id = null, $limit = '', $offset = '', $sort = 'row_order', $order = 'ASC', $has_child_or_item = 'true', $slug = '', $ignore_status = '', $seller_id = '')
    {
        $level = 0;
        $store_id = getStoreId();

        if ($ignore_status == 1) {
            $where = (isset($id) && !empty($id)) ? ['id' => $id, 'store_id' => $store_id] : ['parent_id' => 0, 'store_id' => $store_id];
        } else {
            $where = (isset($id) && !empty($id)) ? ['id' => $id, 'status' => 1, 'store_id' => $store_id] : ['parent_id' => 0, 'status' => 1, 'store_id' => $store_id];
        }

        $query = Category::orderBy($sort, $order)
            ->where($where);

        if ($has_child_or_item == 'false') {
            $query->leftJoin('categories as c2', 'c2.parent_id', '=', 'id')
                ->leftJoin('products as p', 'p.category_id', '=', 'id')

                ->where(function ($query) {
                    $query->where('id', '=', DB::raw('p.category_id'))
                        ->orWhere('c2.parent_id', '=', 'id');
                })
                ->groupBy('id');
        } else {

            if (!empty($limit)) {
                $query->take($limit);
            }

            if (!empty($offset)) {
                $query->skip($offset);
            }
        }


        $categories = $query->get();

        $i = 0;
        foreach ($categories as $p_cat) {
            $categories[$i]->children = $this->subCategories($p_cat->id, $level);
            $categories[$i]->text = e($p_cat->name);
            $categories[$i]->name = ($categories[$i]->name);
            $categories[$i]->state = ['opened' => true];
            $categories[$i]->icon = "jstree-folder";
            $categories[$i]->level = $level;
            $p_cat['image'] = getMediaImageUrl($p_cat['image']);
            $p_cat['banner'] = getMediaImageUrl($p_cat['banner']);
            $i++;
        }

        return $categories;
    }

    public function subCategories($id, $level)
    {
        $level = $level + 1;
        $category = Category::find($id);
        $categories = $category->children;

        $i = 0;
        foreach ($categories as $p_cat) {
            $categories[$i]->children = $this->subCategories($p_cat->id, $level);
            $categories[$i]->text = e($p_cat->name); // Use the Laravel "e" helper for output escaping
            $categories[$i]->state = ['opened' => true];
            $categories[$i]->level = $level;
            $p_cat['image'] = getMediaImageUrl($p_cat['image']);
            $p_cat['banner'] = getMediaImageUrl($p_cat['banner']);
            $i++;
        }

        return $categories;
    }

    public function seller_register()
    {
        $store_id = getStoreId();

        $categories = $this->getCategories();


        $stores = Store::where('status', 1)->get();

        return view('seller/pages/forms/register', compact('categories', 'stores'));
    }

    public function sellerStore(Request $request, SellerController $sellerController)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'mobile' => 'required',
            'email' => 'required',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
            'address' => 'required',
            'store_name' => 'required',
            'account_number' => 'required',
            'account_name' => 'required',
            'bank_name' => 'required',
            'bank_code' => 'required',
            'store_logo' => 'required',
            'store_thumbnail' => 'required',
            // 'national_identity_card' => 'required',
            'city' => 'required',
            'zipcode' => 'required',
            'description' => 'required',
            'latitude' => 'numeric|between:-90,90',
            'longitude' => 'numeric|between:-180,180'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            if ($request->ajax()) {
                return response()->json(['errors' => $errors->all()], 422);
            } else {
                $response = [
                    'error' => true,
                    'message' => $validator->errors()->first(),
                    'code' => 102,
                ];
                return response()->json($response);
            }
        } else {
            $res = $sellerController->store($request);
            $responseData = json_decode($res->getContent(), true);

            if (isset($responseData['error_message'])) {
                return response()->json([
                    'message' => $responseData['error_message']
                ]);
            } else {
                return response()->json([
                    'message' => isset($responseData['message']) ? $responseData['message'] : $responseData['errors'],
                    'location' => route('seller.login')
                ]);
            }
        }
    }
    public function delete_selected_data(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id'
        ]);

        foreach ($request->ids as $id) {
            $users = User::find($id);

            if ($users) {
                User::where('id', $id)->delete();
            }
        }
        User::destroy($request->ids);

        return response()->json(['message' => 'Selected data deleted successfully.']);
    }
}
