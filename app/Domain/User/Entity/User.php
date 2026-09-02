<?php

declare(strict_types=1);

namespace App\Domain\User\Entity;

use App\Domain\User\Service\PasswordHasherInterface;
use App\Domain\User\ValueObject\EmailAddress;
use App\Domain\User\ValueObject\HashedPassword;
use App\Domain\User\ValueObject\NfcCredential;
use App\Domain\User\ValueObject\TwoFactorSecret;
use App\Domain\User\ValueObject\WalletAddress;

/**
 * 業務ツールの利用者。
 *
 * このアプリは Illuminate\Auth を使わず素のセッションで認証しているため、
 * このエンティティは Authenticatable を実装しない。認証の手続きは
 * Application 層の Auth ユースケースが持つ。
 */
final class User
{
    private function __construct(
        private ?int $id,
        private string $name,
        private EmailAddress $email,
        private HashedPassword $password,
        private ?NfcCredential $nfcCredential,
        private ?WalletAddress $walletAddress,
        private ?TwoFactorSecret $twoFactorSecret,
        private readonly ?\DateTimeImmutable $updatedAt,
    ) {
    }

    public static function create(
        string $name,
        EmailAddress $email,
        HashedPassword $password,
    ): self {
        return new self(null, $name, $email, $password, null, null, null, null);
    }

    public static function reconstitute(
        int $id,
        string $name,
        EmailAddress $email,
        HashedPassword $password,
        ?NfcCredential $nfcCredential,
        ?WalletAddress $walletAddress,
        ?TwoFactorSecret $twoFactorSecret,
        ?\DateTimeImmutable $updatedAt,
    ): self {
        return new self($id, $name, $email, $password, $nfcCredential, $walletAddress, $twoFactorSecret, $updatedAt);
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    // --- 参照 -------------------------------------------------------------

    public function id(): ?int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): EmailAddress
    {
        return $this->email;
    }

    public function password(): HashedPassword
    {
        return $this->password;
    }

    public function nfcCredential(): ?NfcCredential
    {
        return $this->nfcCredential;
    }

    public function walletAddress(): ?WalletAddress
    {
        return $this->walletAddress;
    }

    public function twoFactorSecret(): ?TwoFactorSecret
    {
        return $this->twoFactorSecret;
    }

    public function updatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->twoFactorSecret !== null;
    }

    // --- 認証 ---------------------------------------------------------------

    public function verifyPassword(string $plainPassword, PasswordHasherInterface $hasher): bool
    {
        return $hasher->verify($plainPassword, $this->password);
    }

    public function matchesNfc(string $serialNumber, string $pin): bool
    {
        return $this->nfcCredential?->matches($serialNumber, $pin) ?? false;
    }

    public function matchesWallet(string $address): bool
    {
        return $this->walletAddress?->matches($address) ?? false;
    }

    // --- 状態変更 -----------------------------------------------------------

    public function rename(string $name): void
    {
        $this->name = $name;
    }

    public function changeEmail(EmailAddress $email): void
    {
        $this->email = $email;
    }

    public function changePassword(HashedPassword $password): void
    {
        $this->password = $password;
    }

    /** null を渡すと NFC ログインを無効にする */
    public function changeNfcCredential(?NfcCredential $credential): void
    {
        $this->nfcCredential = $credential;
    }

    public function changeWalletAddress(?WalletAddress $address): void
    {
        $this->walletAddress = $address;
    }

    public function enableTwoFactor(TwoFactorSecret $secret): void
    {
        $this->twoFactorSecret = $secret;
    }

    public function disableTwoFactor(): void
    {
        $this->twoFactorSecret = null;
    }
}
