<?php

/**
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Opulence\Views\Tests\Mocks;

use Opulence\Views\IViewBuilder;
use Opulence\Views\IView;

/**
 * Mocks a builder for use in testing
 */
class FooBuilder implements IViewBuilder
{
    /**
     * @inheritdoc
     */
    public function build(IView $view): IView
    {
        $view->setVar('foo', 'bar');

        return $view;
    }
}