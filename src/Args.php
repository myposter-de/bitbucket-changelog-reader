<?php

namespace App;

class Args
{
    const DEFAULT_NUMBER_OF_RESULTS = '100';

    private $args;

    public function __construct(array $args)
    {
        $this->args = $args;
    }

    public function project(): string
    {
        return $this->args['project'] ?? $this->args['p'] ?? '';
    }

    public function repo(): string
    {
        return $this->args['repo'] ?? $this->args['r'] ?? '';
    }

    public function since(): string
    {
        return $this->args['since'] ?? $this->args['s'] ?? '';
    }

    public function until(): string
    {
        return $this->args['until'] ?? $this->args['u'] ?? '';
    }

    public function tagFrom(): string
    {
        return $this->args['tagFrom'] ?? $this->args['f'] ?? '';
    }

    public function tagTo(): string
    {
        return $this->args['tagTo'] ?? $this->args['t'] ?? '';
    }

    public function excludeMerges(): bool
    {
        return isset($this->args['excludeMerges']) || isset($this->args['m']);
    }

    public function outputText(): bool
    {
        return isset($this->args['outputText']) || isset($this->args['o']);
    }

    public function removeDuplicateMessages(): bool
    {
        return isset($this->args['removeDuplicateMessages']) || isset($this->args['d']);
    }

    public function numberOfResults(): string
    {
        return $this->args['numberOfResults'] ?? $this->args['n'] ?? self::DEFAULT_NUMBER_OF_RESULTS;
    }
}
