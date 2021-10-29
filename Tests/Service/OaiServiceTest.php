<?php

namespace Subugoe\OaiBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Subugoe\OaiBundle\Service\OaiService;

class OaiServiceTest extends TestCase
{
    /**
     * @var OaiService
     */
    protected $fixture;

    public function setUp()
    {
        $this->fixture = $this
            ->getMockBuilder(OaiService::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
