<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\property;
use App\propertyenquiry;
use App\User;
use App\homeloanenquiry;
use App\Userrole;
use App\Franchise;

class admincontroller extends Controller
{
    public function dashboard()
    {
    	$properties = property::count();

    	$enquiries = propertyenquiry::count();

    	$loanEnquiries = homeloanenquiry::count();

    	$users = User::count();

        return view('admin.dashboard', ['propCount' => $properties, 'enqCount' =>$enquiries, 
    	'loanCount' => $loanEnquiries, 'userCount' => $users]);
    }

    public function showProps() {
    	$properties = property::all();
    	
    	foreach($properties as $property)
        {
	    	$agent = User::where('id','=',$property->userid)->get();
            if($agent)
            {
                $property->fullname = $agent[0]->fullname;
	        }
	    }

    	return view('admin.property', ['properties' => $properties]);
    }

    public function showEnquiry() {
    	$enquiries = propertyenquiry::all();

    	return view('admin.enquiry', ['enquiries' => $enquiries]);
    }

    public function showHomeLoanEnquiry()
    {
    	$loadEnquiries = homeloanenquiry::all();

    	return view('admin.homeloanenq', ['loanEnquiries' => $loadEnquiries]);
    }


    public function showUsers()
    {
    	$users = User::all();

    	foreach ($users as $user) {
    		$role = Userrole::where('id', '=', $user->roleid)->get();

    		$user->role = $role[0]->role;
    	}

    	return view('admin.users', ['users' => $users]);
    } 

    public function showFranchise()
    {
        $franchises = Franchise::get();
        // dd($franchises);

      /*  foreach ($franchise as $franchise) {
            $role = Franchiserole::where('id', '=', $franchise->id)->get();

            $franchise->role = $franchise[0]->role;
        }*/
       // $demos = Demo::get();
        //return view('demo',compact('demos'));
        return view('admin.franchises', compact('franchises'));
         //return view('demo',compact('demos'));
    }
     public function add1()
    {
        //$franchises = \DB::table('franchise')->get();
        return view('admin.user.addfranchise');
    }

    public function addUsers()
    {
        $franchises = \DB::table('franchise')->get();
        return view('admin.user.adduser', ['franchises' => $franchises]);
    }
    public function addFranchise(Request $request)
    {
         $franchise = new Franchise;
        $franchise->fill($request->all());
        $franchise->address = $request->address;
        $franchise->save();

        //return redirect()->back();
        //$franchises = \DB::table('franchise')->get();
        return view('admin.user.addfranchise');
    }


    public function edituser($id)
    {
        $user = User::where('id', $id)->first();
        $franchises = \DB::table('franchise')->get();

        return view('admin.user.edituser', ['user'=> $user, 'franchises' => $franchises]);
    }

    public function editfranchises($id)
    {
        $franchise = Franchise::where('id', $id)->first();
      //  $franchises = \DB::table('franchise')->get();


        return view('admin.user.editfranchises', ['franchises'=> $franchise]);
        
      /*   $franchise = Franchise::find($request->id);
        $franchise->fill($request->all());
        $franchise->save();*/
    }

    public function upadteuser(Request $request, $id)
    {
        $array = [
            'fullname' => $request->name,
            
            'franchiseid' => $request->franchise,
            'otherfranchise' => $request->otherfranchise,
        ];

        if($request->userrole) {
            $array['roleid'] = $request->userrole;
        }
        $user = User::where('id', $id)
                ->update($array);

        return redirect()->back();

        
    }

    public function upadtefranchises(Request $request)
    {
        $franchise = Franchise::find($request->id);
        $franchise->fill($request->all());
        $franchise->save();

      return redirect()->back();
      
    }


    public function deleteuser($id)
    {
        $user = User::find($id);
        if($user) {
            foreach ($user->propetries as $propertie) {
               
                $propertie->delete();
            }
            $user->delete();
        }

        return redirect()->back();
        
    }

     public function deletefranchises($id)
    {
        $franchises = Franchise::find($id);
        $franchises->delete();

        return redirect()->back();
        
    }
    
    public function changeStatusUsers($status,$id)
    {
        $user = User::find($id);

        $user->active = $status;
        $user->save();
        return redirect()->back();
        $franchise=Franchise::find($id);
        $franchise->active=$status;
        $franchise->save();


        return redirect()->back();
        
    }
    
    /*public function addFranchise()
    {
        //$franchises = \DB::table('franchise')->get();
        return view('admin.user.addfranchise');
    }*/
}
