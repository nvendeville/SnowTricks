<?php

namespace App\Tests\Service;

use App\Service\HasMore;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\TestCase;

class HasMoreTest extends TestCase
{
    use HasMore;

    private function mockRepository($arrayResult)
    {
        $repository = $this->createMock(ServiceEntityRepository::class);
        $repository->expects($this->any())
            ->method('findBy')
            ->willReturn($arrayResult);

        return $repository;
    }

    public function testFalseWhenLastQueryCountInferiorLimit()
    {
        $repository = $this->createMock(ServiceEntityRepository::class);
        $receivedResult = $this->hasMore($repository, 3, 5, 5);
        self::assertFalse($receivedResult, "Le lastQueryCount n'est pas inférieur à Limit");
    }

    public function testFalseWhenResultEqualsZero()
    {
        $arrayResult = [];
        $repository = $this->mockRepository($arrayResult);
        $receivedResult = $this->hasMore($repository, 5, 5, 5);
        self::assertFalse($receivedResult, "Le résultat de la requête n'est pas égal à 0");
    }

    public function testTrueWhenResultEqualsOne()
    {
        $arrayResult = ['Result1'];
        $repository = $this->mockRepository($arrayResult);
        $receivedResult = $this->hasMore($repository, 5, 5, 5);
        self::assertTrue($receivedResult, "Le résultat de la requête n'est pas égal à 1");
    }

    public function testTrueWhenResultSuperiorOne()
    {
        $arrayResult = ['Result1', 'Result2'];
        $repository = $this->mockRepository($arrayResult);
        $receivedResult = $this->hasMore($repository, 5, 5, 5);
        self::assertTrue($receivedResult, "Le résultat de la requête n'est pas supérieur à 1");
    }
}
