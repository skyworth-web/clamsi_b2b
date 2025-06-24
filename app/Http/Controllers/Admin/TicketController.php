<?php

namespace App\Http\Controllers\Admin;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\HtmlString;
use SplFileInfo;

class TicketController extends Controller
{
    public function index()
    {
        return view('admin.pages.forms.ticket_types');
    }

    public function store(Request $request)
    {
        $rules = [
            'title' => 'required',
        ];

        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        }

        $ticket_data['title'] = $request->title ?? "";

        TicketType::create($ticket_data);

        if ($request->ajax()) {
            return response()->json([
                'message' => labels('admin_labels.ticket_type_added_successfully', 'Ticket Type added successfully')
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
        $faqs = TicketType::when($search, function ($query) use ($search) {
            return $query->where('title', 'like', '%' . $search . '%');
        });

        $total = $faqs->count();
        $faqs = $faqs->orderBy(
            $sort == 'date_created' ? 'created_at' : $sort,
            $order
        )
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->map(function ($f) {

                $edit_url = route('ticket_types.edit', $f->id);
                $delete_url = route('ticket_types.destroy', $f->id);
                $action = '<div class="dropdown bootstrap-table-dropdown">
                <a href="#" class="text-dark" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                   <i class="bx bx-dots-horizontal-rounded"></i>
                </a>
                <div class="dropdown-menu table_dropdown ticket_action_dropdown" aria-labelledby="dropdownMenuButton">
                <a class="dropdown-item edit-ticket-type dropdown_menu_items" data-id="' . $f->id . '"><i class="bx bx-pencil mx-2"></i> Edit</a>
                    <a class="dropdown-item delete-data dropdown_menu_items" data-url="' . $delete_url . '"><i class="bx bx-trash mx-2"></i> Delete</a>
                </div>
            </div>';

                return [
                    'id' => $f->id,
                    'title' => $f->title,
                    'date_created' =>  Carbon::parse($f->created_at)->format('d-m-Y'),
                    'operate' => $action
                ];
            });

        return response()->json([
            "rows" => $faqs,
            "total" => $total,
        ]);
    }

    public function destroy($id)
    {
        $ticket = TicketType::find($id);

        if ($ticket) {
            $ticket->delete();
            return response()->json([
                'error' => false,
                'message' => labels('admin_labels.ticket_type_deleted_successfully', 'Ticket Type deleted successfully!')
            ]);
        } else {
            return response()->json(['error' => labels('admin_labels.data_not_found', 'Data Not Found')]);
        }
    }

    public function edit($id)
    {
        $ticket = TicketType::find($id);

        if (!$ticket) {
            return response()->json(['error' => true, 'message' => labels('admin_labels.data_not_found', 'Data Not Found')], 404);
        }

        return response()->json($ticket);
    }

    public function update(Request $request, $id)
    {

        $ticket = TicketType::find($id);
        if (!$ticket) {
            return response()->json(['error' => true, 'message' => labels('admin_labels.data_not_found', 'Data Not Found')], 404);
        } else {
            $rules = [
                'title' => 'required',
            ];

            if ($response = validatePanelRequest($request, $rules)) {
                return $response;
            }

            $ticket->title = $request->input('title');

            $ticket->save();

            if ($request->ajax()) {
                return response()->json([
                    'message' => labels('admin_labels.ticket_type_updated_successfully', 'Ticket Type updated successfully')
                ]);
            }
        }
    }

    public function getTickets($ticketId = null, $ticketTypeId = null, $userId = null, $status = null, $search = null, $offset = 0, $limit = 25, $sort = 'id', $order = 'DESC')
    {
        $multipleWhere = [];
        $where = [];

        if (!empty($search)) {
            $multipleWhere = [
                'u.id' => $search,
                'u.username' => $search,
                'u.email' => $search,
                'u.mobile' => $search,
                't.subject' => $search,
                't.email' => $search,
                't.description' => $search,
                'tty.title' => $search,
            ];
        }

        if (!empty($ticketId)) {
            $where[] = ['t.id', '=', $ticketId];
        }

        if (!empty($ticketTypeId)) {
            $where[] = ['t.ticket_type_id', '=', $ticketTypeId];
        }

        if (!empty($userId)) {
            $where[] = ['t.user_id', '=', $userId];
        }

        if (!empty($status)) {
            $where[] = ['t.status', '=', $status];
        }

        $countQuery = DB::table('tickets as t')
            ->leftJoin('ticket_types as tty', 'tty.id', '=', 't.ticket_type_id')
            ->leftJoin('users as u', 'u.id', '=', 't.user_id')
            ->select(DB::raw('COUNT(u.id) as total'));

        $countQuery->where(function ($query) use ($multipleWhere) {
            foreach ($multipleWhere as $column => $value) {
                $query->orWhere($column, 'like', '%' . $value . '%');
            }
        });

        if (!empty($where)) {
            foreach ($where as $condition) {
                $countQuery->where($condition[0], $condition[1], $condition[2]);
            }
        }

        $total = $countQuery->first()->total;

        $searchQuery = DB::table('tickets as t')
            ->leftJoin('ticket_types as tty', 'tty.id', '=', 't.ticket_type_id')
            ->leftJoin('users as u', 'u.id', '=', 't.user_id')
            ->select(
                't.*',
                'tty.title as ticket_type',
                'u.username as name'
            );

        $searchQuery->where(function ($query) use ($multipleWhere) {
            foreach ($multipleWhere as $column => $value) {
                $query->orWhere($column, 'like', '%' . $value . '%');
            }
        });

        if (!empty($where)) {
            foreach ($where as $condition) {
                $searchQuery->where($condition[0], $condition[1], $condition[2]);
            }
        }

        $results = $searchQuery->orderBy($sort, $order)
            ->offset($offset)
            ->limit($limit)
            ->get();



        $bulkData['error'] = $results->isEmpty();
        $bulkData['message'] = $results->isEmpty() ? labels('admin_labels.ticket_not_exist', 'Ticket(s) does not exist')
            :
            labels('admin_labels.tickets_retrieved_successfully', 'Tickets retrieved successfully');
        $bulkData['total'] = $total;
        $bulkData['data'] = $results->toArray();

        return $bulkData;
    }

    public function getMessages($ticket_id = "", $user_id = "", $search = "", $offset = 0, $limit = 10, $sort = "id", $order = "DESC", $data = [], $msg_id = "")
    {
        $ticket_id = request()->input('ticket_id', '');
        $user_id = request()->input('user_id', '');
        $search = trim(request()->input('search', ''));
        $offset = request()->input('offset', 0);

        $limit = request()->input('limit', 10);

        $data = config('eshop_pro.type');

        $multipleWhere = [];
        $where = [];

        if (!empty($search)) {
            $multipleWhere = [
                'u.id' => $search,
                'u.username' => $search,
                't.subject' => $search,
                'tm.message' => $search,
            ];
        }

        if (!empty($ticket_id)) {
            $where['tm.ticket_id'] = $ticket_id;
        }

        if (!empty($user_id)) {
            $where['tm.user_id'] = $user_id;
        }

        if (!empty($msg_id)) {
            $where['tm.id'] = $msg_id;
        }

        $countRes = DB::table('ticket_messages as tm')
            ->leftJoin('tickets as t', 't.id', '=', 'tm.ticket_id')
            ->leftJoin('users as u', 'u.id', '=', 'tm.user_id')
            ->select(DB::raw('COUNT(tm.id) as total'));

        if (!empty($multipleWhere)) {
            $countRes->where(function ($query) use ($multipleWhere) {
                foreach ($multipleWhere as $column => $value) {
                    $query->orWhere($column, 'like', '%' . $value . '%');
                }
            });
        }

        if (!empty($where)) {
            $countRes->where($where);
        }

        $total = $countRes->first()->total;

        $searchRes = DB::table('ticket_messages as tm')
            ->leftJoin('tickets as t', 't.id', '=', 'tm.ticket_id')
            ->leftJoin('users as u', 'u.id', '=', 'tm.user_id')
            ->select(
                'tm.id',
                'tm.user_type as user_type',
                'tm.user_id',
                'tm.ticket_id',
                'tm.message',
                'u.username as name',
                'tm.attachments',
                't.subject',
                'tm.updated_at',
                'tm.created_at'
            );

        if (!empty($multipleWhere)) {
            $searchRes->where(function ($query) use ($multipleWhere) {
                foreach ($multipleWhere as $column => $value) {
                    $query->orWhere($column, 'like', '%' . $value . '%');
                }
            });
        }

        if (!empty($where)) {
            $searchRes->where($where);
        }

        $msgSearchRes = $searchRes->orderBy($sort, $order)->skip($offset)->take($limit)->get();
        $rows = [];

        $bulkData = [
            'error' => $msgSearchRes->isEmpty(),
            'message' => $msgSearchRes->isEmpty() ? labels('admin_labels.ticket_messages_not_exist', 'Ticket Message(s) does not exist')
                :
                labels('admin_labels.messages_retrieved_successfully', 'Message retrieved successfully'),
            'total' => $total,
            'data' => [],
        ];

        if (!$msgSearchRes->isEmpty()) {
            foreach ($msgSearchRes as $row) {
                $row = (array) $row;
                $tempRow = [
                    'id' => $row['id'],
                    'user_type' => $row['user_type'],
                    'user_id' => $row['user_id'],
                    'ticket_id' => $row['ticket_id'],
                    'message' => !empty($row['message']) ? $row['message'] : "",
                    'name' => $row['name'],
                    'attachments' => [],
                    'subject' => $row['subject'],
                    'updated_at' => $row['updated_at'],
                    'created_at' => Carbon::parse($row['created_at'])->format('d-m-Y'),
                ];



                if (!empty($row['attachments']) && $row['attachments'] != '' && $row['attachments'] != "null") {

                    $attachments = json_decode($row['attachments'], true);
                    $counter = 0;
                    foreach ($attachments as $row1) {

                        $tmpRow = [
                            'media' => getMediaImageUrl($row1),
                        ];

                        $file = new SplFileInfo($row1);
                        $ext = $file->getExtension();

                        if (in_array($ext, $data['image']['types'])) {
                            $tmpRow['type'] = "image";
                        } elseif (in_array($ext, $data['video']['types'])) {
                            $tmpRow['type'] = "video";
                        } elseif (in_array($ext, $data['document']['types'])) {
                            $tmpRow['type'] = "document";
                        } elseif (in_array($ext, $data['archive']['types'])) {
                            $tmpRow['type'] = "archive";
                        }

                        $attachments[$counter] = $tmpRow;
                        $counter++;
                    }
                } else {
                    $attachments = [];
                }

                $tempRow['attachments'] = $attachments;
                $rows[] = $tempRow;
            }

            $bulkData['data'] = $rows;
        }

        return $bulkData;
    }

    public function viewTickets()
    {
        return view('admin.pages.tables.manage_tickets');
    }

    public function getTicketList()
    {
        $offset = request()->input('search') || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = request()->input('limit', 10);
        $sort = request()->input('sort', 't.id');
        $order = request()->input('order', 'ASC');
        $multipleWhere = [];

        if (request()->has('search') && trim(request()->input('search')) !== '') {
            $search = trim(request()->input('search'));
            $multipleWhere = [
                'u.id' => $search,
                'u.username' => $search,
                'u.email' => $search,
                'u.mobile' => $search,
                't.subject' => $search,
                't.email' => $search,
                't.description' => $search,
                'tty.title' => $search
            ];
        }

        $count_res = DB::table('tickets as t')
            ->select(DB::raw('COUNT(t.id) as total'))
            ->leftJoin('ticket_types as tty', 'tty.id', '=', 't.ticket_type_id')
            ->leftJoin('users as u', 'u.id', '=', 't.user_id');

        if (!empty($multipleWhere)) {
            $count_res->where(function ($query) use ($multipleWhere) {
                foreach ($multipleWhere as $column => $value) {
                    $query->orWhere($column, 'like', '%' . $value . '%');
                }
            });
        }

        $count_res = $count_res->get();
        $total = $count_res->first()->total;

        $search_res = DB::table('tickets as t')
            ->select('t.*', 'tty.title', 'u.username')
            ->leftJoin('ticket_types as tty', 'tty.id', '=', 't.ticket_type_id')
            ->leftJoin('users as u', 'u.id', '=', 't.user_id');

        if (!empty($multipleWhere)) {
            $search_res->where(function ($query) use ($multipleWhere) {
                foreach ($multipleWhere as $column => $value) {
                    $query->orWhere($column, 'like', '%' . $value . '%');
                }
            });
        }

        $search_res = $search_res->orderBy($sort, $order)->skip($offset)->take($limit)->get();


        $rows = [];
        $status = "";

        foreach ($search_res as $row) {
            $delete_url = route('tickets.destroy', $row->id);

            $operate = '<div class="dropdown bootstrap-table-dropdown">
                <a href="#" class="text-dark" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                   <i class="bx bx-dots-horizontal-rounded"></i>
                </a>
                <div class="dropdown-menu table_dropdown offer_action_dropdown" aria-labelledby="dropdownMenuButton">
                <a class="dropdown-item dropdown_menu_items view_ticket" data-id=' . $row->id . ' data-username=" ' . $row->username . '" data-date_created=' . $row->created_at . ' data-subject="' . $row->subject . '" data-status=' . $row->status . ' data-ticket_type="' . $row->title . '" title="View" data-bs-target="#ticket_modal" data-bs-toggle="modal"><i class="bx bx-pencil mx-2"></i> Edit</a>
                    <a class="dropdown-item delete-data dropdown_menu_items" id="delete-ticket" data-url="' . $delete_url . '" data-id=' . $row->id . '><i class="bx bx-trash mx-2"></i> Delete</a>
                </div>
            </div>';


            if ($row->status == "1") {
                $status = '<label class="badge bg-secondary">PENDING</label>';
            } else if ($row->status == "2") {
                $status = '<label class="badge bg-info">OPENED</label>';
            } else if ($row->status == "3") {
                $status = '<label class="badge bg-success">RESOLVED</label>';
            } else if ($row->status == "4") {
                $status = '<label class="badge bg-danger">CLOSED</label>';
            } else if ($row->status == "5") {
                $status = '<label class="badge bg-warning">REOPENED</label>';
            }
            $rows[] = [
                'id' => $row->id,
                'ticket_type_id' => $row->ticket_type_id,
                'user_id' => $row->user_id,
                'subject' => $row->subject,
                'email' => $row->email,
                'description' => $row->description,
                'status' => $status,
                'last_updated' => $row->updated_at,
                'date_created' => Carbon::parse($row->created_at)->format('d-m-Y'),
                'username' => $row->username,
                'ticket_type' => $row->title,
                'operate' => $operate,
            ];
        }

        $bulkData = [
            'total' => $total,
            'rows' => $rows,
        ];

        return response()->json($bulkData);
    }
    public function tickets_destroy($id)
    {
        $ticket = Ticket::find($id);

        if ($ticket) {
            $ticket->delete();
            return response()->json([
                'error' => false,
                'message' => labels('admin_labels.ticket_deleted_successfully', 'Ticket deleted successfully!')
            ]);
        } else {
            return response()->json(['error' => labels('admin_labels.data_not_found', 'Data Not Found')]);
        }
    }
    public function sendMessage(Request $request)
    {
        $rules = [
            'ticket_id' => 'required|numeric',
        ];

        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        }
        $user_id = auth()->id();
        $ticket_id = $request->input('ticket_id');
        $message = $request->input('message');
        $attachments = $request->input('attachments');

        $user = User::find($user_id);

        if (!$user) {
            return response()->json(['error' => true, 'message' =>
            labels('admin_labels.user_not_found', 'User not found!'), 'csrfName' => csrf_token(), 'csrfHash' => csrf_token(), 'data' => []]);
        }

        $ticket_messages = new TicketMessage([
            'user_type' => 'admin',
            'user_id' => $user_id,
            'ticket_id' => $ticket_id,
            'message' => $message,
            'attachments' => json_encode($attachments),
        ]);

        $response = $ticket_messages->save();
        $last_insert_id = $ticket_messages->id;


        if ($response) {
            $type = config('eshop_pro.type');
            $result = $this->getMessages($ticket_id, $user_id, "", "", "1", "id", "DESC", $type, $last_insert_id);

            return response()->json(['error' => false, 'message' =>
            labels('admin_labels.ticket_message_sent_successfully', 'Ticket message sent successfully'), 'data' => $result['data'][0]]);
        } else {
            return response()->json(['error' => true, 'message' =>
            labels('admin_labels.ticket_message_not_sent', 'Ticket message could not be sent!'), 'data' => []]);
        }
    }

    public function editTicketStatus(Request $request)
    {

        $rules = [
            'ticket_id' => 'required|numeric',
            'status' => 'required',
        ];

        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        }
        $status = $request->input('status');
        $ticket_id = $request->input('ticket_id');

        $ticket = Ticket::find($ticket_id);

        if (!$ticket) {
            return response()->json(['error' => true, 'message' => labels('admin_labels.data_not_found', 'Data Not Found'), 'data' => []]);
        }


        // Update ticket status
        $ticket->status = $status;
        $ticket->save();

        // Additional logic for notifications...

        return response()->json(['error' => false, 'message' =>
        labels('admin_labels.ticket_updated_successfully', 'Ticket updated successfully'), 'data' => $ticket]);
    }
    public function delete_selected_data(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:ticket_types,id'
        ]);

        foreach ($request->ids as $id) {
            $ticket_type = TicketType::find($id);

            if ($ticket_type) {
                TicketType::where('id', $id)->delete();
            }
        }
        TicketType::destroy($request->ids);

        return response()->json(['message' => 'Selected data deleted successfully.']);
    }
    public function delete_selected_ticket_data(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:tickets,id'
        ]);

        foreach ($request->ids as $id) {
            $tickets = Ticket::find($id);

            if ($tickets) {
                Ticket::where('id', $id)->delete();
            }
        }
        Ticket::destroy($request->ids);

        return response()->json(['message' => 'Selected data deleted successfully.']);
    }
}
