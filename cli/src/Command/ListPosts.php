<?php
namespace Scrappy\Command;

use Scrappy\VdmScrapper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

//TODO: PHPDocs
class ListPosts extends Command
{
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
        $scrapper = new VdmScrapper($this->url, $logger);

        $output->writeln(json_encode($scrapper->fetchPosts()));
    }
}
