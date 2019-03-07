<?php

namespace App\Http\Controllers\Web;

use App\User;
use Auth;
use Socialite;
use App\Http\Controllers\Controller;

class AuthenticationController extends Controller
{
    public function getSocialRedirect( $account ){
    	try {
    		return Socialite::with($account)->redirect();
    	} catch (\InvalidArgumentException $e) {
    		return redirect('/login');
    	}
    }

    public function getSocialCallback( $account ){

    	// Recebe o usuário que foi autenticado, via (google , facebook, twitter)
		$socialUser = Socialite::with( $account )->user();

		// Procura na base um usuário correspondente
        $user = User::where( 'provider_id', '=', $socialUser->id )
            ->where( 'provider', '=', $account )
            ->first();

        // Caso não encontre o usuário
        if(!$user) {
        	$newUser = new User();
        	$newUser->name = $socialUser->getName();
        	$newUser->email = $socialUser->getEmail() == '' ? '' : $socialUser->getEmail();
        	$newUser->avatar = $socialUser->getAvatar();
        	$newUser->password = '';
        	$newUser->provider = $account;
        	$newUser->provider_id = $socialUser->getId();
        	$newUser->save();

        	$user = $newUser;
        }

        // Loga o usuário na aplicação
        // Essa parte está meio estranha visto que não faz uso do Passport
        // Para se conectar com o Laravel...
        // Porém tudo bem
        Auth::login($user);

        // Retorna para a aplicação
        return redirect('/');
    }
}