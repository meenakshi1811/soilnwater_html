<?php

namespace App\Models\Concerns;

use App\Support\ProfilePhoneNumbers;

trait HasProfilePhoneNumbers
{
    /**
     * @return list<string>
     */
    public function phoneNumbersList(): array
    {
        $stored = $this->phone_numbers ?? null;
        if (is_array($stored) && $stored !== []) {
            return ProfilePhoneNumbers::normalize($stored);
        }

        $legacy = $this->phone_number ?? $this->phone ?? null;

        return ProfilePhoneNumbers::normalize($legacy ? [$legacy] : []);
    }

    public function primaryPhoneNumber(): ?string
    {
        return ProfilePhoneNumbers::primary($this->phoneNumbersList());
    }
}
