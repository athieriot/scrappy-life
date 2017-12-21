<?php
namespace Scrappy\Command;

use function foo\func;
use Scrappy\Model\VDMPost;
use Scrappy\VdmScrapper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * "posts" command.
 *
 * Fetch and display VDM posts.
 *
 * @package Scrappy\Command
 */
class ListPosts extends Command
{
    const LIMIT_ARGUMENT = 'limit';

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
          ->addArgument(self::LIMIT_ARGUMENT, InputArgument::REQUIRED, 'Number of VDM posts to fetch')
      ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = new ConsoleLogger($output);
        $scrapper = new VdmScrapper($this->url, $logger);
        $limit = $input->getArgument(self::LIMIT_ARGUMENT);

        $posts = array_map(function (VDMPost $post) { return $post->bsonSerialize(); }, $scrapper->fetchPosts($limit));
        $output->writeln(json_encode($posts));
    }
}
