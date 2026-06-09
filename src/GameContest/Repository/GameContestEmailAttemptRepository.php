<?php

namespace App\GameContest\Repository;

use App\GameContest\Entity\GameContestEmailAttempt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class GameContestEmailAttemptRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameContestEmailAttempt::class);
    }

    public function existsForEmail(string $email): bool
    {
        return $this->findOneBy([
            'email' => strtolower(trim($email)),
        ]) !== null;
    }

    public function deleteAll(): int
    {
        return $this->createQueryBuilder('attempt')
            ->delete()
            ->getQuery()
            ->execute();
    }
}