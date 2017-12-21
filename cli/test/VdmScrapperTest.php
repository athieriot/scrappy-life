<?php

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Scrappy\Model\VDMPost;
use Scrappy\VdmScrapper;
use Symfony\Component\DomCrawler\Crawler;

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

    public function testHandleInvalidContent() {
        $invalidPost = new Crawler("<div class=\"panel-body\"></div>");

        $this->assertEquals(
            null,
            $this->scrapper->getContent($invalidPost)
        );
    }

    public function testHandleInvalidFooter() {
        $invalidPost = new Crawler("");

        $this->assertEquals(
            null,
            $this->scrapper->getFooter($invalidPost)
        );
    }

    public function testCanParseFrenchDates(): void
    {
        $this->assertEquals(
            "2017-12-15T11:30:00+0000",
            $this->scrapper->parseFrenchDate("vendredi 15 décembre 2017 11:30")->format(DateTime::ISO8601)
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

    public function testCanExtractPosts() {
        $classicPage = new Crawler(file_get_contents(__DIR__ . '/resources/classic.html'));

        $this->assertEquals(
            array(new VDMPost(
                "Aujourd’hui, la maîtresse de ma fille me dit, désespérée : \"C’est fou, elle ne veut pas écrire de la main droite, il faudrait consulter.\" Tout ça parce qu'elle est gauchère. VDM",
               "Anonyme",
                DateTime::createFromFormat(DATE_ISO8601, "2017-12-19T22:30:00+0000")
            )),
            $this->scrapper->extractPosts($classicPage)
        );
    }
}