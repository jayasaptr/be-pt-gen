<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendances;

class DashboardController extends Controller
{
    public function getDashboard(Request $request)
    {

        $user = $request->user();


        $attendance = Attendances::where('employee_id', $user->employee_id)
            ->whereDate('created_at', now()->toDateString())
            ->first();

        $data = [
            'attendance' => [
                'attendance_id' => $attendance ? $attendance->id : null,
                'check_in_time' => $attendance ? $attendance->check_in_time : null,
                'check_out_time' => $attendance ? $attendance->check_out_time : null,
            ],
        ];

        return response()->json($data);
    }
}
