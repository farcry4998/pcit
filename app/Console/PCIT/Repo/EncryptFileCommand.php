<?php

declare(strict_types=1);

namespace App\Console\PCIT\Repo;

use Symfony\Component\Console\Command\Command;

class EncryptFileCommand extends Command
{
    public function configure(): void
    {
        $this->setName('encrypt-file');
        $this->setDescription('Encrypts a file and adds decryption steps to .pcit.yml');
    }
}
