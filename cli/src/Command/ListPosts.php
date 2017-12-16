<?php
namespace Scrappy\Command;

use DateTime;
use Goutte\Client;
use IntlDateFormatter;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

//TODO: PHPDocs
class ListPosts extends Command
{
    const CONTENT_SELECTOR = '.panel-content > p > a';
    const FOOTER_REGEXP = "/Par ([\w \.]*)[ -\/]*([^\/]*)/u";
    const VDM_DATE_FORMAT = 'EEEE dd MMMM y hh:mm';

    private $url;

    public function __construct(string $url)
    {
        $this->url = $url;

        parent::__construct();
    }

    protected function configure()
    {
        $this
          ->setName('posts')
          ->setDescription('Liste les postes sur vie de merde.')
          ->setHelp('Cette command affiche les 200 derniers postes depuis le site Vie de Merde...')
      ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = new ConsoleLogger($output);
        $posts = $this->fetchPosts($logger);

        print json_encode($posts);
        print "\n";
    }

    public function fetchPosts($logger) {
        $client = new Client();

        $logger->log(LogLevel::INFO, "Start Crawling $this->url");
        $crawler = $client->request('GET', $this->url);

        //TODO: Pagination (This will need Selenium)
        return $crawler->filter('.panel-body')->each(function (Crawler $post) {

            $footer = $this->getFooter($post);
            return array(
                "content" => $this->getContent($post),
                "author" => $footer[0],
                "date" => $this->parseFrenchDate(self::VDM_DATE_FORMAT, trim($footer[1]))
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

    public function parseFrenchDate($format, $date)
    {
        $format = new IntlDateFormatter(
            "fr-FR",
            IntlDateFormatter::FULL, IntlDateFormatter::FULL,
            'Etc/UTC',
            IntlDateFormatter::GREGORIAN,
            $format
        );

        $timestamp = $format->parse($date);
        if ($timestamp) {
            return DateTime::createFromFormat('U', $timestamp)->format(DateTime::ISO8601);
        } else {
            return false;
        }
    }
}
