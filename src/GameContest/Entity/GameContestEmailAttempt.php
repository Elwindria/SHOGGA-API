<?php

namespace App\GameContest\Entity;

use App\GameContest\Repository\GameContestEmailAttemptRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameContestEmailAttemptRepository::class)]
#[ORM\Table(name: 'game_contest_email_attempt')]
#[ORM\UniqueConstraint(name: 'UNIQ_GAME_CONTEST_EMAIL_ATTEMPT_EMAIL', fields: ['email'])]
class GameContestEmailAttempt
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private string $email;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(string $email)
    {
        $this->email = strtolower(trim($email));
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}