<?php

namespace App\Data\DTO\Registration;

readonly class GuardianDTO
{
    public function __construct(
        public string $first_name,
        public string $last_name,
        public ?string $email = null,
        public ?string $phone = null,
        /** @var array{street?:string,city?:string,zip?:string}|null */
        public ?array $address = null,
        public ?string $preferred_contact = null, // 'email'|'sms'|'phone'
        public ?string $notes = null,
        public bool $newsletter_consent = false
    ) {}
}
