<?php
namespace Scrappy\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        $output->writeln($this->url);
    }
}
