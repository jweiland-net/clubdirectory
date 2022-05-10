<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\EventListener;

use JWeiland\Clubdirectory\Domain\Repository\ClubRepository;
use JWeiland\Glossary2\Service\GlossaryService;
use JWeiland\Clubdirectory\Domain\Repository\SchoolRepository;
use JWeiland\Clubdirectory\Event\PostProcessFluidVariablesEvent;
use TYPO3\CMS\Core\Utility\ArrayUtility;

class AddGlossaryEventListener extends AbstractControllerEventListener
{
    /**
     * @var GlossaryService
     */
    protected $glossaryService;

    /**
     * @var ClubRepository
     */
    protected $clubRepository;

    protected $allowedControllerActions = [
        'Club' => [
            'list',
            'search'
        ]
    ];

    public function __construct(GlossaryService $glossaryService, ClubRepository $clubRepository)
    {
        $this->glossaryService = $glossaryService;
        $this->clubRepository = $clubRepository;
    }

    public function __invoke(PostProcessFluidVariablesEvent $event): void
    {
        if ($this->isValidRequest($event)) {
            $event->addFluidVariable(
                'glossar',
                $this->glossaryService->buildGlossary(
                    $this->clubRepository->getQueryBuilderToFindAllEntries(),
                    $this->getOptions($event)
                )
            );
        }
    }

    protected function getOptions(PostProcessFluidVariablesEvent $event): array
    {
        $options = [
            'extensionName' => 'clubdirectory',
            'pluginName' => 'clubdirectory',
            'controllerName' => 'Club',
            'column' => 'title',
            'settings' => $event->getSettings()
        ];

        if (
            isset($event->getSettings()['glossary'])
            && is_array($event->getSettings()['glossary'])
        ) {
            ArrayUtility::mergeRecursiveWithOverrule($options, $event->getSettings()['glossary']);
        }

        return $options;
    }
}
