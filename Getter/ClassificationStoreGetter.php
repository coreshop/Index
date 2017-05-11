<?php
/**
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2015-2017 Dominik Pfaffenbauer (https://www.pfaffenbauer.at)
 * @license    https://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
*/

namespace CoreShop\Component\Index\Getter;

use CoreShop\Component\Index\Model\IndexColumnInterface;
use CoreShop\Component\Resource\Pimcore\Model\PimcoreModelInterface;

class ClassificationStoreGetter implements GetterInterface
{
    /**
     * {@inheritdoc}
     */
    public function get(PimcoreModelInterface $object, IndexColumnInterface $config)
    {
        $columnConfig = $config->getConfiguration();

        $classificationStore = $config->getGetterConfig()['classificationStoreField'];
        $classificationStoreGetter = 'get'.ucfirst($classificationStore);

        if (method_exists($object, $classificationStoreGetter)) {
            $classificationStore = $object->$classificationStoreGetter();

            if ($classificationStore instanceof \Pimcore\Model\Object\Classificationstore) {
                return $classificationStore->getLocalizedKeyValue($columnConfig['groupConfigId'], $columnConfig['keyConfigId']);
            }
        }

        return null;
    }
}
