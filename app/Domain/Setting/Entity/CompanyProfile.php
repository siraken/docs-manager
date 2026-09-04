<?php

declare(strict_types=1);

namespace App\Domain\Setting\Entity;

use App\Domain\Shared\ValueObject\Money;

/**
 * 自社情報 (settings テーブル)。発注書 PDF の差出人欄と社印に使う。
 *
 * 移行前は PDF 生成メソッドの中に会社名・住所・電話番号が直書きされていて、
 * 「自社設定をマスタから取ってくる」という TODO が残っていた。settings テーブルは
 * 最初から存在していたが、参照する画面もコードも無かった。
 *
 * 設定が 1 件も無い環境でも PDF が出せるよう、default() に移行前の直書き値を
 * 既定値として持たせてある。
 */
final class CompanyProfile
{
    private function __construct(
        private ?int $id,
        private string $name,
        private string $nameEn,
        private string $zipcode,
        private string $address,
        private string $representative,
        private string $telNo,
        private ?\DateTimeImmutable $established,
        private ?Money $capital,
        private string $bank,
        private string $logoUrl,
        private string $companyStampUrl,
        private string $representativeStampUrl,
        private string $applyStampUrl,
    ) {
    }

    public static function create(
        string $name,
        string $zipcode,
        string $address,
        string $representative,
        string $telNo,
        string $nameEn = '',
        ?\DateTimeImmutable $established = null,
        ?Money $capital = null,
        string $bank = '',
        string $logoUrl = 'img/Logo.png',
        string $companyStampUrl = 'img/CompanyStamp.png',
        string $representativeStampUrl = '',
        string $applyStampUrl = '',
    ): self {
        return new self(null, $name, $nameEn, $zipcode, $address, $representative, $telNo, $established, $capital, $bank, $logoUrl, $companyStampUrl, $representativeStampUrl, $applyStampUrl);
    }

    public static function reconstitute(
        int $id,
        string $name,
        string $nameEn,
        string $zipcode,
        string $address,
        string $representative,
        string $telNo,
        ?\DateTimeImmutable $established,
        ?Money $capital,
        string $bank,
        string $logoUrl,
        string $companyStampUrl,
        string $representativeStampUrl,
        string $applyStampUrl,
    ): self {
        return new self($id, $name, $nameEn, $zipcode, $address, $representative, $telNo, $established, $capital, $bank, $logoUrl, $companyStampUrl, $representativeStampUrl, $applyStampUrl);
    }

    /**
     * 設定が未登録のときに使う既定値。
     * 移行前に OrderController::pdf() へ直書きされていた文字列をそのまま持ってきている。
     *
     * TODO: 設定画面から保存すればこの既定値は使われなくなる。実運用の値を
     *       登録したらこのメソッドは消してよい。
     */
    public static function default(): self
    {
        return new self(
            id: null,
            name: 'Novalumo合同会社',
            nameEn: '',
            zipcode: '000-000',
            address: "〇〇県〇〇市１行目\n２行目001号室",
            representative: '',
            telNo: '000-0000-0000',
            established: null,
            capital: null,
            // 振込先は既定値を持たない。未設定なら PDF に振込先欄を出さない
            bank: '',
            logoUrl: 'img/Logo.png',
            companyStampUrl: 'img/CompanyStamp.png',
            representativeStampUrl: '',
            applyStampUrl: '',
        );
    }

    public function update(
        string $name,
        string $nameEn,
        string $zipcode,
        string $address,
        string $representative,
        string $telNo,
        ?\DateTimeImmutable $established,
        ?Money $capital,
        string $bank,
        string $logoUrl,
        string $companyStampUrl,
        string $representativeStampUrl,
        string $applyStampUrl,
    ): void {
        $this->name = $name;
        $this->nameEn = $nameEn;
        $this->zipcode = $zipcode;
        $this->address = $address;
        $this->representative = $representative;
        $this->telNo = $telNo;
        $this->established = $established;
        $this->capital = $capital;
        $this->bank = $bank;
        $this->logoUrl = $logoUrl;
        $this->companyStampUrl = $companyStampUrl;
        $this->representativeStampUrl = $representativeStampUrl;
        $this->applyStampUrl = $applyStampUrl;
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function nameEn(): string
    {
        return $this->nameEn;
    }

    public function zipcode(): string
    {
        return $this->zipcode;
    }

    public function established(): ?\DateTimeImmutable
    {
        return $this->established;
    }

    public function capital(): ?Money
    {
        return $this->capital;
    }

    /** 振込先 (銀行名・支店名・口座番号を改行で並べたもの) */
    public function bank(): string
    {
        return $this->bank;
    }

    /**
     * 振込先を行に割る。PDF は 1 行ずつ座標を指定して描くため。
     *
     * @return list<string>
     */
    public function bankLines(): array
    {
        return self::toLines($this->bank);
    }

    public function address(): string
    {
        return $this->address;
    }

    /**
     * PDF の差出人欄は 1 行ずつ座標を指定して描くため、住所を行に割る。
     *
     * @return list<string>
     */
    public function addressLines(): array
    {
        return self::toLines($this->address);
    }

    /**
     * 改行区切りの文字列を、空行を除いた行の配列にする。
     *
     * @return list<string>
     */
    private static function toLines(string $value): array
    {
        return array_values(array_filter(
            array_map(trim(...), preg_split('/\R/', $value) ?: []),
            static fn (string $line): bool => $line !== '',
        ));
    }

    public function representative(): string
    {
        return $this->representative;
    }

    public function telNo(): string
    {
        return $this->telNo;
    }

    public function logoUrl(): string
    {
        return $this->logoUrl;
    }

    public function companyStampUrl(): string
    {
        return $this->companyStampUrl;
    }

    public function representativeStampUrl(): string
    {
        return $this->representativeStampUrl;
    }

    public function applyStampUrl(): string
    {
        return $this->applyStampUrl;
    }
}
