<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Socialite;
use App\User;
use Validator;
use Illuminate\Support\Str;
use Mail;
class PublicController extends Controller
{
	public function index()
	{
		$user = Auth::user();
		return view('app', compact('user'));
	}

	 public function googleLogin()
  {
    $this->middleware('guard:web');
    return Socialite::driver('google')->redirect();
  }


  public function googleConnect(Request $request)
  {
    $google = Socialite::driver('google')->stateless()->user();
    $customer = User::where('google_id', $google->id)->first();
    if (is_null($customer)) {
      // coba cek emailnya udah terdaftar apa belum
      $emailCustomer = User::where('email', $google->email)->first();
      // belum pernah daftar sama sekali waktunya save session
      if (is_null($emailCustomer)) {
         $dataCustomer = array(
          'email' => $google->email,
          'name' => $google->name,
          'google_id' => $google->id,
          'email_verified_at' => date('Y-m-d H:i:s'),
          'password' => bcrypt('password')
        );
        $customer = User::create($dataCustomer);
        // sendEmailActivate
        Auth::guard('web')->loginUsingId($customer->id);
        return redirect()->route('public.index');
        
      }

      // kalau udah daftar tinggal update aja
      $dataInsert = array(
        'google_id' => $google->id,
        'email'       => $emailCustomer->email
      );

      // update data
      $customer = User::updateOrCreate(array('email'=>$emailCustomer->email, 'id' => $emailCustomer->id), $dataInsert);
    }
    // force login
    Auth::guard('web')->loginUsingId($customer->id);
    return redirect()->route('public.index');
  }


  public function forgotPassword(Request $request)
  {

    $validator = Validator::make($request->all(), [
      'email'      => 'required',
    ]);

    if ($validator->fails()) {
      return response()->json(array(
        'status' => false,
        'message' => 'Email Not Found'
      ), 402) ;
    }

    $user = User::where('email', $request->email)->first();

    if (is_null($user)) {

        return response()->json(array(
          'status' => false,
          'message' => 'Email Not Found'
        ), 402) ;
   
    }

    $token = Str::random(10);

    $user->forgot_token = $token;
    $user->save();

    $email = env('MAIL_DUMMY');


    Mail::send('forgot', array('link' => route('public.reset', array('token' => $token))), function ($message) use ($email)
    {
      $message->subject('forgot');
      $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_ADDRESS'));
      $message->to($email);
    });

   return response()->json(array(
      'status' => true
    )) ;
  }


  public function resetPasswordRender(Request $request, $token)
  {
    $user = User::where('forgot_token', $token)->first();
    if (is_null($user)) {
      abort(404);
    }
    return view('resetPassword', compact('token'));
  }

  public function resetPassword(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'password'      => 'required|min:8|confirmed',
      'token' => 'required'
    ]);

    if ($validator->fails()) {
      return response()->json($validator->errors(), 402) ;
    }

    $user = User::where('forgot_token', $request->token)->first();


    if (is_null($user)) {
      abort(404);
    }

    $user->password = bcrypt($request->password);
    $user->forgot_token = null;
    $user->save();


    return response()->json(array(
      'status' => true
    )) ;

  }
}
