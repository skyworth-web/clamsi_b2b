<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Notification;
use App\Models\Setting;
use App\Models\UserFcm;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class NotificationController extends Controller
{
    protected $categoryController;
    public function __construct(CategoryController $categoryController)
    {
        $this->categoryController = $categoryController;
    }
    public function index()
    {
        $store_id = getStoreId();
        $categories = Category::where('status', 1)->where('store_id', $store_id)->orderBy('id', 'desc')->get();
        return view('admin.pages.forms.send_notification', compact('categories'));
    }
    public function seller_notification_index()
    {
        $store_id = getStoreId();
        return view('admin.pages.forms.send_seller_notification');
    }
    public function seller_email_notification_index()
    {
        return view('admin.pages.forms.seller_email_notification');
    }

    public function store(Request $request)
    {
        $rules = [
            'send_to' => 'required',
            'type' => 'required',
            'title' => 'required',
            'message' => 'required',
        ];

        switch ($request->type) {
            case 'categories':
                $rules['category_id'] = 'required|exists:categories,id';
                break;
            case 'products':
                $rules['product_id'] = 'required|exists:products,id';
                break;
            case 'notification_url':
                $rules['link'] = 'required|url';
                break;
        }

        if ($request->send_to == 'specific_user' || $request->send_to == 'specific_seller') {
            $rules['select_user_id'] = 'required';
        }

        if ($request->image_checkbox == 'on') {
            $rules['image'] = 'required';
        }

        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        }

        $fcm_key = getSettings('fcm_server_key');
        if (empty($fcm_key)) {
            return response()->json([
                'error' => 'true',
                'message' => labels('admin_labels.no_fcm_server_key_found', 'No FCM server key Found')
            ]);
        }

        $settings = Setting::whereIn('variable', ['firebase_project_id', 'service_account_file'])
            ->pluck('value', 'variable');
        // dd($settings);
        $project_id = $settings['firebase_project_id'] ?? null;
        $service_account_file = $settings['service_account_file'] ?? null;
        // dd(($project_id == '' || $project_id == null) || ($service_account_file == '' || $service_account_file == null));
        if (($project_id == '' || $project_id == null) || ($service_account_file == '' || $service_account_file == null)) {
            return response()->json([
                'error' => true,
                'error_message' => labels('admin_labels.please_add_service_json_file_from_notification_setting', 'Please add service json file from notification setting')
            ]);
        }
        $store_id = getStoreId();
        $data = $request->all();
        $title = $request->input('title');
        $send_to = $request->input('send_to');
        $type = $request->input('type');
        $message = $request->input('message');
        $users = 'all';
        $type_ids = '';
        $category_data = '';
        $is_image_included = (isset($request->image_checkbox) && $request->image_checkbox == 'on') ? TRUE : FALSE;
        $image = $request->image;


        if ($type === 'categories') {
            $type_ids = $request->input('category_id');
            $categoryData = Category::where('id', $request->input('category_id'))->first(); // Use first() to get a single category
            $subcategories = Category::where('parent_id', $request->input('category_id'))->get()->toArray();

            if ($categoryData) {
                $categoryData = $categoryData->toArray();
                $categoryData['image'] = getMediaImageUrl($categoryData['image']);
                $categoryData['name'] = json_decode($categoryData['name'])->en;
                $categoryData['banner'] = getMediaImageUrl($categoryData['banner']);
                $categoryData['children_count'] = count($subcategories);

                foreach ($subcategories as &$subcategory) {
                    $subcategory['image'] = isset($subcategory['image']) && !empty($subcategory['image']) ? getMediaImageUrl($subcategory['image']) : "";
                    $subcategory['banner'] = isset($subcategory['banner']) && !empty($subcategory['banner']) ? getMediaImageUrl($subcategory['banner']) : "";
                    $subcategory['name'] = json_decode($subcategory['name'])->en;
                }

                $categoryData['children'] = $subcategories;
                $category_data = $categoryData;
                // dd($categoryData);
            }
        } elseif ($type === 'products') {
            $type_ids = $request->input('product_id');
        } else {
            $type_ids = '';
        }
        if (isset($send_to) && $send_to == 'specific_user') {
            $user_ids = $request->input("select_user_id", []);
            if (empty($user_ids)) {
                return response()->json(['error' => 'User IDs are not provided.']);
            }
            $results = UserFcm::join('users', 'user_fcm.user_id', '=', 'users.id')
                ->whereIn('user_fcm.user_id', $user_ids)
                ->select('user_fcm.fcm_id', 'users.is_notification_on')
                ->get();
            $fcm_ids = [];
            foreach ($results as $result) {
                if (is_object($result)) {
                    if ($result->is_notification_on == 0) {
                        return response()->json([
                            'notification' => [],
                            'data' => [],
                            'error' => true,
                            'error_message' => 'One or more users have notifications turned off.',
                        ]);
                    }
                    $fcm_ids[] = $result->fcm_id;
                }
            }
            if (empty($fcm_ids)) {
                return response()->json([
                    'notification' => [],
                    'data' => [],
                    'error' => true,
                    'error_message' => 'FCM IDs are not set for the selected users.',
                ]);
            }
        } elseif (isset($send_to) && $send_to == 'specific_seller') {
            $user_ids = $request->input("select_user_id", []);
            // dd($user_ids);
            if (empty($user_ids)) {
                return response()->json(['error' => 'User IDs are not provided.']);
            }
            $results = UserFcm::join('users', 'user_fcm.user_id', '=', 'users.id')
                ->whereIn('user_fcm.user_id', $user_ids)
                ->where('users.role_id', 4)
                ->select('user_fcm.fcm_id', 'users.is_notification_on')
                ->get();
            $fcm_ids = [];
            // dd($results);
            foreach ($results as $result) {
                if (is_object($result)) {
                    if ($result->is_notification_on == 0) {
                        return response()->json([
                            'notification' => [],
                            'data' => [],
                            'error' => true,
                            'error_message' => 'One or more sellers have notifications turned off.',
                        ]);
                    }
                    $fcm_ids[] = $result->fcm_id;
                }
            }
            if (empty($fcm_ids)) {
                return response()->json([
                    'notification' => [],
                    'data' => [],
                    'error' => true,
                    'error_message' => 'FCM IDs are not set for the selected sellers.',
                ]);
            }
        } elseif (isset($send_to) && $send_to == 'all_sellers') {
            /* To all sellers */
            $user_fcm1 = UserFcm::join('users', 'user_fcm.user_id', '=', 'users.id')
                ->where('users.is_notification_on', 1)
                ->where('role_id', '4')
                ->select('user_fcm.fcm_id')
                ->get();

            foreach ($user_fcm1 as $fcm) {
                if (is_object($fcm)) {
                    // If it's an object, get the fcm_id property
                    $fcm_ids[] = $fcm->fcm_id;
                } else {
                    // If it's already a string (FCM ID directly)
                    $fcm_ids[] = $fcm;
                }
            }
        } else {
            /* To all users */
            $user_fcm1 = UserFcm::join('users', 'user_fcm.user_id', '=', 'users.id')
                ->where('users.is_notification_on', 1)
                ->where('role_id', '!=', '4')
                ->select('user_fcm.fcm_id')
                ->get();

            foreach ($user_fcm1 as $fcm) {
                if (is_object($fcm)) {
                    // If it's an object, get the fcm_id property
                    $fcm_ids[] = $fcm->fcm_id;
                } else {
                    // If it's already a string (FCM ID directly)
                    $fcm_ids[] = $fcm;
                }
            }
        }

        if (empty($fcm_ids)) {

            return response()->json([
                'notification' => [],
                'data' => [],
                'error' => true,
                'error_message' => 'User fcm id is not set',
            ]);
        }


        if ($is_image_included == true) {
            $fcmMsg = array(
                'title' => "$title",
                'body' => "$message",
                'type' => "$type",
                'type_id' => "$type_ids",
                'store_id' => "$store_id",
                'image' => getImageUrl($image),
                'link' => (isset($data['link']) && !empty($data['link']) ? $data['link'] : ''),

            );
        } else {
            //if the push don't have an image give null in place of image
            $fcmMsg = array(
                'title' => "$title",
                'body' => "$message",
                'image' => '',
                'type' => "$type",
                'type_id' => "$type_ids",
                'store_id' => "$store_id",
                'link' => (isset($data['link']) && !empty($data['link']) ? $data['link'] : ''),

            );
        }

        $registrationIDs = $fcm_ids;

        if ($request->input('send_to') == 'specific_user' || $request->input('send_to') == 'specific_seller') {
            $select_user_id = $request->has('select_user_id') ? json_encode($request->input('select_user_id')) : json_encode([]);
        }


        $notification_image_name = $request->input('image');

        $notification = new Notification();
        $notification->send_to = $request->send_to;
        $notification->store_id = $store_id;
        $notification->type = $request->type;
        $notification->title = $request->title;
        $notification->message = $request->message;
        $notification->type_id = $type_ids;
        $notification->link = $request->link;
        $notification->users_id = isset($select_user_id) && !empty($select_user_id) ? $select_user_id : '';
        $notification->image = ($is_image_included == 'true') ? $notification_image_name : '';

        $notification->save();
        $registrationIDs_chunks = array_chunk($registrationIDs, 1000);

        sendNotification('', $registrationIDs_chunks, $fcmMsg);

        if ($request->ajax()) {
            return response()->json([
                'error' => 'false',
                'message' => labels('admin_labels.notification_sent_successfully', 'Notification Sended Successfully')
            ]);
        }
    }
    public function store_email_notification(Request $request)
    {
        $rules = [
            'send_to' => 'required',
            'subject' => 'required',
            'message' => 'required',
        ];

        if ($request->send_to == 'specific_seller') {
            $rules['select_user_id'] = 'required';
        }

        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        }


        $email_settings = getSettings('email_settings', true);
        $email_settings = json_decode($email_settings, true);
        // dd($email_settings);
        if ($email_settings) {
            $email = $email_settings['email'] ?? "";
            $password = $email_settings['password'] ?? "";
            $smtp_host = $email_settings['smtp_host'] ?? "";
            if ($email == '' || $password == null || $smtp_host == '') {
                return response()->json([
                    'error' => true,
                    'error_message' => labels('admin_labels.please_add_smtp_settings_from_email_setting', 'Please add SMTP settings from email setting')
                ]);
            }
        }
        // dd($request);
        $sendTo = $request->input('send_to');
        $subject = $request->input('subject');
        $messageContent = $request->input('message');
        if ($sendTo === 'all_sellers') {
            // Get all users with status 1 and role_id 4
            $users = User::where('status', 1)->where('role_id', 4)->get();
        } else {
            // Get specific users from the request
            $userIds = $request->input('select_user_id', []);
            $users = User::whereIn('id', $userIds)->get();
        }
        // Send emails
        foreach ($users as $user) {
            $email = $user->email;
            Mail::send([], [], function ($message) use ($email, $subject, $messageContent) {
                $message->to($email)
                    ->subject($subject)
                    ->html($messageContent);
            });
        }
        return response()->json([
            'error' => 'false',
            'message' => labels('admin_labels.mail_sent_successfully', 'Mail Sended Successfully')
        ]);
    }

    public function list($offset = 0, $limit = 10, $sort = 'id', $order = 'ASC')
    {
        $store_id = !empty(request('store_id')) ? request('store_id') : getStoreId();
        if (request()->has('offset')) {
            $offset = request()->input('search') || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        }

        if (request()->has('limit')) {
            $limit = request('limit');
        }

        if (request()->has('sort')) {
            $sort = request('sort') == 'id' ? 'id' : request('sort');
        }

        if (request()->has('order')) {
            $order = request('order');
        }


        $count_res = DB::table('notifications')
            ->select(DB::raw('COUNT(id) as total'));

        $count_res->where('store_id', $store_id);

        if (request()->filled('search')) {
            $search = trim(request('search'));
            $count_res->where(function ($q) use ($search) {
                $q->where('id', (string) $search)
                    ->orWhere('message', 'LIKE', "%$search%")
                    ->orWhere('title', 'LIKE', "%$search%");
            });
        }

        $total = $count_res->first()->total;

        $search_res = DB::table('notifications')
            ->select('*');

        if (request()->filled('search')) {
            $search = trim(request('search'));
            $search_res->where(function ($q) use ($search) {
                $q->where('id', (string) $search)
                    ->orWhere('message', 'LIKE', "%$search%")
                    ->orWhere('title', 'LIKE', "%$search%");
            });
        }

        $search_res->where('store_id', $store_id);

        $city_search_res = $search_res
            ->orderBy($sort, $order)
            ->skip($offset)
            ->take($limit)
            ->get()
            ->toArray();

        $bulkData['total'] = $total;
        $rows = [];

        foreach ($city_search_res as $row) {
            $delete_url = route('admin.notification.destroy', $row->id);
            $action = '<div class="d-flex align-items-center">
                    <a class="dropdown-item single_action_button delete-data dropdown_menu_items" data-url="' . $delete_url . '"><i class="bx bx-trash mx-2"></i></a>
                </div>';
            // Fetch usernames based on user IDs

            $image = route('admin.dynamic_image', [
                'url' => getMediaImageUrl($row->image),
                'width' => 60,
                'quality' => 90
            ]);
            $usernames = $this->get_users_by_ids($row->users_id);

            $tempRow = [
                'id' => $row->id,
                'title' => $row->title,
                'type' => $row->type,
                'message' => $row->message,
                'send_to' => ucwords(str_replace('_', " ", $row->send_to)),
                'users' => $usernames,
                'image' => '<div class="d-flex justify-content-around"><a href="' . asset('/storage/' . $row->image) . '" data-lightbox="banner-' . $row->id . '"><img src="' . $image . '" alt="Avatar" class="rounded"/></a></div>',
                'link' => $row->link,

                'operate' => $action,
            ];

            $rows[] = $tempRow;
        }



        $bulkData['rows'] = $rows;

        return response()->json($bulkData);
    }
    public function seller_notification_list($offset = 0, $limit = 10, $sort = 'id', $order = 'ASC')
    {
        $store_id = !empty(request('store_id')) ? request('store_id') : getStoreId();
        if (request()->has('offset')) {
            $offset = request()->input('search') || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        }

        if (request()->has('limit')) {
            $limit = request('limit');
        }

        if (request()->has('sort')) {
            $sort = request('sort') == 'id' ? 'id' : request('sort');
        }

        if (request()->has('order')) {
            $order = request('order');
        }


        $count_res = DB::table('notifications')
            ->whereIn('send_to', ['specific_seller', 'all_sellers'])
            ->select(DB::raw('COUNT(id) as total'));

        $count_res->where('store_id', $store_id);

        if (request()->filled('search')) {
            $search = trim(request('search'));
            $count_res->where(function ($q) use ($search) {
                $q->where('id', (string) $search)
                    ->orWhere('message', 'LIKE', "%$search%")
                    ->orWhere('title', 'LIKE', "%$search%");
            });
        }
        // dd($count_res->toSql());
        $total = $count_res->first()->total;

        $search_res = DB::table('notifications')
            ->select('*');

        if (request()->filled('search')) {
            $search = trim(request('search'));
            $search_res->where(function ($q) use ($search) {
                $q->where('id', (string) $search)
                    ->orWhere('message', 'LIKE', "%$search%")
                    ->orWhere('title', 'LIKE', "%$search%");
            });
        }

        $search_res->where('store_id', $store_id);
        $search_res->whereIn('send_to', ['specific_seller', 'all_sellers']);
        // dd($search_res->toSql(),$search_res->getBindings());
        $city_search_res = $search_res
            ->orderBy($sort, $order)
            ->skip($offset)
            ->take($limit)
            ->get()
            ->toArray();

        $bulkData['total'] = $total;
        $rows = [];

        foreach ($city_search_res as $row) {
            $delete_url = route('admin.notification.destroy', $row->id);
            $action = '<div class="d-flex align-items-center">
                    <a class="dropdown-item single_action_button delete-data dropdown_menu_items" data-url="' . $delete_url . '"><i class="bx bx-trash mx-2"></i></a>
                </div>';
            // Fetch usernames based on user IDs

            $image = route('admin.dynamic_image', [
                'url' => getMediaImageUrl($row->image),
                'width' => 60,
                'quality' => 90
            ]);
            $usernames = $this->get_users_by_ids($row->users_id);

            $tempRow = [
                'id' => $row->id,
                'title' => $row->title,
                'type' => $row->type,
                'message' => $row->message,
                'send_to' => ucwords(str_replace('_', " ", $row->send_to)),
                'users' => $usernames,
                'image' => '<div class="d-flex justify-content-around"><a href="' . asset('/storage/' . $row->image) . '" data-lightbox="banner-' . $row->id . '"><img src="' . $image . '" alt="Avatar" class="rounded"/></a></div>',
                'link' => $row->link,

                'operate' => $action,
            ];

            $rows[] = $tempRow;
        }



        $bulkData['rows'] = $rows;

        return response()->json($bulkData);
    }

    public function destroy($id)
    {
        $notification = Notification::find($id);

        if ($notification->delete()) {
            return response()->json([
                'error' => false,
                'message' => labels('admin_labels.notification_deleted_successfully', 'Notification deleted successfully!')
            ]);
        } else {
            return response()->json(['error' => labels('admin_labels.something_went_wrong', 'Something went wrong')]);
        }
    }
    public function get_users_by_ids($user_ids)
    {
        // Decode the JSON-encoded string into an array
        $ids_array = json_decode($user_ids);

        // Ensure the array is not empty
        if (empty($ids_array)) {
            return '';
        }

        // Fetch the users based on the array of IDs
        $users = User::whereIn('id', $ids_array)->get();

        // Extract the 'username' attribute from each User model
        $users_array = $users->pluck('username')->toArray();

        // Join the usernames into a comma-separated string
        $comma_separated_users = implode(',', $users_array);

        return $comma_separated_users;
    }

    public function get_notifications($offset, $limit, $sort, $order, $user_id = '', $language_code = '')
    {
        $notificationData = [];

        // Calculate total number of notifications
        $countRes = DB::table('notifications')->select(DB::raw('COUNT(id) as total'))->get()->toArray();

        // Adjust the query to filter by user_id if provided, or to include all general notifications
        $query = Notification::orderBy($sort, $order)
            ->where(function ($q) use ($user_id) {
                $q->where('send_to', 'all_users')
                    ->orWhereJsonContains('users_id', (string) $user_id);
            })

            ->limit($limit)
            ->offset($offset);

        $searchRes = $query->get()->toArray();

        foreach ($searchRes as $key => $notification) {
            $searchRes[$key]['title'] = $notification['title'] ?? '';
            $searchRes[$key]['message'] = $notification['message'] ?? '';
            $searchRes[$key]['send_to'] = $notification['send_to'] ?? '';
            $usersId = !empty($notification['users_id']) ? implode(',', json_decode($notification['users_id'], true)) : '';
            $searchRes[$key]['users_id'] = $usersId;
            $searchRes[$key]['link'] = $notification['link'] ?? '';

            // Handling the image path conversion
            if (!empty($notification['image'])) {
                $searchRes[$key]['image'] = getMediaImageUrl($notification['image']);
            }

            // Fetch category data if type is 'categories'
            if ($searchRes[$key]['type'] === 'categories' && !empty($searchRes[$key]['type_id'])) {
                $categoryData = Category::where('id', $searchRes[$key]['type_id'])->first();
                $subcategories = Category::where('parent_id', $searchRes[$key]['type_id'])->get()->toArray();

                if ($categoryData) {
                    $categoryData = $categoryData->toArray();
                    $categoryData['image'] = getMediaImageUrl($categoryData['image']);
                    $categoryData['name'] = getDynamicTranslation('categories', 'name', $categoryData['id'], $language_code);
                    $categoryData['banner'] = getMediaImageUrl($categoryData['banner']);
                    $categoryData['children_count'] = count($subcategories);

                    foreach ($subcategories as &$subcategory) {
                        $subcategory['image'] = getMediaImageUrl($subcategory['image'] ?? "");
                        $subcategory['name'] = getDynamicTranslation('categories', 'name', $subcategory['id'], $language_code);
                        $subcategory['banner'] = getMediaImageUrl($subcategory['banner'] ?? "");
                    }

                    $categoryData['children'] = $subcategories;
                    $searchRes[$key]['category_data'] = $categoryData;
                }
            }
        }

        $notificationData['total'] = $countRes[0]->total;
        $notificationData['data'] = $searchRes;

        return $notificationData;
    }
    public function get_seller_notifications($offset, $limit, $sort, $order, $user_id = '')
    {
        $notificationData = [];

        // Calculate total number of notifications
        $countRes = DB::table('notifications')->select(DB::raw('COUNT(id) as total'))->whereIn('send_to', ['specific_seller', 'all_sellers'])->get()->toArray();

        $query = Notification::orderBy($sort, $order)
            ->where(function ($q) use ($user_id) {
                $q->whereIn('send_to', ['specific_seller', 'all_sellers'])
                    ->orWhereJsonContains('users_id', (string) $user_id);
            })

            ->limit($limit)
            ->offset($offset);

        $searchRes = $query->get()->toArray();

        foreach ($searchRes as $key => $notification) {
            $searchRes[$key]['title'] = $notification['title'] ?? '';
            $searchRes[$key]['message'] = $notification['message'] ?? '';
            $searchRes[$key]['send_to'] = $notification['send_to'] ?? '';
            $usersId = !empty($notification['users_id']) ? implode(',', json_decode($notification['users_id'], true)) : '';
            $searchRes[$key]['users_id'] = $usersId;
            $searchRes[$key]['link'] = $notification['link'] ?? '';
            if (!empty($notification['image'])) {
                $searchRes[$key]['image'] = getMediaImageUrl($notification['image']);
            }
        }

        $notificationData['total'] = $countRes[0]->total;
        $notificationData['data'] = $searchRes;

        return $notificationData;
    }
    public function delete_selected_data(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:notifications,id'
        ]);

        foreach ($request->ids as $id) {
            $notification = Notification::find($id);

            if ($notification) {
                Notification::where('id', $id)->delete();
            }
        }

        return response()->json([
            'error' => false,
            'message' => labels('admin_labels.notifications_deleted_successfully', 'Selected notifications deleted successfully!'),
        ]);
    }
}
