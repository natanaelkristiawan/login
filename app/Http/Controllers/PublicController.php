<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Socialite;
use App\User;

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
      $customer = Customers::updateOrCreate(array('email'=>$emailCustomer->email, 'id' => $emailCustomer->id), $dataInsert);
    }
    // force login
    Auth::guard('web')->loginUsingId($customer->id);
    return redirect()->route('public.index');
  }
}
