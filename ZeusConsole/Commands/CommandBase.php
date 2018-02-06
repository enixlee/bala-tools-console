<?php

namespace ZeusConsole\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommandBase extends Command
{

    protected $verboseDebug = false;

    /**
     * @return boolean
     */
    public function isVerboseDebug()
    {
        return $this->verboseDebug;
    }

    /**
     * Initializes the command just after the input has been validated.
     *
     * This is mainly useful when a lot of commands extends one main command
     * where some things need to be initialized based on the input arguments and options.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->verboseDebug = $output->getVerbosity() == OutputInterface::VERBOSITY_DEBUG;
    }

}