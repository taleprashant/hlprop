<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\User;
use Auth;

class usercontroller extends Controller
{
    public function showall()
    {
        if(!Auth::guest() && Auth::user()->roleid == 2)
        {
            $users = DB::table('user')->get();
            foreach($users as $user)
            {
                $userrole = DB::table('userrole')->where('id','=',$user->roleid)->get();
                $user->role = $userrole[0]->role;
            }

            return view('user.showall',['users' => $users]);
        }
        else
        {
            return view('errors.auth');
        }
    }

    public function create()
    {
        return view('admin/home');
    }

    public function show(Request $request)
    {
        // dd(Auth::user()->role->role);
        if(Auth::user()) {
            return view('user.myprofile', ['user' => Auth::user() ]);
        }
    }

    public function editprofile(Request $request, $id)
    {
        $user = User::where('id', $id)->update([
            'fullname' => $request->fullname,
            'email' => $request->email,
            'contactno' => $request->contactno,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'dob' => $request->dob > date('1970-01-02') ? date('Y-m-d H:i:s',strtotime($request->dob)) : NULL
        ]);

        return redirect()->back();
    }
}
