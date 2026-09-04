<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Domain\Learning\Entity\Course;
use Illuminate\Support\Collection;

final readonly class CourseView implements \JsonSerializable
{
    private function __construct(
        public ?int $id,
        public string $title,
        public ?string $description,
        public int $exp,
        public string $expLabel,
        public bool $isPublished,
    ) {
    }

    public static function fromEntity(Course $course): self
    {
        return new self(
            id: $course->id(),
            title: $course->title(),
            description: $course->description(),
            exp: $course->exp()->value,
            expLabel: $course->exp()->format(),
            isPublished: $course->isPublished(),
        );
    }

    /**
     * Inertia の props 用。
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'exp' => $this->exp,
            'expLabel' => $this->expLabel,
            'isPublished' => $this->isPublished,

            'urls' => $this->id === null ? null : [
                'edit' => route('courses.edit', ['id' => $this->id]),
                'delete' => route('courses.delete', ['id' => $this->id]),
            ],
        ];
    }

    /**
     * セレクトの選択肢。受講記録フォームが使う。
     *
     * @param list<Course> $courses
     * @return list<array{id: int|null, name: string, exp: int}>
     */
    public static function options(array $courses): array
    {
        return array_map(
            static fn (Course $course): array => [
                'id' => $course->id(),
                'name' => $course->title(),
                'exp' => $course->exp()->value,
            ],
            $courses,
        );
    }

    /**
     * @param list<Course> $courses
     * @return Collection<int, self>
     */
    public static function collection(array $courses): Collection
    {
        return collect($courses)->map(self::fromEntity(...))->values();
    }
}
