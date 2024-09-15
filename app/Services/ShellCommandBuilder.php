<?php

namespace App\Services;

class ShellCommandBuilder
{
    protected string $command = '';

    public function setCommand(string $command): static
    {
        $this->command = $command;
        return $this;
    }

    public function addOption(string $option, $value): static
    {
        $this->command .= " $option $value";
        return $this;
    }

    public function getCommand(): string
    {
        return $this->command;
    }
}
