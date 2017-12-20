<?php

namespace Scrappy\Command;

use PHPUnit\Framework\TestCase;

class LoadPostsTest extends TestCase
{
    private $cmd;

    /**
     * @before
     */
    public function setup()
    {
        $this->cmd = new LoadPosts("vmd.fr", "mongodb://localhost:27017", "test");
    }

    public function testCanAddId()
    {
        $result = $this->cmd->addId(array(
            "author" => "Test",
            "date" => "2017-08-08"
        ));

        $this->assertEquals("b124f706ac4c4b09e0a03a14c71492409df846be", $result["_id"]);
    }
}
