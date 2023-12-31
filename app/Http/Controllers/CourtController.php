<?php

namespace App\Http\Controllers;

use \Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator,Redirect,Response;

class CourtController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $roleID = Auth::user()->role_id;
        $officeInfo = user_office_info();
        //
        $data['page_title']= 'আদালতের তালিকা'; 
        if($roleID == 1 || $roleID == 2 || $roleID == 3 || $roleID == 4 || $roleID == 22 || $roleID == 23  || $roleID == 24 || $roleID == 25 ){
        $query= DB::table('court')
                        ->leftjoin('division', 'court.division_id', '=', 'division.id')
                        ->leftjoin('district', 'court.district_id', '=', 'district.id')
                        ->leftjoin('upazila', 'court.upazila_id', '=', 'upazila.id')
                        ->select('court.*', 'division.division_name_bn', 'district.district_name_bn', 'upazila.upazila_name_bn');
        if(!empty($_GET['division'])) {
            $query->where('court.division_id','=',$_GET['division']);
        }
        if(!empty($_GET['district'])) {
            $query->where('court.district_id','=',$_GET['district']);
        }
        
        
        $data['courts'] = $query->paginate(10);

        // dd($courts);
        // Dorpdown
        $data['divisions'] = DB::table('division')->select('id', 'division_name_bn')->get();

        }elseif($roleID == 5 || $roleID == 6 || $roleID == 7 || $roleID == 8 || $roleID == 13) {
            $data['courts']= DB::table('court')
                        ->join('division', 'court.division_id', '=', 'division.id')
                        ->join('district', 'court.district_id', '=', 'district.id')
                        ->leftjoin('upazila', 'court.upazila_id', '=', 'upazila.id')
                        ->select('court.*', 'division.division_name_bn', 'district.district_name_bn', 'upazila.upazila_name_bn')
                        ->where('court.district_id', $officeInfo->district_id)
                        ->paginate(10);
        }
        return view('court.index')
        ->with('i', (request()->input('page',1) - 1) * 10)
        ->with('court_page', true)
        ->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roleID = Auth::user()->role_id;
        $officeInfo = user_office_info();
        //
        if($roleID == 1 || $roleID == 2 || $roleID == 3 || $roleID == 4 ){
            $data['division']= DB::table('division')
                            ->select('division.*')
                            ->get();
            
            $data['page_title'] = 'নতুন আদালত এন্ট্রি ফরম';
        }elseif($roleID == 5 || $roleID == 6 || $roleID == 7 || $roleID == 8 || $roleID == 13) {

            $data['district']= DB::table('district')
                            ->select('district.*')
                            ->where('id',$officeInfo->district_id)
                            ->get();
            
            $data['page_title'] = 'নতুন আদালত এন্ট্রি ফরম';

        }    

        return view('court.add')->with($data);
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $roleID = Auth::user()->role_id;
        $officeInfo = user_office_info();
        //
        if($roleID == 1 || $roleID == 2 || $roleID == 3 || $roleID == 4 ){
            $validator = $request->validate([
                'division' => 'required',
                'district' => 'required',
                'court_name' => 'required',
                'level' => 'required',
                'status' => 'required'
            ]);
            DB::table('court')->insert([
               'division_id' => $request->division, 
               'district_id' => $request->district,
               'court_name' => $request->court_name, 
               'level' => $request->level, 
               'status' => $request->status, 
            ]);
        }elseif($roleID == 5 || $roleID == 6 || $roleID == 7 || $roleID == 8 || $roleID == 13) {
            $validator = $request->validate([
                'district' => 'required',
                'court_name' => 'required',
                'status' => 'required'
            ]);
            DB::table('court')->insert([
               'division_id' => $officeInfo->division_id, 
               'district_id' => $request->district,
               'court_name' => $request->court_name, 
               'status' => $request->status, 
            ]);
        }    
        return redirect()->route('court')
            ->with('success', 'আদালত সফলভাবে সংরক্ষণ করা হয়েছে');
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $data['division']= DB::table('division')
                        ->select('division.*')
                        ->get();
        $data['districts']= DB::table('district')
                        ->select('district.*')
                        ->get();
        $data['courts']= DB::table('court')
                        ->select('court.*')
                        ->where('court.id', $id)
                        ->first();
        $data['page_title'] = 'আদালত সংশোধন ফরম';

        return view('court.edit')->with($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $validator = $request->validate([
            'division' => 'required',
            'district' => 'required',
            'court_name' => 'required',
            'level' => 'required',
            'status' => 'required'
        ]);
         $data = [ 
           'division_id' => $request->division, 
           'district_id' => $request->district,
           'court_name' => $request->court_name, 
           'level' => $request->level, 
           'status' => $request->status, 
        ];
        DB::table('court')
         ->where('id', $id)
         ->update($data);

        return redirect()->route('court')
            ->with('success', 'আদালত সফলভাবে সংশোধন করা হয়েছে');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        
        
    }

    public function getDependentDistrict($id)
    {
        $subcategories = DB::table("district")->where("division_id",$id)->pluck("district_name_bn","id");
        return json_encode($subcategories);
    }
    public function getDependentUpazila($id)
    {
        $subcategories = DB::table("upazila")->where("district_id",$id)->pluck("upazila_name_bn","id");
        return json_encode($subcategories);
    }
}


