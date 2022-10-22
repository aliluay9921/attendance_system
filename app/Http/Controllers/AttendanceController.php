<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\Absent;
use App\Models\Attendance;
use App\Traits\Pagination;
use App\Traits\SendResponse;
use Illuminate\Http\Request;
use function PHPSTORM_META\type;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    use SendResponse, Pagination;

    public function sendAttendance(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            "mac_address" => "required",
            "ip_mobile" => "required",
            "lang_tude" => "required",
            "lat_tude" => "required",
        ]);
        if ($validator->fails()) {
            return $this->send_response(401, 'خطأ بالمدخلات', $validator->errors(), []);
        }
        $data = [];
        $data = [
            "user_id" => auth()->user()->id,
            "mac_address" => $request["mac_address"],
            "ip_mobile" => $request["ip_mobile"],
            "lang_tude" => $request["lang_tude"],
            "lat_tude" => $request["lat_tude"],
            "status" => 0,
            "date" => Carbon::now()->format("Y-m-d")

        ];

        // save attendance time when send request time 
        $attendance_time = Carbon::now()->addHours(3)->format("g:i A");
        $data["attendance_time"] = $attendance_time;


        $attendance = Attendance::create($data);
        return $this->send_response(200, 'تم تسجيل الحضور بنجاح', [], $attendance);
    }
    public function sendLeaving(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            "attendance_id" => "required|exists:attendances,id",
            "mac_address" => "required",
            "ip_mobile" => "required",
            "lang_tude" => "required",
            "lat_tude" => "required",
        ]);
        if ($validator->fails()) {
            return $this->send_response(401, 'خطأ بالمدخلات', $validator->errors(), []);
        }
        $data = [];
        $data = [
            "user_id" => auth()->user()->id,
            "mac_address" => $request["mac_address"],
            "ip_mobile" => $request["ip_mobile"],
            "lang_tude" => $request["lang_tude"],
            "lat_tude" => $request["lat_tude"],
            "status" => 0
        ];
        // save attendance time when send request time 
        $leaving_time = Carbon::now()->addHours(3)->format("g:i A");
        $data["leaving_time"] = $leaving_time;


        $attendance = Attendance::find($request["attendance_id"]);
        $attendance->update($data);
        return $this->send_response(200, 'تم تسجيل الانصراف  بنجاح', [], $attendance);
    }

    public function addAbsents(Request $request)
    {
        // $request = $request->json()->all();
        $users = User::where("user_type", 1)->get();

        foreach ($users as $user) {

            $attendance = Attendance::where("date", Carbon::now()->format("Y-m-d"))->where("user_id", $user->id)->get();
            if (count($attendance) > 0) {
                continue;
            } else {
                $filter = Absent::where("date", Carbon::now()->format("Y-m-d"))->where("user_id", $user->id)->first();
                if ($filter) {
                    continue;
                } else {
                    Absent::create([
                        "user_id" => $user->id,
                        "status" => 0,
                        "date" => Carbon::now()->format("Y-m-d")
                    ]);
                }
            }
        }
        return  $this->send_response(200, "تم تسجيل الغياب بنجاح", [], []);
    }
    public function addReward(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            "user_id" => "required|exists:users,id",
        ], [
            "user_id.required" => "يجب ادخال المستخدم",
            "user_id.exists" => "المستخدم الذي قمت بأستخدامه غير متوفر ",
        ]);
        if ($validator->fails()) {
            return $this->send_response(401, 'خطأ بالمدخلات', $validator->errors(), []);
        }
        $user = User::find($request["user_id"]);
        Absent::create([
            "user_id" => $user->id,
            "status" => 2,
            "date" => Carbon::now()->format("Y-m-d")
        ]);
        return $this->send_response(200, "تم أضافة مكافئة الى المستخدم", [], User::find($request["user_id"]));
    }

    public function changeStatusAbsent(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            "absent_id" => "required|exists:absents,id"
        ]);
        if ($validator->fails()) {
            return $this->send_response(401, 'خطأ بالمدخلات', $validator->errors(), []);
        }
        $absent = Absent::find($request["absent_id"]);
        $absent->update(["status" => 1]);
        return $this->send_response(200, "تم تحويل الحاله الى مجاز", [], Absent::with("user")->find($request["absent_id"]));
    }

    public function getAbsents()
    {
        $absents = Absent::with("user");
        if (isset($_GET['query'])) {
            $absents->where(function ($q) {
                $columns = Schema::getColumnListing('absents');
                foreach ($columns as $column) {
                    $q->orWhere($column, 'LIKE', '%' . $_GET['query'] . '%');
                }
            });
        }
        if (isset($_GET['filter'])) {
            $filter = json_decode($_GET['filter']);
            $absents->where($filter->name, $filter->value);
        }
        if (isset($_GET)) {
            foreach ($_GET as $key => $value) {
                if ($key == 'skip' || $key == 'limit' || $key == 'query' || $key == 'filter') {
                    continue;
                } else {
                    $sort = $value == 'true' ? 'desc' : 'asc';
                    $absents->orderBy($key,  $sort);
                }
            }
        }
        if (!isset($_GET['skip']))
            $_GET['skip'] = 0;
        if (!isset($_GET['limit']))
            $_GET['limit'] = 10;
        $res = $this->paging($absents->orderBy("created_at", "desc"),  $_GET['skip'],  $_GET['limit']);
        return $this->send_response(200, 'تم جلب الغائبين  بنجاح', [], $res["model"], null, $res["count"]);
    }

    public function getAttendaces()
    {
        $attendances = Attendance::with("user");
        if (isset($_GET['query'])) {
            $attendances->where(function ($q) {
                $columns = Schema::getColumnListing('attendances');
                foreach ($columns as $column) {
                    $q->orWhere($column, 'LIKE', '%' . $_GET['query'] . '%');
                }
            });
        }
        if (isset($_GET['filter'])) {
            $filter = json_decode($_GET['filter']);
            $attendances->where($filter->name, $filter->value);
        }
        if (isset($_GET)) {
            foreach ($_GET as $key => $value) {
                if ($key == 'skip' || $key == 'limit' || $key == 'query' || $key == 'filter') {
                    continue;
                } else {
                    $sort = $value == 'true' ? 'desc' : 'asc';
                    $attendances->orderBy($key,  $sort);
                }
            }
        }
        if (!isset($_GET['skip']))
            $_GET['skip'] = 0;
        if (!isset($_GET['limit']))
            $_GET['limit'] = 10;
        $res = $this->paging($attendances->orderBy("created_at", "desc"),  $_GET['skip'],  $_GET['limit']);
        return $this->send_response(200, 'تم جلب جدول الحضور', [], $res["model"], null, $res["count"]);
    }
}