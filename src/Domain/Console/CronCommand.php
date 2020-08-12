<?php

namespace App\Domain\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Domain\Main\CronService;
use App\Application\Payload\Payload;

final class CronCommand extends Command {

    private $service;

    public function __construct(CronService $service, ?string $name = null) {
        parent::__construct($name);
        $this->service = $service;
    }

    /**
     * Configure.
     *
     * @return void
     */
    protected function configure(): void {
        parent::configure();

        $this->setName('cron');
        $this->setDescription('Run cron');
    }

    /**
     * Execute command.
     *
     * @param InputInterface $input The input
     * @param OutputInterface $output The output
     *
     * @return int The error code, 0 on success
     */
    protected function execute(InputInterface $input, OutputInterface $output): int {

        $payload = $this->service->cron();
        $output->writeln($payload->getResult()["result"]);

        return 0;
    }

}
