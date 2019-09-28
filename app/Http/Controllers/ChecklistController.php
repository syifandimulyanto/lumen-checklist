<?php

namespace App\Http\Controllers;

use App\Libraries\CustomPaginate;
use App\Models\Checklist;
use App\Models\Item;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Validator;
use DB;

class ChecklistController extends Controller
{
    public function index(Request $request)
    {
        try {
            $page = ($request->page ? $request->page : 1);
            $limit = ($request->limit ? $request->limit : 10);
            $paginate = new Checklist();

            if ($limit && $page > 0) {
                $offset   = $limit * ($page - 1);
                $paginate = $paginate->offset($offset)->limit($limit);
            }

            $data = CustomPaginate::build($paginate->paginate($limit));
            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(),400);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->data['attributes'], [
                'object_id' => 'required',
                'object_domain' => 'required',
                'urgency' => 'required',
                'description' => 'required',
                'task_id' => 'required'
            ]);

            if ($validator->fails())
                throw new Exception($validator->errors()->first());

            $attr = $request->data['attributes'];

            DB::beginTransaction();
            $checklist = new Checklist();
            $checklist->task_id = $attr['task_id'];
            $checklist->object_id = $attr['object_id'];
            $checklist->object_domain = $attr['object_domain'];
            $checklist->due = Carbon::parse($attr['due']);
            $checklist->urgency = $attr['urgency'];
            $checklist->description = $attr['description'];
            if ($checklist->save()) {
                $items = [];
                foreach ($attr['items'] as $item) {
                    $items[] = [
                        'task_id' => $attr['task_id'],
                        'checklist_id' => $checklist->id,
                        'description' => $item
                    ];
                }
                Item::insert($items);
            }
            $response = [
                'data' => [
                    'type' => 'checklists',
                    'id' => $checklist->id,
                    'attributes' => $checklist,
                    'links' => [
                        'self' => url('checklists' , $checklist->id)
                    ]
                ]
            ];

            DB::commit();

            return response()->json($response, 201);

        } catch (Exception $e) {
            DB::rollback();
            return response()->json($e->getMessage(),400);
        }
    }

    public function update(Request $request, $checklistId)
    {
        try {
            $validator = Validator::make($request->data['attributes'], [
                'object_id' => 'required',
                'object_domain' => 'required',
                'urgency' => 'required',
                'description' => 'required',
                'task_id' => 'required'
            ]);

            if ($validator->fails())
                throw new Exception($validator->errors()->first());

            $checklist = Checklist::find($checklistId);
            if (!$checklist) return response()->json('Checklist not found', 404);

            $attr = $request->data['attributes'];

            DB::beginTransaction();
            $checklist->object_id = $attr['object_id'];
            $checklist->object_domain = $attr['object_domain'];
            $checklist->description = $attr['description'];
            $checklist->is_completed = $attr['is_completed'];
            $checklist->save();

            $response = [
                'data' => [
                    'type' => 'checklists',
                    'id' => $checklist->id,
                    'attributes' => $checklist,
                    'links' => [
                        'self' => url('checklists' , $checklist->id)
                    ]
                ]
            ];

            DB::commit();

            return response()->json($response, 201);

        } catch (Exception $e) {
            DB::rollback();
            return response()->json($e->getMessage(),400);
        }
    }

    public function show(Request $request, $checklistId)
    {
        try {
            $checklist = Checklist::find($checklistId);
            if (!$checklist) return response()->json('Checklist not found', 404);

            $response = [
                'type' => 'checklists',
                'id' => $checklistId,
                'attributes' => $checklist,
                'links' => [
                    'self' => $request->url()
                ]
            ];

            return response()->json($response);

        } catch (Exception $e) {
            return response()->json($e->getMessage(),400);
        }
    }

    public function destroy(Request $request, $checklistId)
    {
        try {
            $checklist = Checklist::find($checklistId);
            if (!$checklist) return response()->json('Checklist not found', 404);

            $checklist->items()->delete();
            $checklist->delete();

            return response()->json('', 204);

        } catch (Exception $e) {
            return response()->json($e->getMessage(),400);
        }
    }
}