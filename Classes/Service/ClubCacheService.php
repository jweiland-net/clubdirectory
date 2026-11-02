<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Service;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\CacheTag;

/**
 * Tags the cached pages showing clubs and flushes them after clubs were
 * changed in frontend.
 *
 * The tag names "<table>" and "<table>_<uid>" are the ones DataHandler
 * (backend) and the automatic cache clearing of Extbase flush for a
 * changed record. So list and detail pages get updated without any
 * TCEMAIN.clearCacheCmd in page TSconfig.
 */
readonly class ClubCacheService
{
    private const TABLE_CLUB = 'tx_clubdirectory_domain_model_club';

    public function __construct(
        private CacheManager $cacheManager,
    ) {}

    /**
     * A list of clubs has to be updated, if any club gets changed
     */
    public function addCacheTagsForClubList(ServerRequestInterface $request): void
    {
        $request->getAttribute('frontend.cache.collector')?->addCacheTags(
            new CacheTag(self::TABLE_CLUB),
        );
    }

    public function addCacheTagsForClub(ServerRequestInterface $request, int $clubUid): void
    {
        $request->getAttribute('frontend.cache.collector')?->addCacheTags(
            new CacheTag(self::TABLE_CLUB . '_' . $clubUid),
        );
    }

    /**
     * Call it after the club was persisted. Otherwise, a concurrent
     * request could cache the page with the old record again.
     */
    public function flushCachesOfClub(int $clubUid): void
    {
        $this->cacheManager->flushCachesInGroupByTags('pages', [
            self::TABLE_CLUB,
            self::TABLE_CLUB . '_' . $clubUid,
        ]);
    }
}
