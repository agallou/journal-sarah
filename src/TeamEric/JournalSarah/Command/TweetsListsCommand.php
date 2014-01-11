<?php

namespace TeamEric\JournalSarah\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TweetsListsCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('tweets:lists')
            ->setDescription("Récupère la liste des tweets d'un utilisateur")
            ->addArgument("username")
            ->addArgument("query", null, "texte a trouver dans le tweet", null);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $search = $input->getArgument('query');

        $client = $this->makeClient();
        $parameters = array(
            'screen_name' => $username,
            'count' => 200,
        );

        do {
            $tweets = $client->get(
                'statuses/user_timeline.json?' . http_build_query($parameters)
            )->send()->json();

            $lastId = null;
            foreach ($tweets as $tweet) {
                $lastId = $tweet["id"] -1;

                if (null === $search || strstr($tweet["text"], $search)) {
                    $output->writeln($tweet["id"] . " " . $tweet["text"]);
                }


            }
            $parameters["max_id"] = $lastId;

        } while (count($tweets) > 0);
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
