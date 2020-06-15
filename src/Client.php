<?php

namespace App;

use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

class Client
{
    const BASE_URI = '/rest/api/1.0';
    const OUTPUT_DEFAULT = self::OUTPUT_JSON;
    const OUTPUT_JSON = 'json';
    const OUTPUT_TEXT = 'text';

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

    private $project;

    private $repo;

    private $since;

    private $until;

    private $errors = [];

    private $excludeMerges = false;

    private $output = self::OUTPUT_JSON;

    private $tagFrom;

    private $tagTo;

    public function __construct(ClientInterface $client, string $username, string $password)
    {
        $this->client = $client;
        $this->username = $username;
        $this->password = $password;
    }

    public function project(string $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function repo(string $repo): self
    {
        $this->repo = $repo;

        return $this;
    }

    public function excludeMerges(bool $excludeMerges = false): self
    {
        $this->excludeMerges = $excludeMerges;

        return $this;
    }

    public function since(string $commitId): self
    {
        $this->since = $commitId;

        return $this;
    }

    public function until(string $commitId): self
    {
        $this->until = $commitId;

        return $this;
    }

    public function tagFrom(string $tagFrom): self
    {
        $this->tagFrom = $tagFrom;

        return $this;
    }

    public function tagTo(string $tagTo): self
    {
        $this->tagTo = $tagTo;

        return $this;
    }

    public function outputText(bool $outputText = false): self
    {
        $this->output = $outputText ? self::OUTPUT_TEXT : self::OUTPUT_DEFAULT;

        return $this;
    }

    public function result(): string
    {
        if (! $this->valid()) {
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
        if (is_null($this->project) || $this->project === '') {
            $this->errors[] = 'set a project (->project())';
        }

        if (is_null($this->repo) || $this->repo === '') {
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
            $result[$commit[self::COMMITTER][self::USER_ID]][self::COMMITS][] = $commit[self::MESSAGE];
        }


        return $result;
    }

    private function output(array $formattedResponse): string
    {
        if ($this->output === self::OUTPUT_TEXT) {
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
            $this->project,
            $this->repo,
            $resource
        );
    }

    private function resolveTagFromToCommit(): void
    {
        if (is_null($this->tagFrom) || $this->tagFrom === '') {
            return;
        }

        $tagFromResponse = $this->request(
            $this->endpoint('tags/') . rawurldecode($this->tagFrom)
        );

        $tagFromResponse = json_decode($tagFromResponse->getBody(), true);

        $this->since = $tagFromResponse['latestCommit'] ?? $this->since;
    }

    private function resolveTagToToCommit(): void
    {
        if (is_null($this->tagTo) || $this->tagTo === '') {
            return;
        }

        $tagToResponse = $this->request(
            $this->endpoint('tags/') . rawurldecode($this->tagTo)
        );

        $tagToResponse = json_decode($tagToResponse->getBody(), true);

        $this->until = $tagToResponse['latestCommit'] ?? $this->until;
    }

    private function queryParams(): string
    {
        $params = [];

        if ($this->excludeMerges) {
            $params[] = 'merges=exclude';
        }

        if (! is_null($this->since) || $this->since !== '') {
            $params[] = 'since=' . $this->since;
        }

        if (! is_null($this->until) || $this->until !== '') {
            $params[] = 'until=' . $this->until;
        }

        if ($params === []) {
            return '';
        }

        return '?' . implode('&', $params);
    }

    private function reset()
    {
        $this->errors = [];
        $this->repo = null;
        $this->project = null;
        $this->excludeMerges = false;
        $this->since = null;
        $this->until = null;
        $this->tagFrom = null;
        $this->tagTo = null;
        $this->output = self::OUTPUT_JSON;
    }
}
