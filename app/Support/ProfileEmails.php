<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

/**
 * A profile's contact emails, like its phone numbers (ProfilePhoneNumbers): a list whose first
 * entry is the main one, kept in the profile's `email` too.
 */
class ProfileEmails
{
    public const MAX_ITEMS = 5;

    /**
     * @return array<string, mixed>
     */
    public static function validationRules(int $maxItems = self::MAX_ITEMS): array
    {
        return [
            'emails' => ['required', 'array', 'min:1', 'max:'.$maxItems],
            'emails.*' => ['required', 'string', 'email', 'max:255', 'distinct:ignore_case'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function validationMessages(): array
    {
        return [
            'emails.required' => 'Please enter at least one email address.',
            'emails.min' => 'Please enter at least one email address.',
            'emails.max' => 'You can add up to :max email addresses.',
            'emails.*.required' => 'Each email address is required.',
            'emails.*.email' => 'Each email address must be valid.',
            'emails.*.max' => 'An email address can be up to 255 characters.',
            'emails.*.distinct' => 'Email addresses must be different.',
        ];
    }

    /**
     * Trimmed and lower-cased, blanks and repeats dropped.
     *
     * @param  array<int, mixed>|null  $values
     * @return list<string>
     */
    public static function normalize(?array $values): array
    {
        $normalized = [];

        foreach ($values ?? [] as $value) {
            $email = strtolower(trim((string) $value));
            if ($email !== '' && ! in_array($email, $normalized, true)) {
                $normalized[] = $email;
            }
        }

        return $normalized;
    }

    /**
     * The form's rows: what was just typed, else the saved list, else the account's email.
     *
     * @return list<string>
     */
    public static function forForm(?Model $entity = null, ?Model $user = null): array
    {
        $fromOld = old('emails');
        if (is_array($fromOld)) {
            $list = self::normalize($fromOld);

            return $list !== [] ? $list : [''];
        }

        $list = $entity && method_exists($entity, 'emailsList') ? $entity->emailsList() : [];
        if ($list === [] && filled($user?->email ?? null)) {
            $list = [strtolower((string) $user->email)];
        }

        return $list !== [] ? $list : [''];
    }
}
