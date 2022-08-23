<?php
/**
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) CoreShop GmbH (https://www.coreshop.org)
 * @license    https://www.coreshop.org/license     GPLv3 and CCL
 */

declare(strict_types=1);

namespace CoreShop\Component\Index\Interpreter;

use CoreShop\Component\Index\Model\IndexableInterface;
use CoreShop\Component\Index\Model\IndexColumnInterface;
use CoreShop\Component\Registry\ServiceRegistryInterface;

class RelationalNestedInterpreter implements RelationInterpreterInterface
{
    use NestedTrait;

    public function __construct(ServiceRegistryInterface $interpreterRegistry)
    {
        $this->interpreterRegistry = $interpreterRegistry;
    }

    public function interpretRelational(
        mixed $value,
        IndexableInterface $indexable,
        IndexColumnInterface $config,
        array $interpreterConfig = []
    ): array {
        $this->assert($interpreterConfig);

        return $this->loop($value, $interpreterConfig, static function (mixed $value, InterpreterInterface $interpreter, array $interpreterConfig) use ($indexable, $config): mixed {
            if ($interpreter instanceof RelationInterpreterInterface) {
                return $interpreter->interpretRelational($value, $indexable, $config, $interpreterConfig);
            }

            return $value;
        });
    }

    public function interpret(
        mixed $value,
        IndexableInterface $indexable,
        IndexColumnInterface $config,
        array $interpreterConfig = []
    ): mixed {
        $this->assert($interpreterConfig);

        return $this->loop($value, $interpreterConfig, static function (mixed $value, InterpreterInterface $interpreter, array $interpreterConfig) use ($indexable, $config): mixed {
            return $interpreter->interpret($value, $indexable, $config, $interpreterConfig);
        });
    }
}
