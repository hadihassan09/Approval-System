<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Temp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CountryController extends Controller
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
        $countries = Country::all();
        return response()->json([
            'countries' => $countries,
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
            'country' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 422);
        }
        $Query = 'INSERT INTO Countries (country) VALUES (?)';
        $params = [$request->country];

        $country = new Country();
        $country->country = $request->country;

        $temp = Temp::create([
            'type'=>'create',
            'table' => 'countries',
            'query' => $Query,
            'bindings' => json_encode($params),
            'output' => json_encode($country)
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
            $country = Country::findOrFail($id);
        }catch (\Exception $e){
            return response()->json(['error'=>'Country Not Found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'country' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 422);
        }

        $newCountry = new Country();
        $newCountry->country = $request->country;

        $output = new \stdClass();
        $output->old = $country;
        $output->new = $newCountry;

        $Query = 'UPDATE Countries SET country = ?  WHERE id = ?';
        $params = [$request->country, $country->id];
        $temp = Temp::create([
            'type'=>'update',
            'table' => 'countries',
            'query' => $Query,
            'bindings' => json_encode($params),
            'output' => json_encode($output)
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
            $country = Country::findOrFail($id);
        }catch (\Exception $e){
            return response()->json(['error'=>'Country Not Found'], 404);
        }

        $Query = 'DELETE FROM Countries WHERE id=?';
        $params = [$id];
        $temp = Temp::create([
            'type'=>'delete',
            'table' => 'countries',
            'query' => $Query,
            'bindings' => json_encode($params),
            'output' => json_encode($country)
        ]);

        return response()->json([
            'status' => 'pending'
        ]);
    }
}
