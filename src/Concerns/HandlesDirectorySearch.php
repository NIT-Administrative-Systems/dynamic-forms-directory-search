<?php

namespace Northwestern\SysDev\DirectoryLookupComponent\Concerns;

use Illuminate\Http\JsonResponse;
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
                'netid' => $data['uid'],
                'email' => $data['mail'],
                'name' => $data['displayName'][0],
                'title' => $data['nuAllTitle'][0],
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
