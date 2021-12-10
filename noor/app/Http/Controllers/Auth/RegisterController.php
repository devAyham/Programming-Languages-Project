<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\UserVerify;
use Illuminate\Support\Str;
use Mail;
class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

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
        $message = array(
            'name.required' => 'الرجاء إدخال الاسم',

        );
    
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ],$message);

        

    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    { 
      $request = request();
      $data =  [
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => Hash::make($data['password']),
    ];
    $user = User::create($data);

      $token = Str::random(64);
      $email =  $user->email;

      UserVerify::create([
            'user_id' => $user->id, 
            'token' => $token
          ]);

      Mail::send('emails.emailVerificationEmail', ['token' => $token], function($message) use($email){
            $message->to($email);
            $message->subject('Email Verification Mail');
            $message->from('support@mojtammat.com','Mojtammat');
        });
     

     
      return $user;
  }
    public function verifyAccount($token)
    {
        $verifyUser = UserVerify::where('token', $token)->first();
        if(isset($verifyUser) ){
          $user = $verifyUser->user;
          if(!$user->verified) {
            $verifyUser->user->email_verified_at = now();
            $verifyUser->user->is_email_verified = 1;
            $verifyUser->user->save();
            $status = "Your e-mail is verified. You can now login.";
          } else {
            $status = "Your e-mail is already verified. You can now login.";
          }
        } else {
          return redirect('/login')->with('warning', "Sorry your email cannot be identified.");
        }
        return redirect('/login');
    }
}
