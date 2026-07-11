<?php
namespace Shared\Interfaces;

interface PaymentGatewayInterface
{
    public function request(float $amount, string $callbackUrl, string $description, array $options = []): object;
    public function verify(string $trackId): object;
    public function inquiry(string $trackId): object;
    public function getGatewayName(): string;
}