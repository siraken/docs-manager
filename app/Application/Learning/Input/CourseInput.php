<?php

declare(strict_types=1);

namespace App\Application\Learning\Input;

use App\Domain\Learning\ValueObject\ExperiencePoint;

final readonly class CourseInput
{
    public function __construct(
        public string $title,
        public ?string $description,
        public mixed $exp,
        public bool $isPublished,
    ) {
    }

    public function expValue(): ExperiencePoint
    {
        return ExperiencePoint::fromNullable($this->exp);
    }
}
