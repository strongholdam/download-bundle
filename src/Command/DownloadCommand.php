<?php

/*
 * This file is part of the desarrolla2 download bundle package
 *
 * Copyright (c) 2017-2018 Daniel González
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Daniel González <daniel@desarrolla2.com>
 */

namespace Desarrolla2\DownloadBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class DownloadCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this->setName('downloader:download')
            ->addOption('avoid-database-download')
            ->addOption('avoid-database-load')
            ->addOption('avoid-directories-download')
            ->addOption('database-download-extra-options', mode: InputOption::VALUE_OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $databaseHandler = $this->container->get('desarrolla2_download.handler.database_handler');
        $databaseHandler->setLogger(new ConsoleLogger($output));

        if (!$input->getOption('avoid-database-download')) {
            $output->writeln(' - downloading database');
            $databaseHandler->download($input->getOption('database-download-extra-options'));

            $output->writeln(' - deleting old databases');
            $totalDeleted = $databaseHandler->delete();
            $output->writeln(sprintf(' - done. %s databases deleted', $totalDeleted));
        }

        if (!$input->getOption('avoid-database-load')) {
            $output->writeln(' - loading database');
            $databaseHandler->load();
        }

        if (!$input->getOption('avoid-directories-download')) {
            $directoryHandler = $this->container->get('desarrolla2_download.handler.directory_handler');
            $directoryHandler->setLogger(new ConsoleLogger($output));
            $output->writeln(' - downloading directories');
            $directoryHandler->download();
        }

        $output->writeln(' - done');

        $this->finalize($output);

        return Command::SUCCESS;
    }
}
