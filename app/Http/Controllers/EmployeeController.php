<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use Carbon\Carbon;
use DB;

class EmployeeController extends Controller
{
    function test1(Request $request) {
        try {
            $current_page = isset($request->page) ? $request->page : 1;
            $limit = isset($request->limit) ? $request->limit : 10;
            $keyword = isset($request->keyword) ? $request->keyword : null;
            
            $data = Employee::when($keyword, function($query, $keyword) {
                $query->where(function($query) use ($keyword) {
                    $query->where('name', 'like', '%'.$keyword.'%')
                        ->orWhere('job_title', 'like', '%'.$keyword.'%')
                        ->orWhere('department', 'like', '%'.$keyword.'%');
                });
            })->orderBy('updated_at', 'DESC')
            ->paginate($perPage = $limit, $columns = ['*'], $pageName = 'page', $page = $current_page);

            return setRes(paginateData($data), 200);
        } catch(\Exception $e) {
            return setRes(null, $e->getMessage() ? 400 : 500, $e->getMessage() ?? null);
        }
    }

    function test2(Request $request) {
        try {
            $data = Employee::where('job_title', $request->job_title)->count();
            $res = [
                'job_title' => $request->job_title,
                'total' => $data
            ];
            return setRes($res, 200);
        } catch(\Exception $e) {
            return setRes(null, $e->getMessage() ? 400 : 500, $e->getMessage() ?? null);
        }
    }

    function test3(Request $request) {
        try {
            //filter params
            $current_page = isset($request->page) ? $request->page : 1;
            $limit = isset($request->limit) ? $request->limit : 10;
            $keyword = isset($request->keyword) ? $request->keyword : null;
            //filter params

            if($request->departments && count($request->departments) > 0) {
                $departments = [];

                foreach($request->departments as $item) {
                    $departments[] = $item;
                }

                $data = Employee::select('name', 'salary')
                    ->where(function($query) use ($departments) {
                        foreach($departments as $index => $item) {
                            if ($index === 0) {
                                $query = $query->where('department', $item);
                            } else {
                                $query = $query->orWhere('department', $item);
                            }
                        }
                    })
                    ->paginate($perPage = $limit, $columns = ['*'], $pageName = 'page', $page = $current_page);

                return setRes(paginateData($data), 200);
            }

            return setRes(null, 400, 'departments is required, minimun 1 department');
        } catch(\Exception $e) {
            return setRes(null, $e->getMessage() ? 400 : 500, $e->getMessage() ?? null);
        }
    }

    function test4(Request $request) {
        try {
            $limit = isset($request->limit) ? $request->limit : 5;
            $start_date = Carbon::now()->subYears($limit)->format('Y-m-d');
            $end_date = Carbon::now()->format('Y-m-d');

            $data = Employee::whereBetween('join_date', [$start_date, $end_date])->limit($limit)->avg('salary');

            $res = [
                'limit' => $limit.' year(s)',
                'average' => $data > 0 ? (float) number_format($data, 2, '.', '') : 0
            ];

            return setRes($res, 200);
        } catch(\Exception $e) {
            return setRes(null, $e->getMessage() ? 400 : 500, $e->getMessage() ?? null);
        }
    }

    function test5(Request $request) {
        try {
            $limit = isset($request->limit) ? $request->limit : 5;
            $data = DB::table('employees')
                ->select('employees.name', 'sales.amount')
                ->join('sales', 'employees.id', '=', 'sales.employee_id')
                ->orderBy('sales.amount', 'desc')
                ->get();

            $listWithoutGroup = [];
            $listWithGroup = [];

            for($min = 0; $min < count($data); $min++) {
                if (count($listWithoutGroup) < $limit) {
                    $listWithoutGroup[] = [
                        'name' => $data[$min]->name,
                        'sales' => $data[$min]->amount,
                    ];
                }

                if (count($listWithGroup) < $limit) {
                    $checkExistEmployee = array_search($data[$min]->name, array_column($listWithGroup, 'name'));
                    if (!$checkExistEmployee) {
                        $listWithGroup[] = [
                            'name' => $data[$min]->name,
                            'sales' => $data[$min]->amount,
                        ];
                    }
                }
            }

            $res = [
                'with_group' => $listWithGroup,
                'without_group' => $listWithoutGroup
            ];

            return setRes($res, 200);
        } catch(\Exception $e) {
            return setRes(null, $e->getMessage() ? 400 : 500, $e->getMessage() ?? null);
        }
    }

    function test6(Request $request) {
        try {
            $current_page = isset($request->page) ? $request->page : 1;
            $limit = isset($request->limit) ? $request->limit : 10;

            $average_salary = (int) Employee::avg('salary');
            $list = Employee::select('name', 'salary')
                ->where('salary', '>' , $average_salary)
                ->paginate($perPage = $limit, $columns = ['*'], $pageName = 'page', $page = $current_page);

            $paginate = paginateData($list);
            $list_data = $paginate['list'];
            unset($paginate['list']);
            $meta_paginate = $paginate;

            foreach($list_data as $index => $item) {
                $list_data[$index]['average_salary'] = $average_salary;
            }
            
            $list = $list->each(function ($item) use ($average_salary) {
                $item->average_salary = $average_salary;
            });

            $res = $meta_paginate;
            $res['list'] = $list_data;

            return setRes($res, 200);
        } catch(\Exception $e) {
            return setRes(null, $e->getMessage() ? 400 : 500, $e->getMessage() ?? null);
        }
    }

    function test7() {
        try {
            $data = DB::select(DB::raw('SELECT ROW_NUMBER() OVER () as rank_number, employees.name as name, SUM(sales.amount) as amount FROM employees JOIN sales ON employees.id = sales.employee_id GROUP BY name ORDER BY amount DESC'));

            return setRes($data, 200);
        } catch(\Exception $e) {
            return setRes(null, $e->getMessage() ? 400 : 500, $e->getMessage() ?? null);
        }
    }

    function test8(Request $request) {
        try {
            $department = $request->department;
            if ($department) {
                $data = DB::select('call GetEmployeeByDepartment(?)', [$department]);
                return setRes($data, 200);
            }

            return setRes(null, 400, 'department is required');
        } catch(\Exception $e) {
            return setRes(null, $e->getMessage() ? 400 : 500, $e->getMessage() ?? null);
        }
    }
}
