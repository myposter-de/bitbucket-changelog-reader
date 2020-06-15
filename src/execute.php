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
    "p:r:s:u:f:t:n:omd",
    [
        'project:',
        'repo:',
        'since:',
        'until:',
        'tagFrom:',
        'tagTo:',
        'numberOfResults:',
        'excludeMerges',
        'outputText',
        'removeDuplicateMessages',
    ]
);

$client = ClientFactory::create($env, new Args($options));

try {
    if (! $client->valid()) {
        echo $client->errors();
        exit(1);
    }

    echo $client->result();
} catch (Throwable $t) {
    echo '[ERROR]' . $t->getMessage();
    exit(1);
}
