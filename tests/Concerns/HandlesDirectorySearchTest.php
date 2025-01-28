<?php

namespace Northwestern\SysDev\DirectoryLookupComponent\Tests\Concerns;

use Northwestern\SysDev\DirectoryLookupComponent\Concerns\HandlesDirectorySearch;
use Northwestern\SysDev\SOA\DirectorySearch;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HandlesDirectorySearch::class)]
final class HandlesDirectorySearchTest extends TestCase
{
    public function testFound(): void
    {
        $api = $this->createStub(DirectorySearch::class);
        $api->method('lookup')->willReturn([
            'uid' => 'test',
            'mail' => 'test@foobar.com',
            'displayName' => ['Test Smith'],
            'nuAllTitle' => ['Head of IT'],
        ]);

        $this->app['router']->get(__FUNCTION__.'/{search}', function (string $search) use ($api) {
            return ($this->mock_controller($api))($search);
        });

        $response = $this->get(__FUNCTION__.'/test');

        $response->assertOk()
            ->assertJson([
                'display' => 'test',
                'searchType' => 'netid',
                'person' => [
                    'netid' => 'test',
                    'email' => 'test@foobar.com',
                    'name' => 'Test Smith',
                    'title' => 'Head of IT',
                ],
            ]);
    }

    public function testNotFound(): void
    {
        $api = $this->createStub(DirectorySearch::class);
        $api->method('lookup')->willReturn(false);

        $this->app['router']->get(__FUNCTION__.'/{search}', function (string $search) use ($api) {
            return ($this->mock_controller($api))($search);
        });

        $response = $this->get(__FUNCTION__.'/test@foo.com');
        $response->assertNotFound()->assertJson([
            'display' => 'test@foo.com',
            'searchType' => 'mail',
            'person' => null,
        ]);
    }

    public function testIncompleteData(): void
    {
        $api = $this->createStub(DirectorySearch::class);
        $api->method('lookup')->willReturn([
            'uid' => 'test',
            'mail' => null,
            'displayName' => ['Test Smith'],
            'nuAllTitle' => null,
        ]);

        $this->app['router']->get(__FUNCTION__.'/{search}', function (string $search) use ($api) {
            return ($this->mock_controller($api))($search);
        });

        $response = $this->get(__FUNCTION__.'/test');
        $response->assertOk()->assertJson([
            'display' => 'test',
            'searchType' => 'netid',
            'person' => [
                'netid' => 'test',
                'email' => null,
                'name' => 'Test Smith',
                'title' => null,
            ],
        ]);
    }

    /**
     * Makes a stub controller using the HandlesDirectorySearch trait & a mock API object.
     */
    protected function mock_controller(DirectorySearch $apiStub): object
    {
        return new class($apiStub) {
            use HandlesDirectorySearch;

            public function __construct(protected DirectorySearch $apiStub)
            {
                //
            }

            public function __invoke(string $search)
            {
                return $this->lookup($this->apiStub, $search);
            }
        };
    }
}
