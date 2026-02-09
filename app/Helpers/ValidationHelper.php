<?php

namespace App\Helpers;

class ValidationHelper
{
    /**
     * Validate UUID
     */
    public static function isValidUuid(string $uuid): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid);
    }

    /**
     * Validate NIS
     */
    public static function isValidNis(string $nis): bool
    {
        return preg_match('/^[0-9]{8,}$/', $nis);
    }

    /**
     * Validate phone number (Indonesia)
     */
    public static function isValidPhone(string $phone): bool
    {
        // Remove spaces and special characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Check if starts with 0 or 62
        return preg_match('/^(0|62)[0-9]{9,12}$/', $phone);
    }

    /**
     * Format phone number
     */
    public static function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (substr($phone, 0, 2) === '62') {
            $phone = '0' . substr($phone, 2);
        }
        
        return $phone;
    }

    /**
     * Validate email
     */
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}
