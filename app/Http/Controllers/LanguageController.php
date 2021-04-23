<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Language;
use App\Models\Temp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LanguageController extends Controller
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
        $language = Language::all();
        return response()->json([
            'languages' => $language,
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
            'language' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 422);
        }
        $Query = 'INSERT INTO LANGUAGES (language ) VALUES (?)';
        $params = [$request->language];

        $language = new Language();
        $language->language = $request->language;

        $temp = Temp::create([
            'type'=>'create',
            'table' => 'languages',
            'query' => $Query,
            'bindings' => json_encode($params),
            'output' => json_encode($language)
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
            $language = Language::findOrFail($id);
        }catch (\Exception $e){
            return response()->json(['error'=>'Language Not Found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'language' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 422);
        }

        $Query = 'UPDATE LANGUAGES SET language = ?  WHERE id = ?';
        $params = [$request->language, $language->id];
        $temp = Temp::create([
            'type'=>'update',
            'table' => 'languages',
            'query' => $Query,
            'bindings' => json_encode($params),
            'output' => json_encode($language)
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
            $language = Language::findOrFail($id);
        }catch (\Exception $e){
            return response()->json(['error'=>'Language Not Found'], 404);
        }

        $Query = 'DELETE FROM LANGUAGES WHERE id=?';
        $params = [$id];
        $temp = Temp::create([
            'type'=>'delete',
            'table' => 'languages',
            'query' => $Query,
            'bindings' => json_encode($params),
            'output' => json_encode($language)
        ]);

        return response()->json([
            'status' => 'pending'
        ]);
    }
}
