<?php
namespace Scrappy\Command;

use MongoDB;
use PHPUnit\Runner\Exception;
use Psr\Log\LogLevel;
use Scrappy\VdmScrapper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class LoadPosts extends Command
{
    private $url;

    private $mongoUri;

    public function __construct(string $url, string $mongoUri)
    {
        $this->url = $url;
        $this->mongoUri = $mongoUri;

        parent::__construct();
    }

    protected function configure()
    {
        $this
          ->setName('load')
          ->setDescription('Charge les postes VDM dans la base de donnée.')
          ->setHelp('Cette command récupere les postes sur VDM et les charges en base de donnée...')
      ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = new ConsoleLogger($output);
        $scrapper = new VdmScrapper($this->url, $logger);

        $posts = $scrapper->fetchPosts();
        $posts = array_map(function($p) { return $this->addId($p); }, $posts);

        try {
            $results = $this->persistPosts($posts);

        } catch (Exception $e) {
            $logger->error($e->getMessage());
            exit(1);
        }

        $logger->log(LogLevel::INFO, $results->getInsertedCount()." Inserted in the Posts collection");
    }

    /**
     * Produce an unique ID based on author and date
     * This will protect existing IDs against reloading
     *
     * TODO: Should we include the content in it?
     *
     * @param $post
     * @return mixed
     */
    public function addId($post)
    {
        $post["_id"] = sha1($post["author"].$post["date"]);

        return $post;
    }

    /**
     * @param $posts
     * @return MongoDB\InsertManyResult
     */
    protected function persistPosts($posts): MongoDB\InsertManyResult
    {
        $uri = getenv('MONGO_URI') ? getenv('MONGO_URI') : $this->mongoUri;
        $mongo = new MongoDB\Client($uri);

        $collection = $mongo->test->posts;
        $collection->drop();

        return $collection->insertMany($posts);
    }
}
