<?php

namespace App\Repositories;

interface CustomerRepositoryInterface extends RepositoryInterface
{
    public function findByEmail($email);
    public function findByCpfCnpj($cpfCnpj);
    public function findByExternalId($externalId);
}