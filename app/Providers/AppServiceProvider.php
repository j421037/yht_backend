<?php

namespace App\Providers;

use App\AReceivable;
use App\AReceivebill;
use App\CustomerRecord;
use App\GeneralOffer;
use App\Observers\AReceivableObserver;
use App\Observers\AReceivebillObserver;
use App\Observers\CustomerRecordObserver;
use App\Observers\GeneralOfferObserver;
use App\RealCustomer;
use App\CustomerTag;
use App\Observers\RealCustomerObserver;
use App\Observers\CustomerTagObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //在mariadb中字符串长度设置最大长度值
        Schema::defaultStringLength(191);
        \Carbon\Carbon::setLocale('zh');
        //监听模型事件
        RealCustomer::observe(RealCustomerObserver::class);
        CustomerTag::observe(CustomerTagObserver::class);
        CustomerRecord::observe(CustomerRecordObserver::class);
        AReceivebill::observe(AReceivebillObserver::class);
        AReceivable::observe(AReceivableObserver::class);
        GeneralOffer::observe(GeneralOfferObserver::class);
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
}
