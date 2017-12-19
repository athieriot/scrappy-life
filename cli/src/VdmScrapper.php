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
    const FOOTER_REGEXP = "/Par (.*) -  \/ ?([^\/]*)/u";
    const VDM_DATE_FORMAT = 'EEEE dd MMMM y hh:mm';

    private $url;

    private $logger;

    public function __construct(string $url, LoggerInterface $logger)
    {
        $this->url = $url;
        $this->logger = $logger;
    }

    public function fetchPosts() {
        $allPosts = array();
        $page = 0;

        while (count($allPosts) < 200 && $page < 20) {
            $page++;
            $posts = $this->fetchPostsOn($this->url."?page=".$page);
            $allPosts = array_merge($allPosts, $posts);
            $this->logger->info("Posts scrapped so far: ".count($allPosts));
        }

        return $allPosts;
    }

    private function fetchPostsOn($url) {
        $client = new Client();

        $this->logger->info("Start Crawling $url");
        $crawler = $client->request('GET', $url);

        $results = $crawler->filter('.panel-body')->each(function (Crawler $post) {

            $footer = $this->getFooter($post);
            $content = $this->getContent($post);
            if ($footer != null && $content != null
                && $this->isClassic($content)) {

                return array(
                    "content" => $content,
                    "author" => $footer[0],
                    "date" => $this->parseFrenchDate(trim($footer[1]))
                );
            }

            return null;
        });

        return array_values(array_filter($results));
    }

    /**
     * Returns true if the content ends with "VDM"
     * Do not even try to deal with VDMNews, VDMPhoto and People
     *
     * @param $c string content of the VDM Post
     * @return bool
     */
    public function isClassic($c) {
        $vdm = "VDM";
        return strlen($vdm) === 0 || (substr($c, -strlen($vdm)) === $vdm);
    }

    protected function getContent(Crawler $post)
    {
        try {
            $content = $post
                ->filter(self::CONTENT_SELECTOR)
                ->first();

            return trim($content->text());
        } catch (\InvalidArgumentException $e) {
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
            return null;
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

        return null;
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
            return $timestamp;
        }
    }
}