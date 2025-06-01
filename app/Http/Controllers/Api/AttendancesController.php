<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendances;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttendancesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Check if user_id parameter is present
        $query = Attendances::with('employeeId');

        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->input('employee_id'));
        }

        // Fetch attendances with pagination
        $attendances = $query->paginate(10);

        // Return the attendances as a JSON response
        return response()->json([
            'status' => true,
            'message' => 'Attendances retrieved successfully',
            'data' => $attendances,
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'employee_id'    => 'required|exists:employees,id',
            'date'           => 'required|date',
        ]);

        // If validation fails, return a JSON response with errors
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if attendance already exists for the same employee and date
        $exists = Attendances::where('employee_id', $request->input('employee_id'))
            ->where('date', $request->input('date'))
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'Attendance for this employee on the given date already exists.',
            ], 409);
        }

        // Create a new attendance record
        $attendance = Attendances::create([
            'employee_id'    => $request->input('employee_id'),
            'date'           => $request->input('date'),
            'check_in_time'  => $request->input('check_in_time'),
            'check_out_time' => $request->input('check_out_time'),
        ]);

        // Return a JSON response indicating success
        return response()->json([
            'status' => true,
            'message' => 'Attendance created successfully',
            'data' => $attendance,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Find the attendance record by ID
        $attendance = Attendances::find($id);
        // If the attendance record is not found, return a JSON response with an error message
        if (!$attendance) {
            return response()->json([
                'status' => false,
                'message' => 'Attendance not found',
            ], 404);
        }

        // Update the attendance record
        $attendance->update([
            'employee_id'    => $request->input('employee_id') ?? $attendance->employee_id,
            'date'           => $request->input('date') ?? $attendance->date,
            'check_in_time'  => $request->input('check_in_time') ?? $attendance->check_in_time,
            'check_out_time' => $request->input('check_out_time') ?? $attendance->check_out_time,
        ]);

        // Return a JSON response indicating success
        return response()->json([
            'status' => true,
            'message' => 'Attendance updated successfully',
            'data' => $attendance,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Find the attendance record by ID
        $attendance = Attendances::find($id);
        // If the attendance record is not found, return a JSON response with an error message
        if (!$attendance) {
            return response()->json([
                'status' => false,
                'message' => 'Attendance not found',
            ], 404);
        }
        // Delete the attendance record
        $attendance->delete();
        // Return a JSON response indicating success
        return response()->json([
            'status' => true,
            'message' => 'Attendance deleted successfully',
        ], 200);
    }
}
