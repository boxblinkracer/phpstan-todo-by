<?php

namespace staabm\PHPStanTodoBy\Tests\utils\ticket;

use PHPUnit\Framework\TestCase;
use staabm\PHPStanTodoBy\utils\HttpClient;
use staabm\PHPStanTodoBy\utils\ticket\LinearTicketStatusFetcher;

class LinearTicketStatusFetcherTest extends TestCase
{

    /**
     * This test verifies that our resolve-url, which is our deep link to the ticket
     * is always correctly built.
     *
     * @return void
     */
    public function testTicketResolveUrl(): void
    {
        $host = 'https://linear.app/my-company';

        $fetcher = new LinearTicketStatusFetcher($host, '', '', new HttpClient());

        $resolveUrl = $fetcher->resolveTicketUrl('ABC-123');

        $this->assertEquals($host . '/issue/ABC-123', $resolveUrl);
    }

}
