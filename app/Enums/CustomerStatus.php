<?php
namespace App\Enums;

enum CustomerStatus: int
{
    case ACTIVE = 1;
    case INACTIVE = 0;
    case BLACKLISTED = -1;

    public function label(): string
    {
        return match($this) {
            CustomerStatus::ACTIVE => 'Active',
            CustomerStatus::INACTIVE => 'Inactive',
            CustomerStatus::BLACKLISTED => 'Blacklisted',
        };
    }

    /**
     * Map common string/boolean inputs to enum.
     * Returns null if unrecognized.
     */
    public static function fromString(string|int|bool|null $value): ?self
    {
        if (is_bool($value)) {
            return $value ? self::ACTIVE : self::INACTIVE;
        }

        $v = strtolower(trim((string)$value));
        return match($v) {
            '1', 'true', 'active', 'a', 'yes' => self::ACTIVE,
            '0', 'false', 'inactive', 'i', 'no' => self::INACTIVE,
            'blacklisted', 'blacklist', 'b' => self::BLACKLISTED,
            default => null,
        };
    }
}
