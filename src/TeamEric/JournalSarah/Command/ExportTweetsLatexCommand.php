<?php

namespace TeamEric\JournalSarah\Command;

use Rvdv\Guzzle\Twitter\TwitterClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportTweetsLatexCommand extends Command
{
    /**
     * @var TwitterClient
     */
    private $client;

    /**
     * @param string        $name
     * @param TwitterClient $client
     */
    public function __construct($name = null, TwitterClient $client)
    {
        $this->client = $client;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('export:latex')
            ->setDescription("Creer un fichier Latex d'après les tweets")
            ->addArgument(
                'filename',
                InputArgument::REQUIRED
            )
            ->addOption(
                'with-background',
                null,
                InputOption::VALUE_NONE
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument('filename');

        $rootDir = __DIR__ . '/../../../../';

        $loader = new \Twig_Loader_Filesystem($rootDir . '/views/');
        $twig = new \Twig_Environment($loader);

        $filter = new \Twig_SimpleFilter('latex_escaping', function ($string) {
          return str_replace(array('#', '@', '_'), array('\#', '\verb+@+', "\_"), $string);
        }, array('is_safe' => array('html')));
        $twig->addFilter($filter);

        $tweets = array();
        foreach ($this->getTweetIdsToExport() as $id) {
          $twitt = $this->prepareTweet($id);
          $tweets[$twitt['timestamp']] = $twitt;
        }
        ksort($tweets);
        file_put_contents($filename, $twig->render('output.latex.twig', array(
          'tweets' => $tweets,
          'with_background' => $input->getOption('with-background'),
          'graphicspath' => $rootDir . '/Ressources/images',
        )));

        $output->writeln(sprintf('Write %s', $filename));
    }

    public function getTweetIdsToExport()
    {
        return json_decode(file_get_contents(__DIR__ . '/../../../../data/tweets.json'));
    }

    public function prepareTweet($twittId)
    {
          $twitt = $this->client->get(sprintf('statuses/show/%s.json', $twittId))->send()->json();
          $expandedUrls = array();
          foreach ($twitt['entities']['urls'] as $urlInfos) {
            $expandedUrls[] = $urlInfos['expanded_url'];
          }
          $export = array(
            'tweet_id' => $twitt['id_str'],
            'user' => array(
              'screen_name' => $twitt['user']['screen_name'],
              'name' => $twitt['user']['name'],
            ),
            'timestamp'                  => strtotime($twitt['created_at']), //TODO décalage de 1 sur la date problème fuseau horaire ?
            'text'                       => $twitt['text'],
            'expanded_urls'              => implode(',', $expandedUrls),
          );
          return $export;
    }
}
