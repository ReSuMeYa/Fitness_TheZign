<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB as DB;
use Illuminate\Routing\Controller as BaseController;

class Checkin extends Controller
{
    public function CheckInPage(Request $request)
    {
    	return view('CheckIn');
    }

    public function CheckInProcessor(Request $request)
    {
        $Code = $request->post('inputcode');
        $CheckNum = DB::table('member')->where('code', $Code)->count();
        $CheckStatus = DB::table('member')->where('code', $Code)->where('status', 'Active')->count();
        $Data = DB::table('member')->where('code', $Code)->get();
        $Item = DB::table('item')->get();

        return view('CheckIn',[
            'Code'=>$Code,
            'CheckNum'=>$CheckNum,
            'CheckStatus'=>$CheckStatus,
            'Data'=>$Data,
            'Item'=>$Item]);
    }

    public function Namesearching(Request $request)
    {
    	$name = $request->post('name');
    	$status = $request->post('status');
    	if ($status=='all') {
    	$users_data = DB::table('member')
    					->select('*')
    					->where('name', 'like', '%'.$name.'%')
    					->orWhere('code', 'like', '%'.$name.'%')
    					->limit(100)
    					->get();
    	}else{
    	$users_data = DB::table('member')
    					->select('*')
    					->where('status', 'Active')
    					->where('name', 'like', '%'.$name.'%')
    					->orWhere('code', 'like', '%'.$name.'%')
    					->where('status', 'Active')
    					->limit(100)
    					->get();
    	}
    	// Table
    	$table = '
    	<br>
        <div class="row">
        <div class="col-md-12">
		  <table class="table table-sm">
		  <thead>
		    <tr align="center" class="bg-primary">
		      <th scope="col">Code</th>
		      <th scope="col">History</th>
		      <th scope="col">ชื่อ-สนามสกุล</th>
		      <th scope="col">วันที่สิ้นสุดใช้งาน</th>
		      <th scope="col">ประเภท</th>
		      <th scope="col">สถานะ</th>
		    </tr>
		  </thead>
		  <tbody>';
		  foreach ($users_data as $key => $data) {
		// Change value
		if ($data->status == 'Active') {
			$restatus = 'ใช้งาน';
			$recode = "<a href='#'><span onclick='posttocheckin(this);' class='badge badge-pill badge-primary' code='$data->code'>$data->code</span></a>";
		}else{
			$restatus = 'หมดอายุ';
			$recode = "<a href='#'><span class='badge badge-pill badge-danger'>$data->code</span></a>";
		}
		// Table
		$table .= "
		    <tr align='center'>
		      <td>$recode</td>
		      <td><a href='#'><span class='badge badge-pill badge-primary'>ดูประวัติ</span></a></td>
		      <td>$data->name</td>
		      <td>".date('d-m-Y', strtotime($data->expire))."</td>
		      <td>$data->type</td>
		      <td>$restatus</td>
		    </tr>";
		    }
		$table .= '</tbody></table></div></div>';
		// Show Json
      $array = array('Table' => $table);
      echo  json_encode($array);
    }

    public function CheckInOnline(Request $request)
    {
      date_default_timezone_set("Asia/Bangkok");
      $today = now();
      $date = date("Y-m-d");
    	$Code = $request->post('Code');
    	$Data = DB::table('member')->where('code', $Code)->get();
    	foreach ($Data as $key => $row) {
	    // Insert Data Online
	    $id = DB::table('main_table')->insertGetId([
	    	'Code' => $row->code,
	    	'Name' => $row->name,
	    	'Guset_in' => $today,
	    	'Status' => 'IN',
        'date' => $date]);
        // Update Main Id
        DB::table('fake_table')
            ->where('Fake_code', $Code)
            ->update(['main_id' => $id]);
        }
    }

    public function Insert_type_L(Request $request)
    {
        date_default_timezone_set("Asia/Bangkok");
        $today = now();
        $code = $request->post('Code');
        $itemcode = $request->post('Item_code');
        $itemtype = $request->post('Item_type');
        $itemname = $request->post('Item_name');
        $itemprice= $request->post('Item_price');
        $itemcodetype = $request->post('Item_codetype');
        $CheckOnline_Mainid = DB::table('main_table')->where('Code', $code)->where('Status', 'IN')->count();
        if ($CheckOnline_Mainid == '0') {
          // Insert Data Fake Table
          DB::table('fake_table')->insert([
            'Fake_code' => $code,
       	    'Fake_datetime' => $today,
      	    'Fake_itemcode' => $itemcode,
            'Fake_itemcodetype' => $itemcodetype,
       	    'Fake_itemtype' => $itemtype,
       	    'Fake_itemname' => $itemname,
      	    'Fake_price'    => $itemprice,
      	    'Fake_sum'      => '1',
      	  ]);
        }else{
        $DataMain_ID = DB::table('main_table')->where('Code', $code)->where('Status', 'IN')->get();
          foreach ($DataMain_ID as $key => $Data) {
            // Insert Data Fake Table
            DB::table('fake_table')->insert([
          	  'Fake_code' => $code,
              'main_id' => $Data->ID,
         	    'Fake_datetime' => $today,
        	    'Fake_itemcode' => $itemcode,
              'Fake_itemcodetype' => $itemcodetype,
         	    'Fake_itemtype' => $itemtype,
         	    'Fake_itemname' => $itemname,
        	    'Fake_price'    => $itemprice,
        	    'Fake_sum'      => '1',
        	  ]);
          }
        }
    }

    public function Insert_type_C(Request $request)
    {
          date_default_timezone_set("Asia/Bangkok");
          $today = now();
          $code = $request->post('Code');
          $itemcode = $request->post('Item_code');
          $itemtype = $request->post('Item_type');
          $itemname = $request->post('Item_name');
          $itemprice= $request->post('Item_price');
          $itemcodetype = $request->post('Item_codetype');
          $CheckOnline_Mainid = DB::table('main_table')->where('Code', $code)->where('Status', 'IN')->count();
          if ($CheckOnline_Mainid == '0') {
            // Insert Data Fake Table
            $fake_id = DB::table('fake_table')->insertGetId([
              'Fake_code' => $code,
              'Fake_datetime' => $today,
              'Fake_itemcode' => $itemcode,
              'Fake_itemcodetype' => $itemcodetype,
              'Fake_itemtype' => $itemtype,
              'Fake_itemname' => $itemname,
              'Fake_price'    => $itemprice,
              'Fake_sum'      => '1',
            ]);
          }else{
          $DataMain_ID = DB::table('main_table')->where('Code', $code)->where('Status', 'IN')->get();
            foreach ($DataMain_ID as $key => $Data) {
              // Insert Data Fake Table
              $fake_id = DB::table('fake_table')->insertGetId([
                'Fake_code' => $code,
                'main_id' => $Data->ID,
                'Fake_datetime' => $today,
                'Fake_itemcode' => $itemcode,
                'Fake_itemcodetype' => $itemcodetype,
                'Fake_itemtype' => $itemtype,
                'Fake_itemname' => $itemname,
                'Fake_price'    => $itemprice,
                'Fake_sum'      => '1',
              ]);
            }
          }
        $Data = array('fake_id' => $fake_id);
        echo json_encode($Data);
    }

    public function Insert_type_P(Request $request)
    {
        date_default_timezone_set("Asia/Bangkok");
        $today = now();
        $date = date("Y-m-d");
        $code = $request->post('Code');
        $itemcode = $request->post('Item_code');
        $itemtype = $request->post('Item_type');
        $itemname = $request->post('Item_name');
        $itemprice= $request->post('Item_price');
        $itemcodetype = $request->post('Item_codetype');
        $itemsetnumber = $request->post('Item_setnumber');
        // Insert Data Package Fake
        $fake_package = DB::table('fake_package')->insertGetId([
          'fake_code' => $code,
          'fake_datetime' => $today,
          'fake_itemcode' => $itemcode,
          'fake_typeitemcode' => $itemcodetype,
          'fake_typeitem' => $itemtype,
          'fake_itemname' => $itemname,
          'fake_price'    => $itemprice,
          'fake_sum'      => $itemsetnumber,
          'Status'        => 'BUY'
        ]);
        // Insert Data Package Main
        $package_detail_id = DB::table('package_detail')->insertGetId([
          'fake_code' => $code,
          'fake_datetime' => $today,
          'fake_itemcode' => $itemcode,
          'fake_typeitemcode' => $itemcodetype,
          'fake_typeitem' => $itemtype,
          'fake_itemname' => $itemname,
          'fake_price'    => $itemprice,
          'fake_sum'      => $itemsetnumber,
        ]);
        // Insert Package Main
        $idpackage = DB::table('main_package')->insertGetId([
          'main_package_id' => $package_detail_id,
          'Code' => $code,
          'date' => $date,
          'Status' => 'Active',
          'name_package' => $itemname,
          'total_sum' => $itemsetnumber,
          'have_sum' => $itemsetnumber]);
        // Update Main Id
        DB::table('fake_package')
            ->where('fake_code', $code)
            ->where('fake_package_id', $fake_package)
            ->update(['main_package_id' => $idpackage]);
        // Update Package Main
        DB::table('package_detail')
            ->where('fake_code', $code)
            ->where('package_id', $package_detail_id)
            ->update(['main_package_id' => $idpackage]);
        // Insert Data Fake Table
        $fake_id = DB::table('fake_table')->insertGetId([
          'Fake_code' => $code,
          'Fake_datetime' => $today,
          'Fake_itemcode' => $itemcode,
          'Fake_itemcodetype' => $itemcodetype,
          'Fake_itemtype' => $itemtype,
          'Fake_itemname' => $itemname,
          'Fake_price'    => $itemprice,
          'Fake_sum'      => $itemsetnumber,
        ]);
        // Update Fake Id
        DB::table('fake_package')
            ->where('Fake_code', $code)
            ->where('fake_package_id', $fake_package)
            ->update(['fake_table_id' => $fake_id]);
        $Data = array('itemsetnumber' => $itemsetnumber, 'itemtype' => $itemtype, 'fake_id' => $fake_id);
        echo json_encode($Data);
    }

    public function DisplayPackage(Request $request)
    {
        date_default_timezone_set("Asia/Bangkok");
        $today = now();
        $Code = $request->post('Code');
        $Datajoin = DB::table('main_package')
                        ->select('*')
                        ->join('fake_package', 'main_package.main_package_id', '=', 'fake_package.main_package_id')
                        ->where('main_package.Status', '=', 'Active')
                        ->where('main_package.Code', '=', $Code)
                        ->get();
        $CheckNum = DB::table('main_package')->where('Code', $Code)->where('Status', '=', 'Active')->count();
        if ($CheckNum != '0') {
        $Table = '
          <table class="table table-striped table-sm" style="margin-bottom: 0rem;">
          <tbody>';
        foreach ($Datajoin as $key => $Row) {
        $rehavesum = $Row->have_sum;
        // เช็คว่า มี เทรนเนอร์ หรือไม่
        if ($Row->trainner_emp_id != '') {
        $trainner_name = DB::table('trainner_emp')->where('tn_emp_id',  $Row->trainner_emp_id)->get();
        foreach ($trainner_name as $key => $name) {
          $Name = '<b>ผู้สอน: </b>'.$name->fname .' '. $name->lname;
        }}else{
          $Name = "";
        }
        // เช็คว่า Package ฟรี หรือไม่ แยกสี ถ้าฟรี = P15
        if ($Row->fake_typeitemcode == 'P15') {
        $Table .= "
          <tr class='free_package animated flipInX'>
          <td><b>แพ็กเกจ:</b> $Row->name_package<br>$Name</td>
          <td><b>วันที่ซื้อ:</b> ".date('d/m/Y', strtotime($Row->date))."</td>
          <td><b>จำนวนที่ซื้อ:<b> $Row->total_sum ครั้ง</td>
          <td><b>จำนวนคงเหลือ:</b> $rehavesum ครั้ง</td>";
        $Table .='<td><button class="btn btn-sm btn-success" package_id="'.$Row->id.'" onclick="History_Package_Useing(this);">ประวัติ</button></td></tr>';
        }else {
        $Table .= "
          <tr class='buy_package animated flipInX'>
          <td><b>แพ็กเกจ:</b> $Row->name_package<br>$Name</td>
          <td><b>วันที่ซื้อ:</b> ".date('d/m/Y', strtotime($Row->date))."</td>
          <td><b>จำนวนที่ซื้อ:<b> $Row->total_sum ครั้ง</td>
          <td><b>จำนวนคงเหลือ:</b> $rehavesum ครั้ง</td>";
        $Table .='<td><button class="btn btn-sm btn-success" package_id="'.$Row->id.'" onclick="History_Package_Useing(this);">ประวัติ</button></td></tr>';         
        }
        
        }
        $Table .= '
          </tbody>
          </table>';
        }else{
        $Table = "";
        }
        // Show Json
        $array = array('Table' => $Table);
        echo json_encode($array);
    }

    public function PackageItem(Request $request)
    {
        date_default_timezone_set("Asia/Bangkok");
        $today = now();
        $Code = $request->post('Code');
        $Datajoin = DB::table('main_package')
                        ->select('*')
                        ->join('fake_package', 'main_package.main_package_id', '=', 'fake_package.main_package_id')
                        ->where('main_package.Status', '=', 'Active')
                        ->where('main_package.Code', '=', $Code)
                        ->get();
        $CheckNum = DB::table('main_package')->where('Code', $Code)->where('Status', '=', 'Active')->count();
        $CounMainOnline = DB::table('main_table')->where('Code', $Code)->where('Status', 'IN')->count();

        if ($CheckNum != '0') {
        $Data = '
          <div class="card card-info card-outline">
          <div class="card-body" style="padding: 0.5rem;">
          <table class="table table-striped table-sm">
          <tbody>
          <tr class="bg-primary" align="center"><td><b>รายการ</b></td><td><b>ตัวช่วย</b></td></tr>';
        foreach ($Datajoin as $key => $data) {
        $rehavesum = $data->have_sum;
        // เช็คว่า Package ฟรี หรือไม่ แยกสี ถ้าฟรี = P15
        if ($data->fake_typeitemcode == 'P15') {
        $Data .= "
          <tr class='free_package' class='animated flipInX'>
          <td width='70%'>
          <b>$data->name_package | คงเหลือ:</b> $rehavesum
          </td>
          <td width='30%'>";
        $Data .= "<button class='btn btn-sm btn-success' main_package_id='$data->id' package_detail_id='$data->main_package_id' code='$data->Code' total='$data->total_sum' havesum='$data->have_sum' trainner_emp='$data->trainner_emp_id' onclick='OnUsePackage(this);'>เลือกใช้งาน</button>";
        $Data .= "</td>
          </tr>";
        }else{
        $Data .= "
          <tr class='buy_package' class='animated flipInX'>
          <td width='70%'>
          <b>$data->name_package | คงเหลือ:</b> $rehavesum
          </td>
          <td width='30%'>";
        $Data .= "<button class='btn btn-sm btn-success' main_package_id='$data->id' package_detail_id='$data->main_package_id' code='$data->Code' total='$data->total_sum' havesum='$data->have_sum' trainner_emp='$data->trainner_emp_id' onclick='OnUsePackage(this);'>เลือกใช้งาน</button>";
        $Data .= "</td>
          </tr>";         
        }
        }
        $Data .= '
          </tbody>
          </table>
          </div></div>';
        }else{
        $Data = "";
        }
        // Show Json
        $array = array('Data' => $Data);
        echo json_encode($array);
    }

    public function TableDisplay(Request $request)
    {
    	$Code = $request->post('Code');
    	$DataCode = DB::table('fake_table')->where('Fake_code', $Code)->get();
    	$SumPrice = DB::table('fake_table')->where('Fake_code','=', $Code)->sum('Fake_price');
    	$CounCheckNull = DB::table('fake_table')->where('Fake_code', $Code)->count();
      $CounMainOnline = DB::table('main_table')->where('Code', $Code)->where('Status', 'IN')->count();
    	//  Table Data
    	$Data = '
    	<table class="table table-sm table-hover animated flipInX">
    		<thead>
          <tr align="center" class="bg-primary"><th colspan="6">รายการสินค้าหลัก</th></tr>
    		  <tr align="center" class="bg-primary">
    		  <th>ลำดับที่</th>
    		  <th>รหัสโค้ด</th>
    		  <th>รายการ</th>
    		  <th>ราคา</th>
    		  <th>จำนวน</th>
    		  <th>ตัวช่วย</th>
    		  </tr>
    		</thead><tbody>';
    	// Have Data
    	if ($CounCheckNull > 0) {
    	$i = 0;
    	foreach ($DataCode as $key => $DataDisplay) {
    	$i++;
        //Select Item type code
        if ($DataDisplay->Fake_itemcodetype == 'P') {
            //Piece
            $Itemtypcode = 'ผืน';
        }elseif ($DataDisplay->Fake_itemcodetype == 'C') {
            //Bottle
            $Itemtypcode = 'ขวด';
        }elseif ($DataDisplay->Fake_itemcodetype == 'T') {
            //Time
            $Itemtypcode = 'ชั่วโมง';
        }elseif ($DataDisplay->Fake_itemcodetype == 'G') {
            //Glass
            $Itemtypcode = 'แก้ว';
        }elseif ($DataDisplay->Fake_itemcodetype == 'W') {
            //Wood
            $Itemtypcode = 'ไม้';
        }else{
            // Null
            $Itemtypcode = '';
        }

    	$Data .= "
        <input type='hidden' id='Main_idhidden' value='$DataDisplay->main_id'>
    	  <tr align='center'>
    	  <td>$i</td>
    	  <td>$DataDisplay->Fake_itemcode</td>
    	  <td>$DataDisplay->Fake_itemname</td>
        <td>".number_format($DataDisplay->Fake_price)." ฿</td>";
      if ($DataDisplay->Fake_itemcodetype == 'F') {
        $Data .= "<td>เลขกุญแจ $DataDisplay->Fake_sum#</td>";
      }elseif ($DataDisplay->Fake_itemcodetype == 'O') {
        // ค่าเริ่มต้นคือว่าง
        if($DataDisplay->Fake_sum == '1') {
          $Data .= "<td>ว่าง</td>"; 
        }else{
          $Data .= "<td>$DataDisplay->Fake_sum</td>";  
        }
      }elseif ($DataDisplay->Fake_itemcodetype == 'L') {
        if($DataDisplay->Fake_sum == '1') {
          $Data .= "<td>รอเลือกหมายเลขห่วงยาง</td>"; 
        }else{
          $Data .= "<td>ห่วงยางหมายเลข $DataDisplay->Fake_sum#</td>";  
        }
      }else{
        $Data .= "<td>$DataDisplay->Fake_sum $Itemtypcode</td>";
      }
    	$Data .= "<td align='right'>";
        // Now Not Online User
        if ($CounMainOnline == '0') {
        // ItemCodeType == T
        if ($DataDisplay->Fake_itemcodetype == 'T') {
          if ($DataDisplay->Fake_itemcodetype == 'T' AND $DataDisplay->Fake_itemtype == 'C') {
            $Data .= " <button class='btn btn-sm btn-danger' onclick='Delete_item(this);' fake_table_id='$DataDisplay->id'><i class='fas fa-trash'></i></button>";
          }else{
            // ถ้า package มีจำนวนมากกว่า 1 ชั่วโมง ให้เพิ่มปุ่มเลือก ผู้สอน ยังไม่ได้ CheckIn
            if ($DataDisplay->Fake_sum > 1) {
              if ($DataDisplay->trainner_emp_id == '') {
                $Data .="<button class='btn btn-sm btn-danger' fake_table_id='$DataDisplay->id'  onclick='trianner_emp_select_modal(this);'><i class='fas fa-user-tie'></i></button> ";  
              }else{
                $Data .="<button class='btn btn-sm btn-success' fake_table_id='$DataDisplay->id'  onclick='trianner_emp_select_modal(this);'><i class='fas fa-user-tie'></i></button> "; 
              }
            }
            // ปุ่มลบ package Item ของ เวลา
            $Data .= "<button class='btn btn-sm btn-danger' onclick='Delete_item_time(this);' fake_table_id='$DataDisplay->id' data-toggle='tooltip' data-placement='left' title='Tooltip on bottom'><i class='fas fa-times'></i></button>";
          }
        }elseif ($DataDisplay->Fake_itemcodetype == 'F') {
          $Data .= "<button class='btn btn-sm btn-primary' onclick='Edit_Number_Key(this);' fake_table_id='$DataDisplay->id'><i class='fas fa-key'></i></button>
                    <button class='btn btn-sm btn-danger' onclick='Delete_item(this);' fake_table_id='$DataDisplay->id'><i class='fas fa-trash'></i></button>";
        }elseif ($DataDisplay->Fake_itemcodetype == 'O') {
          $Data .= "<button class='btn btn-sm btn-primary' onclick='Edit_Other(this);' fake_table_id='$DataDisplay->id'><i class='fas fa-exclamation-triangle'></i></button>
                    <button class='btn btn-sm btn-danger' onclick='Delete_item(this);' fake_table_id='$DataDisplay->id'><i class='fas fa-trash'></i></button>";         
        }elseif ($DataDisplay->Fake_itemcodetype == 'L') {
          $Data .= "<button class='btn btn-sm btn-primary' onclick='Life_ring(this);' fake_table_id='$DataDisplay->id'><i class='far fa-life-ring'></i></button>
                    <button class='btn btn-sm btn-danger' onclick='Delete_item(this);' fake_table_id='$DataDisplay->id'><i class='fas fa-trash'></i></button>";      
        }
        // ItemCodeType != T
        else{
          $Data .= "<button class='btn btn-sm btn-warning' onclick='Discount(this);' fake_table_id='$DataDisplay->id'><i class='fas fa-percent'></i></button>
                    <button class='btn btn-sm btn-primary' onclick='Edit_Number(this);' fake_table_id='$DataDisplay->id'><i class='fas fa-dollar-sign'></i></button>
                    <button class='btn btn-sm btn-danger' onclick='Delete_item(this);' fake_table_id='$DataDisplay->id'><i class='fas fa-trash'></i></button>";
        }
        }
        //Now Online User
        else{
        // Check Void Item
        if ($DataDisplay->Fake_status == 'V' OR $DataDisplay->Fake_status == 'RV') {
        // Void Success
            $Data .= "<span class='badge badge-danger'>Void</span>";
        }elseif ($DataDisplay->Fake_status == 'C') {
        // Charge Success
            $Data .= "<span class='badge badge-danger'>Charge</span>";
        }else{
          // Check Item_Type
          if ($DataDisplay->Fake_itemcodetype == 'P') {
            $Data .= "<button class='btn btn-sm btn-warning' onclick='Charge_modal(this);' fake_table_id='$DataDisplay->id'>Charge</button>";
            $Data .= " <button class='btn btn-sm btn-danger' onclick='Delete_item(this);' fake_table_id='$DataDisplay->id'><i class='fas fa-trash'></i></button>";
          }elseif ($DataDisplay->Fake_itemcodetype == 'C' OR $DataDisplay->Fake_itemcodetype == 'G') {
            $Data .= "<button class='btn btn-sm btn-danger' onclick='VoidItem_modal(this);' fake_table_id='$DataDisplay->id'>Void</button>";
          }elseif ($DataDisplay->Fake_itemcodetype == 'F') {
            $Data .= "<button class='btn btn-sm btn-warning' onclick='Charge_modal(this);' fake_table_id='$DataDisplay->id'>Charge</button> ";
            $Data .= "<button class='btn btn-sm btn-primary' onclick='Edit_Number_Key(this);' fake_table_id='$DataDisplay->id'><i class='fas fa-key'></i></button>";
          }elseif ($DataDisplay->Fake_itemcodetype == 'O') {
            $Data .= "<button class='btn btn-sm btn-primary' onclick='Edit_Other(this);' fake_table_id='$DataDisplay->id'><i class='fas fa-exclamation-triangle'></i></button>
                      <button class='btn btn-sm btn-danger' onclick='Delete_item(this);' fake_table_id='$DataDisplay->id'><i class='fas fa-trash'></i></button>";
          }elseif ($DataDisplay->Fake_itemcodetype == 'T') {
            if ($DataDisplay->Fake_itemcodetype == 'T' AND $DataDisplay->Fake_itemtype == 'C') {
              $Data .= " <button class='btn btn-sm btn-danger' onclick='Delete_item(this);' fake_table_id='$DataDisplay->id'><i class='fas fa-trash'></i></button>";
            }else{
              // ถ้า package มีจำนวนมากกว่า 1 ชั่วโมง ให้เพิ่มปุ่มเลือก ผู้สอน CheckIn แล้ว
              if ($DataDisplay->Fake_sum > 1) {
                if ($DataDisplay->trainner_emp_id == '') {
                  $Data .="<button class='btn btn-sm btn-danger' fake_table_id='$DataDisplay->id'  onclick='trianner_emp_select_modal(this);'><i class='fas fa-user-tie'></i></button> ";  
                }else{
                  $Data .="<button class='btn btn-sm btn-success' fake_table_id='$DataDisplay->id'  onclick='trianner_emp_select_modal(this);'><i class='fas fa-user-tie'></i></button> "; 
                }  
              }
              // ปุ่มลบ package Item ของ เวลา
              $Data .= "<button class='btn btn-sm btn-danger' onclick='Delete_item_time(this);' fake_table_id='$DataDisplay->id'><i class='fas fa-times'></i></button>";
            }
          }
        }
        }
        $Data .= "</td></tr>";
    	}
    	}
      // Not Have Data
      else{
    	$Data .= "
    	  <tr align='center'>
    	  <td colspan='6'>Null</td>
      	</tr>";
    	}
    	//  Table Data
    	$Data .= "
    	  <tr class='bg-primary'>
    	  <td colspan='5' align='right'><b>ราคารวม:</b></td>
    	  <td align='center'>".number_format($SumPrice)." <b>฿</b></td>
        <input type='hidden' id='pricehidden' value='$SumPrice'>
        <input type='hidden' id='pricehiddenformat' value='".number_format($SumPrice)."'>
    	  </tr>";
    	$Data .= '</tbody></table>';
    	// Have Data
    	if ($CounCheckNull >= 0) {
        // CheckData Main Online == 0
        if ($CounMainOnline == '0') {

        $Data .= '
          <div align="center">
          <button class="btn btn-success animated pulse" data-toggle="tooltip" data-placement="bottom" title="ยืนยันเข้าใช้งานวันนี้" onclick="CheckInOnline(this);">เข้าใช้งาน</button>
          </div>';
        }
        // CheckData Main Online == 1
        else{
        $Data .= '
          <div align="center">
          <button class="btn btn-danger animated pulse" data-toggle="tooltip" data-placement="bottom" title="เลิกใช้งาน" onclick="Dologout(this);">เลิกใช้งาน</button>
          </div>';
        }
    	}
      // Not Have Data
      else{
      // ยังไม่ได้เลือก ใช้งานสินค้า
    	$Data .= '
    	  <div align="center">
    	  <button class="btn btn-success animated pulse" data-toggle="tooltip" data-placement="bottom" title="กรุณาเลือกรายการก่อน" onclick="CheckInOnline(this);">เข้าใช้งาน</button>
    	  </div>';
    	}
		  // Show Json
    	$array = array('Table' => $Data);
    	echo json_encode($array);
    }

    public function TablePane(Request $request)
    {
        $Code = $request->post('Code');
        $Item = DB::table('item')->where('item_eye_hide', '0')->get();
        $CounMainOnline = DB::table('main_table')->where('Code', $Code)->where('Status', 'IN')->count();
        //Nav tab
        $Navtab = '
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" role="tab" aria-controls="general" aria-selected="true">ทั่วไป</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="cose-tab" data-toggle="tab" href="#cose" role="tab" aria-controls="cose" aria-selected="false">คลาส</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="contact-tab" data-toggle="tab" href="#contact" role="tab" aria-controls="contact" aria-selected="false">ซื้อแพ็กเก็จ</a>
            </li>
        </ul>';
        //Tab pane
        $Navtab .= '
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                <table class="table table-bordered table-sm table-hover">
                    <thead>
                    <tr align="center" class="bg-primary">
                        <th>ชื่อรายการ</th>
                        <th>จำนวน</th>
                        <th>ราคา</th>
                        <th>ตัวช่วย</th>
                    </tr>
                    </thead>
                    <tbody>';
                    foreach ($Item as $Item_Free) {
                    if ($Item_Free->item_type == "L") {
                        $Navtab .= "
                        <tr item_codetype='$Item_Free->item_code_type' item_code='$Item_Free->item_code' item_name='$Item_Free->item_name' item_price='$Item_Free->item_price' item_type='$Item_Free->item_type' item_setnumber='$Item_Free->item_setnumber' ondblclick='Item_To_Disktop(this)'>
                            <td><b>$Item_Free->item_name</b></td>
                            <td align='center'>$Item_Free->item_setnumber</td>
                            <td align='right'>".number_format((float)$Item_Free->item_price)."</td>
                            <td align='center'><button item_codetype='$Item_Free->item_code_type' item_code='$Item_Free->item_code' item_name='$Item_Free->item_name' item_price='$Item_Free->item_price' item_type='$Item_Free->item_type' item_setnumber='$Item_Free->item_setnumber' onclick='Item_To_Disktop(this)' class='btn btn-sm btn-primary'><i class='far fa-check-square'></i></button></td>
                        </tr>";
                    }
                    }
        //Nav tab
        $Navtab .= '
                    </tbody>
                </table>
            </div>
            <div class="tab-pane fade" id="cose" role="tabpanel" aria-labelledby="cose-tab">
                <table class="table table-bordered table-sm table-hover">
                    <thead>
                        <tr align="center" class="bg-primary">
                            <th>ชื่อรายการ</th>
                            <th>จำนวน</th>
                            <th>ราคา</th>
                            <th>ตัวช่วย</th>
                        </tr>
                    </thead>
                    <tbody>';
                    foreach ($Item as $Item_Free) {
                    if ($Item_Free->item_type == "C") {
                        $Navtab .= "
                        <tr item_codetype='$Item_Free->item_code_type' item_code='$Item_Free->item_code' item_name='$Item_Free->item_name' item_price='$Item_Free->item_price' item_type='$Item_Free->item_type' item_setnumber='$Item_Free->item_setnumber' ondblclick='Item_To_Disktop(this)'>
                            <td><b>$Item_Free->item_name</b></td>
                            <td align='center'>$Item_Free->item_setnumber</td>
                            <td align='right'>".number_format((float)$Item_Free->item_price)."</td>
                            <td align='center'><button item_codetype='$Item_Free->item_code_type' item_code='$Item_Free->item_code' item_name='$Item_Free->item_name' item_price='$Item_Free->item_price' item_type='$Item_Free->item_type' item_setnumber='$Item_Free->item_setnumber' onclick='Item_To_Disktop(this)' class='btn btn-sm btn-primary'><i class='far fa-check-square'></i></button></td>
                        </tr>";
                    }
                    }
        //Nav tab
        $Navtab .= '
                    </tbody>
                </table>
            </div>
            <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
            <table class="table table-bordered table-sm table-hover">
                <thead>
                    <tr align="center" class="bg-primary">
                        <th>ชื่อรายการ</th>
                        <th>จำนวน</th>
                        <th>ราคา</th>
                        <th>ตัวช่วย</th>
                    </tr>
                </thead>
                <tbody>';
                foreach ($Item as $Item_Free) {
                if ($Item_Free->item_type == "P") {
                    $Navtab .= "
                    <tr item_codetype='$Item_Free->item_code_type' item_code='$Item_Free->item_code' item_name='$Item_Free->item_name' item_price='$Item_Free->item_price' item_type='$Item_Free->item_type' item_setnumber='$Item_Free->item_setnumber' ondblclick='Item_To_Disktop(this)'>
                        <td><b>$Item_Free->item_name</b></td>
                        <td align='center'>$Item_Free->item_setnumber</td>
                        <td align='right'>".number_format((float)$Item_Free->item_price)."</td>
                        <td align='center'><button item_codetype='$Item_Free->item_code_type' item_code='$Item_Free->item_code' item_name='$Item_Free->item_name' item_price='$Item_Free->item_price' item_type='$Item_Free->item_type' item_setnumber='$Item_Free->item_setnumber' onclick='Item_To_Disktop(this)' class='btn btn-sm btn-primary'><i class='far fa-check-square'></i></button></td>
                    </tr>";
                }
                }
    //Nav tab
    $Navtab .= '
                </tbody>
            </table>
            </div>
        </div>';

        // Show Json
        $array = array('Navtab' => $Navtab);
        echo json_encode($array);
    }

    public function EditNumber(Request $request)
    {
        $Fake_table_id = $request->post('Fake_table_id');
        $Fake_Table  = DB::table('fake_table')->where('id', $Fake_table_id)->get();
        foreach ($Fake_Table as $key => $GetItemFull) {
            $TableItem = DB::table('item')->where('item_code', $GetItemFull->Fake_itemcode)->get();
        }
        $From = "
          <div class='row'>
          <div class='col-md-6' align='center'>";
        foreach ($TableItem as $key => $row) {
            $From .= "<h5><b>รายการ:</b> $row->item_name</h5>";
        }
        $From .= "</div><div class='col-md-6' align='center'>";
        foreach ($TableItem as $key => $row) {
            $From .= "<h5><b>ราคา:</b> $row->item_price ฿</h5>";
        }
        $From .= "</div></div>";
        $From .= "
          <div class='row'>
          <div class='col-md-12' align='center'>
          <div class='col-sm-3'>";
        foreach ($Fake_Table as $key => $GetItemFull) {
           $From .= "<input type='number' class='form-control' autofocus id='newnumitem' value='$GetItemFull->Fake_sum'>";
        }
        $From .= "</div>
          <hr>
          <button class='btn btn-sm btn-primary' Fake_table_id='$Fake_table_id' onclick='Foronchangenum(this);'>ยืนยันเปลี่ยนจำนวน</button>
          </div>
          </div>";
        // Show Json
        $array = array('From' => $From);
        echo json_encode($array);
    }

    public function Edit_Number_Key(Request $request)
    {
        $Fake_table_id = $request->post('Fake_table_id');
        $Fake_Table  = DB::table('fake_table')->where('id', $Fake_table_id)->get();
        foreach ($Fake_Table as $key => $GetItemFull) {
            $TableItem = DB::table('item')->where('item_code', $GetItemFull->Fake_itemcode)->get();
        }
        $From = "
          <div class='row'>
          <div class='col-md-6' align='center'>";
        foreach ($TableItem as $key => $row) {
            $From .= "<h5><b>รายการ:</b> $row->item_name</h5>";
        }
        $From .= "</div><div class='col-md-6' align='center'>";
        foreach ($TableItem as $key => $row) {
            $From .= "<h5><b>ราคา:</b> $row->item_price ฿</h5>";
        }
        $From .= "</div></div>";
        $From .= "
          <div class='row'>
          <div class='col-md-12' align='center'>
          <div class='col-sm-4'>";
        foreach ($Fake_Table as $key => $GetItemFull) {
           $From .= "<input type='text' class='form-control' autofocus id='newnumitem' placeholder='หมายเลขกุญแจ'>";
        }
        $From .= "</div>
          <hr>
          <button class='btn btn-sm btn-primary' Fake_table_id='$Fake_table_id' onclick='Foronchangenumkey(this);'>ยืนยันเปลี่ยนหมายเลขกุญแจ</button>
          </div>
          </div>";
        // Show Json
        $array = array('From' => $From);
        echo json_encode($array);
    }

    public function Edit_Other(Request $request)
    {
        $Fake_table_id = $request->post('Fake_table_id');
        $Fake_Table  = DB::table('fake_table')->where('id', $Fake_table_id)->get();
        foreach ($Fake_Table as $key => $GetItemFull) {
            $TableItem = DB::table('item')->where('item_code', $GetItemFull->Fake_itemcode)->get();
        }
        $From = "
          <div class='row'>
          <div class='col-md-6' align='center'>";
        foreach ($TableItem as $key => $row) {
            $From .= "<h5><b>รายการ:</b> $row->item_name</h5>";
        }
        $From .= "</div><div class='col-md-6' align='center'>";
        $From .= "</div></div>";
        $From .= "
          <div class='row'>
          <div class='col-md-12' align='center'>
          <div class='col-sm-4'>";
        foreach ($Fake_Table as $key => $GetItemFull) {
           $From .= "<input type='text' class='form-control' autofocus id='newnumitem' placeholder='รายละเอียด'>";
        }
        $From .= "</div>
          <hr>
          <button class='btn btn-sm btn-primary' Fake_table_id='$Fake_table_id' onclick='ForonEdit_Other(this);'>ยืนยันเปลี่ยนรายละเอียด</button>
          </div>
          </div>";
        // Show Json
        $array = array('From' => $From);
        echo json_encode($array);
    }

    public function Discount(Request $request)
    {
        $Fake_table_id = $request->post('Fake_table_id');
        $Fake_Table  = DB::table('fake_table')->where('id', $Fake_table_id)->get();
        foreach ($Fake_Table as $key => $GetItemFull) {
            $TableItem = DB::table('item')->where('item_code', $GetItemFull->Fake_itemcode)->get();
        }
        $From = "
          <div class='row'>
          <div class='col-md-6' align='center'>";
        foreach ($TableItem as $key => $row) {
            $From .= "<h5><b>รายการ:</b> $row->item_name</h5>";
        }
        $From .= "</div><div class='col-md-6' align='center'>";
        foreach ($TableItem as $key => $row) {
            $From .= "<h5><b>ราคา:</b> $row->item_price ฿</h5>";
        }
        $From .= "</div></div>";
        $From .= "
          <div class='row'>
          <div class='col-md-12' align='center'>
          <div class='col-sm-4'>";
        foreach ($Fake_Table as $key => $GetItemFull) {
           $From .= "<input type='text' class='form-control' autofocus id='newdiscount' placeholder='ส่วนลดสินค้า'>";
        }
        $From .= "</div>
          <hr>
          <button class='btn btn-sm btn-primary' Fake_table_id='$Fake_table_id' onclick='Discount_Save(this);'>ยืนยันส่วนลดสินค้า</button>
          </div>
          </div>";
        // Show Json
        $array = array('From' => $From);
        $json = json_encode($array);
        echo $json;
    }

    public function Delete_item(Request $request)
    {
        $Fake_table_id = $request->post('Fake_table_id');
        // Delete
        DB::table('fake_table')->where('id', $Fake_table_id)->delete();
    }

    public function Delete_item_time(Request $request)
    {
        $Fake_table_id = $request->post('Fake_table_id');
        // Select
        $Fake_Package = DB::table('fake_package')
                     ->select('*')
                     ->where('fake_table_id', '=', $Fake_table_id)
                     ->get();
        $Get_main_package = DB::table('main_package')
                     ->select('*')
                     ->where('id', '=', $Fake_Package[0]->main_package_id)
                     ->get();
        // Delete
        DB::table('package_detail')->where('main_package_id', $Get_main_package[0]->id)->delete();
        DB::table('main_package')->where('id', $Fake_Package[0]->main_package_id)->delete();
        DB::table('fake_table')->where('id', $Fake_table_id)->delete();
        DB::table('fake_package')->where('fake_table_id', $Fake_table_id)->delete();
    }

    public function Foronchangenum(Request $request)
    {
        $Fake_table_id = $request->post('Fake_table_id');
        $NewNum = $request->post('NewNum');
        $Fake_Table  = DB::table('fake_table')->where('id', $Fake_table_id)->get();
        foreach ($Fake_Table as $key => $GetItemFull) {
            $TableItem = DB::table('item')->where('item_code', $GetItemFull->Fake_itemcode)->get();
        }
        foreach ($TableItem as $key => $row) {
            $item_price = $row->item_price;
            $totalprice = (int)$NewNum * (int)$item_price;
            // Update
            DB::table('fake_table')
                ->where('id', $Fake_table_id)
                ->update(['Fake_sum' => $NewNum, 'Fake_price' => $totalprice]);
        }
    }

    public function Discount_Save(Request $request)
    {
        $Fake_table_id = $request->post('Fake_table_id');
        $NewNum = $request->post('NewNum');
        $Fake_Table  = DB::table('fake_table')->where('id', $Fake_table_id)->get();
        foreach ($Fake_Table as $key => $GetItemFull) {
            $TableItem = DB::table('item')->where('item_code', $GetItemFull->Fake_itemcode)->get();
        }
        foreach ($TableItem as $key => $row) {
            $item_price = $row->item_price;
            $totalprice = (int)$item_price - (int)$NewNum;
            // Update
            DB::table('fake_table')
                ->where('id', $Fake_table_id)
                ->update(['Fake_price' => $totalprice]);
        }
    }

    public function History(Request $request)
    {
        $Code = $request->post('Code');
        $Limit = $request->post('set_limit');
        $Data = DB::table('main_table')->where('Code', $Code)->where('Status', 'OUT')->limit($Limit)->orderBy('date', 'desc')->get();
        $CheckHaveHistory = DB::table('main_table')->where('Code', $Code)->count();
        if ($CheckHaveHistory > 1) {
        $Table = '
          <div id="HistoryTable">';
        foreach ($Data as $key => $row) {
        $Table .= "
          <div class='p-2 mb-2 bg-primary shadow-sm' id='heading$row->ID' data-toggle='collapse' data-target='#collapse$row->ID' aria-expanded='false' aria-controls='collapse$row->ID'>
          <div class='clearfix'>
          <div class='float-left'>
          <b>วันที่ใช้งาน:</b> $row->Guset_in | <b>สถานะ:</b> $row->Status
          </div>
          <div class='float-right'>
          <i class='fas fa-arrow-right'></i>
          </div>
          </div>
          </div>
          <div id='collapse$row->ID' class='collapse shadow' aria-labelledby='heading$row->ID' data-parent='#HistoryTable'>
          <div align='center'>
          <table class='table table-striped'>
          <tbody>";
        $Datasub = DB::table('detail_table')->where('main_id', $row->ID)->get();
        foreach ($Datasub as $key => $rowsub) {
        $Table .= "
          <tr align='left'>
          <td><b>รายการ:</b> $rowsub->itemname</td>
          <td><b>จำนวน:</b> $rowsub->sum</td>
          <td><b>ราคา:</b> ".number_format($rowsub->price)."</td>
          </tr>";
        }
        $Table .="
          </tbody>
          </table>
          </div>
          </div>";
        }
        $Table .= '</div>';
        }else{
        $Table = '<div align="center" style="color:red;"><h5><b>ไม่พบข้อมูลการใช้งาน</b></h5></div>';
        }
        // Show Json
        $array = array('Table' => $Table);
        echo json_encode($array);
    }

    public function OnUsePackage(Request $request)
    {
        date_default_timezone_set("Asia/Bangkok");
        $today = now();
        $code = $request->post('Code');
        $main_package_id = $request->post('main_package_id');
        $package_detail_id = $request->post('package_detail_id');
        $trainner_emp_id = $request->post('trainner_emp_id');
        $total = $request->post('total');
        $havesum = $request->post('havesum');
        // New Have Sum
        $newhavesum = ((int)$havesum - (int)'1');
        // Insert Package Log
        $package_log_id = DB::table('package_log')->insertGetId([
          'main_package_id' => $main_package_id,
          'package_detail' => $package_detail_id,
          'code' => $code,
          'date' => $today,
          'total_sum' => $total,
          'havesum' => $newhavesum,
          'onuse' => '1',
          'trainner_emp_id' => $trainner_emp_id,
          'status' => 'U']);
        // Insert Package OnUsePackage
        $package_onuse_id = DB::table('package_onuse')->insertGetId([
          'package_log_id' => $package_log_id,
          'main_package_id' => $main_package_id,
          'code' => $code,
          'date' => $today,
          'total_sum' => $total,
          'havesum' => $newhavesum,
          'onuse' => '1',
          'status' => 'N',
          'trainner_emp_id' => $trainner_emp_id]);
        // Update Package Detail
        DB::table('package_detail')
            ->where('package_id', $package_detail_id)
            ->update(['last_use_package_id' => $package_log_id]);
        // Update Main Package
        DB::table('main_package')
            ->where('id', $main_package_id)
            ->update(['have_sum' => $newhavesum]);
        if ($newhavesum == '0') {
        // Update Main Package
        DB::table('main_package')
            ->where('id', $main_package_id)
            ->update(['Status' => 'Expired']);
        }
    }

    public function Modal_History_Package_Useing_Display(Request $request)
    {
      $Code = $request->post('Code');
      $Package_id = $request->post('Package_id');
      $Data = DB::table('package_log')
              ->join('package_detail','package_detail.package_id','=','package_log.package_detail')
              ->where('package_log.main_package_id', $Package_id)
              ->where('code', $Code)
              ->where('status', 'U')
              ->orderBy('package_log_id', 'desc')
              ->get();
      $Count = DB::table('package_log')
              ->join('package_detail','package_detail.package_id','=','package_log.package_detail')
              ->where('package_log.main_package_id', $Package_id)
              ->where('code', $Code)
              ->where('status', 'U')
              ->orderBy('package_log_id', 'desc')
              ->count();
      if ($Count > 0) {
        $Table  = '<div class="row"><div class="col-md-12">';
        $Table .= '<table class="table-bordered table-sm table-hover" style="width: 100%;">';
        $Table .= '<tr class="bg-primary"><td>รายการ Package</td><td>วันที่เข้าใช้งาน</td><td>จำนวนครั้งที่ใช้งาน</td><td>จำนวนครั้งคงเหลือ</td></tr>';
      foreach ($Data as $key => $row) {
        $Table .= "<tr align='center'><td>$row->fake_itemname</td><td>".date('d-m-Y', strtotime($row->date))."</td><td>$row->onuse ชั่วโมง</td><td>$row->havesum ครั้ง</td></tr>";
      }
        $Table .= '</table></div></div>';
      }else{
        $Table = '<div align="center" style="color:red;"><h5><b>ไม่พบประวัติการใช้งาน</b></h5></div>';
      }
      // Show Json
      $array = array('Table' => $Table);
      echo json_encode($array);
    }

    public function PackageOnuseDisplay(Request $request)
    {
      $Code =  $request->post('Code');
      $Data = DB::table('package_onuse')
              ->join('main_package','main_package.id','=','package_onuse.main_package_id')
              ->where('package_onuse.code', $Code)
              ->where('package_onuse.status', 'N')
              ->orderBy('package_onuse.package_onuse_id', 'desc')
              ->get();
      $counData = DB::table('package_onuse')
              ->join('main_package','main_package.id','=','package_onuse.main_package_id')
              ->where('package_onuse.code', $Code)
              ->where('package_onuse.status', 'N')
              ->orderBy('package_onuse.package_onuse_id', 'desc')
              ->count();
      $CounMainOnline = DB::table('main_table')->where('Code', $Code)->where('Status', 'IN')->count();
      //  Table Data
      if ($counData > 0) {
      $Table = '
      <table class="table table-sm table-hover animated flipInX">
        <thead>
           <tr class="bg-success" align="center">
             <td colspan="6"><b>รายการใช้งาน Package</b></td>
           </tr>
           <tr class="bg-success" align="center">
             <th>ลำดับ</th>
             <th>ชื่อ Package</th>
             <th>จำนวนที่ใช้งาน</th>
             <th>ใช้งานครั้งที่</th>
             <th>ตัวช่วย</th>
           </tr>
        </thead><tbody>';
      $i = 0;
      foreach ($Data as $key => $row) {
      $i++;
      $rehavesum = $row->havesum + 1;
      if ($row->trainner_emp_id != '') {
      $trainner_name = DB::table('trainner_emp')->where('tn_emp_id',  $row->trainner_emp_id)->get();
      foreach ($trainner_name as $key => $name) {
        $Name = $name->fname .' '. $name->lname;
      }}else { $Name = '';}
      $Table .= "
           <tr align='center'>
              <td>$i</td>
              <td>$row->name_package <br>( <b>ผู้สอน: </b> $Name )</td>
              <td>$row->onuse ชั่วโมง</td>
              <td>ครั้งที่ $rehavesum</td>
           <td>";
      if ($i > 1) {
      $Table .="<button class='btn btn-sm btn-danger' disabled><i class='fas fa-trash'></i></button>";
      }else{
      $Table .="<button class='btn btn-sm btn-warning' on_use_id='$row->package_onuse_id' onclick='trianner_emp_select_modal_edit(this);'><i class='fas fa-user-tie'></i></button> ";
      $Table .="<button class='btn btn-sm btn-danger' code='$row->code' package_onuse_id='$row->package_onuse_id' package_log_id='$row->package_log_id' main_package_id='$row->main_package_id' onusesum='$rehavesum' onclick='DeleteOnusePackage(this);'><i class='fas fa-trash'></i></button>";
      }
      $Table .="</td></tr>";
      }
      $Table .= '</tbody><tfoot><tr class="bg-success" align="center"><td colspan="6">Free</td></tr></tfoot></table>';
    }else{
      $Table = "";
    }
      // Show Json
      $array = array('Table' => $Table);
      echo json_encode($array);
    }

    public function DeleteOnusePackage(Request $request)
    {
      date_default_timezone_set("Asia/Bangkok");
      $date = date("Y-m-d");
      $Code = $request->post('Code');
      $Package_onuse_id = $request->post('Package_onuse_id');
      $Package_log_id = $request->post('Package_log_id');
      $Main_package_id = $request->post('Main_package_id');
      $Onusesum = $request->post('Onusesum');
      // Select Data Package Onuse
      $DataPackageOnuse = DB::table('package_onuse')->where('package_onuse_id', $Package_onuse_id)->get();
      foreach ($DataPackageOnuse as $key => $rowonuse) {
        $ReHavesumOnuse = $rowonuse->havesum + 1;
      }
      // Select Data Package Log
      $DataPackageLog = DB::table('package_log')->where('package_log_id', $Package_log_id)->get();
      foreach ($DataPackageLog as $key => $rowlog) {
        $Package_detail  = $rowlog->package_detail;
        $Totalsum = $rowlog->total_sum;
      }
      // Delete Old Data
      DB::table('package_onuse')->where('package_onuse_id', $Package_onuse_id)->delete();
      // Update Status In Package_log
      DB::table('package_log')
          ->where('package_log_id', $Package_log_id)
          ->update(['status' => 'R']);
      // Update Have_Sum In Main_package
      DB::table('main_package')
          ->where('id', $Main_package_id)
          ->update(['have_sum' => $ReHavesumOnuse]);
      if ($ReHavesumOnuse >= '1') {
      // Update Main Package
      DB::table('main_package')
          ->where('id', $Main_package_id)
          ->update(['Status' => 'Active']);
      }
    }

    public function VoidItem(Request $request)
    {
      date_default_timezone_set("Asia/Bangkok");
      $today = now();
      $Fake_id = $request->post('Fake_id');
      $commentvoiditem = $request->post('commentvoiditem');
      $DataFake_Table = DB::table('fake_table')->where('id', $Fake_id)->get();
      foreach ($DataFake_Table as $key => $row) {
        if ($row->main_id != '') {
          // Re Price
            $Re_price = '-'.$row->Fake_price;
          // Re Num
            $Re_sum = '-'.$row->Fake_sum;
          // Insert New Void Item
          DB::table('fake_table')->insert([
        	  'Fake_code' => $row->Fake_code,
            'main_id' => $row->main_id,
       	    'Fake_datetime' => $today,
      	    'Fake_itemcode' => $row->Fake_itemcode,
            'Fake_itemcodetype' => $row->Fake_itemcodetype,
       	    'Fake_itemtype' => $row->Fake_itemtype,
       	    'Fake_itemname' => $row->Fake_itemname,
      	    'Fake_price'    => $Re_price,
      	    'Fake_sum'      => $Re_sum,
            'Fake_status'   => 'V',
            'Fake_comment'  => $commentvoiditem,
      	  ]);

        }
        // Update Fake_id Old
        DB::table('fake_table')
            ->where('id', $Fake_id)
            ->update(['Fake_status' => 'RV','Fake_comment' => $commentvoiditem]);
      }
    }

    public function VoidItem_modal(Request $request)
    {
        date_default_timezone_set("Asia/Bangkok");
        $today = now();
        $Fake_id = $request->post('Fake_id');
        $DataFake_Table = DB::table('fake_table')->where('id', $Fake_id)->get();
        foreach ($DataFake_Table as $key => $row) {
        $fake_table_id = $row->id;
        }
        $Table  = "<div class='row'><div class='col-md-12'><h4 align='left'>หมายเหตุ:</h4>";
        $Table .= "<textarea id='commentvoiditem' placeholder='กรุณากรอกสาเหตุของการ Void' class='form-control' rows='2'>";
        $Table .= "</textarea>";
        $Table .= "<br><button class='btn btn-danger' onclick='VoidItem(this);' fake_table_id='$fake_table_id'>ยืนยันการ Void</button></div></div>";
        // Show Json
        $array = array('Table' => $Table,'Row' => $row);
        $json = json_encode($array);
        echo $json;
    }

    public function ChargeItem(Request $request)
    {
        date_default_timezone_set("Asia/Bangkok");
        $today = now();
        $Fake_id = $request->post('Fake_id');
        $inputchargeitem = $request->post('inputchargeitem');
        $commentchargeitem = $request->post('commentchargeitem');
        if ($inputchargeitem != '') {
          DB::table('fake_table')
              ->where('id', $Fake_id)
              ->update(['Fake_price' => $inputchargeitem, 'Fake_status' => 'C','Fake_comment' => $commentchargeitem]);
        }
        print_r($_POST);
    }

    public function Charge_modal(Request $request)
    {
        date_default_timezone_set("Asia/Bangkok");
        $today = now();
        $Fake_id = $request->post('Fake_id');
        $DataFake_Table = DB::table('fake_table')->where('id', $Fake_id)->get();
        foreach ($DataFake_Table as $key => $row) {
        $fake_table_id = $row->id;
        $ItemData = DB::table('item')->where('item_code', $row->Fake_itemcode)->get();
        foreach ($ItemData as $key => $Item) {
          if ($Item->item_charge_default != '') {
          $Table  = "<div class='row'><div class='col-md-3'><h5 align='left'>จำนวนเงิน:</h5></div><div class='col-md-9'><input type='text' id='inputchargeitem' class='form-control form-control-sm' placeholder='จำนวนเงินที่จะ Charge' value='$Item->item_charge_default'></div>";
          }else{
          $Table  = "<div class='row'><div class='col-md-3'><h5 align='left'>จำนวนเงิน:</h5></div><div class='col-md-9'><input type='text' id='inputchargeitem' class='form-control form-control-sm' placeholder='จำนวนเงินที่จะ Charge'></div>";
          }
        }
        }
        $Table .= "<div class='col-md-12'><h5 align='left'>หมายเหตุ:</h5>";
        $Table .= "<textarea id='commentchargeitem' placeholder='กรุณากรอกสาเหตุของการ Charge' class='form-control' rows='2'>";
        $Table .= "</textarea>";
        $Table .= "<br><button class='btn btn-sm btn-danger' onclick='ChargeItem(this);' fake_table_id='$fake_table_id'>ยืนยันการ Charge</button></div></div>";
        // Show Json
        $array = array('Table' => $Table,'Row' => $row);
        echo json_encode($array);
    }

    public function Life_ring(Request $request)
    {
      date_default_timezone_set("Asia/Bangkok");
      //$Life_ring_data = DB::table('fake_table')->where('id', $Fake_id)->get();
      print_r($request->post());
      $data = "";
    }

    public function Display_select_trainner_emp_model(Request $request)
    {
      $Trainner_emp = DB::table('trainner_emp')->select('*')
                        ->where('status_emp', '<>', 'Class')
                        ->get();
      $Select = "<div align='center'><select class='custom-select' id='select_emp_to_member'>";
      foreach ($Trainner_emp as $key => $row) {
        $Select .= "<option value='".$row->tn_emp_id."'>[".$row->status_emp."] ".$row->fname."  ".$row->lname."</option>";
      }
      $Select .= "</select></div>";
      $Select .= "<hr><div align='center'><button class='btn btn-sm btn-success' fake_table_id='".$request->post('Fake_id')."' onclick='save_trainner_emp_select_modal(this);'>อัพเดต</button></div>";
      $ResArray = ['select' => $Select,'request' => $request->post('Fake_id')];
      echo json_encode($ResArray);
    }

    public function Display_select_trainner_emp_model_edit(Request $request)
    {
      $Trainner_emp = DB::table('trainner_emp')->select('*')
                        ->where('status_emp', '<>', 'Class')
                        ->get();
      $Select = "<div align='center'><select class='custom-select' id='select_emp_to_member_edit'>";
      foreach ($Trainner_emp as $key => $row) {
        $Select .= "<option value='".$row->tn_emp_id."'>[".$row->status_emp."] ".$row->fname."  ".$row->lname."</option>";
      }
      $Select .= "</select></div>";
      $Select .= "<hr><div align='center'><button class='btn btn-sm btn-success' fake_table_id='".$request->post('Fake_id')."' onclick='save_trainner_emp_select_modal_edit(this);'>อัพเดต</button></div>";
      $ResArray = ['select' => $Select,'request' => $request->post('Fake_id')];
      echo json_encode($ResArray);
    }

    public function Display_select_trainner_class_model(Request $request)
    {
      $Trainner_emp = DB::table('trainner_emp')->select('*')
                        ->where('status_emp', '=', 'Class')
                        ->get();
      $Select = "<div align='center'><select class='custom-select' id='select_class_to_member'>";
      foreach ($Trainner_emp as $key => $row) {
        $Select .= "<option value='".$row->tn_emp_id."'>[".$row->status_emp."] ".$row->fname."  ".$row->lname."</option>";
      }
      $Select .= "</select></div>";
      $Select .= "<hr><div align='center'><button class='btn btn-sm btn-success' fake_table_id='".$request->post('Fake_id')."' onclick='save_trainner_class_select_modal(this);'>อัพเดต</button></div>";
      $ResArray = ['select' => $Select,'request' => $request->post('Fake_id')];
      echo json_encode($ResArray);
    }

    public function Save_select_trainner_emp_to_member(Request $request)
    {
      // Update Package Table
      DB::table('fake_package')
        ->where('fake_table_id', $request->post('Fake_id'))
        ->update(['trainner_emp_id' => $request->post('Trainner_emp_id')]);
      // Update Fake Table
      DB::table('fake_table')
        ->where('id', $request->post('Fake_id'))
        ->update(['trainner_emp_id' => $request->post('Trainner_emp_id')]);
        echo 'OK';
    }

    public function Save_select_trainner_emp_to_member_edit(Request $request)
    {
      // Update Package_onuse
      DB::table('package_onuse')
        ->where('package_onuse_id', $request->post('Fake_id'))
        ->update(['trainner_emp_id' => $request->post('Trainner_emp_id')]);
        echo 'OK';
    }

    public function Save_select_trainner_class_to_member(Request $request)
    {
      // Update Fake Table
      DB::table('fake_table')
        ->where('id', $request->post('Fake_id'))
        ->update(['trainner_id' => $request->post('Trainner_emp_id')]);
        echo 'OK';
    }


}