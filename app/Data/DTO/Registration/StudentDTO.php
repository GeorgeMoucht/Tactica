<?php

namespace App\Data\DTO\Registration;

readonly class StudentDTO
{
    public function __construct(
        public string $first_name,
        public string $last_name,
        public string $birthdate, // yyyy-mm-dd
        public ?string $email = null,
        public ?string $phone = null,
        /** @var array{street?:string,city?:string,zip?:string}|null */
        public ?array $address = null,
        public ?string $level = null,        // 'beginner'|'intermediate'|'advanced'
        /** @var string[] */
        public array $interests = [],
        public ?string $notes = null,
        public ?string $medical_note = null,
        public bool $consent_media = false,
    ) {}
}
