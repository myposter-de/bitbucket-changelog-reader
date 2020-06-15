<?php

namespace App;

class ClientFactory
{
    public static function create(
        Env $env
    ): Client
    {
        return new Client(
            new \GuzzleHttp\Client(
                [
                    'base_uri' => $env->bitbucketUrl(),
                    'timeout' => 2.0
                ]
            ),
            $env->username(),
            $env->password()
        );
    }
}
