<?php

namespace TeamEric\JournalSarah\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExportTweetsCommand extends Command
{
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
        $twitter = $this->makeClient();

        $file = fopen($filename, 'w');
        foreach ($this->getTweetIdsToExport() as $id) {
          fputcsv($file, $this->prepareTweet($twitter, $id));
        }
        fclose($file);


        $output->writeln(sprintf('Write %s', $filename));
    }

    public function getTweetIdsToExport()
    {
        return json_decode(file_get_contents(__DIR__ . '/../../../../data/tweets.json'));
    }

    public function prepareTweet($twitter, $twittId)
    {
          $twitt = $twitter->get(sprintf('statuses/show/%s.json', $twittId))->send()->json();

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
            'expanded_urls'              => implode(',', $twitt['entities']['urls']),
          );
          return $export;
    }

    public function makeClient()
    {
        $config = array(
          'consumer_key'    => 'ShCj42SXL1AubTmIdrCsLg',
          'consumer_secret' => 'DIeHtZjhSPhzddtSwmSJDc2LCMkLCuDW3dWJJMyHs',
          'token'           => '159978634-Vam6Ymupy3oEG7oiVMkqKnJh10T7E3ENgprIAbZP',
          'token_secret'    => 'AuCd9PRT34r7D0D08M5ymn3wiPf0bhBj37bGpjuBcWaUP',
        );
        return \Rvdv\Guzzle\Twitter\TwitterClient::factory($config);
    }
}
