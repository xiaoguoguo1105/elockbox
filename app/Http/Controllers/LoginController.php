<?php

namespace App\Http\Controllers;

use Cartalyst\Sentinel\Checkpoints\NotActivatedException;
use Cartalyst\Sentinel\Checkpoints\ThrottlingException;
use Illuminate\Http\Request;
use Sentinel;
use DB;
use App\Http\Requests;
use App\Http\Requests\VrfycodeFormRequest;
use App\VrfyCode;
use Cartalyst\Sentinel\Users\EloquentUser;

class LoginController extends Controller
{
    //
    public function login() {
        return view('login.login');
    }

    public function authenticate(Request $request) {
        $input = $request->only('email', 'password');
        try{
            if(Sentinel::authenticate($input)) {
                $code = rand(100000, 999999);
                $email = $request->only('email');
                $user_id = DB::table('users')->where('email', $email)->first()->id;
                DB::table('code')->where('user_id', $user_id)->update(['code'=> $code]);
//                $this->vrfy($user_id);
                return $this->redirectVrfyCode($user_id);
            }
            return redirect()->back()->withInput()->withErrorMessage('Invalid credentials provided');
        } catch (NotActivatedException $e) {
            return redirect()->back()->withInput()->withErrorMessage('User Not Activated.');
        } catch (ThrottlingException $e) {
            return redirect()->back()->withInput()->withErrorMessage($e->getMessage());
        }
    }

    protected function redirectVrfyCode($user_id) {
        return view('login.verify', [
            'user_id' => $user_id,
        ]);
    }
    protected function vrfy(VrfycodeFormRequest $request) {
        $input = $request->only('user_id', 'vrfycode');
        $excode = DB::table('code')->where('user_id', $input['user_id'])->first()->code;
        if($excode == $input['vrfycode']) {
            return $this->redirectWhenLoggedIn();
        }
        return redirect()->back()->withInput()->withErrorMessage('Wrong verification code');
    }
    protected function redirectWhenLoggedIn() {
        echo 'Logged in page';
        return null;
    }

//    public function generatecode() {
//        $code = rand(1000, 9999);
//        VrfyCode::create($code);
//    }
}