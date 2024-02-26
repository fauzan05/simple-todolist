<?php

namespace App\Http\Controllers\Auth;

use App\Enum\UserRoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Token;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class DoodleController extends Controller
{
    use AuthenticatesUsers;
    public function redirect()
    {
        $response = Client::where('name', 'doodle')->first();
        $query = http_build_query([
            'client_id' => $response->client_id,
            'redirect_uri' => $response->redirect_url,
            'response_type' => 'code',
            'scope' => 'get-user'
        ]);
        return redirect('http://127.0.0.1:8000/oauth/authorize?' . $query);
    }

    public function callback(Request $request)
    {
        $response = Client::where('name', 'doodle')->first();
        $response = Http::post('http://127.0.0.1:8000/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $response->client_id,
            'client_secret' => $response->secret,
            'redirect_uri' => $response->redirect_url,
            'code' => $request->query('code')
        ]);
        $response = json_decode($response->body());
        // dd($response);
        if(auth()->user()->token ?? null != null) {
            $request->user()->token()->delete();
        }
        $get_user = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer $response->access_token"
        ])->get('http://127.0.0.1:8000/api/user');
        $get_user = json_decode($get_user->body());
        // dd($get_user);
        $user = User::updateOrCreate([
            'doodle_id' => $get_user->id
        ], [
            'name' => $get_user->name,
            'email' => $get_user->email,
            'role' => UserRoleEnum::USER
        ]);
        Auth::login($user);
        Token::updateOrCreate([
            'user_id' => auth()->user()->id,
        ],[
            'access_token' => $response->access_token,
            'expires_in' => $response->expires_in,
            'refresh_token' => $response->refresh_token
        ]);
        return redirect('/home');
    }

    public function refreshToken()
    {
        $response = Client::where('name', 'doodle')->first();
        $response = Http::post('http://127.0.0.1:8000/oauth/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => auth()->user()->token->refresh_token,
            'client_id' => $response->client_id,
            'client_secret' => $response->secret,
            'scope' => 'get-user'
        ]);
        $response = json_decode($response->body());
        // dd($response);
        Token::updateOrCreate([
            'user_id' => auth()->user()->id,
        ],[
            'access_token' => $response->access_token,
            'expires_in' => $response->expires_in,
            'refresh_token' => $response->refresh_token
        ]);

        return redirect()->back();
    }
}
