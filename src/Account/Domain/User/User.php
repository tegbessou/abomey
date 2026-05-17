<?php

declare(strict_types=1);

namespace App\Account\Domain\User;

use App\Shared\Domain\EventRecording;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
#[ORM\UniqueConstraint(name: 'users_external_identifier_unique', columns: ['external_identifier'])]
final class User
{
    use EventRecording;

    #[ORM\Column(name: 'privacy_consent_version', type: Types::STRING, length: 50, nullable: true, enumType: PrivacyPolicyVersion::class)]
    private ?PrivacyPolicyVersion $privacyConsentVersion = null;

    #[ORM\Column(name: 'privacy_consent_accepted_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $privacyConsentAcceptedAt = null;

    private function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'user_id', length: 36)]
        private readonly UserId $id,
        #[ORM\Column(type: Types::STRING, length: 255)]
        private readonly string $externalIdentifier,
        #[ORM\Column(type: 'email', length: 255)]
        private Email $email,
        #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
        private ?string $name,
    ) {}

    public static function register(
        UserId $id,
        string $externalIdentifier,
        Email $email,
        ?string $name,
    ): self {
        return new self($id, $externalIdentifier, $email, $name);
    }

    public function syncFromProvider(Email $email, ?string $name): void
    {
        $this->email = $email;
        $this->name = $name;
    }

    public function acceptPrivacyPolicy(PrivacyPolicyVersion $version, \DateTimeImmutable $at): void
    {
        $this->privacyConsentVersion = $version;
        $this->privacyConsentAcceptedAt = $at;
    }

    public function delete(): void
    {
        $this->recordEvent(new UserDeleted($this->id->toString()));
    }

    public function getId(): UserId
    {
        return $this->id;
    }

    public function getExternalIdentifier(): string
    {
        return $this->externalIdentifier;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getPrivacyConsent(): ?PrivacyConsent
    {
        if (null === $this->privacyConsentVersion || null === $this->privacyConsentAcceptedAt) {
            return null;
        }

        return PrivacyConsent::for($this->privacyConsentVersion, $this->privacyConsentAcceptedAt);
    }
}
