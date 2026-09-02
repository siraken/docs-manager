<?php

declare(strict_types=1);

namespace App\Application\Academy\Input;

final readonly class AcademyInquiryInput
{
    public function __construct(
        public string $name,
        public string $email,
        public string $inquiry,
    ) {
    }
}
