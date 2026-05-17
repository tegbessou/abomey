<?php

declare(strict_types=1);

namespace App\Tests\Builder\Account;

use App\Account\Domain\User\Email;
use App\Account\Domain\User\PrivacyPolicyVersion;
use App\Account\Domain\User\User;
use App\Account\Domain\User\UserId;

final class UserBuilder
{
    private UserId $id;
    private string $externalIdentifier = 'external-default';
    private Email $email;
    private ?string $name = 'Default Name';
    private ?PrivacyPolicyVersion $consentVersion = null;
    private ?\DateTimeImmutable $consentAcceptedAt = null;

    private function __construct()
    {
        $this->id = UserId::fromString('01900000-0000-7000-8000-000000000000');
        $this->email = Email::fromString('default@example.com');
    }

    public static function aUser(): self
    {
        return new self();
    }

    public function withId(UserId|string $id): self
    {
        $this->id = $id instanceof UserId ? $id : UserId::fromString($id);

        return $this;
    }

    public function withExternalIdentifier(string $externalIdentifier): self
    {
        $this->externalIdentifier = $externalIdentifier;

        return $this;
    }

    public function withEmail(Email|string $email): self
    {
        $this->email = $email instanceof Email ? $email : Email::fromString($email);

        return $this;
    }

    public function named(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function withoutName(): self
    {
        $this->name = null;

        return $this;
    }

    public function havingAcceptedPrivacyPolicy(?PrivacyPolicyVersion $version = null, ?\DateTimeImmutable $at = null): self
    {
        $this->consentVersion = $version ?? PrivacyPolicyVersion::current();
        $this->consentAcceptedAt = $at ?? new \DateTimeImmutable('2026-05-15T12:00:00+00:00');

        return $this;
    }

    public function build(): User
    {
        $user = User::register($this->id, $this->externalIdentifier, $this->email, $this->name);

        if (null !== $this->consentVersion && null !== $this->consentAcceptedAt) {
            $user->acceptPrivacyPolicy($this->consentVersion, $this->consentAcceptedAt);
        }

        return $user;
    }
}
