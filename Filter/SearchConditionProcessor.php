<?php
/**
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) CoreShop GmbH (https://www.coreshop.org)
 * @license    https://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
 */

declare(strict_types=1);

namespace CoreShop\Component\Index\Filter;

use CoreShop\Component\Index\Condition\ConcatCondition;
use CoreShop\Component\Index\Condition\LikeCondition;
use CoreShop\Component\Index\Listing\ListingInterface;
use CoreShop\Component\Index\Model\FilterConditionInterface;
use CoreShop\Component\Index\Model\FilterInterface;
use Pimcore\Model\DataObject\Concrete;
use Symfony\Component\HttpFoundation\ParameterBag;

class SearchConditionProcessor implements FilterConditionProcessorInterface
{
    public function prepareValuesForRendering(FilterConditionInterface $condition, FilterInterface $filter, ListingInterface $list, array $currentFilter): array
    {
        $fields = $condition->getConfiguration()['fields'];

        $objects = $list->load();

        return [
            'type' => 'search',
            'label' => $condition->getLabel(),
            'currentValue' => $currentFilter['searchTerm'],
            'objects' => $objects,
            'fieldName' => 'searchTerm',
        ];
        return [];
    }

    public function addCondition(FilterConditionInterface $condition, FilterInterface $filter, ListingInterface $list, array $currentFilter, ParameterBag $parameterBag, bool $isPrecondition = false): array
    {
        $fields = $condition->getConfiguration()['fields'];

        $value = $parameterBag->get('searchTerm');
        
        if (empty($value)) {
            $value = $condition->getConfiguration()['searchTerm'];
        }

        $currentFilter['searchTerm'] = $value;

        if ($value === static::EMPTY_STRING) {
            $value = null;
        }

        if (!empty($value) && !empty($fields)) {

            $likeConditions = [];

            foreach ($fields as $field) {
                $fieldName = $isPrecondition ? 'PRECONDITION_' . $field : $field;

                $likeConditions[] = new LikeCondition($field, 'both', $value);

                unset($field);
            }

            $concatenator = $condition->getConfiguration()['concatenator'] ? $condition->getConfiguration()['concatenator'] : 'OR';

            $list->addCondition(new ConcatCondition($fieldName, $concatenator, $likeConditions), $fieldName);
        }

        return $currentFilter;
    }
}
