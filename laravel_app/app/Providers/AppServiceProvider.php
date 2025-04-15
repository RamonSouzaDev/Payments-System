<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\PaymentServiceInterface;
use App\Services\PaymentService;

use App\Services\AsaasServiceInterface;
use App\Services\AsaasService;

use App\Repositories\CustomerRepositoryInterface;
use App\Repositories\CustomerRepository;

use App\Repositories\PaymentRepositoryInterface;
use App\Repositories\PaymentRepository;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentServiceInterface::class, PaymentService::class);
        $this->app->bind(AsaasServiceInterface::class, AsaasService::class);
        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
