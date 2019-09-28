<?php

namespace App\Http\Controllers;

use App\Libraries\CustomPaginate;
use App\Models\Checklist;
use App\Models\Item;
use App\Models\Task;
use Illuminate\Http\Request;
use Validator;
use Exception;
use DB;
class TaskController extends Controller
{
    public function index(Request $request)
    {
        try {
            $page = ($request->page ? $request->page : 1);
            $limit = ($request->limit ? $request->limit : 10);
            $paginate = Task::select('id','name')->with([
                'checklist' => function($query) {
                    $query->select('id', 'task_id', 'due', 'description');
                },
                'items' => function($query) {
                    $query->select('id', 'task_id', 'urgency', 'due', 'description');
                }
            ]);

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
            $validator = Validator::make($request->all(), [
                'data' => 'required',
            ]);

            if ($validator->fails())
                throw new Exception($validator->errors()->first());

            $attributes     = $request->data['attributes'];
            $attrCheklist   = $attributes['checklist'];
            $attrItems      = $attributes['items'];

            DB::beginTransaction();

                $task = new Task();
                $task->name = $attributes['name'];
                if ($task->save()) {

                    $checklist = new Checklist();
                    $checklist->task_id = $task->id;
                    $checklist->description = $attrCheklist['description'];
                    $checklist->due = Checklist::due($attrCheklist['due_unit'], $attrCheklist['due_interval']);
                    if ($checklist->save()) {
                        $items = [];
                        foreach ($attrItems as $attrItem) {
                            $items[] = [
                                'task_id' => $task->id,
                                'checklist_id' => $checklist->id,
                                'description' => $attrItem['description'],
                                'urgency' => $attrItem['urgency'],
                                'due' => Checklist::due($attrItem['due_unit'], $attrItem['due_interval'])
                            ];
                        }
                        Item::insert($items);
                    }
                }

                $response = [
                    'data' => [
                        'id' => $task->id,
                        'attributes' => $attributes
                    ]
                ];

            DB::commit();

            return response()->json($response, 201);

        } catch (Exception $e) {
            DB::rollback();
            return response()->json($e->getMessage(),400);
        }
    }

    public function update(Request $request, $templateId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'data' => 'required',
            ]);

            if ($validator->fails())
                throw new Exception($validator->errors()->first());

            $attributes     = $request->data['attributes'];
            $attrCheklist   = $attributes['checklist'];
            $attrItems      = $attributes['items'];

            $task = Task::find($templateId);
            if (!$task) return response()->json('Template not found', 404);

            DB::beginTransaction();
                $task->name = $attributes['name'];
                if ($task->save()) {

                    Checklist::where('task_id', $task->id)->delete();
                    Item::where('task_id', $task->id)->delete();

                    $checklist = new Checklist();
                    $checklist->task_id = $task->id;
                    $checklist->description = $attrCheklist['description'];
                    $checklist->due = Checklist::due($attrCheklist['due_unit'], $attrCheklist['due_interval']);
                    if ($checklist->save()) {
                        $items = [];
                        foreach ($attrItems as $attrItem) {
                            $items[] = [
                                'task_id' => $task->id,
                                'checklist_id' => $checklist->id,
                                'description' => $attrItem['description'],
                                'urgency' => $attrItem['urgency'],
                                'due' => Checklist::due($attrItem['due_unit'], $attrItem['due_interval'])
                            ];
                        }
                        Item::insert($items);
                    }
                }
                $response = [
                    'data' => [
                        'id' => $task->id,
                        'attributes' => $attributes
                    ]
                ];
            DB::commit();

            return response()->json($response);

        } catch (Exception $e) {
            DB::rollback();
            return response()->json($e->getMessage(),400);
        }
    }

    public function show(Request $request, $templateId)
    {
        try {
            $task = Task::select('id','name')->with([
                'checklist' => function($query) {
                    $query->select('id', 'task_id', 'due', 'description');
                },
                'items' => function($query) {
                    $query->select('id', 'task_id', 'urgency', 'due', 'description');
                }
            ])->find($templateId);

            if (!$task) return response()->json('Template not found', 404);

            $response = [
                'type' => 'templates',
                'id' => $templateId,
                'attributes' => $task,
                'links' => [
                    'self' => $request->url()
                ]
            ];

            return response()->json($response, 200);

        } catch (Exception $e) {
            return response()->json($e->getMessage(),400);
        }
    }

    public function destroy(Request $request, $templateId)
    {
        try {
            DB::beginTransaction();
                $task = Task::find($templateId);
                if (!$task) return response()->json('Template not found', 404);

                $task->checklist()->delete();
                $task->items()->delete();
                $task->delete();

            DB::commit();
            return response()->json('', 204);

        } catch (Exception $e) {
            DB::rollback();
            return response()->json($e->getMessage(),400);
        }
    }
}