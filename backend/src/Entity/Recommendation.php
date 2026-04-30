<?php

namespace App\Entity;

use App\Repository\RecommendationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: RecommendationRepository::class)]
#[ORM\Table(name: 'recommendations')]
class Recommendation
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'text')]
    private string $answersJson;

    #[ORM\Column(type: 'text')]
    private string $resultJson;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->id = (string) Uuid::v4();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getAnswersJson(): string
    {
        return $this->answersJson;
    }

    public function setAnswersJson(string $answersJson): self
    {
        $this->answersJson = $answersJson;
        return $this;
    }

    public function getResultJson(): string
    {
        return $this->resultJson;
    }

    public function setResultJson(string $resultJson): self
    {
        $this->resultJson = $resultJson;
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

    public function getAnswers(): array
    {
        return json_decode($this->answersJson, true) ?? [];
    }

    public function getResult(): array
    {
        return json_decode($this->resultJson, true) ?? [];
    }
}
