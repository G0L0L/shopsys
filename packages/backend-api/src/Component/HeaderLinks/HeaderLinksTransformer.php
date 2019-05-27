<?php

declare(strict_types=1);

namespace Shopsys\BackendApiBundle\Component\HeaderLinks;

use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Symfony\Component\HttpFoundation\Request;

class HeaderLinksTransformer
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult $paginationResult
     * @param string $baseUrl
     * @return \Shopsys\BackendApiBundle\Component\HeaderLinks\HeaderLinks
     */
    public function fromPaginationResult(PaginationResult $paginationResult, string $baseUrl): HeaderLinks
    {
        $links = new HeaderLinks();

        if (!$paginationResult->isFirst()) {
            $firstUrl = $this->createUrlWithParameter($baseUrl, 'page', '1');
            $previousUrl = $this->createUrlWithParameter($baseUrl, 'page', (string)$paginationResult->getPrevious());

            $links = $links
                ->add($firstUrl, 'first')
                ->add($previousUrl, 'prev');
        }

        if (!$paginationResult->isLast()) {
            $nextUrl = $this->createUrlWithParameter($baseUrl, 'page', (string)$paginationResult->getNext());
            $lastUrl = $this->createUrlWithParameter($baseUrl, 'page', (string)$paginationResult->getPageCount());

            $links = $links
                ->add($nextUrl, 'next')
                ->add($lastUrl, 'last');
        }

        return $links;
    }

    /**
     * @param string $baseUrl
     * @param string $parameter
     * @param string $value
     * @return string
     */
    protected function createUrlWithParameter(string $baseUrl, string $parameter, string $value): string
    {
        return Request::create($baseUrl, 'GET', [$parameter => $value])->getUri();
    }
}
