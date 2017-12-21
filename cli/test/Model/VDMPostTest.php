<?php
namespace Scrappy\Model;

use DateTime;
use MongoDB\BSON\UTCDateTime;
use PHPUnit\Framework\TestCase;

class VDMPostTest extends TestCase
{
    private $post;

    /**
     * @before
     */
    public function setup()
    {
        $this->post = new VDMPost(
            "Blabla VDM",
            "Me",
            DateTime::createFromFormat(DATE_ISO8601, "2017-12-19T22:30:00+0000")
        );
    }

    public function testCanSerialize()
    {
        $this->assertEquals(
            $this->post->bsonSerialize(),
            array(
                '_id' => '1acf2fb8ef0f3fd908c7de2ef801db1879778d87',
                'content' => 'Blabla VDM',
                'author' => 'Me',
                'date' => '2017-12-19T22:30:00Z',
                'timestamp' => new UTCDateTime(1513722600000)
            )
        );
    }
}