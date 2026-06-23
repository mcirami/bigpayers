<?php

namespace App\Support;

use App\User;

final readonly class CurrentUserContext
{
    public function __construct(
        public int $id,
        public int $type,
        public mixed $data,
        public mixed $permissions,
    ) {
    }

    public function user(): ?User
    {
        return User::query()->where('idrep', '=', $this->id)->first();
    }

    public function can(string $permission): bool
    {
        return $this->permissions->can($permission);
    }
}
