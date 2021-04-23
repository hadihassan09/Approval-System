<?php

namespace App\Http\Controllers;

use App\Models\Temp;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{

    public function array_replace_value($search, $replace, array $subject) {
        $updatedArray = [];
        foreach ($subject as $key => $value) {
            if ($value == $search) {
                $updatedArray = array_merge($updatedArray, [$key => $replace]);
                continue;
            }
            $updatedArray = array_merge($updatedArray, [$key => $value]);
        }
        return $updatedArray;
    }

    public function executeQuery($queries, $type, $params, $tables=null){
        $key = null;
        for($i=0; $i < count($params); $i++)
            if($type == 'create'){
                if($i != 0) $params[$i] = $this->array_replace_value('#',$key, $params[$i]);
                if($i == 0) $key = DB::table($tables[$i])->insertGetId($params[$i]);
                else DB::table($tables[$i])->insertGetId($params[$i]);
            }elseif ($type == 'update'){
                DB::update($queries[$i], $params[$i]);
            }elseif ($type == 'delete'){
                DB::delete($queries[$i], $params[$i]);
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

        $this->executeQuery(json_decode($request->queries), $request->type, json_decode($request->bindings, true), json_decode($request->table));
        $request->forceDelete();

        return response()->json([
            'success' => 'true',
            'message' => 'Request Approved Successfully'
        ]);
    }

    public function unapproveRequest($id){
        try {
            $request = Temp::findOrFail($id);
            if (Auth::user()->cannot('system-admin')) {
                return response()->json(['error'=>'User cannot validate requests, invalid access'], 403);
            }
        }catch (\Exception $e){
            return response()->json(['error'=>'Request Not Found'], 404);
        }

        $request->delete();

        return response()->json([
            'success' => 'true',
            'message' => 'Request UnApproved Successfully'
        ]);
    }

    public function Requests(){
        if (Auth::user()->cannot('system-admin')) {
            return response()->json(['error'=>'Invalid access'], 403);
        }
        return response()->json([
            'requests' => Temp::all(),
        ]);
    }

    public function unapprovedRequests(){
        if (Auth::user()->cannot('system-admin')) {
            return response()->json(['error'=>'Invalid access'], 403);
        }
        return response()->json([
            'unapproved requests' => Temp::onlyTrashed()->get(),
        ]);
    }

    public function reapproveRequest($id){
        try {
            $request = Temp::onlyTrashed()->findOrFail($id);
            if (Auth::user()->cannot('system-admin')) {
                return response()->json(['error'=>'User cannot validate requests, invalid access'], 403);
            }
        }catch (\Exception $e){
            return response()->json(['error'=>'Request Not Found'], 404);
        }

        $this->executeQuery(json_decode($request->queries), $request->type, json_decode($request->bindings, true), json_decode($request->table));
        $request->forceDelete();

        return response()->json([
            'success' => 'true',
            'message' => 'Request Approved Successfully'
        ]);
    }
}
