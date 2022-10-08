# Laravel Nuxt Auth Backend Code

## 1) Install Laravel Sanctum

``
composer require laravel/sanctum
``

## 2) Publish the Sanctum configuration and migration files using the vendor:publish Artisan command
``
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
``

## 3) Create DB and connect it with app

## 4) Run database migrations
- Sanctum will create one database table in which to store API tokens
``
php artisan migrate
``

## 5) Add sanctum's middleware to api middleware group within application's app/Http/Kernel.php file
- Uncomment following line in 'app/Http/Kernel.php'
``
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
``

## 6) Set session domain
- In config/sanctum.php, copy "SANCTUM_STATEFUL_DOMAINS" and define it in .env file. localhost:3000 is Nuxt project local URL.
``
SESSION_DOMAIN=localhost
SANCTUM_STATEFUL_DOMAINS=localhost:3000
``

## 7) Set allowed origin
- In config/cors.php, set allowed_origin to your frontend URL
``
'allowed_origins' => ['http://localhost:3000'],
``

## 8) Set support credentials
- In config/cors.php, set supports_credentials to true
``
'supports_credentials' => true,
``

## 9) Create login, register, logout API
- Create AuthController
``
php artisan make:controller AuthController
``

- Define routes in api.php
``
Route::post("login",[AuthController::class,"login"]);
Route::post("register",[AuthController::class,"register"]);
Route::post("logout",[AuthController::class,"logout"]);
``

- Define login, register and logout methods
``
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request) {
        if(!Auth::attempt($request->only('email','password'))) {
            throw new AuthenticationException();
        }
    }

    public function register(Request $request) {
        $validatedData = $request->validate([
            'name'      => 'required',
            'email'     => 'required|unique:users|max:255',
            'password'  => 'required',
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);

        $user->save();
    }

    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
``