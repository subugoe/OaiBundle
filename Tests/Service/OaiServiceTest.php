<?php

namespace Subugoe\OaiBundle\Tests\Service;

use Subugoe\OaiBundle\Service\OaiService;
use PHPUnit\Framework\TestCase;

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
