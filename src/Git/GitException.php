<?php
namespace Common\Git;

class GitException extends \RuntimeException
{
    protected $command;

    public function getCommand()
    {
        return $this->command;
    }

    public function setCommand($command)
    {
        $this->command = $command;
    }

}
