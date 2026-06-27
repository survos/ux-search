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
use Mezcalito\UxSearchBundle\Search\Filter\RangeFilter;
use Mezcalito\UxSearchBundle\Search\Query;
use Mezcalito\UxSearchBundle\Search\ResultSet\FacetStat;
use Mezcalito\UxSearchBundle\Search\ResultSet\ResultSet;
use Mezcalito\UxSearchBundle\Search\SearchInterface;
use Mezcalito\UxSearchBundle\Tests\Twig\Components\AbstractComponentTestCase;
use Mezcalito\UxSearchBundle\Twig\Components\Facet\RangeSlider;
use Symfony\UX\TwigComponent\Test\InteractsWithTwigComponents;

class RangeSliderTest extends AbstractComponentTestCase
{
    use InteractsWithTwigComponents;

    public function testComponentRenders(): void
    {
        $search = $this->createStub(SearchInterface::class);
        $search->method('getFacet')
            ->willReturn(new Facet('price', 'Price', RangeSlider::class));

        $context = new Context();
        $context->setQuery((new Query())->addActiveFilter(new RangeFilter('price', 10, 20)));
        $context->setSearch($search);
        $context->setResults((new ResultSet())->setFacetStats([
            new FacetStat('price', 0, 50, 10, 20),
        ]));

        $this->setCurrentContext($context);

        $rendered = $this->renderTwigComponent(
            name: RangeSlider::class,
            data: ['property' => 'price'],
        );

        // Label
        $this->assertStringContainsString('<span class="ux-search-facet__title-text">Price</span>', $rendered->toString());
        $this->assertStringContainsString('data-ux-search-facet-toggle', $rendered->toString());
        $this->assertStringContainsString('click-&gt;ux-search#toggleFacetCollapse', $rendered->toString());
        $this->assertStringContainsString('class="ux-search-facet__collapse btn btn-action btn-sm"', $rendered->toString());
        $this->assertStringContainsString('aria-expanded="true"', $rendered->toString());
        $this->assertStringContainsString('data-action="ux-search#toggleFacetCollapse"', $rendered->toString());
        $this->assertStringContainsString('class="card-body ux-search-facet__panel"', $rendered->toString());

        $this->assertStringContainsString('id="price-min"', $rendered->toString());
        $this->assertStringContainsString('id="price-max"', $rendered->toString());
        $this->assertSame(2, substr_count($rendered->toString(), 'min="0"'));
        $this->assertSame(2, substr_count($rendered->toString(), 'max="50"'));
        $this->assertStringContainsString('value="10"', $rendered->toString());
        $this->assertStringContainsString('value="20"', $rendered->toString());
        $this->assertStringContainsString('id="price-min-value"', $rendered->toString());
        $this->assertStringContainsString('id="price-max-value"', $rendered->toString());
    }
}
