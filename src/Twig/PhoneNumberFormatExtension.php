<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class PhoneNumberFormatExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('phone_format', [$this, 'formatPhoneNumber']),
        ];
    }

    /**
     * Formats a phone number for better readability.
     * Example: 0612345678 => 061 234 5678 or similar, depending on length.
     */
    public function formatPhoneNumber(?string $number): string
    {
        if (empty($number)) {
            return '';
        }

        // Remove all non-digit characters
        $digits = preg_replace('/\D+/', '', $number);

        // Example for Serbian numbers: 0612345678 -> 061 234 5678
        // Adjust grouping as needed for your locale
        if (10 === strlen($digits)) {
            // e.g. 06X XXX XXXX
            return preg_replace('/^(\d{3})(\d{3})(\d{4})$/', '$1 $2 $3', $digits);
        } elseif (9 === strlen($digits)) {
            // e.g. 06X XXX XXX
            return preg_replace('/^(\d{3})(\d{3})(\d{3})$/', '$1 $2 $3', $digits);
        } else {
            // fallback: group by 3s
            return trim(chunk_split($digits, 3, ' '));
        }
    }
}
