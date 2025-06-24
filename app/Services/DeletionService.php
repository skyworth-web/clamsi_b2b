<?php

namespace App\Services;


use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Session;

class DeletionService
{
    public static function delete($model, $id, $type)
    {
        if ($id > 0) {
            $item = $model::find($id);
            if (!$item) {
                return self::errorResponse($type . ' not found.');
            }
            $title = $type == 'User' || $type == 'Client' ? $item->first_name . ' ' . $item->last_name : ($type == 'Payslip' ? get_label('payslip_id_prefix', 'PSL - ') . $id : $item->title);

            if ($item->delete()) {
                return self::successResponse($type . ' deleted successfully.', $id, $title);
            }

            return self::errorResponse($type . ' couldn\'t be deleted.');
        } else {
            return self::errorResponse('Cannot delete the default ' . $type . '.');
        }
    }

    private static function successResponse($message, $id, $title)
    {
        Session::flash('message', $message);
        return response()->json(['error' => false, 'message' => $message, 'id' => $id, 'title' => $title]);
    }

    private static function errorResponse($message)
    {
        Session::flash('error', $message);
        return response()->json(['error' => true, 'message' => $message]);
    }
}
