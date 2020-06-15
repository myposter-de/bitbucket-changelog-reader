<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Args;
use App\ClientFactory;
use App\Env;

$env = new Env();

if (! $env->valid()) {
    echo $env->errors();
    exit(1);
}

$options = getopt(
    "p:r:s:u:f:t:om",
    [
        'project:',
        'repo:',
        'since:',
        'until:',
        'tagFrom:',
        'tagTo:',
        'excludeMerges',
        'outputText',
    ]
);

$args = new Args($options);

$client = ClientFactory::create($env);

try {
    $client
        ->project($args->project())
        ->repo($args->repo())
        ->excludeMerges($args->excludeMerges())
        ->since($args->since())
        ->until($args->until())
        ->tagFrom($args->tagFrom())
        ->tagTo($args->tagTo())
        ->outputText($args->outputText());

    if (! $client->valid()) {
        echo $client->errors();
        exit(1);
    }

    echo $client->result();
} catch (Throwable $t) {
    echo $t->getMessage();
    exit(1);
}
