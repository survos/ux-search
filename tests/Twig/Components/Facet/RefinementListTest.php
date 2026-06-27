<?php

/*
 * This file is part of the UxSearch project.
 *
 * (c) Mezcalito (https://www.mezcalito.fr)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Mezcalito\UxSearchBundle\Tests\Twig\Components\Facet;

use Mezcalito\UxSearchBundle\Context\Context;
use Mezcalito\UxSearchBundle\Search\Facet;
use Mezcalito\UxSearchBundle\Search\Filter\TermFilter;
use Mezcalito\UxSearchBundle\Search\Query;
use Mezcalito\UxSearchBundle\Search\ResultSet\FacetTermDistribution;
use Mezcalito\UxSearchBundle\Search\ResultSet\ResultSet;
use Mezcalito\UxSearchBundle\Search\SearchInterface;
use Mezcalito\UxSearchBundle\Tests\Twig\Components\AbstractComponentTestCase;
use Mezcalito\UxSearchBundle\Twig\Components\Facet\RefinementList;
use Symfony\UX\TwigComponent\Test\InteractsWithTwigComponents;

class RefinementListTest extends AbstractComponentTestCase
{
    use InteractsWithTwigComponents;

    public function testComponentRenders(): void
    {
        $search = $this->createStub(SearchInterface::class);
        $search->method('getFacet')
            ->willReturn(new Facet('brand', 'Brand'));

        $context = new Context();
        $context->setQuery((new Query())->addActiveFilter(new TermFilter('brand')));
        $context->setSearch($search);
        $context->setResults((new ResultSet())->setFacetDistributions([
            (new FacetTermDistribution())
                ->setProperty('brand')
                ->setValues([
                    'GoPro' => 10,
                    'Apple' => 50,
                    'Samsung' => 20,
                ])
                ->setCheckedValues(['Apple']),
        ]));

        $this->setCurrentContext($context);

        $rendered = $this->renderTwigComponent(
            name: RefinementList::class,
            data: ['property' => 'brand'],
        );

        // Label
        $this->assertStringContainsString('<span class="ux-search-refinement-list__title-text">Brand</span>', $rendered->toString());
        $this->assertStringContainsString('class="ux-search-refinement-list__sort form-select form-select-sm w-auto"', $rendered->toString());
        $this->assertStringContainsString('<option value="count_desc">Most Results</option>', $rendered->toString());
        $this->assertStringContainsString('data-value-type="string">A → Z</option>', $rendered->toString());
        $this->assertStringNotContainsString('class="ux-search-refinement-list__search-input ux-search-input form-control form-control-sm"', $rendered->toString());

        // GoPro
        $this->assertStringContainsString('<label class="ux-search-refinement-list__label" for="brand-GoPro">', $rendered->toString());
        $this->assertStringContainsString('<span class="ux-search-refinement-list__label-text" data-ux-search--refinement-list-target="label">GoPro</span>', $rendered->toString());
        $this->assertStringContainsString('<span class="ux-search-refinement-list__count" data-ux-search--refinement-list-target="count">10</span>', $rendered->toString());

        // Apple
        $this->assertStringContainsString('<label class="ux-search-refinement-list__label" for="brand-Apple">', $rendered->toString());
        $this->assertStringContainsString('<span class="ux-search-refinement-list__label-text" data-ux-search--refinement-list-target="label">Apple</span>', $rendered->toString());
        $this->assertStringContainsString('<span class="ux-search-refinement-list__count" data-ux-search--refinement-list-target="count">50</span>', $rendered->toString());
        $this->assertStringContainsString('id="brand-Apple" checked data-action="live#action"', $rendered->toString());

        // Samsung
        $this->assertStringContainsString('<label class="ux-search-refinement-list__label" for="brand-Samsung">', $rendered->toString());
        $this->assertStringContainsString('<span class="ux-search-refinement-list__label-text" data-ux-search--refinement-list-target="label">Samsung</span>', $rendered->toString());
        $this->assertStringContainsString('<span class="ux-search-refinement-list__count" data-ux-search--refinement-list-target="count">20</span>', $rendered->toString());
    }

    public function testComponentRendersSearchWhenValuesExceedLimit(): void
    {
        $search = $this->createStub(SearchInterface::class);
        $search->method('getFacet')
            ->willReturn(new Facet('brand', 'Brand', null, ['limit' => 2]));

        $context = new Context();
        $context->setQuery(new Query());
        $context->setSearch($search);
        $context->setResults((new ResultSet())->setFacetDistributions([
            (new FacetTermDistribution())
                ->setProperty('brand')
                ->setValues([
                    'GoPro' => 10,
                    'Apple' => 50,
                    'Samsung' => 20,
                ]),
        ]));

        $this->setCurrentContext($context);

        $rendered = $this->renderTwigComponent(
            name: RefinementList::class,
            data: ['property' => 'brand'],
        );

        $this->assertStringContainsString('class="ux-search-refinement-list__sort form-select form-select-sm w-auto"', $rendered->toString());
        $this->assertStringContainsString('class="ux-search-refinement-list__search-input ux-search-input form-control form-control-sm"', $rendered->toString());
        $this->assertStringContainsString('data-action="input-&gt;ux-search--refinement-list#search"', $rendered->toString());
        $this->assertStringContainsString('class="ux-search-refinement-list__show-more"', $rendered->toString());
    }

    public function testComponentHidesControlsForSingleValue(): void
    {
        $search = $this->createStub(SearchInterface::class);
        $search->method('getFacet')
            ->willReturn(new Facet('brand', 'Brand'));

        $context = new Context();
        $context->setQuery(new Query());
        $context->setSearch($search);
        $context->setResults((new ResultSet())->setFacetDistributions([
            (new FacetTermDistribution())
                ->setProperty('brand')
                ->setValues(['Apple' => 50]),
        ]));

        $this->setCurrentContext($context);

        $rendered = $this->renderTwigComponent(
            name: RefinementList::class,
            data: ['property' => 'brand'],
        );

        $this->assertStringNotContainsString('ux-search-refinement-list__sort', $rendered->toString());
        $this->assertStringNotContainsString('ux-search-refinement-list__search-input', $rendered->toString());
    }
}
