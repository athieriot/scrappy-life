<?php

namespace Scrappy\Command;

use PHPUnit\Framework\TestCase;

class LoadPostsTest extends TestCase
{
    public function testSetup()
    {
        $this->assertNotNull(new LoadPosts("vmd.fr", "mongodb://localhost:27017", "test"));
    }
}
