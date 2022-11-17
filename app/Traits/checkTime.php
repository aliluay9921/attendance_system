<?php

namespace App\Traits;

trait checkTime
{
    use SendResponse;
    public function check($attendance_request, $attendance_required)
    {
        // $attendance_required = '08:00:00';
        // $attendance_request = '09:30:00';
        $array1 = explode(':', $attendance_required);
        $array2 = explode(':', $attendance_request);

        $minutes1 = ($array1[0] * 60.0 + $array1[1]);
        $minutes2 = ($array2[0] * 60.0 + $array2[1]);
        return  $minutes1 / 60.0;
        // if ($minutes1 < $minutes2) {
        //     return $this->send_response(400, "لايمكنك تسجيل الدخول قبل وقت الدوام", [], []);
        // }
        $diff = $minutes1 - $minutes2;
        return $diff / 60;
    }
    public function check2($attendance_request, $attendance_required, $type)
    {
        $time1 = strtotime($attendance_required); //07:00:00
        $time2 = strtotime($attendance_request);  //07:10:40  07:23:27
        // حضور
        if ($type == 0) {
            // if (
            //     $time1 > $time2
            // ) {
            //     return $this->send_response(400, "لايمكنك تسجيل الدخول قبل وقت الدوام", [], []);
            // }
            $difference = round(abs($time2 - $time1) / 3600, 1);
            return $difference;
        } else {
            // مغادرة
            if (
                $time1 < $time2
            ) {
                return 0;
            }
            $difference = round(abs($time1 - $time2) / 3600, 1);
            return $difference;
        }
    }
}