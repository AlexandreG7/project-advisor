<?php

namespace App\Entity;

use App\Repository\AdviceRequestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdviceRequestRepository::class)]
#[ORM\Table(name: 'advice_requests')]
class AdviceRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $email;

    #[ORM\Column(type: 'string', length: 255)]
    private string $subject;

    #[ORM\Column(type: 'text')]
    private string $message;

    #[ORM\Column(type: 'string', length: 36, nullable: true)]
    private ?string $recommendationId = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $questionnaireSnapshot = null;

    #[ORM\Column(type: 'string', length: 50)]
    private string $status = 'pending';

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $answeredAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function getRecommendationId(): ?string
    {
        return $this->recommendationId;
    }

    public function setRecommendationId(?string $recommendationId): self
    {
        $this->recommendationId = $recommendationId;
        return $this;
    }

    public function getQuestionnaireSnapshot(): ?string
    {
        return $this->questionnaireSnapshot;
    }

    public function setQuestionnaireSnapshot(?string $questionnaireSnapshot): self
    {
        $this->questionnaireSnapshot = $questionnaireSnapshot;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getAnsweredAt(): ?\DateTime
    {
        return $this->answeredAt;
    }

    public function setAnsweredAt(?\DateTime $answeredAt): self
    {
        $this->answeredAt = $answeredAt;
        return $this;
    }

    public function getQuestionnaireData(): ?array
    {
        if ($this->questionnaireSnapshot === null) {
            return null;
        }
        return json_decode($this->questionnaireSnapshot, true);
    }
}
