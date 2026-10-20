<?php

namespace App\User\Infrastructure\Models;

use App\User\Domain\Entity\User;
use App\User\Domain\Entity\UserRole;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property UserRole $role
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class UserModel extends Authenticatable
{
    use HasApiTokens;

    /**
     * @use HasFactory<\Database\Factories\UserFactory>
     */
    use HasFactory;

    protected $table = 'users';

    protected $fillable = [
        UserColumn::Name->value,
        UserColumn::Email->value,
        UserColumn::Password->value,
        UserColumn::Role->value,
        UserColumn::CreatedAt->value,
        UserColumn::UpdatedAt->value,
    ];

    protected $hidden = [
        UserColumn::Password->value,
    ];

    protected function casts(): array
    {
        return [UserColumn::Role->value => UserRole::class];
    }

    public function fillFromEntity(User $user): static
    {
        return $this->fill([
            UserColumn::Name->value => $user->getName(),
            UserColumn::Email->value => $user->getEmail(),
            UserColumn::Password->value => $user->getPasswordHash(),
            UserColumn::Role->value => $user->getRole(),
            UserColumn::CreatedAt->value => $user->getCreatedAt(),
            UserColumn::UpdatedAt->value => $user->getUpdatedAt(),
        ]);
    }

    public function toEntity(): User
    {
        return User::create(
            id: $this->id,
            name: $this->name,
            email: $this->email,
            passwordHash: $this->password,
            role: $this->role,
            createdAt: DateTimeImmutable::createFromInterface($this->created_at),
            updatedAt: DateTimeImmutable::createFromInterface($this->updated_at),
        );
    }
}
