<?php

namespace tests\Service;

use PHPUnit\Framework\TestCase;
use Subugoe\OaiBundle\Service\OaiService;

class OaiServiceTest extends TestCase
{
    protected OaiService|\PHPUnit\Framework\MockObject\MockObject $fixture;

    public function setUp(): void
    {
        $this->fixture = $this
            ->getMockBuilder(OaiService::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
