<?php

declare(strict_types=1);

namespace App\Application\Learning\Input;

use App\Application\Shared\DateParser;

final readonly class AssignmentInput
{
    public function __construct(
        public int $courseId,
        public string $title,
        public ?string $description,
        public mixed $dueOn,
    ) {
    }

    public function dueOnValue(): ?\DateTimeImmutable
    {
        return DateParser::parseNullable($this->dueOn, '提出期限');
    }
}
