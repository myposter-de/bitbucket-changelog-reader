<?php

namespace App;

use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

class Client
{
    const BASE_URI = '/rest/api/1.0';

    /**
     * api keys
     */
    const VALUES = 'values';
    const COMMITTER = 'committer';
    const USER_ID = 'id';
    const DISPLAY_NAME = 'displayName';
    const MESSAGE = 'message';

    /**
     * result keys
     */
    const NAME = 'name';
    const COMMITS = 'commits';

    private $client;

    private $username;

    private $password;

    private $args;

    private $errors = [];

    private $until;

    private $since;

    public function __construct(
        ClientInterface $client,
        string $username,
        string $password,
        Args $args
    )
    {
        $this->client = $client;
        $this->username = $username;
        $this->password = $password;
        $this->args = $args;
    }

    public function result(): string
    {
        if (!$this->valid()) {
            return $this->errors();
        }

        $this->resolveTagFromToCommit();
        $this->resolveTagToToCommit();

        $response = $this->request($this->endpoint() . $this->queryParams());

        $result = $this->output(
            $this->formatResponse(
                $response->getBody()
            )
        );

        $this->reset();

        return $result;
    }

    public function valid(): bool
    {
        if ($this->isEmpty($this->args->project())) {
            $this->errors[] = 'set a project (->project())';
        }

        if ($this->isEmpty($this->args->repo())) {
            $this->errors[] = 'set a repo (->repo())';
        }

        return $this->errors === [];
    }

    public function errors(): string
    {
        return '[ERROR] ' . implode(', ', $this->errors);
    }

    private function request(string $endpoint): ResponseInterface
    {
        return $this->client->request(
            'GET',
            $endpoint,
            [
                'auth' => [$this->username, $this->password]
            ]
        );
    }

    private function formatResponse(string $responseBody): array
    {
        $result = [];

        $response = json_decode($responseBody, true);

        foreach ($response[self::VALUES] as $commit) {
            $result[$commit[self::COMMITTER][self::USER_ID]] = [
                self::NAME => $commit[self::COMMITTER][self::DISPLAY_NAME] ?? $commit[self::COMMITTER][self::NAME],
                self::COMMITS => []
            ];
        }

        foreach ($response[self::VALUES] as $commit) {
            if ($this->args->removeDuplicateMessages()) {
                if (in_array(
                    $commit[self::MESSAGE],
                    $result[$commit[self::COMMITTER][self::USER_ID]][self::COMMITS]
                )) {
                    continue;
                }
            }

            $result[$commit[self::COMMITTER][self::USER_ID]][self::COMMITS][] = $commit[self::MESSAGE];
        }

        return $result;
    }

    private function output(array $formattedResponse): string
    {
        if ($this->args->outputText()) {
            return $this->formatToText($formattedResponse);
        }

        return $this->formatToJson($formattedResponse);
    }

    private function formatToJson(array $formattedResponse): string
    {
        return json_encode($formattedResponse);
    }

    private function formatToText(array $formattedResponse): string
    {
        $result = '';

        foreach ($formattedResponse as $user) {
            $result .= $user[self::NAME] . PHP_EOL;

            foreach ($user[self::COMMITS] as $commitMessage) {
                $commitMessageRows = explode(PHP_EOL, $commitMessage);

                $result .= ' - ' . $commitMessageRows[0] . PHP_EOL;

                unset($commitMessageRows[0]);

                foreach ($commitMessageRows as $commitMessageRow) {
                    if ($commitMessageRow === '') {
                        continue;
                    }

                    $result .= '   ' . $commitMessageRow . PHP_EOL;
                }
            }

            $result .= PHP_EOL;
        }

        return $result;
    }

    private function endpoint(string $resource = 'commits'): string
    {
        return sprintf(
            '%s/projects/%s/repos/%s/%s',
            self::BASE_URI,
            $this->args->project(),
            $this->args->repo(),
            $resource
        );
    }

    private function resolveTagFromToCommit(): void
    {
        if (is_null($this->args->tagFrom()) || $this->args->tagFrom() === '') {
            return;
        }

        $tagFromResponse = $this->request(
            $this->endpoint('tags/') . rawurldecode($this->args->tagFrom())
        );

        $tagFromResponse = json_decode($tagFromResponse->getBody(), true);

        $this->since = $tagFromResponse['latestCommit'] ?? $this->args->since();
    }

    private function resolveTagToToCommit(): void
    {
        if (is_null($this->args->tagTo()) || $this->args->tagTo() === '') {
            return;
        }

        $tagToResponse = $this->request(
            $this->endpoint('tags/') . rawurldecode($this->args->tagTo())
        );

        $tagToResponse = json_decode($tagToResponse->getBody(), true);

        $this->until = $tagToResponse['latestCommit'] ?? $this->args->until();
    }

    private function queryParams(): string
    {
        $params = [];

        if ($this->args->excludeMerges()) {
            $params[] = 'merges=exclude';
        }

        if (!$this->isEmpty($this->since)) {
            $params[] = 'since=' . $this->since;
        }

        if (!$this->isEmpty($this->until)) {
            $params[] = 'until=' . $this->until;
        }

        if (!$this->isEmpty($this->args->numberOfResults())) {
            $params[] = 'limit=' . $this->args->numberOfResults();
        }

        if ($params === []) {
            return '';
        }

        return '?' . implode('&', $params);
    }

    private function isEmpty($value): bool
    {
        return is_null($value) || $value === '';
    }

    private function reset()
    {
        $this->errors = [];
        $this->args = null;
        $this->since = null;
        $this->until = null;
    }
}
