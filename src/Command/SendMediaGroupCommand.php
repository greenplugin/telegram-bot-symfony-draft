<?php

namespace App\Command;

use Greenplugin\TelegramBot\Method\SendMediaGroupMethod;
use Greenplugin\TelegramBot\Type\InputFileType;
use Greenplugin\TelegramBot\Type\InputMedia\InputMediaPhotoType;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SendMediaGroupCommand extends BotCommand
{
    protected static $defaultName = 'telegram:sendMediaGroup';

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $images = [];
        foreach (explode(',', $this->params->get('send.images')) as $image) {
            $images[] = InputMediaPhotoType::create(new \SplFileInfo($image));
        }

        $mediaGroup = SendMediaGroupMethod::create($this->params->get('chat.default'), $images);

        $result = $this->bot->sendMediaGroup($mediaGroup);

        $rows = [];

        foreach ($result as $item) {
            $rows[] = [
                $item->messageId,
                $item->mediaGroupId,
                $item->chat->title,
                $item->chat->id,
                $item->chat->firstName,
            ];
        }

        $io->table([
            'id',
            'mediaGroupId',
            'chat title',
            'chat id',
            'chat firstName'
        ], $rows);
        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
    }
}
