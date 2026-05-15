<?php

declare(strict_types=1);

namespace App\Validators;

use App\Support\Exceptions\ValidationException;
use Respect\Validation\Validator as v;

final class AuthValidator
{
    /**
     * @return array{name: string, email: string, password: string}
     */
    public function validateRegister(array $data): array
    {
        $errors = [];

        $name = trim((string) ($data['name'] ?? ''));
        if (! v::stringType()->length(2, 120)->validate($name)) {
            $errors['name'][] = 'Ad 2 ilə 120 simvol arasında olmalıdır.';
        }

        $email = mb_strtolower(trim((string) ($data['email'] ?? '')));
        if (! v::email()->length(5, 190)->validate($email)) {
            $errors['email'][] = 'Etibarlı e-poçt ünvanı daxil edin.';
        }

        $password = (string) ($data['password'] ?? '');
        if (! v::stringType()->length(8, 128)->validate($password)) {
            $errors['password'][] = 'Şifrə ən azı 8 simvol olmalıdır.';
        } elseif (! preg_match('/[A-Z]/', $password) || ! preg_match('/[a-z]/', $password) || ! preg_match('/\d/', $password)) {
            $errors['password'][] = 'Şifrə həm böyük, həm kiçik hərf, həm də rəqəm ehtiva etməlidir.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return ['name' => $name, 'email' => $email, 'password' => $password];
    }

    /**
     * @return array{email: string, password: string}
     */
    public function validateLogin(array $data): array
    {
        $errors = [];

        $email = mb_strtolower(trim((string) ($data['email'] ?? '')));
        if (! v::email()->validate($email)) {
            $errors['email'][] = 'Etibarlı e-poçt ünvanı daxil edin.';
        }

        $password = (string) ($data['password'] ?? '');
        if ($password === '') {
            $errors['password'][] = 'Şifrə tələb olunur.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return ['email' => $email, 'password' => $password];
    }
}
