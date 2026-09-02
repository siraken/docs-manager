<?php

declare(strict_types=1);

namespace App\Domain\Order\Entity;

use App\Domain\Order\ValueObject\IssueStatus;
use App\Domain\Order\ValueObject\OrderNo;
use App\Domain\Order\ValueObject\OrderStatus;
use App\Domain\Order\ValueObject\StatusKind;
use App\Domain\Shared\ValueObject\Money;

/**
 * 発注書。ヘッダー (order_headers) と明細 (order_details) をまとめた集約ルート。
 *
 * 明細は order_details.slip_id で紐づく (order_header_id ではない)。
 * 集約の外から明細だけを更新することはできず、必ずこのエンティティ経由で扱う。
 *
 * 削除は Laravel の SoftDeletes ではなく is_deleted カラムの手動運用。
 * 既存データとの互換のためこの方式を維持している。
 */
final class Order
{
    /** @param list<OrderLine> $lines */
    private function __construct(
        private ?int $id,
        private int $customerId,
        private ?string $responsible,
        private ?string $honorTitle,
        private \DateTimeImmutable $issuedDate,
        private ?\DateTimeImmutable $expDate,
        private OrderNo $orderNo,
        private ?string $title,
        private ?string $remarks,
        private IssueStatus $issueStatus,
        private OrderStatus $orderStatus,
        private bool $isDeleted,
        private bool $isConverted,
        private ?string $note,
        private array $lines,
    ) {
    }

    /**
     * 新規作成。各フラグは初期値で始まる。
     *
     * @param list<OrderLine> $lines
     */
    public static function create(
        int $customerId,
        ?string $responsible,
        ?string $honorTitle,
        \DateTimeImmutable $issuedDate,
        ?\DateTimeImmutable $expDate,
        OrderNo $orderNo,
        ?string $title,
        ?string $remarks,
        array $lines,
    ): self {
        return new self(
            id: null,
            customerId: $customerId,
            responsible: $responsible,
            honorTitle: $honorTitle,
            issuedDate: $issuedDate,
            expDate: $expDate,
            orderNo: $orderNo,
            title: $title,
            remarks: $remarks,
            issueStatus: IssueStatus::NotIssued,
            orderStatus: OrderStatus::NotOrdered,
            isDeleted: false,
            isConverted: false,
            note: null,
            lines: array_values($lines),
        );
    }

    /**
     * 永続化層からの復元。リポジトリの実装以外から呼ばないこと。
     *
     * @param list<OrderLine> $lines
     */
    public static function reconstitute(
        int $id,
        int $customerId,
        ?string $responsible,
        ?string $honorTitle,
        \DateTimeImmutable $issuedDate,
        ?\DateTimeImmutable $expDate,
        OrderNo $orderNo,
        ?string $title,
        ?string $remarks,
        IssueStatus $issueStatus,
        OrderStatus $orderStatus,
        bool $isDeleted,
        bool $isConverted,
        ?string $note,
        array $lines,
    ): self {
        return new self(
            id: $id,
            customerId: $customerId,
            responsible: $responsible,
            honorTitle: $honorTitle,
            issuedDate: $issuedDate,
            expDate: $expDate,
            orderNo: $orderNo,
            title: $title,
            remarks: $remarks,
            issueStatus: $issueStatus,
            orderStatus: $orderStatus,
            isDeleted: $isDeleted,
            isConverted: $isConverted,
            note: $note,
            lines: array_values($lines),
        );
    }

    // --- 参照 -------------------------------------------------------------

    public function id(): ?int
    {
        return $this->id;
    }

    public function customerId(): int
    {
        return $this->customerId;
    }

    public function responsible(): ?string
    {
        return $this->responsible;
    }

    public function honorTitle(): ?string
    {
        return $this->honorTitle;
    }

    public function issuedDate(): \DateTimeImmutable
    {
        return $this->issuedDate;
    }

    public function expDate(): ?\DateTimeImmutable
    {
        return $this->expDate;
    }

    public function orderNo(): OrderNo
    {
        return $this->orderNo;
    }

    public function title(): ?string
    {
        return $this->title;
    }

    public function remarks(): ?string
    {
        return $this->remarks;
    }

    public function issueStatus(): IssueStatus
    {
        return $this->issueStatus;
    }

    public function orderStatus(): OrderStatus
    {
        return $this->orderStatus;
    }

    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }

    public function isConverted(): bool
    {
        return $this->isConverted;
    }

    public function note(): ?string
    {
        return $this->note;
    }

    /** @return list<OrderLine> */
    public function lines(): array
    {
        return $this->lines;
    }

    /** 宛名。「顧客名 担当者 御中」の形に組む (空の要素は詰める) */
    public function addresseeLine(string $customerName): string
    {
        return implode(' ', array_filter([
            $customerName,
            $this->responsible,
            $this->honorTitle,
        ], static fn (?string $part): bool => $part !== null && $part !== ''));
    }

    // --- 金額 (明細から導出する) -------------------------------------------

    public function subtotal(): Money
    {
        return array_reduce(
            $this->lines,
            static fn (Money $carry, OrderLine $line): Money => $carry->add($line->subtotal()),
            Money::zero(),
        );
    }

    public function tax(): Money
    {
        return array_reduce(
            $this->lines,
            static fn (Money $carry, OrderLine $line): Money => $carry->add($line->tax()),
            Money::zero(),
        );
    }

    public function total(): Money
    {
        return $this->subtotal()->add($this->tax());
    }

    // --- 状態変更 -----------------------------------------------------------

    /**
     * ヘッダー項目と明細を差し替える。
     *
     * 旧実装は「行ごと物理削除してから作り直す」方式で id が変わっていたが、
     * 発注書番号を外部と共有する運用上 id が変わるのは望ましくないため、
     * 集約の同一性を保ったまま更新する。明細は洗い替え (全削除 → 再作成) で、
     * これはリポジトリ側の実装詳細。
     *
     * @param list<OrderLine> $lines
     */
    public function update(
        int $customerId,
        ?string $responsible,
        ?string $honorTitle,
        \DateTimeImmutable $issuedDate,
        ?\DateTimeImmutable $expDate,
        OrderNo $orderNo,
        ?string $title,
        ?string $remarks,
        array $lines,
    ): void {
        $this->customerId = $customerId;
        $this->responsible = $responsible;
        $this->honorTitle = $honorTitle;
        $this->issuedDate = $issuedDate;
        $this->expDate = $expDate;
        $this->orderNo = $orderNo;
        $this->title = $title;
        $this->remarks = $remarks;
        $this->lines = array_values($lines);
    }

    /** ステータスピルを 1 回押したときの遷移 */
    public function advanceStatus(StatusKind $kind): void
    {
        match ($kind) {
            StatusKind::Issue => $this->issueStatus = $this->issueStatus->next(),
            StatusKind::Order => $this->orderStatus = $this->orderStatus->next(),
        };
    }

    public function trash(): void
    {
        $this->isDeleted = true;
    }

    public function restore(): void
    {
        $this->isDeleted = false;
    }

    public function changeNote(?string $note): void
    {
        $this->note = $note;
    }

    /** リポジトリが採番した id を書き戻す */
    public function assignId(int $id): void
    {
        $this->id = $id;
    }
}
