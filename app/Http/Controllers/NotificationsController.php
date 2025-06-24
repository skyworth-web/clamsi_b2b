<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;

class NotificationsController extends Controller
{
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


    public function get_notifications($offset = 0, $limit = 10, $sort = 'id', $order = 'ASC')
    {
        $user_id = Auth::user()->id ?? null;
        $multipleWhere = [];
        if (request()->has('offset')) {
            $offset = request('offset');
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

        if (request()->has('search') && request('search') !== null) {
            $search = trim(request()->input('search'));
            $multipleWhere = [
                'notifications.id' => $search,
                'notifications.title' => $search,
                'notifications.message' => $search,
            ];
        }

        $count_res = DB::table('notifications')
            ->select(DB::raw('COUNT(id) as total'));

        if (!empty($multipleWhere)) {
            $count_res->where(function ($query) use ($multipleWhere) {
                foreach ($multipleWhere as $column => $value) {
                    $query->orWhere($column, 'like', '%' . $value . '%');
                }
            });
        }

        $total = $count_res->first()->total;

        $search_res = DB::table('notifications')
            ->select('*');

        if (!empty($multipleWhere)) {
            $search_res->where(function ($query) use ($multipleWhere) {
                foreach ($multipleWhere as $column => $value) {
                    $query->orWhere($column, 'like', '%' . $value . '%');
                }
            });
        }

        $city_search_res = $search_res
            ->orderBy($sort, $order)
            ->skip($offset)
            ->take($limit)
            ->get()
            ->toArray();

        $bulkData['total'] = $total;
        $rows = [];
        foreach ($city_search_res as $row) {
            // Fetch usernames based on user IDs
            // $row->users_id = json_decode($row->users_id);
            $row->users_id = json_decode($row->users_id, true) ?? [];
            $row->image = dynamic_image($row->image,80);
            if ($row->send_to == 'all_users' || in_array($user_id, $row->users_id)) {

                $tempRow = [
                    'id' => $row->id,
                    'title' => $row->title,
                    'type' => $row->type,
                    'message' => implode(' ', array_slice(explode(' ', $row->message), 0, 10)) . (str_word_count($row->message) > 10 ? '...' : ''),
                    'send_to' => ucwords(str_replace('_', " ", $row->send_to)),
                    'image' => '<div class="d-flex justify-content-around"><a href="' .  $row->image . '" data-lightbox="banner-' . $row->id . '"><img src="' .  $row->image. '" alt="Avatar" class="rounded table-image"/></a></div>',
                    'link' => $row->link,
                    'full_notification' => '<h4>' . $row->title . '</h4><p>' . $row->message . '</p><img src=' . url($row->image) . ' alt="Notification Image" />',
                ];

                $rows[] = $tempRow;
            }
        }

        $bulkData['rows'] = $rows;

        return response()->json($bulkData);
    }
}
