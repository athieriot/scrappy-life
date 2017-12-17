<?php
namespace Scrappy;

use DateTime;
use Goutte\Client;
use IntlDateFormatter;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;

class VdmScrapper
{
    const CONTENT_SELECTOR = '.panel-content > p > a';
    const FOOTER_REGEXP = "/Par ([\w \.]*)[ -\/]*([^\/]*)/u";
    const VDM_DATE_FORMAT = 'EEEE dd MMMM y hh:mm';

    private $url;

    private $logger;

    public function __construct(string $url, LoggerInterface $logger)
    {
        $this->url = $url;
        $this->logger = $logger;
    }

    public function fetchPosts() {
        $client = new Client();

        $this->logger->info("Start Crawling $this->url");
        $crawler = $client->request('GET', $this->url);

        //TODO: Pagination (This will need Selenium)
        return $crawler->filter('.panel-body')->each(function (Crawler $post) {

            $footer = $this->getFooter($post);
            return array(
                "content" => $this->getContent($post),
                "author" => $footer[0],
                "date" => $this->parseFrenchDate(trim($footer[1]))
            );
        });
    }

    protected function getContent(Crawler $post)
    {
        //TODO: Gérer les VDM images et VDM news
        try {
            $content = $post
                ->filter(self::CONTENT_SELECTOR)
                ->first();

            return trim($content->text());
        } catch (\InvalidArgumentException $e) {
            //TODO: Logs
            return null;
        }
    }

    protected function getFooter(Crawler $post)
    {
        try {
            $footer = $post->filter('div')->last();
            $cleaned = str_replace("\n", "", $footer->text());

            return self::extractAuthorAndDate($cleaned);
        } catch (\InvalidArgumentException $e) {
            return array(null, null);
        }
    }

    public function extractAuthorAndDate(string $footer) {
        preg_match(self::FOOTER_REGEXP, $footer, $matches);
        if (!empty($matches)) {
            return array(
                trim($matches[1]),
                trim($matches[2])
            );
        }

        return array(null, null);
    }

    public function parseFrenchDate($date)
    {
        $format = new IntlDateFormatter(
            "fr-FR",
            IntlDateFormatter::FULL, IntlDateFormatter::FULL,
            'Etc/UTC',
            IntlDateFormatter::GREGORIAN,
            self::VDM_DATE_FORMAT
        );

        $timestamp = $format->parse($date);
        if ($timestamp) {
            return DateTime::createFromFormat('U', $timestamp)->format(DateTime::ISO8601);
        } else {
            return false;
        }
    }
}