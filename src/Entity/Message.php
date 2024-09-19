<?php

namespace App\Entity;

use App\Enum\MessageStatus;
use App\Repository\MessageRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Message
{
	#[ORM\Id]
	#[ORM\GeneratedValue(strategy: 'AUTO')]
	#[ORM\Column(type: Types::INTEGER)]
	#[Groups(['message:read'])]
	private ?int $id = null;

	#[ORM\Column(type: Types::GUID, unique: true)]
	#[Groups(['message:read'])]
	private string $uuid;

	#[ORM\Column(length: 255)]
	#[Assert\NotBlank]
	#[Assert\Length(max: 255)]
	#[Groups(['message:read'])]
	private string $text;

	#[ORM\Column(length: 255, nullable: true)]
	#[Groups(['message:read'])]
	private ?string $status = null;

	#[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
	#[Groups(['message:read'])]
	private DateTimeImmutable $createdAt;

	public function __construct()
	{
		$this->uuid = Uuid::v6()->toRfc4122();
	}

	#[ORM\PrePersist]
	public function onPrePersist(): void
	{
		$this->createdAt = new DateTimeImmutable();
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getUuid(): string
	{
		return $this->uuid;
	}

	public function setUuid(string $uuid): self
	{
		if (!Uuid::isValid($uuid)) {
			throw new \InvalidArgumentException('Invalid UUID format');
		}
		$this->uuid = $uuid;

		return $this;
	}

	public function getText(): string
	{
		return $this->text;
	}

	public function setText(string $text): self
	{
		$this->text = $text;

		return $this;
	}

	public function getStatus(): ?string
	{
		return $this->status;
	}

	public function setStatus(MessageStatus $status): self
	{
		$this->status = $status->value;
		return $this;
	}

	public function getCreatedAt(): DateTimeImmutable
	{
		return $this->createdAt;
	}
}
