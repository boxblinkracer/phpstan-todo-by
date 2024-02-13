<?php

namespace staabm\PHPStanTodoBy\utils\ticket;

use RuntimeException;
use staabm\PHPStanTodoBy\utils\CredentialsHelper;
use staabm\PHPStanTodoBy\utils\HttpClient;

final class LinearTicketStatusFetcher implements TicketStatusFetcher
{

    private string $host;
    private string $apiKey;
    private HttpClient $client;


    /**
     * @param string $host
     * @param string|null $credentials
     * @param string|null $credentialsFilePath
     * @param HttpClient $httpClient
     */
    public function __construct(string $host, ?string $credentials, ?string $credentialsFilePath, HttpClient $httpClient)
    {
        $credentials = CredentialsHelper::getCredentials($credentials, $credentialsFilePath);

        $this->host = $host;
        $this->apiKey = (string)$credentials;

        $this->client = $httpClient;
    }


    /**
     * @return string
     */
    public static function getKeyPattern(): string
    {
        return '[A-Z0-9]+-\d+';
    }

    /**
     * @param string $ticketKey
     * @return string
     */
    public function resolveTicketUrl(string $ticketKey): string
    {
        return "$this->host/issue/$ticketKey";
    }

    /**
     * @param array $ticketKeys
     * @return array|null[]|string[]
     * @throws \JsonException
     */
    public function fetchTicketStatus(array $ticketKeys): array
    {
        $endpoint = 'https://api.linear.app/graphql';

        $query = <<<'GRAPHQL'
            query {
              issues {
                nodes {
                  id
                  state
                }
              }
            }
        GRAPHQL;

        $postContent = json_encode([
            'query' => $query,
        ], JSON_THROW_ON_ERROR);


        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
        ];

        $responses = $this->client->getMulti([$endpoint], $headers, $postContent);

        $results = [];

        foreach ($responses as $url => [$responseCode, $response]) {
            if (404 === $responseCode) {
                $results[$url] = null;
                continue;
            }

            if (200 !== $responseCode) {
                throw new RuntimeException("Could not fetch ticket's status from Jira with url $url");
            }

            $data = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

            if (isset($data['data']['issues']['nodes'])) {
                foreach ($data['data']['issues']['nodes'] as $ticket) {
                    if (in_array($ticket['id'], $ticketKeys, true)) {
                        $results[$ticket['id']] = $ticket['state'];
                    }
                }
            }
        }

        return $results;
    }

}
