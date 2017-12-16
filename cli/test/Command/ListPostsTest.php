<?php

use PHPUnit\Framework\TestCase;
use Scrappy\Command\ListPosts;

class ListPostsTest extends TestCase
{
    private $cmd;

    /**
     * @before
     */
    public function setup()
    {
        $this->cmd = new ListPosts("vmd.fr");
    }

    public function testCanParseFrenchDates(): void
    {
        $this->assertEquals(
            "2017-12-15T11:30:00+0000",
            $this->cmd->parseFrenchDate(ListPosts::VDM_DATE_FORMAT, "vendredi 15 décembre 2017 11:30")
        );
    }

    public function testCanHandleInvalidDate(): void
    {
        $this->assertEquals(
            false,
            $this->cmd->parseFrenchDate(ListPosts::VDM_DATE_FORMAT, "Not the date you are looking for")
        );
    }

    public function testCanExtractAuthorAndDate(): void
    {
        $cmd = new ListPosts("vdm.fr");

        $this->assertEquals(
            array("Desespoir", "jeudi 14 décembre 2017 22:30"),
            $this->cmd->extractAuthorAndDate("Par Desespoir -  / jeudi 14 décembre 2017 22:30 /")
        );
    }

    public function testCanHandleInvalidFooter(): void
    {
        $this->assertEquals(
            array(null, null),
            $this->cmd->extractAuthorAndDate("Not the footer you are looking for")
        );
    }
}