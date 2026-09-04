<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Domain\Learning\Entity\Enrollment;
use Illuminate\Support\Collection;

final readonly class EnrollmentView implements \JsonSerializable
{
    private function __construct(
        public ?int $id,
        public int $userId,
        public string $userName,
        public int $courseId,
        public string $courseTitle,
        public int $exp,
        public string $statusValue,
        public string $status,
        public ?string $startedAt,
        public string $startedAtLabel,
        public ?string $completedAt,
        public string $completedAtLabel,
        public ?string $note,
    ) {
    }

    /**
     * @param array<int, string> $userNames ユーザー ID => 名前
     * @param array<int, array{title: string, exp: int}> $courses 講座 ID => 講座名とポイント
     */
    public static function fromEntity(Enrollment $enrollment, array $userNames = [], array $courses = []): self
    {
        $course = $courses[$enrollment->courseId()] ?? null;

        return new self(
            id: $enrollment->id(),
            userId: $enrollment->userId(),
            userName: $userNames[$enrollment->userId()] ?? '(削除されたユーザー)',
            courseId: $enrollment->courseId(),
            courseTitle: $course['title'] ?? '(削除された講座)',
            // 完了していない受講はポイントに数えない
            exp: $enrollment->earnsExperience() ? ($course['exp'] ?? 0) : 0,
            statusValue: $enrollment->status()->value,
            status: $enrollment->status()->label(),
            // 前者はフォームの value、後者は表示に使う
            startedAt: $enrollment->startedAt()?->format('Y-m-d'),
            startedAtLabel: $enrollment->startedAt()?->format('Y/m/d') ?? '-',
            completedAt: $enrollment->completedAt()?->format('Y-m-d'),
            completedAtLabel: $enrollment->completedAt()?->format('Y/m/d') ?? '-',
            note: $enrollment->note(),
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
            'userId' => $this->userId,
            'userName' => $this->userName,
            'courseId' => $this->courseId,
            'courseTitle' => $this->courseTitle,
            'exp' => $this->exp,
            'statusValue' => $this->statusValue,
            'status' => $this->status,
            'startedAt' => $this->startedAt,
            'startedAtLabel' => $this->startedAtLabel,
            'completedAt' => $this->completedAt,
            'completedAtLabel' => $this->completedAtLabel,
            'note' => $this->note,

            'urls' => $this->id === null ? null : [
                'edit' => route('enrollments.edit', ['id' => $this->id]),
                'delete' => route('enrollments.delete', ['id' => $this->id]),
            ],
        ];
    }

    /**
     * @param list<Enrollment> $enrollments
     * @param array<int, string> $userNames
     * @param array<int, array{title: string, exp: int}> $courses
     * @return Collection<int, self>
     */
    public static function collection(array $enrollments, array $userNames = [], array $courses = []): Collection
    {
        return collect($enrollments)
            ->map(static fn (Enrollment $e): self => self::fromEntity($e, $userNames, $courses))
            ->values();
    }
}
