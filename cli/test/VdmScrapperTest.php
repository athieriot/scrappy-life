<?php

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Scrappy\Command\ListPosts;
use Scrappy\VdmScrapper;

class VdmScrapperTest extends TestCase
{
    private $scrapper;

    /**
     * @before
     */
    public function setup()
    {
        $this->scrapper = new VdmScrapper("vmd.fr", new NullLogger());
    }

    public function testCanParseFrenchDates(): void
    {
        $this->assertEquals(
            "2017-12-15T11:30:00+0000",
            $this->scrapper->parseFrenchDate("vendredi 15 décembre 2017 11:30")
        );
    }

    public function testCanHandleInvalidDate(): void
    {
        $this->assertEquals(
            false,
            $this->scrapper->parseFrenchDate("Not the date you are looking for")
        );
    }

    public function testCanExtractAuthorAndDate(): void
    {
        $cmd = new ListPosts("vdm.fr");

        $this->assertEquals(
            array("Desespoir", "jeudi 14 décembre 2017 22:30"),
            $this->scrapper->extractAuthorAndDate("Par Desespoir -  / jeudi 14 décembre 2017 22:30 /")
        );
    }

    public function testCanHandleInvalidFooter(): void
    {
        $this->assertEquals(
            array(null, null),
            $this->scrapper->extractAuthorAndDate("Not the footer you are looking for")
        );
    }
}