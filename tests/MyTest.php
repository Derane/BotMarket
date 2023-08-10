<?php

use PHPUnit\Framework\TestCase;

class MyTest extends TestCase
{
    public function testGetStart()
    {
        $page = 3;
        $per_page = 10;

        $result = getStart($page, $per_page);

        $this->assertEquals(20, $result);
    }
}