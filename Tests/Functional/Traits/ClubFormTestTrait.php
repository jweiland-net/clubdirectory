<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Functional\Traits;

use JWeiland\Clubdirectory\Configuration\ExtConf;
use JWeiland\Clubdirectory\Domain\Validator\GeocodingFailureValidator;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;
use TYPO3\CMS\Form\Domain\Model\FormElements\Page;

/**
 * Builds the request, a reduced form definition and the finisher options of
 * the club form based on EXT:form. The form definition does not need the
 * form element "Maps2PoiPosition" of EXT:maps2, as its type is defined here.
 */
trait ClubFormTestTrait
{
    private function createFormRequest(int $clubUid = 0, string $moderationToken = ''): Request
    {
        $frontendTypoScript = new FrontendTypoScript(new RootNode(), [], [], []);
        $frontendTypoScript->setSetupArray([
            'plugin.' => [
                'tx_clubdirectory.' => [
                    'settings.' => [
                        'userGroup' => '1',
                    ],
                ],
            ],
        ]);

        $serverRequest = (new ServerRequest('https://www.example.com/', 'POST'))
            ->withQueryParams([
                'tx_clubdirectory_management' => array_filter([
                    'club' => $clubUid > 0 ? (string)$clubUid : '',
                    'moderationToken' => $moderationToken,
                ]),
            ])
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE)
            ->withAttribute('frontend.typoscript', $frontendTypoScript)
            ->withAttribute('extbase', (new ExtbaseRequestParameters())
                ->setPluginName('Management')
                ->setControllerExtensionName('Clubdirectory')
                ->setControllerName('Management')
                ->setControllerActionName('perform'));

        return new Request($serverRequest);
    }

    private function createFormDefinition(): FormDefinition
    {
        $genericElement = ['implementationClassName' => GenericFormElement::class];
        $formDefinition = new FormDefinition('newClub', [
            'formElementsDefinition' => [
                'Form' => [],
                'Page' => ['implementationClassName' => Page::class],
                'Text' => $genericElement,
                'SingleSelect' => $genericElement,
                'MultiCheckbox' => $genericElement,
                'Maps2PoiPosition' => $genericElement,
            ],
            'validatorsDefinition' => [
                'GeocodingFailure' => ['implementationClassName' => GeocodingFailureValidator::class],
            ],
        ]);

        $page = $formDefinition->createPage('club-data');
        foreach (['title', 'description', 'address-1-street', 'address-1-house-number', 'address-1-zip', 'address-1-city'] as $identifier) {
            $page->createElement($identifier, 'Text');
        }
        $page->createElement('categories', 'MultiCheckbox')->setProperty('options', [7 => 'Sports', 8 => 'Music']);
        $page->createElement('address-1-title', 'SingleSelect')->setProperty('options', [
            'organizationAddress' => 'Organization address',
            'postAddress' => 'Postal address',
            'clubAddress' => 'Club address',
        ]);

        $formDefinition->createPage('map-positions')
            ->createElement('address-1-position', 'Maps2PoiPosition')
            ->setProperty('geocoding', [
                'requiredElement' => 'address-1-title',
                'errorElement' => 'address-1-street',
                'addressElements' => [
                    'street' => 'address-1-street',
                    'houseNumber' => 'address-1-house-number',
                    'zip' => 'address-1-zip',
                    'city' => 'address-1-city',
                ],
            ]);

        return $formDefinition;
    }

    /**
     * The steps for club, categories, frontend user and the first address of
     * the SaveClubFinisher of NewClub.form.yaml. New records are stored on page 1.
     *
     * @return array<int, mixed>
     */
    private function getSaveClubFinisherOptions(): array
    {
        $formDefinition = Yaml::parseFile(__DIR__ . '/../../../Configuration/Form/NewClub.form.yaml');
        $options = array_intersect_key($formDefinition['finishers'][1]['options'], array_flip([1, 2, 3, 4]));
        $options[1]['databaseColumnMappings']['pid']['value'] = '1';
        $options[4]['databaseColumnMappings']['pid']['value'] = '1';

        return $options;
    }

    private function createExtConf(): ExtConf
    {
        return new ExtConf(poiCollectionPid: 1);
    }
}
