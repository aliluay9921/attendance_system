<?php

namespace App\Http\Controllers;

use App\Models\Bonus;
use App\Models\Shift;
use App\Models\User;
use App\Traits\Pagination;
use App\Traits\SendResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use SendResponse, Pagination;

    public function login(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            'password' => 'required',
            "user_name" => 'required',
        ], [
            'user_name.required' => ' يرجى ادخال اسم المستخدم ',
            'password.required' => 'يرجى ادخال كلمة المرور ',
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, 'حصل خطأ في الأدخال', $validator->errors(), []);
        }

        if (auth()->attempt(array('user_name' => $request['user_name'], 'password' => $request['password']))) {

            $user = auth()->user();
            $token = $user->createToken('attendance_system')->accessToken;
            return $this->send_response(200, 'تم تسجيل الدخول بنجاح', [], $user, $token);
        } else {
            return $this->send_response(400, 'هناك مشكلة تحقق من تطابق المدخلات', null, null, null);
        }
    }

    public function addUser(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            "user_name" => 'required|unique:users',
            "full_name" => 'required',
            "password" => 'required',
            "salary" => "required",
            // "start_attendance" => "required",
            // "leave_attendance" => "required",

        ], [
            'user_name.required' => ' يرجى ادخال اسم المستخدم ',
            'password.required' => 'يرجى ادخال كلمة المرور ',
            'salary.required' => 'يرجى ادخال كلمة المرور ',
            // 'start_attendance.required' => 'يرجى ادخال كلمة المرور ',
            // 'leave_attendance.required' => 'يرجى ادخال كلمة المرور ',
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, 'حصل خطأ في الأدخال', $validator->errors(), []);
        }
        $data = [];
        $data = [
            "user_name" => $request['user_name'],
            "full_name" => $request['full_name'],
            "password" => bcrypt($request['password']),
            "user_type" => 1,
            "salary" => $request['salary'],
            // "start_attendance" =>  $request['start_attendance'],
            // "leave_attendance" =>  $request['leave_attendance'],
        ];

        $user = User::create($data);
        return $this->send_response(200, 'تم أضافة مستخدم بنجاح', [], User::find($user->id));
    }

    public function getUsers()
    {
        $users = User::where("user_type", 1);
        if (isset($_GET['query'])) {
            $users->where(function ($q) {
                $columns = Schema::getColumnListing('users');
                foreach ($columns as $column) {
                    $q->orWhere($column, 'LIKE', '%' . $_GET['query'] . '%');
                }
            });
        }
        if (isset($_GET)) {
            foreach ($_GET as $key => $value) {
                if ($key == 'skip' || $key == 'limit' || $key == 'query') {
                    continue;
                } else {
                    $sort = $value == 'true' ? 'desc' : 'asc';
                    $users->orderBy($key,  $sort);
                }
            }
        }
        if (!isset($_GET['skip']))
            $_GET['skip'] = 0;
        if (!isset($_GET['limit']))
            $_GET['limit'] = 10;
        $res = $this->paging($users->orderBy("created_at", "desc"),  $_GET['skip'],  $_GET['limit']);
        return $this->send_response(200, 'تم جلب المستخدمين بنجاح', [], $res["model"], null, $res["count"]);
    }
    public function updateUser(Request $request)
    {

        $request = $request->json()->all();
        $user = User::find($request['user_id']);

        $validator = Validator::make($request, [
            "user_id" => "required|exists:users,id",
            "user_name" => 'required|unique:users,user_name,' . $user->id,
            "full_name" => 'required',
            "salary" => "required",
            // "start_attendance" => "required",
            // "leave_attendance" => "required",
        ], [
            'user_id.required' => 'يجب ادخال  العنصر المراد التعديل عليه',
            'user_id.exists' => 'العنصر الذي قمت بأدخاله غير موجود',
            'user_name.required' => ' يرجى ادخال اسم المستخدم ',
            'salary.required' => 'يرجى ادخال كلمةألمرتب الخاص بالموضف  ',
            // 'start_attendance.required' => 'يرجى ادخال  وقت الحضور ',
            // 'leave_attendance.required' => 'يرجى ادخال وقت الانصراف  ',
        ]);
        if ($validator->fails()) {
            return $this->send_response(401, 'خطأ بالمدخلات', $validator->errors(), []);
        }

        $data = [];
        $data = [
            "user_name" => $request['user_name'],
            "full_name" => $request['full_name'],
            "salary" => $request['salary'],
            // "start_attendance" =>  $request['start_attendance'],
            // "leave_attendance" =>  $request['leave_attendance'],

        ];
        $user->update($data);
        return $this->send_response(200, 'تم تعديل معلومات المستخدم بنجاح', [], User::find($user->id));
    }
    public function deleteUser(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            'user_id' => 'required|exists:users,id'
        ], [
            'user_id.required' => 'يجب ادخال  العنصر المراد حذفه',
            'user_id.exists' => 'العنصر الذي قمت بأدخاله غير موجود',
        ]);
        if ($validator->fails()) {
            return $this->send_response(401, 'خطأ بالمدخلات', $validator->errors(), []);
        }
        $user = User::find($request['user_id']);
        $user->delete();
        return $this->send_response(200, 'تم حذف المستخدم بنجاح', [], []);
    }

    public function infoUser()
    {

        return $this->send_response(200, "تم جلب معلومات المستخدم بنجاح", [], User::find(auth()->user()->id));
    }
    public function addBonus(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            "user_id" => "required|exists:users,id",
            "bonus" => "required",
            "type" => "required"
        ], [
            "user_id.required" => "يجب ادخال المستخدم المراد مكافئته",
            "user_id.exists" => "المستخدم الذي قمت بأدخاله غير متوفر ",
            "bonus.required" => "يرجى ادخال قيمة المكافئة",
            "type.required" => "يرجى اختيار نوع المكافئة"
        ]);
        if ($validator->fails()) {
            return $this->send_response(401, 'خطأ بالمدخلات', $validator->errors(), []);
        }
        $data = [];
        $data = [
            "user_id" => $request["user_id"],
            "bonus" => $request["bonus"],
            "type" => $request["type"],
            "date" => Carbon::now()->format("Y-m-d"),
        ];

        $bonus = Bonus::create($data);
        return $this->send_response(200, 'تم اضافة مكافئة بنجاح', [], User::find($bonus->user_id));
    }

    public function addShift(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            "user_id" => "required|exists:users,id",
            "start_time" => "required",
            "end_time" => "required",
        ]);
        if ($validator->fails()) {
            return $this->send_response(401, 'خطأ بالمدخلات', $validator->errors(), []);
        }

        $data = [];
        $data = [
            "user_id" => $request["user_id"],
            "start_time" => $request["start_time"],
            "end_time" => $request["end_time"],
        ];
        $get_shift = Shift::where("user_id", $request["user_id"])->count();

        $data["shift"] = $get_shift += 1;
        // return $data;
        $shift = Shift::create($data);
        return $this->send_response(200, "تم أضافة الشفت بنجاح", [], User::find($request["user_id"]));
    }
}