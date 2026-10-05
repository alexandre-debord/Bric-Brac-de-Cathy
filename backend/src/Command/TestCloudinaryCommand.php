<?php

namespace App\Command;

use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:test-cloudinary')]
class TestCloudinaryCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        Configuration::instance($_ENV['CLOUDINARY_URL']);

        $upload = new UploadApi();
        $result = $upload->upload('C:\\Users\\angel\\Desktop\\test.png');

        $output->writeln($result['secure_url']);

        return Command::SUCCESS;
    }
}
