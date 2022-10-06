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