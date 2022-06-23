<?php

namespace Pantheon\UserBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Создать таблицу с сессиями.
 * Необходимо запустить один раз после установки бандла.
 */
class CreateSessionTableCommand extends Command
{
    protected static $defaultName = 'app:create-session-table';

    public function __construct(
        \SessionHandlerInterface $sessionHandlerService
    )
    {
        $this->sessionHandlerService = $sessionHandlerService;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Создать таблицу с сессиями')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->sessionHandlerService->createTable();
            $output->writeln('<info>OK!</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }
}