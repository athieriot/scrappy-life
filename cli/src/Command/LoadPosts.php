<?php
namespace Scrappy\Command;

use MongoDB;
use PHPUnit\Runner\Exception;
use Psr\Log\LogLevel;
use Scrappy\VdmScrapper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * "load" command
 *
 * Fetch and store VDM posts in MongoDB
 *
 * @package Scrappy\Command
 */
class LoadPosts extends Command
{
    const LIMIT_ARGUMENT = 'limit';

    const MONGO_COLLECTION = "posts";

    private $url;

    private $mongoUri;

    private $mongoName;

    public function __construct(string $url, string $mongoUri, string $mongoName)
    {
        $this->url = $url;
        $this->mongoUri = getenv('MONGO_URI') ? getenv('MONGO_URI') : $mongoUri;
        $this->mongoName = $mongoName;

        parent::__construct();
    }

    protected function configure()
    {
        $this
          ->setName('load')
          ->setDescription('Charge les postes VDM dans la base de donnée.')
          ->setHelp('Cette command récupere les postes sur VDM et les charges en base de donnée...')
          ->addArgument(self::LIMIT_ARGUMENT, InputArgument::REQUIRED, 'Number of VDM posts to fetch')
      ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = new ConsoleLogger($output);
        $scrapper = new VdmScrapper($this->url, $logger);
        $limit = $input->getArgument(self::LIMIT_ARGUMENT);

        try {
            $posts = $scrapper->fetchPosts($limit);
            $results = $this->persistPosts($posts);

        } catch (Exception $e) {
            $logger->error($e->getMessage());
            exit(1);
        }

        $logger->log(LogLevel::INFO, $results->getInsertedCount()." Inserted in the Posts collection");
    }

    /**
     * Persist the list of posts.
     *
     * @param $posts
     * @return MongoDB\InsertManyResult
     */
    protected function persistPosts($posts): MongoDB\InsertManyResult
    {
        $mongo = new MongoDB\Client($this->mongoUri);

        $collection = $mongo->selectCollection($this->mongoName, self::MONGO_COLLECTION);
        $collection->drop();

        $this->createIndex($collection);

        return $collection->insertMany($posts);
    }

    /**
     * Create necessary indexes
     *
     * @param $collection
     * @return string
     */
    protected function createIndex($collection)
    {
        return $collection->createIndex(["author" => "text"]);
    }
}
