<?php

namespace App;

class Env
{
    const BITBUCKET_URL = 'BITBUCKET_URL';
    const USERNAME = 'USERNAME';
    const PASSWORD = 'PASSWORD';

    /**
     * fallback to jenkins agent variables
     */
    const JENKINS = 'JENKINS';
    const GIT_AUTH_USR = 'GIT_AUTH_USR';
    const GIT_AUTH_PSW = 'GIT_AUTH_PSW';

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
        $username = $_ENV[self::USERNAME] ?? '';

        if ($username === '') {
            if (isset($_ENV[self::JENKINS])) {
                $username = $_ENV[self::GIT_AUTH_USR] ?? '';
            }
        }

        return $username;
    }

    public function password()
    {
        $password = $_ENV[self::PASSWORD] ?? '';

        if ($password === '') {
            if (isset($_ENV[self::JENKINS])) {
                $password = $_ENV[self::GIT_AUTH_PSW] ?? '';
            }
        }

        return $password;
    }
}
