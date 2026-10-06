<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class ProfilePhoneNumbers
{
    public const ITEM_REGEX = '/^[0-9]{10,15}$/';

    /**
     * @return array<string, mixed>
     */
    public static function validationRules(bool $required = true, int $maxItems = 10): array
    {
        return [
            'phone_numbers' => [
                Rule::requiredIf($required),
                'array',
                Rule::when($required, ['min:1']),
                'max:'.$maxItems,
            ],
            'phone_numbers.*' => [
                'required',
                'string',
                'regex:'.self::ITEM_REGEX,
                'distinct:strict',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function validationMessages(): array
    {
        return [
            'phone_numbers.required' => 'Please enter at least one phone number.',
            'phone_numbers.min' => 'Please enter at least one phone number.',
            'phone_numbers.max' => 'You can add up to :max phone numbers.',
            'phone_numbers.*.required' => 'Each phone number is required.',
            'phone_numbers.*.regex' => 'Each phone number must contain only digits and be between 10 and 15 characters.',
            'phone_numbers.*.distinct' => 'Phone numbers must be unique.',
        ];
    }

    /**
     * @param  array<int, mixed>|null  $values
     * @return list<string>
     */
    public static function normalize(?array $values): array
    {
        $normalized = [];

        foreach ($values ?? [] as $value) {
            $digits = preg_replace('/\D+/', '', (string) $value) ?? '';
            if ($digits === '') {
                continue;
            }

            if (! in_array($digits, $normalized, true)) {
                $normalized[] = $digits;
            }
        }

        return $normalized;
    }

    /**
     * @param  array<int, mixed>|null  $values
     */
    public static function primary(?array $values): ?string
    {
        $list = self::normalize($values);

        return $list[0] ?? null;
    }

    /**
     * @return list<string>
     */
    public static function forForm(?Model $entity = null, ?Model $user = null): array
    {
        $fromOld = old('phone_numbers');
        if (is_array($fromOld)) {
            $list = self::normalize($fromOld);

            return $list !== [] ? $list : [''];
        }

        if ($entity && method_exists($entity, 'phoneNumbersList')) {
            $list = $entity->phoneNumbersList();
            if ($list !== []) {
                return $list;
            }
        }

        if ($user && method_exists($user, 'phoneNumbersList')) {
            $list = $user->phoneNumbersList();
            if ($list !== []) {
                return $list;
            }
        }

        return [''];
    }

    public static function applyToUser(Model $user, array $phones): bool
    {
        $phones = self::normalize($phones);
        $primary = self::primary($phones);
        $previousPrimary = $user->phone_number ?? null;

        $user->phone_numbers = $phones;
        if ($primary !== null) {
            $user->phone_number = $primary;
        }

        return $previousPrimary !== $primary;
    }

    public static function applyToPhoneEntity(Model $entity, array $phones): void
    {
        $phones = self::normalize($phones);
        $primary = self::primary($phones);

        if (in_array('phone_numbers', $entity->getFillable(), true)) {
            $entity->phone_numbers = $phones;
        }

        if ($primary !== null && in_array('phone', $entity->getFillable(), true)) {
            $entity->phone = $primary;
        }
    }
}
