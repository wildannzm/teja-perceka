<?php

namespace App\Support;

use Spatie\Permission\Models\Role;

class RoleLabels
{
    /**
     * Canonical display order (single source).
     *
     * @var array<int, string>
     */
    public const ORDER = [
        'super_admin',
        'pengawas',
        'kepala_desa',
        'direktur_bumdes',
        'sekretaris',
        'bendahara',
        'kepala_unit',
    ];

    /**
     * Plain display label per role (single source).
     */
    public static function label(?string $role): string
    {
        return match ($role) {
            'super_admin' => 'Super Admin',
            'pengawas' => 'Pengawas',
            'kepala_desa' => 'Kepala Desa',
            'direktur_bumdes' => 'Direktur BUMDes',
            'sekretaris' => 'Sekretaris',
            'bendahara' => 'Bendahara',
            'kepala_unit' => 'Kepala Unit',
            default => 'Tidak ada Role',
        };
    }

    /**
     * Roles present in DB, in canonical order.
     *
     * @return array<int, string>
     */
    public static function existingOrdered(): array
    {
        $rank = array_flip(self::ORDER);
        $roles = Role::pluck('name')->all();
        usort($roles, fn ($a, $b) => ($rank[$a] ?? 99) <=> ($rank[$b] ?? 99));

        return array_values($roles);
    }

    /**
     * CASE sort expression for a user's first role.
     *
     * @param  array<int, string>|null  $order
     * @return array{0: string, 1: array<int, string>}
     */
    public static function caseSql(string $modelClass, ?array $order = null): array
    {
        $order ??= self::ORDER;
        $whens = [];

        foreach (array_values($order) as $i => $role) {
            $whens[] = "WHEN '{$role}' THEN ".($i + 1);
        }

        $sql = 'CASE (SELECT r.name FROM roles AS r INNER JOIN model_has_roles AS mhr ON mhr.role_id = r.id WHERE mhr.model_id = users.id AND mhr.model_type = ? LIMIT 1) '.implode(' ', $whens).' ELSE '.(count($order) + 1).' END';

        return [$sql, [$modelClass]];
    }
}
