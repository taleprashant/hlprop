<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Notifications\UserRegisteredSuccessfully;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Auth\Events\Registered;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new user as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect user after registration.
     *
     * @var string
     */
    protected $redirectTo = '/register';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:user',
            'contactno' => 'required|min:10|max:12',
            'password' => 'required|string|min:6|confirmed',
        ]);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        //Log::info($request);
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        //$this->guard()->login($user);

        return redirect()->back()->with('message', 'Successfully created a new account. Please check your email and activate your account.');
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        try
        {   
            $otp = rand(100000, 999999);
            $data['otpcode'] = $otp;

            $user = User::create([
                'fullname' => $data['name'],
                'email' => $data['email'],
                'contactno' => $data['contactno'],
                'roleid' => $data['userrole'],
                'franchiseid' => $data['franchise'],
                'otherfranchise' => $data['otherfranchise'],
                'password' => Hash::make($data['password']),
                'activationcode' => str_random(30).time(),
                'otpcode' => $data['otpcode'],
            ]);

        } catch (\Exception $exception) {
            logger()->error($exception);
            return redirect()->back()->with('message', 'Unable to create new user.');
        }

        $this->sms($user->contactno, $user->otpcode .' is your one time password to proceed on Homeland Properties. Do not share your OTP with anyone.');
        //send activation link email
        $user->notify(new UserRegisteredSuccessfully($user));
        //return redirect()->back()->with('message', 'Successfully created a new account. Please check your email and activate your account.');
        return $user;
    }
    
        /**
     * Activate the user with given activation code.
     * @param string $activationCode
     * @return string
     */
    public function activateUser(string $activationCode)
    {
        try 
        {
            $user = app(User::class)->where('activationcode', $activationCode)->first();
            if (!$user) 
            {
                return "The code does not exist for any user in our system.";
            }
            $user->active = 1;
            $user->activationcode = null;
            $user->save();
            session(['user' => $user]);

            return redirect()->to('/otp_verify');
            // auth()->login($user);
        } 
        catch (\Exception $exception) 
        {
            logger()->error($exception);
            return view('errors.default',  ['error' => $exception->getMessage()]);                       
        }
        return redirect()->to('/');
    }

    /**
     * The user has been registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function registered(Request $request, $user)
    {
        //
    }

    protected function showRegistrationForm()
    {
        $franchises = DB::table('franchise')->get();
        return view('auth.register',['franchises' => $franchises]);
    }

    public function otpverify(Request $request)
    {
        try
        {
            $storeduser = session()->get('user');

            $user = User::where('email', $storeduser->email)->where('otpcode', '=', $request->otpcode)->first();

            if (!$user) 
            {
                return redirect()->back()->with('message', 'Invalid email or OTP, Please try again.');
            }

            $user->otpactive = 1;
            $user->otpcode = null;

            $user->save();
            
            session()->forget('user');   

            auth()->login($user);
        }
        catch (\Exception $exception) 
        {   
            logger()->error($exception);
            return view('errors.default',  ['error' => $exception->getMessage()]);           
        }
        return redirect()->to('/');
        
    }

    public function resendotpcode(Request $request)
    {
        try {
            $user = session()->get('user');

            $otp = rand(100000, 999999);

            $this->sms($user->contactno, $otp .' is your one time password to proceed on Homeland Properties. Do not share your OTP with anyone.');

            $user->otpcode = $otp;
            $user->save();
            return redirect()->back()->with('message', 'Please check your SMS.');
        }
        catch( \Exception $exception) {
            logger()->error($exception);
            return view('errors.default',  ['error' => $exception->getMessage()]);           
        }
    }
}
