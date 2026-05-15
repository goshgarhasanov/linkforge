<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Support\Exceptions\HttpException;
use Illuminate\Database\Capsule\Manager as DB;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final class EmailVerificationService
{
    private const TOKEN_TTL_HOURS = 24;

    public function __construct(
        private readonly ?MailerInterface $mailer = null,
    ) {
    }

    public function sendVerification(User $user): string
    {
        $plain = bin2hex(random_bytes(32));
        $hash  = hash('sha256', $plain);

        DB::table('email_verifications')->insert([
            'user_id'    => $user->id,
            'token_hash' => $hash,
            'expires_at' => (new \DateTimeImmutable())->modify('+' . self::TOKEN_TTL_HOURS . ' hours'),
            'created_at' => now(),
        ]);

        $url = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8080', '/') . '/verify-email?token=' . $plain;

        if ($this->mailer !== null) {
            try {
                $email = (new Email())
                    ->from($_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@linkforge.io')
                    ->to($user->email)
                    ->subject('LinkForge — E-poçtunuzu təsdiqləyin')
                    ->html(sprintf(
                        '<p>Salam %s,</p><p>Hesabınızı təsdiqləmək üçün aşağıdakı linkə klikləyin:</p><p><a href="%s">%s</a></p><p>Bu link %d saat müddətinə etibarlıdır.</p>',
                        htmlspecialchars($user->name),
                        $url,
                        $url,
                        self::TOKEN_TTL_HOURS,
                    ));

                $this->mailer->send($email);
            } catch (\Throwable) {
            }
        }

        return $url;
    }

    public function verify(string $plainToken): User
    {
        $hash = hash('sha256', $plainToken);

        $row = DB::table('email_verifications')
            ->where('token_hash', $hash)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if ($row === null) {
            throw new HttpException(410, 'Təsdiq linki etibarsızdır və ya vaxtı keçib.');
        }

        $user = User::query()->find($row->user_id);
        if (! $user instanceof User) {
            throw new HttpException(404, 'İstifadəçi tapılmadı.');
        }

        DB::table('email_verifications')
            ->where('id', $row->id)
            ->update(['used_at' => now()]);

        $user->forceFill(['email_verified_at' => now()])->save();

        return $user;
    }
}
