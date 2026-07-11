<?php
namespace Shared\Interfaces;

interface NotificationChannelInterface
{
    public function send(string $recipient, string $subject, string $body, array $options = []): bool;
    public function getChannelName(): string;
}