<?php

namespace TeamEric\JournalSarah\Command;

use Rvdv\Guzzle\Twitter\TwitterClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportTweetsCommand extends Command
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
            ->setName('export:tweets')
            ->setDescription("Creer un fichier CSV d'après les tweets")
            ->addArgument(
                'filename',
                InputArgument::REQUIRED
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument('filename');

        $file = fopen($filename, 'w');
        foreach ($this->getTweetIdsToExport() as $id) {
          fputcsv($file, $this->prepareTweet($id));
        }
        fclose($file);


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
            'in_reply_to_status_id'      => $twitt['in_reply_to_status_id_str'],
            'in_reply_to_user_id'        => $twitt['in_reply_to_user_id_str'],
            'timestamp'                  => strtotime($twitt['created_at']), //TODO décalage de 1 sur la date problème fuseau horaire ?
            'source'                     => $twitt['source'],
            'text'                       => $twitt['text'],
            'retweeted_status_id'        => '', //TODO
            'retweeted_status_user_id'   => '', //TODO
            'retweeted_status_timestamp' => '', //TODO
            'expanded_urls'              => implode(',', $expandedUrls),
          );
          return $export;
    }
}
