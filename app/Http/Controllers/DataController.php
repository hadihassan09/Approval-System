<?php

namespace App\Http\Controllers;

use App\Models\Data;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class DataController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $data = Data::where('isApproved', true)->get();
        return response()->json([
            'data' => $data,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 422);
        }

        $data = Data::create([
            'description' => $request->description,
            'data'=> $request->data,
            'user_id' => Auth::user()->id
        ]);

        //We can also approve users (admin user) data automatically
//        if (Auth::user()->can('system-admin')) {
//            $data->isApproved=true;
//            $data->save();
//        }

        return response()->json([
            'success' => true,
            'status' => 'pending'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $data = Data::find($id);
        if($data && $data->isApproved) {
            return response()->json([
                'data' => $data,
                'status' => 'Data approved'
            ]);
        }
        elseif ($data)
            return response()->json([
                'Data' => null,
                'status' => 'Data not approved'
            ]);
        return response()->json(['error'=>'id does not exist'], 404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $data = Data::findOrFail($id);
            if ($request->user()->cannot('data-owner', $data)) {
                return response()->json(['error'=>'Data Not Owned by User'], 403);
            }
        }catch (\Exception $e){
            return response()->json(['error'=>'Data Not Found'], 404);
        }
        $validator = Validator::make($request->all(), [
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 422);
        }

        $data->description =  $request->description;
        $data->data = json_encode($request->data);
        $data->save();

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        try {
            $data = Data::findOrFail($id);
            if (Auth::user()->cannot('data-owner', $data) && Auth::user()->cannot('system-admin')) {
                return response()->json(['error'=>'Data Not Owned by User'], 403);
            }
        }catch (\Exception $e){
            return response()->json(['error'=>'Data Not Found'], 404);
        }

        $data->delete();
        return response()->json([
            'success' => 'true',
            'message' => 'Data Deleted Successfully'
        ]);
    }

    public function approveData($id){
        try {
            $data = Data::findOrFail($id);
            if (Auth::user()->cannot('system-admin')) {
                return response()->json(['error'=>'User cannot validate data, invalid access'], 403);
            }
        }catch (\Exception $e){
            return response()->json(['error'=>'Data Not Found'], 404);
        }

        $data->isApproved = true;
        $data->save();

        return response()->json([
            'success' => 'true',
            'message' => 'Data Approved Successfully'
        ]);
    }

    public function unapprovedData(){
        if (Auth::user()->cannot('system-admin')) {
            return response()->json(['error'=>'Invalid access'], 403);
        }
        $data = Data::where('isApproved', false)->get();
        return response()->json([
            'data' => $data,
        ]);
    }
}
