<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function last_seen()
    {
        $session = \DB::table('sessions')
            ->select('last_activity')
            ->where('user_id', $this->id)
            ->orderBy('last_activity', 'desc')
            ->first();

        if (!$session)
            return null;

        return Carbon::createFromTimestamp($session->last_activity);
    }

    /**
     * Relación: Un usuario pertenece a un rol
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public function hasRole(string $slug): bool
    {
        return $this->role && $this->role->slug === $slug;
    }

    /**
     * Verificar si el usuario es admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Verificar si el usuario tiene alguno de los roles especificados
     * 
     * @param string|array $roles  Slug o array de slugs de roles
     * @return bool
     */
    public function hasAnyRole($roles): bool
    {
        // Si es un string, convertirlo a array
        if (is_string($roles)) {
            $roles = [$roles];
        }

        if (!in_array('admin', $roles)) {
            $roles[] = 'admin';
        }

        // Verificar si el usuario tiene alguno de los roles
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verificar si el usuario tiene acceso a una ruta específica
     * 
     * @param string $routeName  Nombre de la ruta
     * @return bool
     */
    public function canAccessRoute(string $routeName): bool
    {
        return \App\Helpers\RouteHelper::canAccessRoute($routeName);
    }
}
