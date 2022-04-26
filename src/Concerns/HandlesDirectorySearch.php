<?php

namespace Northwestern\SysDev\DirectoryLookupComponent\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Northwestern\SysDev\DirectoryLookupComponent\DirectoryLookup;
use Northwestern\SysDev\SOA\DirectorySearch;

trait HandlesDirectorySearch
{
    /**
     * Performs the API call for the UI component & returns appropriate JSON for Dynamic Forms.
     */
    protected function lookup(DirectorySearch $directoryApi, string $search, $directoryApiLevel = 'basic'): JsonResponse
    {
        $person = null;
        $searchType = $this->guessType($search);
        $data = $directoryApi->lookup($search, $searchType, $directoryApiLevel);

        if ($data) {
            $person = [
                'netid' => Arr::get($data, 'uid'),
                'email' => Arr::get($data, 'mail'),
                'name' => Arr::get($data, 'displayName.0'),
                'title' => Arr::get($data, 'nuAllTitle.0'),
            ];
        }

        return response()->json([
            'display' => $search,
            'searchType' => $searchType,
            'person' => $person,
        ])->setStatusCode($data ? 200 : 404);
    }

    /**
     * Guess the DirectorySearch lookup type for a given value.
     */
    protected function guessType(string $search): string
    {
        if (str_contains($search, '@')) {
            return DirectoryLookup::SEARCH_TYPE_MAIL;
        }

        if (preg_match('/^[0-9]{7}$/', $search)) {
            return DirectoryLookup::SEARCH_TYPE_EMPLID;
        }

        return DirectoryLookup::SEARCH_TYPE_NETID;
    }
}
