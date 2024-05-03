<?php

declare(strict_types=1);

namespace Tests\Browser\Components;

use Facebook\WebDriver\Exception\TimeoutException;
use InvalidArgumentException;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Component;
use PHPUnit\Framework\ExpectationFailedException;

class ChoicesJs extends Component
{
    public function selector(): string
    {
        return '.choices';
    }

    public function assert(Browser $browser): void
    {
        $browser->assertPresent($this->selector());
    }

    /**
     * Select option where data-value is equal to $value
     *
     * @throws InvalidArgumentException
     * @throws TimeoutException
     */
    public function selectOption(Browser $browser, string $value): Browser
    {
        try {
            return $browser
                ->press('')
                ->waitFor('@optionListOpen')
                ->press("@availableOptionItem[data-value='$value']");
        } catch (TimeoutException $e) {
            throw new TimeoutException('The options list did not open in time', $e->getResults());
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException('Could not select option with data-value "' . $value . '", option was not found.', $e->getCode(), $e);
        }
    }

    /**
     * For multiselect, click the remove button on a selected option
     */
    public function removeSelectedOption(Browser $browser, string $value): Browser
    {
        try {
            return $browser->press("@selectedOptionItem[data-value='$value'] button");
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException('Could not remove option with data-value "' . $value . '", button was not found.', $e->getCode(), $e);
        }
    }

    /**
     * Type text into the options filter
     */
    public function typeOptionsFilter(Browser $browser, string $value): Browser
    {
        return $browser
            ->press('')
            ->type('@optionsFilter', $value);
    }

    /**
     * True if option with data-value equal to $value is available, for multiselects a selected option is not available
     *
     * @throws ExpectationFailedException
     */
    public function assertHasOption(Browser $browser, string $value): Browser
    {
        try {
            return $browser->assertPresent("@availableOptionItem[data-value='$value']");
        } catch (ExpectationFailedException $e) {
            throw new ExpectationFailedException('Failed asserting that option with data-value "' . $value . '" is available.', $e->getComparisonFailure(), $e);
        }
    }

    /**
     * True if option with data-value equal to $value is not present
     *
     * @throws ExpectationFailedException
     */
    public function assertNotHasOption(Browser $browser, string $value): Browser
    {
        try {
            return $browser->assertMissing("@availableOptionItem[data-value='$value']");
        } catch (ExpectationFailedException $e) {
            throw new ExpectationFailedException('Failed asserting that option with data-value "' . $value . '" is missing.', $e->getComparisonFailure(), $e);
        }
    }

    /**
     * True if option with data-value equal to $value is selected
     *
     * @throws ExpectationFailedException
     */
    public function assertOptionSelected(Browser $browser, string $value): Browser
    {
        try {
            return $browser->assertPresent("@selectedOptionItem[data-value='$value'][aria-selected='true']");
        } catch (ExpectationFailedException $e) {
            throw new ExpectationFailedException('Failed asserting that option with data-value "' . $value . '" is selected.', $e->getComparisonFailure(), $e);
        }
    }

    /**
     * True if no options have been added to the dropdown, or, for multiselects, all options are selected
     *
     * @throws ExpectationFailedException
     */
    public function assertNoOptionsAvailable(Browser $browser): Browser
    {
        try {
            return $browser->assertPresent('@availableOptionItem.has-no-choices');
        } catch (ExpectationFailedException $e) {
            throw new ExpectationFailedException('Failed asserting that no options are available.', $e->getComparisonFailure(), $e);
        }
    }

    /**
     * True if no options are found when searching/filtering the options list
     *
     * @throws ExpectationFailedException
     */
    public function assertNoOptionsFound(Browser $browser): Browser
    {
        try {
            return $browser->assertPresent('@availableOptionItem.has-no-results');
        } catch (ExpectationFailedException $e) {
            throw new ExpectationFailedException('Failed asserting that no options was found.', $e->getComparisonFailure(), $e);
        }
    }

    /**
     * True if no options are selected
     *
     * @throws ExpectationFailedException
     */
    public function assertNoOptionsSelected(Browser $browser): Browser
    {
        try {
            return $browser->assertNotPresent('@selectedOptionItem');
        } catch (ExpectationFailedException $e) {
            throw new ExpectationFailedException('Failed asserting that no options are selected.', $e->getComparisonFailure(), $e);
        }
    }

    /**
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@optionListOpen' => '.choices__list--dropdown.is-active',
            '@availableOptionItem' => '.choices__list--dropdown .choices__item',
            '@selectedOptionItem' => '.choices__inner .choices__list .choices__item',
            '@optionsFilter' => 'input[name="search_terms"]',
        ];
    }
}
