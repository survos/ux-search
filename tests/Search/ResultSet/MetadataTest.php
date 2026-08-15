<?php

declare(strict_types=1);

namespace Mezcalito\UxSearchBundle\Tests\Search\ResultSet;

use Mezcalito\UxSearchBundle\Search\ResultSet\Hit;
use Mezcalito\UxSearchBundle\Search\ResultSet\ResultSet;
use PHPUnit\Framework\TestCase;

final class MetadataTest extends TestCase
{
    public function testHitMetadataIsOptionalAndMutable(): void
    {
        $hit = new Hit(['id' => 'one'], 0.75);

        self::assertSame([], $hit->getMetadata());
        self::assertSame($hit, $hit->setMetadata(['engine' => 'elasticsearch']));
        self::assertSame(['engine' => 'elasticsearch'], $hit->getMetadata());
    }

    public function testResultSetMetadataIsOptionalAndMutable(): void
    {
        $results = new ResultSet();

        self::assertSame([], $results->getMetadata());
        self::assertSame($results, $results->setMetadata(['took' => 4, 'mode' => 'hybrid']));
        self::assertSame(['took' => 4, 'mode' => 'hybrid'], $results->getMetadata());
    }
}
