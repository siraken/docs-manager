<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Domain\Learning\Entity\Assignment;
use Illuminate\Support\Collection;

final readonly class AssignmentView implements \JsonSerializable
{
    private function __construct(
        public ?int $id,
        public int $courseId,
        public string $courseTitle,
        public string $title,
        public ?string $description,
        public ?string $dueOn,
        public string $dueOnLabel,
        /** 今日の時点で期限を過ぎているか */
        public bool $isOverdue,
    ) {
    }

    /** @param array<int, string> $courseTitles 講座 ID => 講座名 */
    public static function fromEntity(Assignment $assignment, array $courseTitles = []): self
    {
        return new self(
            id: $assignment->id(),
            courseId: $assignment->courseId(),
            courseTitle: $courseTitles[$assignment->courseId()] ?? '(削除された講座)',
            title: $assignment->title(),
            description: $assignment->description(),
            dueOn: $assignment->dueOn()?->format('Y-m-d'),
            dueOnLabel: $assignment->dueOn()?->format('Y/m/d') ?? '期限なし',
            isOverdue: $assignment->isOverdueOn(new \DateTimeImmutable()),
        );
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'courseId' => $this->courseId,
            'courseTitle' => $this->courseTitle,
            'title' => $this->title,
            'description' => $this->description,
            'dueOn' => $this->dueOn,
            'dueOnLabel' => $this->dueOnLabel,
            'isOverdue' => $this->isOverdue,

            'urls' => $this->id === null ? null : [
                'edit' => route('assignments.edit', ['id' => $this->id]),
                'delete' => route('assignments.delete', ['id' => $this->id]),
            ],
        ];
    }

    /**
     * セレクトの選択肢。提出物フォームが使う。
     *
     * @param list<Assignment> $assignments
     * @param array<int, string> $courseTitles
     * @return list<array{id: int|null, name: string, course: string}>
     */
    public static function options(array $assignments, array $courseTitles = []): array
    {
        return array_map(
            static fn (Assignment $a): array => [
                'id' => $a->id(),
                'name' => $a->title(),
                'course' => $courseTitles[$a->courseId()] ?? '',
            ],
            $assignments,
        );
    }

    /**
     * @param list<Assignment> $assignments
     * @param array<int, string> $courseTitles
     * @return Collection<int, self>
     */
    public static function collection(array $assignments, array $courseTitles = []): Collection
    {
        return collect($assignments)
            ->map(static fn (Assignment $a): self => self::fromEntity($a, $courseTitles))
            ->values();
    }
}
