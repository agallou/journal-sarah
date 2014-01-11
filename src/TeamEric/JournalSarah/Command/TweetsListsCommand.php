<?php

namespace TeamEric\JournalSarah\Command;

use Rvdv\Guzzle\Twitter\TwitterClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TweetsListsCommand extends Command
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

        $parameters = array(
            'screen_name' => $username,
            'count' => 200,
        );

        do {
            $tweets = $this->client->get(
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
}
