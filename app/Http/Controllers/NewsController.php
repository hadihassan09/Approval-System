<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Temp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NewsController extends Controller
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
        $news = News::all();
        return response()->json([
            'news' => $news,
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
            'description' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 422);
        }
        $params = [0=>['description'=>$request->description, 'user_id'=>$request->user()->id]];

        $news = new News();
        $news->description = $request->description;
        $news->user_id = Auth::user()->id;

        $temp = Temp::create([
            'type'=>'create',
            'table' => json_encode(['news']),
            'bindings' => json_encode($params),
            'output' => json_encode($news)
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
            $news = News::findOrFail($id);
            if ($request->user()->cannot('owner', $news)) {
                return response()->json(['error'=>'News Not Owned by User'], 403);
            }
        }catch (\Exception $e){
            return response()->json(['error'=>'News Not Found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'description' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 422);
        }

        $newNews = new News();
        $newNews->description = $request->description;
        $newNews->user_id = $request->user()->id;

        $output = new \stdClass();
        $output->old = $news;
        $output->new = $newNews;

        $Query = [0=>'UPDATE NEWS SET description = ?, user_id = ?  WHERE id = ?'];
        $params = [[$request->description, $request->user()->id, $news->id]];
        $temp = Temp::create([
            'type'=>'update',
            'table' => 'news',
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
            $news = News::findOrFail($id);
            if (Auth::user()->cannot('owner', $news) && Auth::user()->cannot('system-admin')) {
                return response()->json(['error'=>'News Not Owned by User'], 403);
            }
        }catch (\Exception $e){
            return response()->json(['error'=>'News Not Found'], 404);
        }

        $Query = [0=>'DELETE FROM NEWS WHERE id=?'];
        $params = [[$id]];
        $temp = Temp::create([
            'type'=>'delete',
            'table' => 'news',
            'queries' => json_encode($Query),
            'bindings' => json_encode($params),
            'output' => json_encode($news)
        ]);

        return response()->json([
            'status' => 'pending'
        ]);
    }
}
