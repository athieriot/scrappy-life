<?php

namespace Scrappy\Command;

use PHPUnit\Framework\TestCase;

class ListPostsTest extends TestCase
{
    public function testSetup()
    {
        $this->assertNotNull(new ListPosts("vmd.fr"));
    }
}
