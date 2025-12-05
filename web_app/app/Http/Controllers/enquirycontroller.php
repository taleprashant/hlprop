<?php

namespace App\Http\Controllers;

use App\propertyenquiry;
use App\homeloanenquiry;
use Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Notifications\PropertyEnquirySuccess;
use App\Notifications\PropertyAdminEnquirySuccess;
use App\User;

class enquirycontroller extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Log::info('enquirycontroller called-');
        //Log::info('Request- '.$request);
        $this->validate($request, [
            'email' => 'required|string|email|max:255',
            'contactno' => 'required|min:10|max:12',
        ]);

        $enquiry = new propertyenquiry;
        $enquiry->name = $request->input('name');
        $enquiry->contactno = $request->input('contactno');
        $enquiry->email = $request->input('email');
        $enquiry->location = $request->input('location');
        $enquiry->city = $request->input('city');
        $enquiry->pincode = $request->input('pincode');
        $enquiry->projectname = $request->input('projectname');
        $enquiry->budget = $request->input('budget');
        $enquiry->message = $request->input('message');
        $enquiry->createdat = date('Y-m-d H:i:s');
        $enquiry->updatedat = date('Y-m-d H:i:s');
        $propid = $request->input('propid');
        if($propid != 0)
        {
            $enquiry->propertyid = $propid;
        }
        
        if(Auth::guest())
        {
            $enquiry->createdby = 99999;
            $enquiry->updatedby = 99999;
        }
        else
        {
            $enquiry->createdby = Auth::user()->id;
            $enquiry->updatedby = Auth::user()->id;    
        }
        $enquiry->save();
        
        $enquiry->notify(new PropertyEnquirySuccess($enquiry->id, Auth::User()));

        $this->sms(Auth::User()->contactno, 'Dear '.Auth::User()->fullname.', your enquiry is successfully send to Homeland Properties. our agent will contact you shortly.');

        $users = User::where('roleid', '=', '2' )->get();
        
        \Notification::send($users, new PropertyAdminEnquirySuccess($enquiry->id, Auth::User()));

         foreach ($users as $user) {
            $this->sms($user->contactno, 'Dear Admin, User '.Auth::User()->fullname.' send enquiry for property on Homeland Properties.');
        }
        
        return redirect()->back()->with('success','Your enquiry posted successfully, our agent will contact you shortly.');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function contactagent(Request $request)
    {
        //Log::info('enquirycontroller called-');
        //Log::info('Request- '.$request);
        $this->validate($request, [
            'email' => 'required|string|email|max:255',
            'contactno' => 'required|min:10|max:12',
        ]);

        $enquiry = new propertyenquiry;
        $enquiry->name = $request->input('name');
        $enquiry->contactno = $request->input('contactno');
        $enquiry->email = $request->input('email');
        $enquiry->location = $request->input('location');
        $enquiry->city = $request->input('city');
        $enquiry->pincode = $request->input('pincode');
        $enquiry->projectname = $request->input('projectname');
        $enquiry->budget = $request->input('budget');
        $enquiry->message = $request->input('message');
        $enquiry->createdat = date('Y-m-d H:i:s');
        $enquiry->updatedat = date('Y-m-d H:i:s');
        $propid = $request->input('propid');
        if($propid != 0)
        {
            $enquiry->propertyid = $propid;
        }
        
        if(Auth::guest())
        {
            $enquiry->createdby = 99999;
            $enquiry->updatedby = 99999;
        }
        else
        {
            $enquiry->createdby = Auth::user()->id;
            $enquiry->updatedby = Auth::user()->id;    
        }
        $enquiry->save();

        //Log::info('Property Id- '.$propid);
        $property = DB::table('property')->where('id','=',$propid)->get();
        //Log::info('User Id-'.$property[0]->userid);
        // $agent = DB::table('user')->where('id','=',$property[0]->userid)->get();
        $agent = User::where('id','=',$property[0]->userid)->get();
        //Log::info('Agent count-'.$agent->count());
        
        $agent[0]->notify(new PropertyEnquirySuccess());
        
        $this->sms($agent[0]->contactno, 'Dear '.$agent[0]->fullname.', User '.$enquiry->name.' insterted in your property on Homeland Properties');

        (new propertyenquiry)->forceFill([
            'email' => $enquiry->email
        ])->notify(new PropertyEnquirySuccess());

        $otp = rand(100000, 999999);
        session(['otp' => $otp]);

        $this->sms($enquiry->contactno, 'Dear '.$enquiry->name.', Your enquiry is send successfully on Homeland Properties. Please verify user Mobile number. Your OTP is : '. $otp);


        return response([
            'name' => $agent[0]->fullname,
            'phone' => $agent[0]->contactno,
            'email' => $agent[0]->email
        ]);
    }

    public function showall()
    {
        if(!Auth::guest() && Auth::user()->roleid == 2)
        {
            $enquiries = DB::table('propertyenquiry')->orderBy('createdat','desc')->get();
            return view('enquiry.showall',['enquiries' => $enquiries]);
        }
        else
        {
            return view('errors.auth');
        }
    }

    public function storehomeloanenquiry(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'contactno' => 'required|min:10|max:12',
            'email' => 'required|string|email|max:255',

        ]);

        $enquiry = new homeloanenquiry;
        $enquiry->name = $request->input('name');
        $enquiry->contactno = $request->input('contactno');
        $enquiry->email = $request->input('email');
        $enquiry->dob = $request->input('dob');
        $enquiry->occupation = $request->input('occupation');
        $enquiry->city = $request->input('city');
        $enquiry->purpose = $request->input('purpose');

        if($request->input('propertyfinalized')=='Yes')
        {
            $enquiry->propertyfinalized =1;
        }
        else
        {
            $enquiry->propertyfinalized = 0;
        }
        $enquiry->monthlyincome = $request->input('monthlyincome');
        $enquiry->loanamount = $request->input('loanamount');
        $enquiry->bankname = $request->input('bankname');
        $enquiry->message = $request->input('message');

        $enquiry->createdat = date('Y-m-d H:i:s');
        $enquiry->updatedat = date('Y-m-d H:i:s');
        
        if(Auth::guest())
        {
            $enquiry->createdby = 99999;
            $enquiry->updatedby = 99999;
        }
        else
        {
            $enquiry->createdby = Auth::user()->id;
            $enquiry->updatedby = Auth::user()->id;    
        }
        $enquiry->save();

        return redirect('/homeloan')->with('success','Your enquiry posted successfully, our agent will contact you shortly.');
    }

    public function showallhomeloanequiries()
    {
        if(!Auth::guest() && Auth::user()->roleid == 2)
        {
            $enquiries = DB::table('homeloanenquiry')->orderBy('createdat','desc')->get();
            return view('enquiry.hlshowall',['enquiries' => $enquiries]);
        }
        else
        {
            return view('errors.auth');
        }
    }

    public function create()
    {
        if(!Auth::guest() && (Auth::user()->roleid == 2 || Auth::user()->roleid == 1))
        {
            return view('enquiry.create');
        }
        else if(!Auth::guest())
        {
            return view('errors.auth');
        }
        else
        {
            return view('forceregister');
        }
    }

    public function verifyagentotp(Request $request)
    {
        try 
        {
            $otp = session()->get('otp');

            if($request->otp == $otp) {
                return response(['success' => 'Matched' ]);
            }
            else {
                return response(['error' => 'Not Matched' ]);
            }
         } catch (Exception $e) {
            logger()->error($exception);
            return view('errors.default',  ['error' => $e->getMessage()]);
         }
         return redirect()->back();
    }
}
