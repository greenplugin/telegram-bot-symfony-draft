<?php

namespace App\Command;

use App\Event\TelegramIncomingUpdateEvent;

use Greenplugin\TelegramBot\BotApiInterface;
use Greenplugin\TelegramBot\Method\GetUpdatesMethod;
use Greenplugin\TelegramBot\Type\UpdateType;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TelegramWatchCommand extends Command
{
    protected static $defaultName = 'telegram:watch';

    private $eventDispatcher;

    private $bot;

    public function __construct(BotApiInterface $bot, EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->bot = $bot;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('offset', InputArgument::OPTIONAL, 'offset of first message');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Greenplugin\TelegramBot\Exception\BadArgumentException
     * @throws \Greenplugin\TelegramBot\Exception\ResponseException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->success('Listening updates');

        $offset = intval($input->getArgument('offset'));

        $updateParams = ['timeout' => 120];

        if ($offset) {
            $updateParams['offset'] = $offset;
        }

        while (true) {
            $io->title('Getting update...');
            $updates = $this->bot->getUpdates(GetUpdatesMethod::create($updateParams));
            foreach ($updates as $update) {
                $event = new TelegramIncomingUpdateEvent($update);
                $this->eventDispatcher->dispatch($event::NAME, $event);
                $updateParams['offset'] = $update->updateId + 1;
                $io->success(sprintf("Incoming %s, with id %s", $this->getUpdateType($update), $update->updateId));
            }
        }
    }

    /**
     * @param UpdateType $updateType
     * @return string
     */
    public function getUpdateType(UpdateType $updateType)
    {
        if ($updateType->editedMessage) {
            return 'editedMessage';
        }
        if ($updateType->message) {
            return 'message';
        }
        if ($updateType->callbackQuery) {
            return 'callbackQuery';
        }
        if ($updateType->channelPost) {
            return 'channelPost';
        }
        if ($updateType->chosenInlineResult) {
            return 'chosenInlineResult';
        }
        if ($updateType->editedChannelPost) {
            return 'editedChannelPost';
        }
        if ($updateType->inlineQuery) {
            return 'inlineQuery';
        }
        if ($updateType->preCheckoutQuery) {
            return 'preCheckoutQuery';
        }
        if ($updateType->shippingQuery) {
            return 'shippingQuery';
        }
        return 'Undefined type';
    }
}
