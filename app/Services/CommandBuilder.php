<?php

namespace App\Services;

class CommandBuilder
{
    protected string $command = '';

    public function setCommand(string $command): static
    {
        $this->command = $command;
        return $this;
    }

    public function addOption(string $option, $value = ''): static
    {
        $this->command .= " $option $value";
        return $this;
    }

    public function addOptionWhen(string $option, mixed $value, bool $condition): static
    {
        $this->command .= $condition ? " $option $value" : '';

        return $this;
    }

    public function addTernaryArgument(string $optionA, string $optionB, bool $condition): static
    {
        $this->command .= $condition ? " $optionA" : " $optionB";

        return $this;
    }

    public function getCommand(): string
    {
        return $this->command;
    }
}
