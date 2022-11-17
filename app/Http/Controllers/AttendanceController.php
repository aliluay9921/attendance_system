<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\Absent;
use App\Models\Holiday;
use App\Models\Attendance;
use App\Models\Shift;
use App\Traits\checkTime;
use App\Traits\Pagination;
use App\Traits\SendResponse;
use Attribute;
use Illuminate\Http\Request;
use function PHPSTORM_META\type;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Console\Input\Input;

class AttendanceController extends Controller
{
    use SendResponse, Pagination, checkTime;

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
        $get_attendance_in_day = Attendance::where("user_id", auth()->user()->id)->where("date", Carbon::now()->format("Y-m-d"))->first();
        if (!$get_attendance_in_day) {
            $shift = Shift::where("user_id", auth()->user()->id)->where("shift", 1)->first();
        } else {
            $shift = Shift::where("user_id", auth()->user()->id)->where("shift", 2)->first();
        }
        // $shift = Shift::where("user_id", auth()->user()->id)->count();
        $attendance_time = Carbon::now()->addHours(3)->format("h:i:s");
        $data["num_clock"] = $this->check2($attendance_time, $shift->start_time, 0);
        $data["attendance_time"] = $attendance_time;
        return $data;
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
        $attendance = Attendance::find($request["attendance_id"]);
        $attendance_count = Attendance::where("user_id", auth()->user()->id)->where("date", Carbon::now()->format("Y-m-d"))->count();
        if ($attendance_count == 1) {
            $shift = Shift::where("user_id", auth()->user()->id)->where("shift", 1)->first();
        } else {
            $shift = Shift::where("user_id", auth()->user()->id)->where("shift", 2)->first();
        }
        if (auth()->user()->id != $attendance->user_id) {
            return $this->send_response(400, 'لايمكنك تسجيل الانصراف لمستخدم غيرك', [], []);
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
        $leaving_time = Carbon::now()->addHours(3)->format("h:i:s");
        $data["leaving_time"] = $leaving_time;
        // return $this->check2($leaving_time, auth()->user()->leave_attendance, 1);
        $attendance->num_clock += $this->check2($leaving_time, $shift->end_time, 1);
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
    public function add_holiday(Request $request)
    {


        $request = $request->json()->all();
        $validator = Validator::make($request, [
            "title" => "required"
        ]);
        if ($validator->fails()) {
            return $this->send_response(401, 'خطأ بالمدخلات', $validator->errors(), []);
        }
        $check = Holiday::whereBetween("from_day", [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->where("user_id", auth()->user()->id)->count();
        if ($check >= 4) {
            return $this->send_response(400, "لايمكنك طلب اجازة اكثر من 4 مرات في الشهر", [], []);
        }
        $data = [
            "user_id" => auth()->user()->id,
            "title" => $request["title"],
            "body" => $request["body"],
            "from_day" => $request["from_day"],
            "to_day" => $request["to_day"],
            "from_hour" => $request["from_hour"],
            "to_hour" => $request["to_hour"],
        ];
        $holiday = Holiday::create($data);
        return  $this->send_response(200, "تم تسجيل الغياب بنجاح", [], $holiday);
    }
    public function get_holiday()
    {
        $holiday = Holiday::with("user");
        if (isset($_GET['query'])) {
            $holiday->where(function ($q) {
                $columns = Schema::getColumnListing('holidays');
                foreach ($columns as $column) {
                    $q->orWhere($column, 'LIKE', '%' . $_GET['query'] . '%');
                }
            });
        }
        if (isset($_GET['filter'])) {
            $filter = json_decode($_GET['filter']);
            $holiday->where($filter->name, $filter->value);
        }
        if (isset($_GET)) {
            foreach ($_GET as $key => $value) {
                if ($key == 'skip' || $key == 'limit' || $key == 'query' || $key == 'filter') {
                    continue;
                } else {
                    $sort = $value == 'true' ? 'desc' : 'asc';
                    $holiday->orderBy($key,  $sort);
                }
            }
        }
        if (!isset($_GET['skip']))
            $_GET['skip'] = 0;
        if (!isset($_GET['limit']))
            $_GET['limit'] = 10;
        $res = $this->paging($holiday->orderBy("created_at", "desc"),  $_GET['skip'],  $_GET['limit']);
        return $this->send_response(200, 'تم جلب الاجازات بنجاح', [], $res["model"], null, $res["count"]);
    }
}