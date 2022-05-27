<?php

declare(strict_types=1);
/**
 * This file is part of the codemagpie/array2object package.
 *
 * (c) CodeMagpie Lyf <https://github.com/codemagpie>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace CodeMagpie\ClassGenerator;

use Hyperf\Utils\CodeGen\Project as HyperfProject;
use Hyperf\Utils\Composer;

class Project extends HyperfProject
{
    protected function getAutoloadRules(): array
    {
        $test = data_get(Composer::getJsonContent(), 'autoload-dev.psr-4', []);
        return array_merge(parent::getAutoloadRules(), $test);
    }
}
