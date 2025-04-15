<?php

namespace Tests\Unit\Repositories;

use App\Models\Customer;
use App\Repositories\CustomerRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new CustomerRepository(new Customer());
    }

    /** @test */
    public function it_can_create_a_customer()
    {
        $data = [
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'cpf_cnpj' => '12345678901',
            'phone' => '1234567890',
        ];

        $customer = $this->repository->create($data);

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertEquals('Test Customer', $customer->name);
        $this->assertEquals('test@example.com', $customer->email);
        $this->assertEquals('12345678901', $customer->cpf_cnpj);
        $this->assertEquals('1234567890', $customer->phone);
    }

    /** @test */
    public function it_can_find_a_customer_by_id()
    {
        $customer = Customer::factory()->create([
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'cpf_cnpj' => '12345678901',
        ]);

        $foundCustomer = $this->repository->find($customer->id);

        $this->assertInstanceOf(Customer::class, $foundCustomer);
        $this->assertEquals($customer->id, $foundCustomer->id);
        $this->assertEquals('Test Customer', $foundCustomer->name);
    }

    /** @test */
    public function it_can_update_a_customer()
    {
        $customer = Customer::factory()->create([
            'name' => 'Test Customer',
            'email' => 'test@example.com',
        ]);

        $updatedCustomer = $this->repository->update($customer->id, [
            'name' => 'Updated Customer',
            'email' => 'updated@example.com',
        ]);

        $this->assertEquals('Updated Customer', $updatedCustomer->name);
        $this->assertEquals('updated@example.com', $updatedCustomer->email);
    }

    /** @test */
    public function it_can_delete_a_customer()
    {
        $customer = Customer::factory()->create();

        $this->repository->delete($customer->id);

        $this->assertDatabaseMissing('customers', [
            'id' => $customer->id,
        ]);
    }

    /** @test */
    public function it_can_find_a_customer_by_email()
    {
        Customer::factory()->create([
            'email' => 'test@example.com',
        ]);

        $customer = $this->repository->findByEmail('test@example.com');

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertEquals('test@example.com', $customer->email);
    }

    /** @test */
    public function it_can_find_a_customer_by_cpf_cnpj()
    {
        Customer::factory()->create([
            'cpf_cnpj' => '12345678901',
        ]);

        $customer = $this->repository->findByCpfCnpj('12345678901');

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertEquals('12345678901', $customer->cpf_cnpj);
    }

    /** @test */
    public function it_can_find_a_customer_by_external_id()
    {
        Customer::factory()->create([
            'external_id' => 'ext_123',
        ]);

        $customer = $this->repository->findByExternalId('ext_123');

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertEquals('ext_123', $customer->external_id);
    }
}