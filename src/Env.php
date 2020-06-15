<?php

namespace App;

class Env
{
    const BITBUCKET_URL = 'BITBUCKET_URL';
    const USERNAME = 'USERNAME';
    const PASSWORD = 'PASSWORD';

    private $errors = [];

    public function valid()
    {
        if ($this->bitbucketUrl() === '') {
            $this->errors[] = 'set env variable BITBUCKET_URL';
        }

        if ($this->username() === '') {
            $this->errors[] = 'set a env variable USERNAME';
        }

        if ($this->password() === '') {
            $this->errors[] = 'set a env variable PASSWORD';
        }

        return $this->errors === [];
    }

    public function errors()
    {
        return '[ERROR] ' . implode(', ', $this->errors);
    }

    public function bitbucketUrl()
    {
        return $_ENV[self::BITBUCKET_URL] ?? '';
    }

    public function username()
    {
        return $_ENV[self::USERNAME] ?? '';
    }

    public function password()
    {
        return $_ENV[self::PASSWORD] ?? '';
    }
}
