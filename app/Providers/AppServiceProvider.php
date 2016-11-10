<?php

namespace App\Providers;
use View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        View::share('asset',function($path){
            return self::cdn($path);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
    
    public static function cdn($path=''){
        if(config('view.is_cdn')) {
            $path = config('view.cdn_url').$path;
        } else {
            $path = asset($path);
        }
        return $path;
    }
}
