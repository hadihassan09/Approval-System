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
        $params = [0=>['language'=>$request->language]];

        $language = new Language();
        $language->language = $request->language;

        $temp = Temp::create([
            'type'=>'create',
            'table' => json_encode(['languages']),
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

        $newLanguage = new Language();
        $newLanguage->language = $request->language;

        $output = new \stdClass();
        $output->old = $language;
        $output->new = $newLanguage;

        $Query = [0=>'UPDATE LANGUAGES SET language = ?  WHERE id = ?'];
        $params = [[$request->language, $language->id]];
        $temp = Temp::create([
            'type'=>'update',
            'table' => 'languages',
            'queries' => json_encode($Query),
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
            $language = Language::findOrFail($id);
        }catch (\Exception $e){
            return response()->json(['error'=>'Language Not Found'], 404);
        }

        $Query = [0=>'DELETE FROM LANGUAGES WHERE id=?'];
        $params = [[$id]];
        $temp = Temp::create([
            'type'=>'delete',
            'table' => 'languages',
            'queries' => json_encode($Query),
            'bindings' => json_encode($params),
            'output' => json_encode($language)
        ]);

        return response()->json([
            'status' => 'pending'
        ]);
    }
}
