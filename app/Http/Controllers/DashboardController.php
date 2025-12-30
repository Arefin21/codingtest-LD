<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        return view('dashboard');
    }

    //Get users for DataTables via AJAX

    public function getUsers(Request $request)
    {
        if ($request->ajax()) {
            $users = User::select('id', 'name', 'email', 'created_at');

            return DataTables::of($users)
                ->addColumn('action', function ($user) {
                    return '<button class="btn btn-sm btn-primary editUser" data-id="' . $user->id . '" data-name="' . htmlspecialchars($user->name) . '" data-email="' . htmlspecialchars($user->email) . '">Edit</button>';
                })
                ->editColumn('created_at', function ($user) {
                    return Carbon::parse($user->created_at)->format('Y-m-d H:i:s');
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return abort(404);
    }

    // Update user data via AJAX
    
    public function updateUser(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id,
        ], [
            'name.required' => 'The name field is required.',
            'email.required' => 'The email field is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already taken.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::findOrFail($id);
            $user->name = $request->name;
            $user->email = $request->email;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the user.'
            ], 500);
        }

    }
}
