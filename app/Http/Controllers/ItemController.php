<?php

namespace App\Http\Controllers;
use App\Models\Checklist;
use App\Models\Item;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Validator;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        try {
            $page = ($request->page ? $request->page : 1);
            $limit = ($request->limit ? $request->limit : 10);
            $paginate = new Item();

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

    public function indexChecklist(Request $request, $checklistId)
    {
        try {
            $checklists = Checklist::with(['items'])->find($checklistId);
            $response = [
                'data' => [
                    'type' => 'checklists',
                    'id' => $checklistId,
                    'attributes' => $checklists
                ]
            ];
            return response()->json($response, 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(),400);
        }
    }

    public function store(Request $request, $checklistId)
    {
        try {
            $validator = Validator::make($request->data['attribute'], [
                'description' => 'required',
                'due' => 'required|date',
                'urgency' => 'required',
                'assignee_id' => 'required'
            ]);

            if ($validator->fails())
                throw new Exception($validator->errors()->first());

            $checklist = Checklist::find($checklistId);
            if (!$checklist) return response()->json('Checklist not found', 404);

            $attr = $request->data['attribute'];

            $item = new Item();
            $item->checklist_id = $checklist->id;
            $item->task_id = $checklist->task_id;
            $item->description = $attr['description'];
            $item->due = Carbon::parse($attr['due']);
            $item->urgency = $attr['urgency'];
            $item->assignee_id = $attr['assignee_id'];
            $item->save();

            $response = [
                'data' => [
                    'type' => 'checklists',
                    'id' => $checklist->id,
                    'attributes' => $item,
                    'links' => [
                        'self' => url('checklists', $checklist->id)
                    ]
                ]
            ];

            return response()->json($response, 200);

        } catch (Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }

    public function update(Request $request, $checklistId, $itemId)
    {
        try {
            $validator = Validator::make($request->data['attribute'], [
                'description' => 'required',
                'due' => 'required|date',
                'urgency' => 'required',
                'assignee_id' => 'required'
            ]);

            if ($validator->fails())
                throw new Exception($validator->errors()->first());

            $item = Item::where('checklist_id', $checklistId)->find($itemId);
            if (!$item) return response()->json('Item not found', 404);

            $attr = $request->data['attribute'];

            $item->description = $attr['description'];
            $item->due = Carbon::parse($attr['due']);
            $item->urgency = $attr['urgency'];
            $item->assignee_id = $attr['assignee_id'];
            $item->save();

            $response = [
                'data' => [
                    'type' => 'checklists',
                    'id' => $checklistId,
                    'attributes' => $item,
                    'links' => [
                        'self' => url('checklists', $checklistId)
                    ]
                ]
            ];

            return response()->json($response, 200);

        } catch (Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }

    public function show(Request $request, $checklistId, $itemId)
    {
        try {
            $item = Item::where('checklist_id', $checklistId)->find($itemId);
            if (!$item) return response()->json('Item not found', 404);

            $response = [
                'type' => 'checklists',
                'id' => $checklistId,
                'attributes' => $item,
                'links' => [
                    'self' => $request->url()
                ]
            ];

            return response()->json($response);

        } catch (Exception $e) {
            return response()->json($e->getMessage(),400);
        }
    }

    public function destroy(Request $request, $checklistId, $itemId)
    {
        try {
            $item = Item::where('checklist_id', $checklistId)->find($itemId);
            if (!$item) return response()->json('Item not found', 404);

            $item->delete();

            return response()->json('', 204);

        } catch (Exception $e) {
            return response()->json($e->getMessage(),400);
        }
    }

    public function bulkUpdate(Request $request, $checklistId)
    {
        try {
            DB::beginTransaction();
            $response = [];
            $reqs = $request->data;
            if ($reqs && count($reqs) > 0) {
                foreach ($reqs as $data) {
                    $item = Item::where('checklist_id', $checklistId)->find($data['id']);
                    if (!$item)
                        $response[] = ['id' => $data['id'], 'action' => $data['action'], 'status' => 404];

                    $item->description = $data['attributes']['description'];
                    $item->due = Carbon::parse($data['attributes']['due']);
                    $item->urgency = $data['attributes']['urgency'];
                    if ($item->save()) {
                        $response[] = ['id' => $data['id'], 'action' => $data['action'], 'status' => 200];
                    } else {
                        $response[] = ['id' => $data['id'], 'action' => $data['action'], 'status' => 403];
                    }
                }
            }
            DB::commit();

            return response()->json($response);

        } catch (Exception $e) {
            DB::rollbak();
            return response()->json($e->getMessage(),400);
        }
    }

    public function complete(Request $request)
    {
        try {
            DB::beginTransaction();
            $response = [];
            $reqs = $request->data;
            if ($reqs && count($reqs) > 0) {
                foreach ($reqs as $key => $data) {
                    $item = Item::find($data['item_id']);
                    if ($item) {
                        $item->is_completed = true;
                        $item->completed_at = Carbon::now();
                        $item->save();

                        $response[] = [
                            'id' => $key + 1,
                            'item_id' => $item->id,
                            'is_completed' => true,
                            'checklist_id' => $item->checklist_id
                        ];
                    }
                }
            }
            DB::commit();

            return response()->json($response);

        } catch (Exception $e) {
            DB::rollbak();
            return response()->json($e->getMessage(),400);
        }
    }

    public function incomplete(Request $request)
    {
        try {
            DB::beginTransaction();
            $response = [];
            $reqs = $request->data;
            if ($reqs && count($reqs) > 0) {
                foreach ($reqs as $key => $data) {
                    $item = Item::find($data['item_id']);
                    if ($item) {
                        $item->is_completed = false;
                        $item->completed_at = null;
                        $item->save();

                        $response[] = [
                            'id' => $key + 1,
                            'item_id' => $item->id,
                            'is_completed' => false,
                            'checklist_id' => $item->checklist_id
                        ];
                    }
                }
            }
            DB::commit();

            return response()->json($response);

        } catch (Exception $e) {
            DB::rollbak();
            return response()->json($e->getMessage(),400);
        }
    }

    public function summaries(Request $request)
    {

    }
}