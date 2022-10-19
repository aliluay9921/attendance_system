<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Traits\Pagination;
use App\Traits\SendResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    use SendResponse, Pagination;
    public function getRoles()
    {
        $roles = Role::select("*");
        if (!isset($_GET['skip']))
            $_GET['skip'] = 0;
        if (!isset($_GET['limit']))
            $_GET['limit'] = 20;
        $res = $this->paging($roles->orderBy("created_at", "desc"),  $_GET['skip'],  $_GET['limit']);
        return $this->send_response(200, 'تم جلب القوانين بنجاح', [], $res["model"], null, $res["count"]);
    }
    public function addRole(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            "role" => 'required|unique:roles',
            "value" => 'required'
        ], [
            'role.required' => 'يرجى ادخال الشرط المراد اضافته',
            'value.required' => 'يرجى ادخال قيمة الشرط ',
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, 'حصل خطأ في الأدخال', $validator->errors(), []);
        }
        $data = [];
        $data = [
            "role" => $request["role"],
            "value" => $request["value"],
        ];
        $role = Role::create($data);
        return $this->send_response(200, 'تم أضافة الشروط بنجاح', [], Role::find($role->id));
    }

    public function updateRole(Request $request)
    {
        $request = $request->json()->all();
        $role = Role::find($request['role_id']);
        $validator = Validator::make($request, [
            "role_id" => "required|exists:roles,id",
            "role" => 'required|unique:roles,role,' . $role->id,
            "value" => 'required',
        ], [
            'role_id.required' => 'يجب ادخال  العنصر المراد التعديل عليه',
            'role_id.exists' => 'العنصر الذي قمت بأدخاله غير موجود',
            'role.required' => ' يرجى ادخال عنوان الشرط ',
            'value.required' => ' يرجى ادخال قيمة الشرط ',
        ]);
        if ($validator->fails()) {
            return $this->send_response(401, 'خطأ بالمدخلات', $validator->errors(), []);
        }

        $data = [];
        $data = [
            "role" => $request["role"],
            "value" => $request["value"],
        ];
        $role->update($data);
        return $this->send_response(200, 'تم التعديل على الشرط بنجاح', [], Role::find($request["role_id"]));
    }
}