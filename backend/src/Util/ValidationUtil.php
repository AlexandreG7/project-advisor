<?php

namespace App\Util;

class ValidationUtil
{
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validateUUID(string $uuid): bool
    {
        $uuidV4Pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
        return preg_match($uuidV4Pattern, $uuid) === 1;
    }

    public static function sanitizeString(string $input, int $maxLength = 255): string
    {
        $sanitized = trim(strip_tags($input));
        return substr($sanitized, 0, $maxLength);
    }

    public static function validateArrayKeys(array $data, array $requiredKeys): array
    {
        $errors = [];

        foreach ($requiredKeys as $key) {
            if (!isset($data[$key]) || empty($data[$key])) {
                $errors[$key] = ucfirst($key) . ' is required';
            }
        }

        return $errors;
    }

    public static function validateArrayInChoice(array $data, string $key, array $choices): bool
    {
        if (!isset($data[$key])) {
            return false;
        }

        $value = $data[$key];
        return in_array($value, $choices, true);
    }
}
