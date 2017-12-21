<?php
namespace Scrappy;

use DateTime;
use Goutte\Client;
use IntlDateFormatter;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Scrappy\Model\VDMPost;
use Symfony\Component\DomCrawler\Crawler;

/**
 * VdmScrapper responsible of fetching VDM posts
 *
 * The scrapper will requests VDM pages and knows how to extract the data.
 *
 * VDMNews, VDMPictures, Videos and People are ignored at the moment.
 *
 * @package Scrappy
 */
class VdmScrapper
{
    const CONTENT_SELECTOR = '.panel-content > p > a';

    const FOOTER_REGEXP = "/Par (.*) -  \/ ?([^\/]*)/u";

    const VDM_DATE_FORMAT = 'EEEE dd MMMM y hh:mm';

    private $url;

    private $logger;

    private $client;

    public function __construct(string $url, LoggerInterface $logger)
    {
        $this->url = $url;
        $this->logger = $logger;
        $this->client = new Client();
    }

    /**
     * Main entry point to the scrapper.
     *
     * Loop through enough pages to fetch the required number of items.
     * Stops after 25 pages to avoid infinite loops
     *
     * @param $limit number of elements to fetch
     * @return array#
     */
    public function fetchPosts($limit) {
        $allPosts = array();
        $page = 0;

        while (count($allPosts) < $limit && $page < 25) {
            $page++;
            $url = $this->url . "?page=" . $page;

            $posts = $this->fetchPostsOn($url);
            $allPosts = array_merge($allPosts, $posts);

            $this->logger->info("Posts scrapped so far: ".count($allPosts));
        }

        return $allPosts;
    }

    /**
     * Fetch and extract posts from the given Url
     *
     * @param $url string the page to crawl
     * @return array
     */
    public function fetchPostsOn($url)
    {
        $this->logger->info("Start Crawling $url");
        $crawler = $this->client->request('GET', $url);

        return $this->extractPosts($crawler);
    }

    /**
     * Extract the data of the page.
     *
     * @param $crawler Crawler of the page to scrap
     * @return array
     */
    public function extractPosts($crawler) {
        $results = $crawler->filter('.panel-body')->each(function (Crawler $post) {

            $footer = $this->getFooter($post);
            $content = $this->getContent($post);
            if ($footer != null && $content != null
                && $this->isClassic($content)) {

                return new VDMPost(
                    $content,
                    $footer[0],
                    $this->parseFrenchDate(trim($footer[1]))
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

    public function getContent(Crawler $post)
    {
        try {
            $content = $post
                ->filter(self::CONTENT_SELECTOR)
                ->first();

            return trim($content->text());
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }

    public function getFooter(Crawler $post)
    {
        try {
            $footer = $post->filter('div')->last();
            $cleaned = str_replace("\n", "", $footer->text());

            return self::extractAuthorAndDate($cleaned);
        } catch (InvalidArgumentException $e) {
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
            return DateTime::createFromFormat('U', $timestamp);
        } else {
            return null;
        }
    }
}