<?php

declare(strict_types=1);

namespace App\Domain\Order\Repository;

use App\Domain\Order\Entity\Order;

/**
 * 発注書集約の永続化。実装は Infrastructure 層に置く。
 *
 * 一覧を返すメソッドは明細も含めて組み立てる。一覧画面が合計金額を表示し、
 * 合計は明細から導出されるため (ヘッダーに保存された値ではなく) 明細が要る。
 * 実装側は N+1 にならないよう明細をまとめて引くこと。
 */
interface OrderRepositoryInterface
{
    /** ごみ箱に入っていない発注書 @return list<Order> */
    public function listActive(): array;

    /** ごみ箱の発注書 @return list<Order> */
    public function listTrashed(): array;

    public function findById(int $id): ?Order;

    /**
     * 新規なら採番して登録、既存なら更新する。明細は洗い替え。
     * 採番された id は渡した集約にも書き戻る。
     */
    public function save(Order $order): Order;

    /** 物理削除 (ごみ箱を空にする操作用) */
    public function delete(int $id): void;
}
