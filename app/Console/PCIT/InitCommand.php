<?php

declare(strict_types=1);

namespace App\Console\PCIT;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('init');

        $this->setDescription('Generates a .pcit.yml');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return $output->write('Please exec <info>pcitinit</info> command');
    }
}
