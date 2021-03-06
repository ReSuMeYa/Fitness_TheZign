<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB as DB;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Choose_Main extends Controller
{
    public function Dashboard()
    {
    	return view('Dashboard');
    }

    public function Check_K_bank()
    {
        date_default_timezone_set("Asia/Bangkok");
        $today = date("Y-m-d");
        $Data = DB::table('main_table')
                ->join('member', 'main_table.Code', '=', 'member.code')
                ->where('member.type', '1D-Kbank')
                ->where('main_table.date', $today)->count();
        if($Data >= '10'){
            $class = 'btn-danger';
        }else{
            $class = 'btn-success';
        }

        $ResArray = ['Count' => $Data,'Class' => $class,'Date' => $today];
        return \Response::json($ResArray);
    }
}
