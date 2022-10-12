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

## 10) Password Reset functionality
- Ref. link: https://dev.to/rabeeaali/how-to-reset-password-via-code-using-laravel-api-2f6p
### 10.1) Create mailtrap.io account and copy laravel constants from it and set in .env file
``
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=XXX
MAIL_PASSWORD=XXX
MAIL_ENCRYPTION=tls
``

### 10.2) Create new password reset code table
- Make PasswordResetCode model and migration
``
php artisan make:model PasswordResetCode -m
``

- Update migration file like below
``
Schema::create('password_reset_codes', function (Blueprint $table) {
    $table->id();
    $table->string('email')->index();
    $table->string('code');
    $table->timestamp('created_at')->nullable();
});
``

- Update model's $fillable property
``
protected $fillable = [
    'email',
    'code',
    'created_at',
];
``

### 10.3) Create a mail class for email
``
php artisan make:mail SendPasswordResetCode
``

### 10.4) Create controller and define methods
- Create PasswordResetController
``
php artisan make:controller PasswordResetController
``

- Update PasswordResetController like below
``
namespace App\Http\Controllers;

use App\Mail\SendPasswordResetCode;
use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PasswordResetController extends Controller
{
    public function generateCode(Request $request) {
        $data = $request->validate([
            'email' => 'required|email|exists:users',
        ]);

        // Delete all old code that user send before.
        PasswordResetCode::where('email', $request->email)->delete();

        // Generate random code
        $data['code'] = mt_rand(100000, 999999);

        // Create a new code
        $codeData = PasswordResetCode::create($data);

        // Send email to user
        Mail::to($request->email)->send(new SendPasswordResetCode($codeData->code));

        return response([
            'message' => trans('passwords.sent')
        ], 200);
    }

    public function codeCheck(Request $request) {
        $request->validate([
            'code' => 'required|string|exists:password_reset_codes',
        ]);

        // find the code
        $passwordReset = PasswordResetCode::firstWhere('code', $request->code);

        // check if it does not expired: the time is one hour
        if ($passwordReset->created_at > now()->addHour()) {
            $passwordReset->delete();
            return response([
                'message' => trans('passwords.code_is_expire')
            ], 422);
        }

        return response([
            'code' => $passwordReset->code,
            'message' => trans('passwords.code_is_valid')
        ], 200);
    }

    public function resetPassword(Request $request) {
        $request->validate([
            'code' => 'required|string|exists:reset_code_passwords',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // find the code
        $passwordReset = PasswordResetCode::firstWhere('code', $request->code);

        // check if it does not expired: the time is one hour
        if ($passwordReset->created_at > now()->addHour()) {
            $passwordReset->delete();
            return response([
                'message' => trans('passwords.code_is_expire')], 422);
        }

        // find user's email 
        $user = User::firstWhere('email', $passwordReset->email);

        // update user password
        $user->update($request->only('password'));

        // delete current code 
        $passwordReset->delete();

        return response([
            'message' =>'password has been successfully reset'
        ], 200);
    }
}
``

## 11) Handel Mail and blade file
- In app\Mail\SendPasswordResetCode.php
``
class SendPasswordResetCode extends Mailable
{
    use Queueable, SerializesModels;
    public $code;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($code)
    {
        $this->code = $code;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        //return $this->view('view.name');
        return $this->markdown('emails.send-password-reset-code');
    }
}
``

- Create new blade file for mail template. resources\views\emails\send-password-reset-code.blade.php
``
@component('mail::message')
<h1>We have received your request to reset your account password</h1>
<p>You can use the following code to recover your account:</p>

@component('mail::panel')
{{ $code }}
@endcomponent

<p>The allowed duration of the code is one hour from the time the message was sent</p>
@endcomponent
``

## 12) Create Routes
``
Route::post("generate-code",[PasswordResetController::class,"generateCode"]);
Route::post("code-check",[PasswordResetController::class,"codeCheck"]);
Route::post("reset-password",[PasswordResetController::class,"resetPassword"]);
``