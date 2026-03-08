<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    // Redirige al usuario a la página de Google
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    // Recibe la respuesta de Google
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Buscamos si el usuario ya existe por su email o google_id
            $user = User::where('email', $googleUser->email)->orWhere('google_id', $googleUser->id)->first();

            if ($user) {
                // Si existe pero no tiene el google_id (se registró normal antes), se lo actualizamos
                $user->update(['google_id' => $googleUser->id]);
            } else {
                // Si es totalmente nuevo, lo creamos
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'password' => bcrypt(Str::random(16)) // Clave aleatoria por seguridad
                ]);
                
                // 🌟 LE ASIGNAMOS EL ROL BÁSICO AUTOMÁTICAMENTE 🌟
                $user->assignRole('user');
            }

            // Lo logueamos
            Auth::login($user);

            // Lo mandamos al feed de videos
            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['error' => 'No se pudo iniciar sesión con Google.']);
        }
    }
}