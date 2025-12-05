<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\customer;
use App\proimage;
use App\property;
use App\franchise;
use App\protype;
use App\prosubtype;
use App\proenquiry;
use App\city;
use App\Enquiry;
use App\Userrole;
use App\User;
use Illuminate\Support\Facades\Hash;
use Auth;
use App\propertyenquiry;
use App\homeloanenquiry;
use App\Notifications\PropertyEnquirySuccess;
use App\Notifications\PropertyAdminEnquirySuccess;

class apiController extends Controller
{
    //
    public function login(Request $request)
    { 
     
      if (Auth::attempt ( array (
          
          'email' => $request->get ( 'email' ),
          'password' => $request->get ( 'password' ) 
      ) )) {
        session ( [ 
            
            'name' => $request->get ( 'username' ) 
        ] );
        return Auth::user();
      } else {
        Session::flash ( 'message', "Invalid Credentials , Please try again." );
        return redirect()->back ();
      }
    
       //echo $request;
        //dd(Hash::make($request->password));
        if($user = User::where('email',$request->email)->first())
        {
          //dd($user->getAuthPassword());
          if($user->getAuthPassword() == \bcrypt($request->password))
            return $user;
          else
           echo 'fail';

        }
        else
        {
          echo 'fail1';
        }
        
    }

    public function register(Request $request)
    {
          if(customer::where('email',$request->email)->first())
           {
               echo "present";
           }
           else
           {
         try 
           
            {
               $customer = new customer;
                 $customer->roleid = $request->roleid;
                 $customer->franchiseid = $request->franchiseid;
                 $customer->otherfranchise = $request->otherfranchise;
                 $customer->fullname = $request->fullname;
                 $customer->email = $request->email;
                 $customer->contactno = $request->contactno;
                 $customer->password = Hash::make($request->password);
                 $customer->active = 1;
                $customer->createdat =   date('Y-m-d H:i:s');

                $customer->updatedat = date('Y-m-d H:i:s');

                 $customer->save();
                 return $customer;
            }
            catch(Illuminate\Database\QueryException $e){
                echo $e;
            }
         }
    }

    public function addimgproperty(Request $request)
    {
               
          $Assignment = new  proimage();

          $file = $request->image;
            
          $id = uniqid();           
          if (!is_dir("propimages")) {
              mkdir("propimages");         
          }
          $upload_folder = "propimages";
          $path = $upload_folder.'/'.$id.'.jpg';
          //$file->storeAs('public/propimages', $fileNameToStore)

          if(file_put_contents(storage_path('app/public/').$path, base64_decode($file)) != false){
             $Assignment->propertyid = $request->propertyid;
             $Assignment->file = $path;
             $Assignment->createdby = $request->userid;
             $Assignment->updatedby = $request->userid;
             $Assignment->save();
            
            echo 'success';

          }
          else{
              echo "fail";
          }
    }
    public function get_listing(Request $request)
    {

        if($request->transactiontype == 'Rent')
        {
           $property = property::where('transactiontype','Rent')->get();
                  
               foreach ($property as $key) {
                       # code...
                        $key->getPropertyType;
                        $contact_info = $key->enproperty;
                       // $contact_ima =  $key->imgproperty;

                        $img = proimage::where('propertyid',$key->id)->get();
                
                        if(count($img) == 0)
                          $key->imgproperty =  null;
                        else
                          $key->imgproperty =  $img;
                }
              // dd($property);
               $result  = array('result' => $property );
        
        return $result;
        }
        else
        {
           $property = property::where('transactiontype','Sell')->get();
                  
               foreach ($property as $key) {
                       # code...
                       $key->getPropertyType;
                       $contact_info = $key->enproperty;
                       // $contact_ima =  $key->imgproperty;

                       $img = proimage::where('propertyid',$key->id)->get();
                
                        if(count($img) == 0)
                          $key->imgproperty =  null;
                        else
                          $key->imgproperty =  $img;
                }
              // dd($property);
               $result  = array('result' => $property );
        
        return $result;
        }
        
    }
    public function get_franchise()
    {
        $franchise = franchise::where('active',1)->get();
        
        $result  = array('result' => $franchise );
        
        return $result;
    }
    public function get_info(Request $request)
    {
      $property = [];
      $property = property::find($request->propertyid);
      $property->getPropertyType;
      $property->getSubPropertyType;
      $contact_info = $property->enproperty;
      // $contact_ima =  $property->imgproperty;

        $img = proimage::where('propertyid',$property->id)->get();
                
        if(count($img) == 0)
          $property->imgproperty =  null;
        else
          $property->imgproperty =  $img;

      $property->customer;
      $property->customer->getRole;
      
      $user = []; 

        if($property) 
        {
           $result  = array('result' => $property);
          return $result;
        } 
        else
        {
          echo 'fail';
        }
       
    }

    public function get_property()
    {
       $protype = protype::get()->toArray();

        $result  = array('result' => $protype);
         if(empty($protype))
         {
           echo 'fail';
         }
         else
         {
          return $result;
         }
          
    }
    public function get_prosubperty(Request $request)
    {
       $prosubtype = prosubtype::where('propertytypeid',$request->propertytypeid)->get();
       $result  = array('result' => $prosubtype);
          return $result;
    }
    public function addproperty(Request $request)
    {
        try
        {
          
         $property = new property;
         $property->userid = $request->userid;
         $property->title = $request->title;
         $property->propertytypeid = $request->propertytypeid;
         $property->propertysubtypeid = $request->propertysubtypeid;
         $property->transactiontype = $request->transactiontype;
         $property->bedrooms = $request->bedrooms;
         $property->washrooms = $request->washrooms;
         $property->furniturestatus = $request->furniturestatus;
         $property->area = $request->area;
         $property->areaunit = $request->areaunit;
         $property->totalfloor = $request->totalfloor;
         $property->floornumber = $request->floornumber;
         $property->amenities = $request->amenities;
         $property->flatfacing = $request->flatfacing; 
         $property->constructionstatus = $request->constructionstatus;
         $property->possessionyear = $request->possessionyear;
         $property->constructionyear = $request->constructionyear;
         $property->possessionmonth = $request->possessionmonth;
         $property->societyname = $request->societyname;
         $property->address = $request->address;
         $property->city = $request->city;
         $property->state = $request->state;
         $property->country = $request->country;
         $property->pincode = $request->pincode;
         $property->description = $request->description;
         $property->rentamount = $request->rentamount;
         $property->depositamount = $request->depositamount;
         $property->maintenance = $request->maintenance;
         $property->sellingamount = $request->sellingamount;
         $property->createdby = $request->userid;
         $property->updatedby = $request->userid;

         $property->save();

         //Generating display id
          $propid = $property->id;
          $trantype = 'HLS';
          if($request->transactiontype == 'Rent')
          {
              $trantype = 'HLR';
          }
          $displayid = $trantype . $property->id;
          $property->displayid = $displayid;
          $property->save();
          
          echo $property->id;

       }
       catch(\Illuminate\Database\QueryException $e)
       {
         echo 'fail';
       }

    }
    public function getCity()
    {
      $city = city::get();

      $result  = array('result' => $city);
          return $result;
    }
    public function getFilter(Request $request)
    {
      $property = property::where('adminapproved',1)->orderby('id','DESC');
      $minprice = (int)$request->minprice;
      $maxprice = (int)$request->maxprice;
      try {
        
        if(isset($request->transactiontype))
        {
           $property->where('transactiontype',$request->transactiontype);
            // dd($request->transactiontype);
           if($request->transactiontype == "Sell")
           {
                  if(isset($request->minprice) && isset($request->maxprice)) 
                  {
                    if((int)$maxprice == 50000)
                    {
                               $property->where('sellingamount','>=',$minprice);

                      }
                   else
                      {
                        //echo $maxprice;
                               $property->where('sellingamount','>=',$minprice)->where('sellingamount','<=',$maxprice);
                      }
                    
                  }
           }
           else
           {
               if(isset($request->minprice) && isset($request->maxprice))
               {
                   if($maxprice == 50000)
                      {

                               $property->where('rentamount','>=',$maxprice);
                      }
                   else
                      {
                        //dd($minprice);
                               $property->where('rentamount','>=',$minprice)->where('rentamount','<=',$maxprice);
                      }
              }

           }
        }


        // else
        // {
        //   //$property = property::orderby('id');
        //   if(isset($request->minprice) && isset($request->maxprice))
        //           if((int)$maxprice == 50000)
        //               {
        //                       $property->where('rentamount','>',$minprice);
        //               }
        //           else
        //               {
        //                       $property->where('rentamount','>',$minprice)->where('rentamount','<',$maxprice);
        //               }
        // }
      
        $furniturestatus = [];
        if($request->semifurnished)
            array_push($furniturestatus,'Semi-furnished');
        if($request->unfurnished)
            array_push($furniturestatus,'Un-furnished');
        if($request->fullyfurnished)
            array_push($furniturestatus,'Fully-furnished');
            
        if(count($furniturestatus) > 0)
            $property->whereIn('furniturestatus',$furniturestatus);
        
      //dd($maxprice);
        // if($request->semifurnished == "false" && $request->unfurnished == "false" && $request->fullyfurnished == "false" 
        // || $request->semifurnished == "true" && $request->unfurnished == "true" && $request->fullyfurnished == "true" )
        // {
            
        // }
        // else 
        // {
        //     if($request->semifurnished == "false")
        //         $property->where('furniturestatus','!=','Semi-furnished');
        //     if($request->unfurnished == "false")
        //         $property->where('furniturestatus','!=','Un-furnished');
        //     if($request->fullyfurnished == "false")
        //         $property->where('furniturestatus','!=','Fully-furnished');
        // }
        $bedrooms = [];
        if($request->OBHK)
            array_push($bedrooms,1);
        if($request->TBHK)
            array_push($bedrooms,2);
        if($request->THBHK)
            array_push($bedrooms,3);
        
            
        if(count($bedrooms) > 0){
            $property->whereIn('bedrooms',$bedrooms);
            if($request->FBHK)
                $property->orwhere('bedrooms','!>=',4);
        }   
        elseif($request->FBHK)
            $property->where('bedrooms','>=',4);
            
        // if($request->OBHK == "false")
        // {
        //     $property->where('bedrooms','!=',1);
        // }
        // else if($request->TBHK == "false")
        // {
        //     $property->where('bedrooms','!=',2);
        // }
        // else if($request->THBHK == "false")
        // {
        //     $property->where('bedrooms','!=',3);
        // } else if($request->FBHK == "false")
        // {
        //     $property->where('bedrooms','!>=',4);
        // }

        if($request->roles) {
            $roles = explode(',', $request->roles);
           // dd($roles);
            $roleids = Userrole::whereIn('role', $roles)->pluck('id');
         
            $userid = User::whereIn('roleid', $roleids)->pluck('id');
      //  dd($userid);
            $property->whereIn('userid', $userid);
         //   dd($property);
        }

        if($request->washroom_count) {
            $property->where('washrooms', '=', $request->washroom_count);
        }

        if($request->amenities) {
            $amenities = explode(',', $request->amenities);
            // dd($amenities);
            foreach ($amenities as $amenitie) {
                $property->where('amenities', 'LIKE', '%'.$amenitie.'%');
            }
        }

        if($request->verify_property) {
            $property->where('adminapproved', '1');
        }

        if($request->subproperty_type) {
            $subtypes = explode(',', $request->subproperty_type);

            $subtypeids = prosubtype::whereIn('propertysubtype', $subtypes)->pluck('id');
            $property->whereIn('propertysubtypeid', $subtypeids);
        }

        if(count($property->get()) > 0) {
            if($request->photos == 'true') {
                $propertyIds = $property->pluck('id');
                $hasimagepropIds = proimage::whereIn('propertyid', $propertyIds)->where('file', 'NOT LIKE', '%no-image.jpg%')->pluck('propertyid');
                //dd($hasimagepropIds);
                $results = $property->whereIn('id', $hasimagepropIds)->get();             
            } else {
                $results  = $property->get();
            }

            foreach ($results as $key) {
               # code...
               $key->getPropertyType;
               $contact_info = $key->enproperty;
               // $contact_ima =  $key->imgproperty;

                $img = proimage::where('propertyid',$key->id)->get();
                    
                if(count($img) == 0)
                  $key->imgproperty =  null;
                else
                  $key->imgproperty =  $img;
            }

            $result = array('result' => $results);
        }
        else {
            $result = 'fail';
        }

        return $result;
        
      } catch (\Illuminate\Database\QueryException $e) {
        echo 'fail';
      }
    }

    public function getRecords(Request $request)
    {
       try{
             $property = property::where('userid',$request->userid)->get();
             $propertyid = [];
             foreach ($property as $propertyre ) {
               # code...
                $propertyre->getPropertyType;

                $contact_info = $propertyre->enproperty;

                $img = proimage::where('propertyid',$propertyre->id)->get();
                
                if(count($img) == 0)
                  $propertyre->imgproperty =  null;
                else
                  $propertyre->imgproperty =  $img;
                
                array_push($propertyid, $propertyre);
        
             }
             $result  = array('result' => $propertyid);
             if(empty($propertyid))
             {
                   echo 'fail';
             }
             else
             {
               return $result;  
             }          
             
       }
       catch(\Illuminate\Database\QueryException $e)
       {
         echo 'fail';
       }
    }
    public function updateRecord(Request $request)
    {
       
        try
        {
          
         $property = property::find($request->propertyid);
         //dd($property);
        /* if(isset($request->userid))
         {
           $customer = customer::find($request->userid);
           $customer->fullname = $request->fullname;
           $customer->email = $request->email;
           $customer->contactno = $request->contactno;
           $customer->save();
         }*/
         //
         $property->userid = $request->userid;
         $property->title = $request->title;
         $property->propertytypeid = $request->propertytypeid;
         $property->propertysubtypeid = $request->propertysubtypeid;
         $property->transactiontype = $request->transactiontype;
         $property->bedrooms = $request->bedrooms;
         $property->washrooms = $request->washrooms;
         $property->furniturestatus = $request->furniturestatus;
         $property->area = $request->area;
         $property->areaunit = $request->areaunit;
         $property->totalfloor = $request->totalfloor;
         $property->floornumber = $request->floornumber;
         $property->amenities = $request->amenities;
         $property->flatfacing = $request->flatfacing; 
         $property->constructionstatus = $request->constructionstatus;
         $property->possessionyear = $request->possessionyear;
         $property->constructionyear = $request->constructionyear;
         $property->possessionmonth = $request->possessionmonth;
         $property->societyname = $request->societyname;
         $property->address = $request->address;
         $property->city = $request->city;
         $property->state = $request->state;
         $property->country = $request->country;
         $property->pincode = $request->pincode;
         $property->description = $request->description;
         $property->rentamount = $request->rentamount;
         $property->depositamount = $request->depositamount;
         $property->maintenance = $request->maintenance;
         $property->sellingamount = $request->sellingamount;
         $property->save();
         echo 'successful';

       }
       catch(\Illuminate\Database\QueryException $e)
       {
         echo 'fail';
       }

    }
    public function getProfile(Request $request)
    {
        try
        {
           $user = customer::find($request->userid);
           
           $result = array('result' => $user );  
          
           if($user)
           {
             return $result;
           }
           else{
             echo 'fail';
           }
        }
        catch(\Illuminate\Database\QueryException $e)
        {
          echo 'fail';
        }
    }
    public function getProComm()
    {
        $property = property::where('propertytypeid','2')->get();
        
        foreach ($property as $key) {
           # code...
           $key->getPropertyType;
           $contact_info = $key->enproperty;
           // $contact_ima =  $key->imgproperty;

            $img = proimage::where('propertyid',$key->id)->get();
                
            if(count($img) == 0)
              $key->imgproperty =  null;
            else
              $key->imgproperty =  $img;
        }
        
        $result  = array('result' => $property);
        return $result;

    }

     public function getProRes()
    {
       $property = property::where('propertytypeid','1')->get();

       foreach ($property as $key) {
           # code...
            $key->getPropertyType;
            $contact_info = $key->enproperty;
            // $contact_ima =  $key->imgproperty;

            $img = proimage::where('propertyid',$key->id)->get();
                
            if(count($img) == 0)
              $key->imgproperty =  null;
            else
              $key->imgproperty =  $img;
        }
      
        $result  = array('result' => $property);
        return $result;

    }
   
    public function checkNumber(Request $request)
    {
       $user = customer::select('id')->where('contactno',$request->number)->first();

        $result = array('result' => $user );  
          
           if($user)
           {
             return $result;
           }
           else{
             echo 'fail';
           }

    }
    public function updatePassword(Request $request)
    {
       $user = customer::find($request->userid);

       if($user)
       {
         $user->password = $request->password;
         $user->save();
         return $user;
       }
       else
       {
        echo 'fail';
       }
    }
    public function sendEnquiry(Request $request)
    {
        
        try
        {
          $Enquiry = new Enquiry;
          $Enquiry->name = $request->name;
          $Enquiry->email = $request->email;
          $Enquiry->contactno = $request->contactno;
          $Enquiry->role = $request->role;
          $Enquiry->message = $request->message;
          $Enquiry->save();
          echo 'successful';
        }
        catch(Illuminate\Database\QueryException $e)
        {
          echo 'fail';
        }
    }
    public function sendPropEnquiry(Request $request)
    {
        
        try
        {
          $Enquiry = new proenquiry;
          $Enquiry->propertyid = $request->propertyid;
          $Enquiry->name = $request->name;
          $Enquiry->email = $request->email;
          $Enquiry->location = $request->location;
          $Enquiry->city = $request->city;
          $Enquiry->pincode = $request->pincode;
          $Enquiry->projectname  = $request->projectname ;
          $Enquiry->budget = $request->budget;
          $Enquiry->message = $request->message;
          $Enquiry->createdat = date('Y-m-d H:i:s');
          $Enquiry->createdby = $request->createdby;          
          $Enquiry->contactno = $request->contactno;
          
          $Enquiry->save();
          echo 'successful';
        }
        catch(Illuminate\Database\QueryException $e)
        {
          echo 'fail';
        }
    }
    public function updateUserInfo(Request $request)
    {
       try
       {
          $user = customer::find($request->userid);
          if($user)
          {
                $user->fullname = $request->fullname;
                $user->email = $request->email;
                $user->contactno = $request->contactno;
                $user->city = $request->city;
                $user->address = $request->address;
                $user->dob = $request->dob;
                $user->updatedby = $request->userid;
                $user->updatedat = date('Y-m-d H:i:s');
                $user->save();
                return $user;
          }
          else
          {
               echo 'fail';
          }
               
       }
       catch(\Illuminate\Database\QueryException $e)
       {
        echo 'fail';
       }
    }
   
    public function search(Request $request)
    {
         
         $property = property::orwhere('title','like','%'.$request->searchText.'%')->orwhere('transactiontype','like','%'.$request->searchText.'%')->orwhere('furniturestatus','like','%'.$request->searchText.'%')->orwhere('societyname','like','%'.$request->searchText.'%')->orwhere('address','like','%'.$request->searchText.'%')->orwhere('city','like','%'.$request->searchText.'%')->orwhere('state','like','%'.$request->searchText.'%')->orwhere('country','like','%'.$request->searchText.'%')->orwhere('description','like','%'.$request->searchText.'%')->get();
         
           foreach ($property as $key) {
                       # code...
                        $key->getPropertyType;
                        $contact_info = $key->enproperty;
                       // $contact_ima =  $key->imgproperty;

                        $img = proimage::where('propertyid',$key->id)->get();
                
                        if(count($img) == 0)
                          $key->imgproperty =  null;
                        else
                          $key->imgproperty =  $img;
                }
              // dd($property);
               $result  = array('result' => $property );
         $result = array('result' => $property);  
          
           if(empty($property))
           {
               echo 'fail';
           }
           else{
               return $result;
           }
    }
    public function send_otp(Request $request)
    {
         $otp = rand(10000,99999);
         $msg = "Your OTP is :- ".$otp;
         // $numbers = $Visitor->contact_no;
          $this->sms($request->contactno,$msg);
          return $otp;

    }

    public function get_all_prosubperty(Request $request)
    {
       $prosubtype = prosubtype::all();
       $result  = array('result' => $prosubtype);
          return $result;
    }

    public function savehomeloanenquiry(Request $request)
    {
        $enquiry = new homeloanenquiry;
        $enquiry->name = $request->input('name');
        $enquiry->contactno = $request->input('contactno');
        $enquiry->email = $request->input('email');
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

        return $enquiry;      
    }

    public function saveenquiry(Request $request)
    {
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
        
        // $enquiry->notify(new PropertyEnquirySuccess($enquiry->id));

        // $this->sms($enquiry->contactno, 'Greetings, your enquiry is send successfully on Homeland Properties. our agent will contact you shortly.');

        // $users = User::where('roleid', '=', '2' )->get();
        
        // \Notification::send($users, new PropertyAdminEnquirySuccess($enquiry->id));

        // foreach ($users as $user) {
        //     $this->sms($user->contactno, 'Greetings, Recived enquiry on Homeland Properties.');
        // }

        return $enquiry;
    }
}
