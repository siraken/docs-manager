<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Domain\Learning\Entity\Assignment;
use App\Domain\Learning\Entity\Submission;
use Illuminate\Support\Collection;

final readonly class SubmissionView implements \JsonSerializable
{
    private function __construct(
        public ?int $id,
        public int $assignmentId,
        public string $assignmentTitle,
        public string $courseTitle,
        public int $userId,
        public string $userName,
        public string $statusValue,
        public string $status,
        public ?string $submittedAt,
        public string $submittedAtLabel,
        public ?string $body,
        public ?string $feedback,
        /** 期限に遅れて出したか */
        public bool $isLate,
    ) {
    }

    /**
     * @param array<int, Assignment> $assignmentsById
     * @param array<int, string> $courseTitles 講座 ID => 講座名
     * @param array<int, string> $userNames ユーザー ID => 名前
     */
    public static function fromEntity(
        Submission $submission,
        array $assignmentsById = [],
        array $courseTitles = [],
        array $userNames = [],
    ): self {
        $assignment = $assignmentsById[$submission->assignmentId()] ?? null;

        return new self(
            id: $submission->id(),
            assignmentId: $submission->assignmentId(),
            assignmentTitle: $assignment?->title() ?? '(削除された課題)',
            courseTitle: $assignment === null ? '' : ($courseTitles[$assignment->courseId()] ?? ''),
            userId: $submission->userId(),
            userName: $userNames[$submission->userId()] ?? '(削除されたユーザー)',
            statusValue: $submission->status()->value,
            status: $submission->status()->label(),
            submittedAt: $submission->submittedAt()?->format('Y-m-d'),
            submittedAtLabel: $submission->submittedAt()?->format('Y/m/d') ?? '-',
            body: $submission->body(),
            feedback: $submission->feedback(),
            // 課題が消えていれば遅延の判定はできない
            isLate: $assignment !== null && $submission->isLate($assignment),
        );
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'assignmentId' => $this->assignmentId,
            'assignmentTitle' => $this->assignmentTitle,
            'courseTitle' => $this->courseTitle,
            'userId' => $this->userId,
            'userName' => $this->userName,
            'statusValue' => $this->statusValue,
            'status' => $this->status,
            'submittedAt' => $this->submittedAt,
            'submittedAtLabel' => $this->submittedAtLabel,
            'body' => $this->body,
            'feedback' => $this->feedback,
            'isLate' => $this->isLate,

            'urls' => $this->id === null ? null : [
                'edit' => route('submissions.edit', ['id' => $this->id]),
                'delete' => route('submissions.delete', ['id' => $this->id]),
            ],
        ];
    }

    /**
     * @param list<Submission> $submissions
     * @param array<int, Assignment> $assignmentsById
     * @param array<int, string> $courseTitles
     * @param array<int, string> $userNames
     * @return Collection<int, self>
     */
    public static function collection(
        array $submissions,
        array $assignmentsById = [],
        array $courseTitles = [],
        array $userNames = [],
    ): Collection {
        return collect($submissions)
            ->map(static fn (Submission $s): self => self::fromEntity($s, $assignmentsById, $courseTitles, $userNames))
            ->values();
    }
}
