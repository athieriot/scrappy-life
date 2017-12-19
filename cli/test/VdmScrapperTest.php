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
        $this->assertEquals(
            array("Desespoir", "jeudi 14 décembre 2017 22:30"),
            $this->scrapper->extractAuthorAndDate("Par Desespoir -  / jeudi 14 décembre 2017 22:30 /")
        );
    }

    public function testCanHandleInvalidFooter(): void
    {
        $this->assertEquals(
            null,
            $this->scrapper->extractAuthorAndDate("Not the footer you are looking for")
        );
    }

    public function testCanExtractAuthorWithSpecialChars(): void
    {
        $this->assertEquals(
            array("Etuncoupd'aspi", "lundi 18 décembre 2017 11:30"),
            $this->scrapper->extractAuthorAndDate("Par Etuncoupd'aspi -  / lundi 18 décembre 2017 11:30 /")
        );
        $this->assertEquals(
            array("Cat-pito", "dimanche 17 décembre 2017 08:00"),
            $this->scrapper->extractAuthorAndDate("Par Cat-pito -  / dimanche 17 décembre 2017 08:00 / France - Compi?gne")
        );
        $this->assertEquals(
            array("Cat pito", "dimanche 17 décembre 2017 08:00"),
            $this->scrapper->extractAuthorAndDate("Par Cat pito -  / dimanche 17 décembre 2017 08:00 / France - Compi?gne")
        );
    }

    public function testValidClassicPost(): void
    {
        $this->assertEquals(true, $this->scrapper->isClassic("Blabla VDM"));
        $this->assertEquals(false, $this->scrapper->isClassic("Blabla"));
    }


}