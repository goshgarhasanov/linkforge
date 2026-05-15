<?php

declare(strict_types=1);

namespace App\Enums;

enum AuditAction: string
{
    case UserCreated      = 'user.created';
    case UserUpdated      = 'user.updated';
    case UserRoleChanged  = 'user.role_changed';
    case UserBanned       = 'user.banned';
    case UserUnbanned     = 'user.unbanned';
    case UserDeleted      = 'user.deleted';

    case LinkFlagged      = 'link.flagged';
    case LinkUnflagged    = 'link.unflagged';
    case LinkDeactivated  = 'link.deactivated';
    case LinkActivated    = 'link.activated';
    case LinkDeleted      = 'link.deleted';

    case AdminLogin       = 'admin.login';
    case AdminSettings    = 'admin.settings_changed';

    public function label(): string
    {
        return match ($this) {
            self::UserCreated     => 'İstifadəçi yaradıldı',
            self::UserUpdated     => 'İstifadəçi yeniləndi',
            self::UserRoleChanged => 'Rol dəyişdirildi',
            self::UserBanned      => 'İstifadəçi bloklandı',
            self::UserUnbanned    => 'İstifadəçi bloku açıldı',
            self::UserDeleted     => 'İstifadəçi silindi',
            self::LinkFlagged     => 'Link işarələndi',
            self::LinkUnflagged   => 'Link işarəsi götürüldü',
            self::LinkDeactivated => 'Link deaktiv edildi',
            self::LinkActivated   => 'Link aktiv edildi',
            self::LinkDeleted     => 'Link silindi',
            self::AdminLogin      => 'Admin girişi',
            self::AdminSettings   => 'Sistem parametrləri dəyişdirildi',
        };
    }
}
