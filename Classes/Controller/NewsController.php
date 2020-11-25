<?php
declare(strict_types=1);

namespace B13\Newspage\Controller;

/*
 * This file is part of TYPO3 CMS-based extension "newspage" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Newspage\Domain\Repository\NewsRepository;
use B13\Newspage\Service\FilterService;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class NewsController extends ActionController
{
    /**
     * @var array
     */
    protected $preFilters = [];

    /**
     * @var NewsRepository
     */
    protected $newsRepository;

    public function injectNewsRepository(NewsRepository $newsRepository)
    {
        $this->newsRepository = $newsRepository;
    }

    public function listAction(array $filter = []): void
    {
        foreach ($this->settings['prefilters'] as $type => $value) {
            if ($value !== '') {
                $filter[$type] = $value;
                $this->preFilters[] = $type;
            }
        }

        $options['filter'] = $filter;

        $news = $this->newsRepository->findFiltered($options);

        if ($this->settings['filter']['show'] && $this->settings['filter']['by'] !== '') {
            $this->view->assign('filterOptions', $this->getFilterOptions());
        }
        $this->view->assignMultiple([
            'news' => $news,
            'filter' => $filter
        ]);
    }

    public function teaserAction(): void
    {
        $uids = explode(',', $this->settings['news']);
        $news = [];
        foreach ($uids as $uid) {
            $news[] = $this->newsRepository->findByUid((int)$uid);
        }
        $this->view->assign('news', $news);
    }

    public function latestAction(): void
    {
        $settings = [
            'limit' => (int)$this->settings['limit'],
            'filter' => [
                'category' => (int)$this->settings['category']
            ]
        ];
        $news = $this->newsRepository->findLatest($settings);
        $this->view->assign('news', $news);
    }

    protected function getFilterOptions(): array
    {
        $filterOptions = [];
        foreach (explode(',', $this->settings['filter']['by']) as $filter) {
            if (!in_array(strtolower($filter), $this->preFilters)) {
                $filterOptions[$filter]['items'] = FilterService::getFilterOptionsForFluid($filter);
            }
        }
        return $filterOptions;
    }
}
