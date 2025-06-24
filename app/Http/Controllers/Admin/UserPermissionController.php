<?php

namespace App\Http\Controllers\Admin;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;

class UserPermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::all()->groupBy(function ($permission) {
            $name = $permission->name;
            $segments = explode(' ', $name);
            return end($segments);
        });

        return view('admin.pages.forms.system_users', compact('permissions'));
    }

    public function store(Request $request)
    {
        $rules = [
            'username' => 'required',
            'mobile' => 'required|unique:users,mobile',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
            'role' => 'required',
        ];

        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        }
        // Store the data in your database
        $user = new User();
        $user->username = $request->input('username');
        $user->mobile = $request->input('mobile');
        $user->email = $request->input('email');
        $user->password = bcrypt($request->input('password'));
        $user->role_id = $request->input('role');
        $user->active = 1;
        $user->save();

        // Update permissions for the user
        $permissions = $request->input('permissions');

        $this->permissionsUpdate($request, $user->id, $permissions);

        if ($request->ajax()) {
            return response()->json([
                'message' => labels('admin_labels.system_user_added_successfully', 'System user Added Successfully'),
                'location' => route('admin.manage_system_users')
            ]);
        }
    }

    public function permissionsUpdate(Request $request, $id)
    {

        $user = User::findOrFail($id);

        $permissions = $request->input('permissions');
        $data = $user->syncPermissions($permissions);

        if ($request->ajax()) {
            return response()->json([
                'message' => labels('admin_labels.permissions_updated_successfully', 'Permissions updated successfully')
            ]);
        }
    }

    public function manageSystemUsers()
    {
        return view('admin.pages.tables.manage_system_users');
    }



    public function systemUsersList(Request $request)
    {
        $search = trim($request->input('search'));
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'DESC');
        $limit = $request->input('limit', 10);
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;

        $query = User::whereIn('role_id', [1, 5, 6])
            ->when($search, function ($query) use ($search) {
                return $query->where('username', 'like', '%' . $search . '%')
                    ->orWhere('mobile', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            })
            ->orderBy($sort, $order)
            ->with('role');
        $total = $query->count();

        // Use Paginator to handle the server-side pagination
        $users = $query->orderBy($sort, $order)->offset($offset)
            ->limit($limit)
            ->get();

        // Prepare the data for the "Actions" field
        $data = $users->map(function ($u) {
            // Convert role name to "Super Admin" if it's "super_admin"
            $role_name = $u->role->name == 'super_admin' ? 'Super Admin' : $u->role->name;

            $delete_url = route('system_user.destroy', $u->id);
            $edit_url = route('system_user.edit', $u->id);
            $action = '<div class="dropdown height-100">
        <a href="#" class="text-dark" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="bx bx-dots-horizontal-rounded"></i>
        </a>
        <div class="dropdown-menu table_dropdown system_user_action_dropdown" aria-labelledby="dropdownMenuButton">
            <a class="dropdown-item dropdown_menu_items" href="' . $edit_url . '"><i class="bx bx-pencil mx-2"></i> Edit</a>
            <a class="dropdown-item delete-data dropdown_menu_items" data-url="' . $delete_url . '"><i class="bx bx-trash mx-2"></i> Delete</a>
        </div>
    </div>';

            return [
                'id' => $u->id,
                'username' => $u->username,
                'mobile' => $u->mobile,
                'email' => $u->email,
                'role' => '<span class="badge badge bg-info">' . $role_name . '</span>',
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
        $user = User::find($id);

        if ($user) {
            $user->delete();
            return response()->json(['error' => false, 'message' => labels('admin_labels.user_deleted_successfully', 'User deleted Successfully')]);
        } else {
            return response()->json(['error' => labels('admin_labels.data_not_found', 'Data Not Found')]);
        }
    }

    public function edit($user_id)
    {

        $user = User::findOrFail($user_id);
        $permissions = Permission::all();

        // Get all permissions and format them for display in the table
        $permissions = Permission::all()->groupBy(function ($permission) {
            $name = $permission->name;

            $segments = explode(' ', $name);

            return end($segments);
        })->map(function ($item) use ($user) {
            return $item->mapWithKeys(function ($permission) use ($user) {

                $permissionName = $permission->name;

                $isChecked = $user->hasPermissionTo($permissionName);
                return ["{$permissionName}" => $isChecked ? 'checked' : ''];
            })->merge([
                'view' => '',
                'create' => '',
                'edit' => '',
                'delete' => '',
            ]);
        });

        return view('admin.pages.forms.update_system_users', compact('user', 'permissions'));
    }
    public function delete_selected_data(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id'
        ]);

        foreach ($request->ids as $id) {
            $user = User::find($id);

            if ($user) {
                User::where('id', $id)->delete();
            }
        }

        return response()->json([
            'error' => false,
            'message' => labels('admin_labels.user_deleted_successfully', 'Selected users deleted successfully!'),
        ]);
    }
}
