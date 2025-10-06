<?php
namespace App\Services;

class SRP6Service
{
    protected const G = 7;
    protected const N_HEX = '894B645E89E1535BBDAD5B8B290650530801B18EBFBF5E8FAB3C82872A3E9BB7';

    /**
     * Генерация случайного соли
     */

    public function generateSalt(): string
    {
        return random_bytes(32); // Генерация 32-битного случайного числа
    }

    /**
     * Вычисление верификатора по паролю и соли
    */

    public function calculateVerifier(string $username, string $password, string $salt): string
    {
        $username = strtoupper($username);
        $password = strtoupper($password);

        // H1 = SHA1(USERNAME:PASSWORD)
        $h1 = hash('sha1', $username . ':' . $password, true);

        // H2 = SHA1(salt | H1)
        $h2 = hash('sha1', $salt . $h1, true);

        // Преобразование в GMP-целое (little-endian)
        $x = gmp_import($h2, 1, GMP_LSW_FIRST);

        // N and g
        $N = gmp_init(self::N_HEX, 16);
        $g = gmp_init(self::G);

        // v = g^x mod N
        $v = gmp_powm($g, $x, $N);

        //Возвращение в виде little-endian + padding до 32 байт
        $verifier = gmp_export($v, 1, GMP_LSW_FIRST);
        return str_pad($verifier, 32, "\0", STR_PAD_RIGHT);
    }

    /**
     * Проверка пароля по верификатору
    */
    public function verifyPassword(string $username, string $password, string $salt, string $storedVerifier): bool
    {
        $computedVerifier = $this->calculateVerifier($username, $password, $salt);
        return hash_equals($computedVerifier, $storedVerifier);
    }
}