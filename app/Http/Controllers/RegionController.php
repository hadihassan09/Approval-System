<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Region;
use App\Models\Temp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegionController extends Controller
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
        $regions = Region::all();
        return response()->json([
            'regions' => $regions,
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
            'region' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 422);
        }
        $Query = 'INSERT INTO REGIONS (region) VALUES (?)';
        $params = [$request->region];

        $region = new Region();
        $region->region = $request->region;

        $temp = Temp::create([
            'type'=>'create',
            'table' => 'regions',
            'query' => $Query,
            'bindings' => json_encode($params),
            'output' => json_encode($region)
        ]);

        //We can also approve users (admin user) posts automatically
//        if (Auth::user()->can('system-admin')) {
//            DB::insert($temp->query, json_decode($temp->bindings));
//        }

        return response()->json([
            'status' => 'pending'
        ]);
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
            $region = Region::findOrFail($id);
        }catch (\Exception $e){
            return response()->json(['error'=>'Region Not Found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'region' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 422);
        }

        $Query = 'UPDATE REGIONS SET region = ?  WHERE id = ?';
        $params = [$request->region, $region->id];
        $temp = Temp::create([
            'type'=>'update',
            'table' => 'regions',
            'query' => $Query,
            'bindings' => json_encode($params),
            'output' => json_encode($region)
        ]);

        return response()->json([
            'status' => 'pending'
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
            $region = Region::findOrFail($id);
        }catch (\Exception $e){
            return response()->json(['error'=>'Region Not Found'], 404);
        }

        $Query = 'DELETE FROM REGIONS WHERE id=?';
        $params = [$id];
        $temp = Temp::create([
            'type'=>'delete',
            'table' => 'regions',
            'query' => $Query,
            'bindings' => json_encode($params),
            'output' => json_encode($region)
        ]);

        return response()->json([
            'status' => 'pending'
        ]);
    }
}
