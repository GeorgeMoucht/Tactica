<?php

namespace App\Data\DTO\Registration;

readonly class CreateRegistrationDTO
{
    /** @param StudentDTO[] $students */
    public function __construct(
        public GuardianDTO $guardian,
        public array $students
    ) {}

    public static function fromArray(array $data): self
    {
        $g = $data['guardian'] ?? [];

        $guardian = new GuardianDTO(
            first_name: $g['first_name'],
            last_name: $g['last_name'],
            email: $g['email'] ?? null,
            phone: $g['phone'] ?? null,
            address: $g['address'] ?? null,                // array{street?,city?,zip?}|null
            preferred_contact: $g['preferred_contact'] ?? null, // string|null
            notes: $g['notes'] ?? null,
            newsletter_consent: (bool)($g['newsletter_consent'] ?? false)
        );

        $students = array_map(function (array $s) {
            return new StudentDTO(
                first_name: $s['first_name'],
                last_name: $s['last_name'],
                birthdate: $s['birthdate'],
                email: $s['email'] ?? null,
                phone: $s['phone'] ?? null,
                address: $s['address'] ?? null,           // array|null
                level: $s['level'] ?? null,               // string|null
                interests: $s['interests'] ?? [],         // string[]
                notes: $s['notes'] ?? null,
                medical_note: $s['medical_note'] ?? null,
                consent_media: (bool)($s['consent_media'] ?? false)
            );
        }, $data['students'] ?? []);

        return new self($guardian, $students);
    }
}
