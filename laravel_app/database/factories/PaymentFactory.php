<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $methods = ['BOLETO', 'CREDIT_CARD', 'PIX'];
        $statuses = ['PENDING', 'CONFIRMED', 'RECEIVED', 'DECLINED', 'FAILED'];
        $method = $this->faker->randomElement($methods);
        
        $data = [
            'customer_id' => Customer::factory(),
            'payment_method' => $method,
            'value' => $this->faker->randomFloat(2, 10, 1000),
            'status' => $this->faker->randomElement($statuses),
            'external_id' => 'pay_' . $this->faker->unique()->randomNumber(6),
            'due_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'error_message' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        if ($method === 'BOLETO') {
            $data['invoice_url'] = $this->faker->url();
        } elseif ($method === 'PIX') {
            $data['pix_code'] = $this->faker->sha256();
            $data['pix_qrcode'] = $this->faker->text(100);
        }
        
        return $data;
    }
    
    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function boleto()
    {
        return $this->state(function (array $attributes) {
            return [
                'payment_method' => 'BOLETO',
                'invoice_url' => $this->faker->url(),
            ];
        });
    }
    
    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function creditCard()
    {
        return $this->state(function (array $attributes) {
            return [
                'payment_method' => 'CREDIT_CARD',
            ];
        });
    }
    
    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function pix()
    {
        return $this->state(function (array $attributes) {
            return [
                'payment_method' => 'PIX',
                'pix_code' => $this->faker->sha256(),
                'pix_qrcode' => $this->faker->text(100),
            ];
        });
    }
    
    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'PENDING',
            ];
        });
    }
    
    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function confirmed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'CONFIRMED',
            ];
        });
    }
    
    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function failed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'FAILED',
                'error_message' => $this->faker->sentence(),
            ];
        });
    }
}