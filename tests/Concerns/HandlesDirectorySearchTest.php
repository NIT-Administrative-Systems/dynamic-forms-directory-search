<?php

namespace Northwestern\SysDev\DirectoryLookupComponent\Tests;

use Northwestern\SysDev\DirectoryLookupComponent\Concerns\HandlesDirectorySearch;
use Northwestern\SysDev\SOA\DirectorySearch;
use Orchestra\Testbench\TestCase;

/**
 * @coversDefaultClass \Northwestern\SysDev\DirectoryLookupComponent\Concerns\HandlesDirectorySearch;
 */
class HandlesDirectorySearchTest extends TestCase
{
    /**
     * @covers ::lookup
     * @covers ::guessType
     */
    public function testFound(): void
    {
        $api = $this->createStub(DirectorySearch::class);
        $api->method('lookup')->willReturn([
            'uid' => 'test',
            'mail' => 'test@foobar.com',
            'displayName' => ['Test Smith'],
            'nuAllTitle' => ['Head of IT'],
        ]);

        $this->app['router']->get(__METHOD__.'/{search}', function (string $search) use ($api) {
            return ($this->mock_controller($api))($search);
        });

        $response = $this->get(__METHOD__.'/test');

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

    /**
     * @covers ::lookup
     * @covers ::guessType
     */
    public function testNotFound(): void
    {
        $api = $this->createStub(DirectorySearch::class);
        $api->method('lookup')->willReturn(null);

        $this->app['router']->get(__METHOD__.'/{search}', function (string $search) use ($api) {
            return ($this->mock_controller($api))($search);
        });

        $response = $this->get(__METHOD__.'/test@foo.com');
        $response->assertNotFound()->assertJson([
            'display' => 'test@foo.com',
            'searchType' => 'mail',
            'person' => null,
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
