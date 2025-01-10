<?php

declare(strict_types=1);

namespace Tests\Browser\Components;

use Closure;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Component;

class FilterForm extends Component
{
    public function selector(): string
    {
        return '#filterForm';
    }

    public function assert(Browser $browser): void
    {
        $browser->assertPresent($this->selector());
    }

    /**
     * Expand the filter by pressing the Filter toggle button
     */
    public function expand(Browser $browser): Browser
    {
        return $browser
            ->press('#filterToggleButton')
            ->waitFor('@filterOpen');
    }

    /**
     * Assert that the filter is expanded
     */
    public function assertExpanded(Browser $browser): Browser
    {
        return $browser->assertPresent('@filterOpen');
    }

    /**
     * Collapse the filter by pressing the Filter toggle button
     */
    public function collapse(Browser $browser): Browser
    {
        return $browser
            ->press('#filterToggleButton')
            ->waitUsing(5, 100, fn() => $this->assertCollapsed($browser));
    }

    /**
     * Assert that the filter is collapsed
     */
    public function assertCollapsed(Browser $browser): Browser
    {
        return $browser->assertPresent('@filterCollapsed');
    }

    /**
     * Assert that number of active filters is visible on the Filter toggle button
     */
    public function assertActiveFilterCount(Browser $browser, int $count): Browser
    {
        if ($count > 0) {
            return $browser
                ->assertVisible('#filterActiveCountLabel')
                ->assertSeeIn('#filterActiveCount', "$count");
        }

        return $browser
            ->assertPresent('#filterActiveCountLabel .visually-hidden')
            ->assertSeeIn('#filterActiveCount', "0");
    }

    /**
     * Type text into the search field
     */
    public function typeSearchText(Browser $browser, string $searchText): Browser
    {
        return $browser->type('#filterQuery', $searchText);
    }

    /**
     * Assert value of the search field
     */
    public function assertSearchTextIs(Browser $browser, string $searchText): Browser
    {
        return $browser->assertInputValue('#filterQuery', $searchText);
    }

    /**
     * Assert that the Clear filter button is enabled
     */
    public function assertClearFilterEnabled(Browser $browser): Browser
    {
        return $browser->assertVisible('#filterClearButton:not(.disabled)');
    }

    /**
     * Assert that the Clear filter button is disabled
     */
    public function assertClearFilterDisabled(Browser $browser): Browser
    {
        return $browser->assertVisible('#filterClearButton.disabled');
    }

    /**
     * Press the Clear filter button
     */
    public function clearFilter(Browser $browser): Browser
    {
        return $browser->press('#filterClearButton');
    }

    /**
     * Interact with the Content type filter combobox
     */
    public function withContentTypeFilter(Browser $browser, Closure $callback): Browser
    {
        return $this->withChoicesJsDropdown($browser, '@filterContentType', $callback);
    }

    /**
     * Interact with the Language filter combobox
     */
    public function withLanguageFilter(Browser $browser, Closure $callback): Browser
    {
        return $this->withChoicesJsDropdown($browser, '@filterLanguage', $callback);
    }

    /**
     * Interact with the Sort combobox
     */
    public function withSortOrder(Browser $browser, Closure $callback): Browser
    {
        return $this->withChoicesJsDropdown($browser, '@filterSort', $callback);
    }

    /**
     * Interact with a ChoicesJs combobox
     */
    public function withChoicesJsDropdown(Browser $browser, string $selector, Closure $callback): Browser
    {
        return $browser->with(
            $selector,
            fn(Browser $dropdown) =>
            $dropdown->with(new ChoicesJs(), $callback),
        );
    }

    /**
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@filterCollapsed' => '#collapseFilter.collapse:not(.show)',
            '@filterOpen' => '#collapseFilter.collapse.show',
            '@filterLanguage' => '#filterLanguageLabel',
            '@filterContentType' => '#filterContentTypeLabel',
            '@filterSort' => '#filterSortLabel',
        ];
    }
}
