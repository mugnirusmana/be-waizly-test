<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Task;
use DB;

class TaskController extends Controller
{
    function list(Request $request) {
        try {
            $keyword = isset($request->keyword) ? $request->keyword : null;
            $status = isset($request->status) ? $request->status : Task::$todo;
            $data_user = getAuth($request);

            $data = Task::when($keyword, function($query, $value) {
                    $query->where('name', 'like', '%'.$value.'%');
                })
                ->when($status, function($query, $value) {
                    $query->where('status', '=', $value);
                })
                ->where('user_id', $data_user->id)
                ->orderBy('sort_number', 'desc')
                ->get();

            return setRes($data, 200);
        } catch(\Exception $e) {
            return setRes(null, $e->getMessage() ? 400 : 500, $e->getMessage() ?? null);
        }
    }

    function create(Request $request) {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'unique:tasks'],
            ], [
                'name.required' => 'Name is required',
                'name.unique' => 'Name has been taken, tray another name',
            ]);

            if ($validator->fails()) {
                DB::rollback();
                $result = setValidationMessage($validator, ['name', 'description']);
                return setRes($result, 400);
            }

            $data_user = getAuth($request);
            $lates_sort = Task::where('user_id', $data_user->id)
                ->where('status', Task::$todo)
                ->orderBy('sort_number','desc')
                ->first();

            Task::create([
                'name' => $request->name,
                'status' => Task::$todo,
                'sort_number' => $lates_sort ? (string) ((int) $lates_sort->sort_number + 1) : '1',
                'user_id' => $data_user->id,
                'desc' => $request->description??'',
            ]);

            DB::commit();

            return setRes(null, 201);
        } catch (\Exception $e) {
            DB::rollback();
            return setRes(null, $e->getMessage() ? 400 : 500, $e->getMessage() ?? null);
        }
    }

    function edit(Request $request, $id) {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'unique:tasks,name,'.$id],
            ], [
                'name.required' => 'Name is required',
                'name.unique' => 'Name has been taken, tray another name',
            ]);

            if ($validator->fails()) {
                DB::rollback();
                $result = setValidationMessage($validator, ['name', 'description']);
                return setRes($result, 400);
            }

            $lates_sort = Task::where('user_id', '')->orderBy('sort_number','desc')->first();
            $data_user = getAuth($request);

            $task = Task::find($id);

            if(!$task) {
                DB::rollback();
                return setRes(null, 404);
            }

            if ($task->user_id !== $data_user->id) {
                DB::rollback();
                return setRes(null, 400, 'You can only edit your own task');
            }

            $task->name = $request->name;
            if ($request->description) $task->desc = $request->description;
            $task->save();

            DB::commit();

            return setRes(null, 200);
        } catch (\Exception $e) {
            DB::rollback();
            return setRes(null, $e->getMessage() ? 400 : 500, $e->getMessage() ?? null);
        }
    }

    function detail(Request $request, $id) {
        try {
            $task = Task::find($id);
            $data_user = getAuth($request);

            if(!$task) {
                return setRes(null, 404);
            }

            if ($task->user_id !== $data_user->id) {
                DB::rollback();
                return setRes(null, 400, 'You can only get your own task');
            }

            return setRes($task, 200);
        } catch (\Exception $e) {
            return setRes(null, $e->getMessage() ? 400 : 500, $e->getMessage() ?? null);
        }
    }

    function destroy(Request $request, $id) {
        DB::beginTransaction();
        try {
            $task = Task::find($id);
            $data_user = getAuth($request);

            if(!$task) {
                DB::rollback();
                return setRes(null, 404);
            }

            if ($task->user_id !== $data_user->id) {
                DB::rollback();
                return setRes(null, 400, 'You can only delete your own task');
            }

            $task->delete();

            DB::commit();

            return setRes(null, 200);
        } catch (\Exception $e) {
            DB::rollback();
            return setRes(null, $e->getMessage() ? 400 : 500, $e->getMessage() ?? null);
        }
    }

    function sort(Request $request) {
        DB::beginTransaction();
        try {
            if ($request->data && count($request->data) > 0) {
                $data_user = getAuth($request);
                $errors = [];
                $errorData = [];
                $payload = [];

                foreach($request->data as $item) {
                    $check = Task::find($item['id']);
                    if ($check->user_id !== $data_user->id) {
                        $errors[] = $item['id'];
                        $errorData[] = [
                            'id' => $item['id'],
                            'sort_number' => $item['sort_number'],
                            'message' => "Id ".$item['id']." is not your task"
                        ];
                    } else {
                        $payload[] = [
                            'id' => $item['id'],
                            'sort_number' => $item['sort_number'],
                        ];
                    }
                }

                if (count($errors) > 0) {
                    DB::rollback();
                    $tobe = count($errors) > 1 ? "are" : "is";
                    $subject = count($errors) > 1 ? "s" : "";
                    $message = "The Id".$subject." ".implode(", ", $errors)." ".$tobe." not your task, you can only update your own task'";
                    $res = [
                        'errors' => $errorData,
                        'error' => [
                            'data' => $message
                        ]
                    ];
                    return setRes($res, 400, $message);
                }

                \Batch::update(new Task, $payload, 'id');

                DB::commit();

                return setRes(null, 200);
            }
            
            DB::rollback();

            return setRes(null, 400, 'Data is required and array, minimun 1 data');
        } catch (\Exception $e) {
            DB::rollback();
            return setRes(null, $e->getMessage() ? 400 : 500, $e->getMessage() ?? null);
        }
    }

    function todo(Request $request, $id) {
        DB::beginTransaction();
        try {
            $task = Task::find($id);
            $data_user = getAuth($request);

            if(!$task) {
                DB::rollback();
                return setRes(null, 404);
            }

            if ($task->user_id !== $data_user->id) {
                DB::rollback();
                return setRes(null, 400, 'You can only update your own task');
            }

            if($task->status === Task::$todo) {
                DB::rollback();
                return setRes(null, 400, 'This task is already todo, cannot change to todo');
            }

            $task->status = Task::$todo;
            $task->save();

            DB::commit();

            return setRes(null, 200);
        } catch (\Exception $e) {
            DB::rollback();
            return setRes(null, $e->getMessage() ? 400 : 500, $e->getMessage() ?? null);
        }
    }

    function complete(Request $request, $id) {
        DB::beginTransaction();
        try {
            $task = Task::find($id);
            $data_user = getAuth($request);

            if(!$task) {
                DB::rollback();
                return setRes(null, 404);
            }

            if ($task->user_id !== $data_user->id) {
                DB::rollback();
                return setRes(null, 400, 'You can only update your own task');
            }

            if($task->status === Task::$complete) {
                DB::rollback();
                return setRes(null, 400, 'This task is already complete, cannot change to complete');
            }

            $task->status = Task::$complete;
            $task->save();

            DB::commit();

            return setRes(null, 200);
        } catch (\Exception $e) {
            DB::rollback();
            return setRes(null, $e->getMessage() ? 400 : 500, $e->getMessage() ?? null);
        }
    }
}
