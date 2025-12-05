<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Auth;
use App\property;
use App\propertyimage;
use App\Notifications\PropertyUploadSuccess;
use App\Notifications\PropertyAdminUploadSuccess;
use App\User;

class propcontroller extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $props = DB::table('property')->where('adminapproved','=','1')->orderBy('featurerank','asc')->take(6)->get();
        foreach($props as $prop)
        {
            $images = DB::table('propertyimage')->where('propertyid','=', $prop->id)->get();
            if($images->count() > 0)
            {
                $prop->image = $images[0]->file;
            }
            else
            {
                $prop->image ="propimages/no-image.jpg";
            }

            $agent = DB::table('user')->where('id','=',$prop->userid)->get();
            if($agent->count() > 0)
            {
                $prop->agent = $agent[0]->fullname;
                if($agent[0]->roleid == 2)
                {
                    $prop->userrole = "Agent";
                }
                else
                {
                    $userrole = DB::table('userrole')->where('id','=',$agent[0]->roleid)->get();
                    $prop->userrole = $userrole[0]->role;
                }
            }
        }
        $cities = DB::table('city')->get();
        return view('index',['props' => $props,
        'cities' => $cities]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(!Auth::guest())
        {
            $cities = DB::table('city')->get();
            $proptype = DB::table('propertytype')->get();
            $propsubtype = DB::table('propertysubtype')->where('id','<','13')->get();
            return view('prop.upload', ['cities' => $cities,
                                        'proptypes' => $proptype,
                                        'propsubtypes' => $propsubtype]);
        }
        // else if(!Auth::guest())
        // {
        //     return view('errors.auth');
        // }
        else
        {
            return view('forceregister');
        }
    }


    public function getpropsubtype(Request $request)
    {
        if($request->ajax())
        {
            //Log::info($request->proptypeid);
            $propsubtype = DB::table('propertysubtype')->where('propertytypeid', '=', $request->proptypeid)->get();
            //Log::info($propsubtype);
            return response()->json(array('propsubtypes'=>$propsubtype));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd('Inside store method');
        //Log::info($request);
        // dd($request->all());
        $this->validate($request, [
            'area' => 'required|string',
            'city' => 'required|string',
            'propertytype' => 'required',
            'propertysubtype' => 'required',
        ]);
        //Log::info($request->input('city'));

        try{

            $citystatecountry = DB::table('city')->where('id','=',$request->input('city'))->get();
            //Log::info($citystatecountry);

            $property = new Property;
            $property->userid = Auth::User()->id;
            $property->title = $request->input('title');
            $property->propertytypeid = $request->input('propertytype');
            $property->propertysubtypeid = $request->input('propertysubtype');
            $property->transactiontype = $request->input('transactiontype');
            $property->bedrooms =  $request->input('bedrooms');
            $property->washrooms = $request->input('washrooms');
            $property->balconies = $request->input('balconies');
            $property->furniturestatus =  $request->input('FurnitureStatus');
            $property->availablefor =  $request->input('availablefor');
            $property->area = $request->input('area');
            $property->areaunit =  $request->input('areaunit');
            $property->totalfloor = $request->input('totalfloors');
            $property->floornumber = $request->input('floorno');
            $property->constructionstatus = $request->input('constructionstatus');
            $property->constructionyear = $request->input('constructionyear');
            $property->possessionyear = $request->input('possessionyear');
            $property->possessionmonth = $request->input('possessionmonth');
            $property->societyname = $request->input('projectname');
            $property->address = $request->input('address');
            $property->city = $citystatecountry[0]->city;
            $property->state = $citystatecountry[0]->state;
            $property->country = $citystatecountry[0]->country;
            $property->pincode = $request->input('pincode');
            $property->description = $request->input('description');
            $property->flatfacing = $request->input('flatfacing');
            //Log::info($request->input('reracertified'));
            if($request->input('reracertified')=="on")
            {
                $property->reracertified = 1;
            }
            $property->reraregistrationno = $request->input('reraregno');
            $property->adminapproved = 0;
            $property->active = 1;
            $property->createdat =   date('Y-m-d H:i:s');
            $property->createdby = Auth::User()->id;
            $property->updatedat = date('Y-m-d H:i:s');
            $property->updatedby =  Auth::User()->id;
            
            if($request->input('rentamount') == "")
            {
                $ra = 0;
            }
            else
            {
                $ra = $request->input('rentamount');
            }
            $property->rentamount = $ra;

            if($request->input('depositamount') == "")
            {
                $da = 0;
            }
            else
            {
                $da = $request->input('depositamount');
            }
            $property->depositamount = $da;
            $property->maintenance = $request->input('maintenance');

            if($request->input('sellingamount') == "")
            {
                $sa = 0;
            }
            else
            {
                $sa = $request->input('sellingamount');
            }
            $property->sellingamount = $sa;

            //if($request->input('propertytype') == 1)
            //{
               //Log::info('has lift-' . $request->has('lift'));
               $amenities = $request->has('lift') ? "Lift":"";
               if($request->has('24x7water')){
                    $amenities .= ',';
                    $amenities .= $request->has('24x7water') ? "24x7 Water":"";
               }

               if($request->has('powerbackup')){
                    $amenities .= ',';
                    $amenities .= $request->has('powerbackup') ? "Power Backup":"";
               }
               if($request->has('solarwaterheater')){
                    $amenities .= ',';
                    $amenities .= $request->has('solarwaterheater') ? "Solar Water Heater":"";
               }
               if($request->has('garden')){
                    $amenities .= ',';
                    $amenities .= $request->has('garden') ? "Garden/Children Play Area":"";
                }
                if($request->has('clubhouse')){
                    $amenities .= ',';
                    $amenities .= $request->has('clubhouse') ? "Club House":"";
                }
                if($request->has('gym')){
                    $amenities .= ','; 
                    $amenities .= $request->has('gym') ? "Gym":"";
                }
                if($request->has('swimmingpool')){
                    $amenities .= ',';
                    $amenities .= $request->has('swimmingpool') ? "Swimming Pool":"";
                }
               //Log::info('amenities-'.$amenities);
               $property->amenities =  $amenities;
            //}

            $property->save();
            //::info('Property saved successfully');

            //Generating display id
            $propid = $property->id;
            $trantype = 'HLS';
            if($request->input('transactiontype') =='Rent')
            {
                $trantype = 'HLR';
            }
            $displayid = $trantype . $property->id;
            $property->displayid = $displayid;
            $property->save();
            // dd($property);
            //Log::info('Property saved successfully');

            if($request->hasFile('propimages'))
            {
                //foreach($request->file('propimage') as $file)
                //{
                    //get file name with ext
                    //$fileNameWithExt = $request->file('propimage')->getClientOriginalName();

                    //Get just file name
                    //$fileName = pathinfo($fileNameWithExt, PATHINFO_FILENAME);

                    //Get just ext
                    //$extension = $request->file('propimage')->getClientOriginalExtension();

                    //$fileNameToStore = $fileName.'_'.time().'.'.$extension;
                    
                    //upload image
                    //$path = $request->file('propimage')->storeAs('public/propimages', $fileNameToStore);
                //}
                foreach($request->file('propimages') as $file)
                {
                    //get file name with ext
                    $fileNameWithExt = $file->getClientOriginalName();

                    //Get just file name
                    $fileName = pathinfo($fileNameWithExt, PATHINFO_FILENAME);

                    //Get just ext
                    $extension = $file->getClientOriginalExtension();

                    $fileNameToStore = $fileName.'_'.time().'.'.$extension;
                    
                    //upload image
                    $path = $file->storeAs('public/propimages', $fileNameToStore);

                    $propimage = new propertyimage;
                    $propimage->propertyid = $property->id;
                    $propimage->file = "propimages/" . $fileNameToStore;
                    $propimage->createdat = date('Y-m-d H:i:s');
                    $propimage->createdby = Auth::User()->id;
                    $propimage->updatedat = date('Y-m-d H:i:s');
                    $propimage->updatedby = Auth::User()->id;

                    $propimage->save();
                }
            }
            else
            {
                $fileNameToStore = 'no-image.jpg';
                $propimage = new propertyimage;
                $propimage->propertyid = $property->id;
                $propimage->file = "propimages/" . $fileNameToStore;
                $propimage->createdat = date('Y-m-d H:i:s');
                $propimage->createdby = Auth::User()->id;
                $propimage->updatedat = date('Y-m-d H:i:s');
                $propimage->updatedby = Auth::User()->id;

                $propimage->save();
            }

            $message = 'Property with Property ID - '. $property->displayid .' uploaded successfully. It will be posted once the review is done.';
            

            $property->notify(new PropertyUploadSuccess($property->id, Auth::User()));

            $this->sms(Auth::User()->contactno, 'Dear '.Auth::User()->fullname.', your property '.$property->title.' is uploaded successfully on Homeland Properties. It will be posted on Homeland Properties after successfull review.');
            
            $users = User::where('roleid', '=', '2' )->get();

            \Notification::send($users, new PropertyAdminUploadSuccess($property->id, Auth::User()));

            foreach ($users as $user) {
                $this->sms($user->contactno, 'Dear Admin, User uploaded property '.$property->displayid.' on Homeland Properties. Please Review and post it on Homeland Properties.');
            }

            return redirect()->back()->with('success',$message);
        }
        catch(Exception $e) {
            echo $e->getMessage();
         }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            
            $vars = explode("-",$id);
            if(count($vars)>1)
            {
                $prop = DB::table('property')->where('id','=',$vars[1])->get();

                //Log::info('amenities-'.$prop[0]->amenities);
                if($prop[0]->amenities != "")
                {
                    $prop[0]->arrayamenities = explode(',', $prop[0]->amenities);
                    //Log::info($prop[0]->arrayamenities); 
                }
                else
                {
                    $prop[0]->arrayamenities = NULL;
                }
                
                if($prop->count()>0)
                {   
                    $agent = DB::table('user')->where('id','=',$prop[0]->createdby)->get();
                    
                    if($agent[0]->roleid ==2)
                    {
                        $agent[0]->role = "Agent";
                    }else{
                        $role = DB::table('userrole')->where('id','=',$agent[0]->roleid)->get();
                        $agent[0]->role = $role[0]->role;
                    }
                    
                    if($agent[0]->otherfranchise != '')
                    {
                        $agent[0]->franchise = $agent[0]->otherfranchise;
                    }
                    else
                    {
                        $franchise = DB::table('franchise')->where('id','=',$agent[0]->franchiseid)->get();
                        $agent[0]->franchise = $franchise[0]->franchisename;
                    }
                    
                    //Hdding Agent phone no and email
                    if(!Auth::Guest())
                    {
                        $propEnquiry = DB::table('propertyenquiry')
                                        ->where('propertyid','=',$vars[1])
                                        ->where('createdby','=',Auth::User()->id)
                                        ->get();
                        //Log::info($propEnquiry->count()); 
                        if($propEnquiry->count()==0)
                        {
                            $agent[0]->contactno = substr_replace($agent[0]->contactno,'XXXX',strlen($agent[0]->contactno)-4, 4);
                            $emailparts = explode('@',$agent[0]->email);
                            $agent[0]->email = substr_replace($emailparts[0],'XXXX',strlen($emailparts[0])-4, 4) . $emailparts[1];
                        }
                    }
                    else
                    {
                        $agent[0]->contactno = substr_replace($agent[0]->contactno,'XXXX',strlen($agent[0]->contactno)-4, 4);
                        $emailparts = explode('@',$agent[0]->email);
                        $agent[0]->email = substr_replace($emailparts[0],'XXXX',strlen($emailparts[0])-4, 4) . $emailparts[1];
                    }

                    $images = DB::table('propertyimage')->where('propertyid','=',$prop[0]->id)->get();
                    // dd($images);
                    
                    return view('prop.show_new',['prop' => $prop,
                    'agent' => $agent,
                    'images' => $images]);
                }
                else
                {
                    return redirect()->back(); 
                }
            }
            else{
                return redirect("/");
            }
        }
        catch(\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showall()
    {
        $props = DB::table('property')->where('adminapproved','=','1')->get();
        foreach($props as $prop)
        {
            $images = DB::table('propertyimage')->where('propertyid','=', $prop->id)->get();
            if($images->count() > 0)
            {
                $prop->image = $images[0]->file;
            }
            else
            {
                $prop->image ="propimages/no-image.jpg";
            }

            $agent = DB::table('user')->where('id','=',$prop->userid)->get();
            if($agent->count() > 0)
            {
                $prop->agent = $agent[0]->fullname;
                if($agent[0]->roleid == 2)
                {
                    $prop->userrole = "Agent";
                }
                else
                {
                    $userrole = DB::table('userrole')->where('id','=',$agent[0]->roleid)->get();
                    $prop->userrole = $userrole[0]->role;
                }
            }
        }
        $cities = DB::table('city')->get();

        return view('prop.showall',['props' => $props,
        'cities' => $cities]);
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
        // dd($request->all());
        //Log::info($request);
        $this->validate($request, [
            'area' => 'required|string',
            'city' => 'required|string',
            'propertytype' => 'required',
            'propertysubtype' => 'required',
        ]);
        $citystatecountry = DB::table('city')->where('id','=',$request->input('city'))->get();

        $property = Property::find($id);
        $property->userid = Auth::User()->id;
        $property->title = $request->input('title');
        $property->propertytypeid = $request->input('propertytype');
        $property->propertysubtypeid = $request->input('propertysubtype');
        $property->transactiontype = $request->input('transactiontype');
        $property->bedrooms =  $request->input('bedrooms');
        $property->washrooms = $request->input('washrooms');
        $property->balconies = $request->input('balconies');
        $property->furniturestatus =  $request->input('FurnitureStatus');
        $property->availablefor =  $request->input('availablefor');
        $property->area = $request->input('area');
        $property->areaunit =  $request->input('areaunit');
        $property->totalfloor = $request->input('totalfloors');
        $property->floornumber = $request->input('floorno');
        $property->constructionstatus = $request->input('constructionstatus');
        $property->constructionyear = $request->input('constructionyear');
        $property->possessionyear = $request->input('possessionyear');
        $property->possessionmonth = $request->input('possessionmonth');
        $property->societyname = $request->input('projectname');
        $property->address = $request->input('address');
        $property->city = $citystatecountry[0]->city;
        $property->state = $citystatecountry[0]->state;
        $property->country = $citystatecountry[0]->country;
        $property->pincode = $request->input('pincode');
        $property->description = $request->input('description');
        $property->flatfacing = $request->input('flatfacing');

        if($request->input('reracertified')=="on")
        {
            $property->reracertified = 1;
        }
        $property->reraregistrationno = $request->input('reraregno');
        $property->active = 1;
        $property->updatedat = date('Y-m-d H:i:s');
        $property->updatedby =  Auth::User()->id;
        
        if($request->input('rentamount') == "")
        {
            $ra = 0;
        }
        else
        {
            $ra = $request->input('rentamount');
        }
        $property->rentamount = $ra;

        if($request->input('depositamount') == "")
        {
            $da = 0;
        }
        else
        {
            $da = $request->input('depositamount');
        }
        $property->depositamount = $da;
        $property->maintenance = $request->input('maintenance');

        if($request->input('sellingamount') == "")
        {
            $sa = 0;
        }
        else
        {
            $sa = $request->input('sellingamount');
        }
        $property->sellingamount = $sa;

        
           $amenities = $request->has('lift') ? "Lift":"";
           if($request->has('24x7water')){
                $amenities .= ',';
                $amenities .= $request->has('24x7water') ? "24x7 Water":"";
           }

           if($request->has('powerbackup')){
                $amenities .= ',';
                $amenities .= $request->has('powerbackup') ? "Power Backup":"";
           }
           if($request->has('solarwaterheater')){
                $amenities .= ',';
                $amenities .= $request->has('solarwaterheater') ? "Solar Water Heater":"";
           }
           if($request->has('garden')){
                $amenities .= ',';
                $amenities .= $request->has('garden') ? "Garden/Children Play Area":"";
            }
            if($request->has('clubhouse')){
                $amenities .= ',';
                $amenities .= $request->has('clubhouse') ? "Club House":"";
            }
            if($request->has('gym')){
                $amenities .= ','; 
                $amenities .= $request->has('gym') ? "Gym":"";
            }
            if($request->has('swimmingpool')){
                $amenities .= ',';
                $amenities .= $request->has('swimmingpool') ? "Swimming Pool":"";
            }
           //Log::info('amenities-'.$amenities);
           $property->amenities =  $amenities;
        //}

        $property->save();

        //Generating display id
        $propid = $property->id;
        $trantype = 'HLS';
        if($request->input('transactiontype') =='Rent')
        {
            $trantype = 'HLR';
        }
        $displayid = $trantype . $property->id;
        $property->displayid = $displayid;
        $property->save();

        if($request->hasFile('propimages'))
        {
            
            foreach($request->file('propimages') as $file)
            {
                //get file name with ext
                $fileNameWithExt = $file->getClientOriginalName();

                //Get just file name
                $fileName = pathinfo($fileNameWithExt, PATHINFO_FILENAME);

                //Get just ext
                $extension = $file->getClientOriginalExtension();

                $fileNameToStore = $fileName.'_'.time().'.'.$extension;
                
                //upload image
                $path = $file->storeAs('public/propimages', $fileNameToStore);

                $propimage = new propertyimage;
                $propimage->propertyid = $property->id;
                $propimage->file = "propimages/" . $fileNameToStore;
                $propimage->createdat = date('Y-m-d H:i:s');
                $propimage->createdby = Auth::User()->id;
                $propimage->updatedat = date('Y-m-d H:i:s');
                $propimage->updatedby = Auth::User()->id;

                $propimage->save();
            }
        }
        $imagecount = propertyimage::where('propertyid', $property->id)->count();

        if($imagecount <= 0) {
            $fileNameToStore = 'no-image.jpg';
            $propimage = new propertyimage;
            $propimage->propertyid = $property->id;
            $propimage->file = "propimages/" . $fileNameToStore;
            $propimage->createdat = date('Y-m-d H:i:s');
            $propimage->createdby = Auth::User()->id;
            $propimage->updatedat = date('Y-m-d H:i:s');
            $propimage->updatedby = Auth::User()->id;

            $propimage->save();
        }

        $message = 'Property with Property ID - '. $property->displayid .' Updated successfully. It will be posted once the review is done.';
        

        $property->notify(new PropertyUploadSuccess($property->id, Auth::User()));

        $this->sms(Auth::User()->contactno, 'Dear '.Auth::User()->fullname.', your property '.$property->title.' is Updated successfully on Homeland Properties.');
        
        $users = User::where('roleid', '=', '2' )->get();

        \Notification::send($users, new PropertyAdminUploadSuccess($property->id, Auth::User()));

        foreach ($users as $user) {
            $this->sms($user->contactno, 'Dear Admin, User updated property '.$property->displayid.' on Homeland Properties. Please review the updates.');
        }

        return redirect()->back()->with('success',$message);
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

    public function showalltoadmin()
    {
        if(!Auth::guest() && Auth::user()->roleid == 2)
        {
            $props = DB::table('property')->get();
            foreach($props as $prop)
            {
                $proptype = DB::table('propertytype')->where('id', $prop->propertytypeid)->get();
                $prop->proptype = $proptype[0]->propertytype;// . " / " . $proptype[0]->propertysubtype;
            }

            return view('prop.approveprop',['props' => $props]);
        }
        else
        {
            return view('errors.auth');
        }
    }

    public function approve($id)
    {
        DB::table('property')->where('id', $id)->update(['adminapproved' => 1]);
        //return 'Hello';
        return redirect()->back();
    }

    public function reject(Request $request, $id)
    {
        DB::table('property')->where('id', $id)->update(['adminapproved' => 0]);

        return redirect()->back();
    }

    public function search(Request $request)
    {
        $keyword = $request->input('keywords');
        session(['keywords' => $keyword]);

        $filter = "adminapproved = 1";
        if($keyword) {
            $filter .= " and societyname = '". $keyword . "'or address = '".$keyword."'";
        }

        $transtype = $request->input('transactiontype');
        session(['transtype' => $transtype]);
        if($transtype != "")
        {
            $filter =  $filter ." and transactiontype='" . $transtype . "'";
        }

        $city = $request->input('city');
        session(['city' => $city]);
        if($city != "" && $city != "All Cities")
        {
            $filter = $filter . " and city='". $city ."'";
        }

        $proptype = $request->input('catagory');
        session(['proptype' => $proptype]);
        if($proptype != 0)
        {
            $filter =  $filter ." and propertytypeid='" . $proptype . "'";
        }
        
        
        
        $prosubtype = $request->input('prosubtype');
        session(['prosubtype' => $prosubtype]);
        if($prosubtype != 0)
        {
            $filter =  $filter ." and propertysubtypeid='" . $prosubtype . "'";
        }
        
        $furniturestatus = $request->input('furniturestatus');
        session(['furniturestatus' => $furniturestatus]);
        if($furniturestatus != "")
        {
            $filter =  $filter ." and furniturestatus='" . $furniturestatus . "'";
        }

        $bedrooms = $request->input('bedrooms');
        session(['bedrooms' => $bedrooms]);
        if($bedrooms != 0)
        {
            $filter =  $filter ." and bedrooms='" . $bedrooms . "'";
        }

        $propage = $request->input('propage');
        //Log::info($propage);
        session(['propage' => $propage]);
        if($propage != 0 && $propage == "5+")
        {
            $constructionyear = date('Y')-5;
            $filter =  $filter ." and constructionyear <=" . $constructionyear;
        }
        else if($propage != 0 && $propage != 'Year Old')
        {
            $constructionyear = date('Y')-$propage;
            $filter =  $filter ." and constructionyear=" . $constructionyear;
        }
        
        
        if($request->minarea) {
            $filter .= ' and area >=' .$request->minarea;
        }
        

        if($request->maxarea) {
            $filter .= ' and area <=' .$request->maxarea;
        }
        
        $amenities = $request->input('amenities');
        session(['amenities' => $amenities]);
        if($amenities != 0)
        {
            $filter =  $filter ." and amenities LIKE '%" . $amenities . "%'";
        }
        
        // dd($filter);
        //Log::info($filter);
        $props = DB::table('property')->whereRaw($filter)->get();

        foreach($props as $prop)
        {
            $images = DB::table('propertyimage')->where('propertyid','=', $prop->id)->get();
            if($images->count() > 0)
            {
                $prop->image = $images[0]->file;
            }
            else
            {
                $prop->image ="propimages/no-image.jpg";
            }

            $agent = DB::table('user')->where('id','=',$prop->userid)->get();
            if($agent->count() > 0)
            {
                $prop->agent = $agent[0]->fullname;
            }
        }
        $cities = DB::table('city')->get();
        // dd($props);

        return view('prop.showall',['props' => $props,
        'cities' => $cities, 'keyword' => $keyword, 'searchtext' => $request]);

        // return redirect('prop.showall',['props' => $props,
        //                                 'cities' => $cities])->back()->withInput();
    }

    public function myProperties()
    {
        $props = DB::table('property')->where('createdby','=',Auth::user()->id)->get();
        foreach($props as $prop)
        {
            $proptype = DB::table('propertytype')->where('id', $prop->propertytypeid)->get();
            $prop->proptype = $proptype[0]->propertytype;// . " / " . $proptype[0]->propertysubtype;
        }
        return view('prop.myprop',['props' => $props]);
    }

    public function autoComplete(Request $request) {
        $query = $request->get('term','');
        
        $properties = DB::table('property')->where('societyname','LIKE','%'.$query.'%')->get();
        
        $data=array();
        foreach ($properties as $property) {
            $data[]=array('value'=>$property->societyname,'id'=>$property->id);
        }

        $address = DB::table('property')->where('address','LIKE','%'.$query.'%')->get();

        foreach ($address as $add) {
            $data[]=array('value'=>$add->address,'id'=>$add->id);            
        }

        if(count($data))
             return $data;
        else
            return ['value'=>'No Result Found','id'=>''];
    }

    public function editprop($id)
    {   

        $cities = DB::table('city')->get();
        $proptype = DB::table('propertytype')->get();
        $property = property::find($id);
        $amenities = explode(",",$property->amenities);
        $propsubtype = DB::table('propertysubtype')->where('id','<','13')->get();
        $images = propertyimage::where('propertyid', $id)->get();
        return view('prop.editprop', ['cities' => $cities,
                                        'proptypes' => $proptype,
                                        'propsubtypes' => $propsubtype,
                                        'property' => $property,
                                        'amenities'=> $amenities,
                                        'images' => $images
                                    ]);
    }

    public function deleteimage(Request $request)
    {
        //dd($image);
        $image = propertyimage::find($request->id);
        if($image) {
            // unlink(storage_path('/app/public/'.$image->file));
            $image->delete();
        }

        return 'success';
    }
}
