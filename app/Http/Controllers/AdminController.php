<?php

namespace App\Http\Controllers;

use App\Models\Temp;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{

    public function executeQuery($query, $type, $param){
        if($type == 'create'){
            DB::insert($query, json_decode($param));
        }elseif ($type == 'update'){
            DB::update($query, json_decode($param));
        }elseif ($type == 'delete'){
            DB::delete($query, json_decode($param));
        }
    }

    public function approveRequest($id){
        try {
            $request = Temp::findOrFail($id);
            if (Auth::user()->cannot('system-admin')) {
                return response()->json(['error'=>'User cannot validate requests, invalid access'], 403);
            }
        }catch (\Exception $e){
            return response()->json(['error'=>'Request Not Found'], 404);
        }

        $this->executeQuery($request->query, $request->type, $request->bindings);
        $request->delete();

        return response()->json([
            'success' => 'true',
            'message' => 'Request Approved Successfully'
        ]);
    }

    public function unapprovedRequests(){
        if (Auth::user()->cannot('system-admin')) {
            return response()->json(['error'=>'Invalid access'], 403);
        }
        return response()->json([
            'requests' => Temp::all(),
        ]);
    }
}
