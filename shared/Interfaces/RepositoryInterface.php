<?php
namespace Shared\Interfaces;

interface RepositoryInterface
{
    public function find(int $id): ?object;
    public function findAll(array $filters = [], string $orderBy = 'id DESC', int $limit = 100, int $offset = 0): array;
    public function create(array $data): int;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function softDelete(int $id): bool;
    public function restore(int $id): bool;
    public function count(array $filters = []): int;
}