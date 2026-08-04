<?php

namespace Laraowl\Client\Tests\Unit;

use Laraowl\Client\Support\DirectoryListing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DirectoryListingTest extends TestCase
{
    #[DataProvider('nonApacheServers')]
    public function test_it_does_not_assume_directory_listing_is_enabled_on_non_apache_servers(string $server): void
    {
        $this->assertFalse(DirectoryListing::isEnabled($server, null));
        $this->assertFalse(DirectoryListing::isEnabled($server, 'Options +Indexes'));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function nonApacheServers(): iterable
    {
        yield 'FrankenPHP' => ['FrankenPHP'];
        yield 'Nginx' => ['nginx/1.27.0'];
        yield 'Caddy' => ['Caddy'];
        yield 'PHP development server' => ['PHP 8.4.0 Development Server'];
        yield 'unknown server' => [''];
    }

    public function test_it_detects_apache_directory_listing_configuration(): void
    {
        $this->assertTrue(DirectoryListing::isEnabled('Apache/2.4.62', 'Options +Indexes'));
        $this->assertTrue(DirectoryListing::isEnabled('Apache/2.4.62', 'Options -MultiViews +Indexes'));
        $this->assertFalse(DirectoryListing::isEnabled('Apache/2.4.62', 'Options -Indexes'));
        $this->assertFalse(DirectoryListing::isEnabled('Apache/2.4.62', 'Options -MultiViews -Indexes'));
    }

    public function test_it_does_not_report_an_unknown_apache_configuration_as_enabled(): void
    {
        $this->assertFalse(DirectoryListing::isEnabled('Apache/2.4.62', null));
    }
}
