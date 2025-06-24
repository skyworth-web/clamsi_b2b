<?php

namespace App\Livewire\MyAccount;

use App\Models\Ticket;
use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\WithPagination;

class Support extends Component
{
    protected $listeners = ['refreshComponent'];

    // public function render()
    // {
    //     $user = Auth::user() ?? null;

    //     $ticket_types = fetchDetails('ticket_types');
    //     $tickets  = $this->get_tickets($user->id);

    //     return view('livewire.' . config('constants.theme') . '.my-account.support', [
    //         'user_info' => $user,
    //         'ticket_types' => $ticket_types,
    //         'tickets' => $tickets['tickets'],
    //         'links' => $tickets['links'],
    //     ])->title("Support |");
    // }




    public $perPage = 8;

    public function render()
    {
        $user = Auth::user();

        return view('livewire.' . config('constants.theme') . '.my-account.support', [
            'user_info' => $user,
            'ticket_types' => fetchDetails('ticket_types'),
            'tickets' => Ticket::where('user_id', $user->id)->orderBy('id', 'desc')->paginate($this->perPage),
        ]);
    }


    public function get_tickets($user_id)
    {
        $user_tickets = fetchDetails('tickets', ['user_id' => $user_id], "*", "", "", "tickets.id", "DESC");
        $totle_tickets = count($user_tickets);
        $tickets = collect($user_tickets);
        $page = request()->get('page', 1);
        if (isset($page)) {
            $perPage = 8;
            $paginator = new LengthAwarePaginator(
                $tickets->forPage((int)$page, (int)$perPage),
                $totle_tickets,
                (int)$perPage,
                (int)$page,
                ['path' => url()->current()]
            );
        }
        $tickets['tickets'] = $paginator->items();
        $tickets['links'] = $paginator->links();
        return $tickets;
    }

    public function get_ticket_by_id(Request $request)
    {
        if (!empty($request['user_id']) && !empty($request['ticket_id'])) {
            $user_ticket = fetchDetails('tickets', ['user_id' => $request['user_id'], 'id' => $request['ticket_id']]);
            $response['error'] = false;
            $response['data'] = $user_ticket[0];
            return $response;
        }
    }

    public function refreshComponent()
    {
        $this->dispatch('$refresh');
    }

    public function add_ticket(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'ticket_type' => 'required',
                'ticket_email' => 'required|email',
                'ticket_subject' => 'required',
                'ticket_description' => 'required',
            ]
        );
        if ($validator->fails()) {
            $errors = $validator->errors();
            $response['error'] = true;
            $response['message'] = $errors;
            return $response;
        }
        $user_id = Auth::user()->id;
        $ticket_data = [
            'user_id' => $user_id,
            'ticket_type_id' => $request['ticket_type'],
            'subject' => $request['ticket_subject'],
            'email' => $request['ticket_email'],
            'description' => $request['ticket_description'],
        ];
        if ($request['ticket_id'] != null) {
            $ticket = Ticket::find($request['ticket_id']);
            $res = $ticket->update($ticket_data);
        } else {
            $res = Ticket::Create($ticket_data);
        }
        if (!$res) {
            $response['error'] = true;
            $response['message'] = 'Something Went Wrong Please Try Again Later!';
            return $response;
        }
        if ($request['ticket_id'] != null) {
            $response['error'] = false;
            $response['message'] = 'Ticket Updated SuccessFully.';
            return $response;
        }
        $response['error'] = false;
        $response['message'] = 'Ticket Added SuccessFully.';
        return $response;
    }
}
