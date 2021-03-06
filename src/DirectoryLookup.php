<?php

namespace Northwestern\SysDev\DirectoryLookupComponent;

use Illuminate\Contracts\Support\MessageBag;
use Illuminate\Support\Arr;
use Illuminate\Validation\Factory;
use Illuminate\Validation\Rule;
use Northwestern\SysDev\DynamicForms\Components\BaseComponent;
use Northwestern\SysDev\SOA\DirectorySearch;

class DirectoryLookup extends BaseComponent
{
    const TYPE = 'nuDirectoryLookup';

    const SEARCH_TYPE_NETID = 'netid';
    const SEARCH_TYPE_MAIL = 'mail';
    const SEARCH_TYPE_EMPLID = 'emplid';

    protected DirectorySearch $api;

    public function __construct(
        string $key,
        ?string $label,
        ?string $errorLabel,
        array $components,
        array $validations,
        bool $hasMultipleValues,
        ?array $conditional,
        ?string $customConditional,
        string $case,
        null|array|string $calculateValue,
        mixed $defaultValue,
        array $additional
    ) {
        // Components are discarded; these are manual mode fields, which is not supported.
        parent::__construct($key, $label, $errorLabel, [], $validations, $hasMultipleValues, $conditional, $customConditional, $case, $calculateValue, $defaultValue, $additional);

        $this->setDirectorySearch(app()->make(DirectorySearch::class));
    }

    public function setDirectorySearch(DirectorySearch $api)
    {
        $this->api = $api;
    }

    protected function processValidations(string $fieldKey, string $fieldLabel, mixed $submissionValue, Factory $validator): MessageBag
    {
        $singleFieldRules = ['nullable', 'string'];
        if ($this->validation('required')) {
            $singleFieldRules = ['string', 'required'];
        }

        $rules = [
            'display' => $singleFieldRules,
            'searchType' => array_merge($singleFieldRules, [
                Rule::in(static::validSearchTypes()),
            ]),
            'person.netid' => $singleFieldRules,
            'person.email' => $singleFieldRules,
            'person.name' => $singleFieldRules,
        ];

        // Make sure the fields we expect to find are actually here
        $errorBag = $validator->make($submissionValue ?? [], $rules)->messages();

        if (! $errorBag->isEmpty()) {
            return $errorBag;
        }

        // Don't try to validate against DS if the person isn't filled in
        if (! Arr::get($submissionValue, 'person')) {
            return $errorBag;
        }

        $errorBag = $this->directoryValidation($submissionValue, $errorBag);

        return $errorBag;
    }

    public static function validSearchTypes(): array
    {
        return [
            self::SEARCH_TYPE_NETID,
            self::SEARCH_TYPE_MAIL,
            self::SEARCH_TYPE_EMPLID
        ];
    }

    /**
     * Perform the DirectorySearch bit of the validation.
     */
    protected function directoryValidation(mixed $submissionValue, MessageBag $errorBag): MessageBag
    {
        $directory = $this->api->lookup($submissionValue['display'], $submissionValue['searchType'], 'basic');
        if (! $directory) {
            $errorBag->add('display', 'Person not found in directory.');

            return $errorBag;
        }

        if (
            Arr::get($directory, 'uid') != $submissionValue['person']['netid']
            || Arr::get($directory, 'mail') != $submissionValue['person']['email']
            || Arr::get($directory, 'displayName.0') != $submissionValue['person']['name']
            || Arr::get($directory, 'nuAllTitle.0') != $submissionValue['person']['title']
        ) {
            $errorBag->add('display', 'Internal error: directory data mismatch');
        }

        return $errorBag;
    }
}
