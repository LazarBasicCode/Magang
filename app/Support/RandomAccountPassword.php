<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class RandomAccountPassword
{
    public const LENGTH = 6;

    private const LOWER = 'abcdefghijkmnopqrstuvwxyz';

    private const UPPER = 'ABCDEFGHJKLMNPQRSTUVWXYZ';

    private const DIGIT = '23456789';

    private const SYMBOL = '!@#$%&*?+=';

    public static function isValid(string $password): bool
    {
        return strlen($password) === self::LENGTH
            && preg_match('/[a-z]/', $password) === 1
            && preg_match('/[A-Z]/', $password) === 1
            && preg_match('/[2-9]/', $password) === 1
            && preg_match('/[!@#$%&*?+=]/', $password) === 1
            && strpbrk($password[0], self::SYMBOL) === false;
    }

    public static function generateUnique(): string
    {
        $hashes = User::query()->pluck('password');

        for ($attempt = 0; $attempt < 100; $attempt++) {
            $password = self::generate();
            $used = false;

            foreach ($hashes as $hash) {
                if (Hash::check($password, $hash)) {
                    $used = true;
                    break;
                }
            }

            if (! $used) {
                return $password;
            }
        }

        throw new RuntimeException('Tidak dapat membuat password unik. Silakan coba lagi.');
    }

    public static function isUsedByAnotherUser(string $password, int $userId): bool
    {
        foreach (User::query()->where('id', '!=', $userId)->pluck('password') as $hash) {
            if (Hash::check($password, $hash)) {
                return true;
            }
        }

        return false;
    }

    private static function generate(): string
    {
        $symbols = self::SYMBOL;
        $characters = [
            self::pick(self::LOWER),
            self::pick(self::UPPER),
            self::pick(self::DIGIT),
            self::pick($symbols),
            self::pick(self::LOWER.self::UPPER.self::DIGIT.$symbols),
            self::pick(self::LOWER.self::UPPER.self::DIGIT.$symbols),
        ];

        for ($i = count($characters) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$characters[$i], $characters[$j]] = [$characters[$j], $characters[$i]];
        }

        if (strpbrk($characters[0], $symbols) !== false) {
            foreach ($characters as $i => $character) {
                if (strpbrk($character, $symbols) === false) {
                    [$characters[0], $characters[$i]] = [$characters[$i], $characters[0]];
                    break;
                }
            }
        }

        return implode('', $characters);
    }

    private static function pick(string $pool): string
    {
        return $pool[random_int(0, strlen($pool) - 1)];
    }
}
