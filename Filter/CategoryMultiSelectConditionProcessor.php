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

class CategoryMultiSelectConditionProcessor implements FilterConditionProcessorInterface
{
    public function prepareValuesForRendering(FilterConditionInterface $condition, FilterInterface $filter, ListingInterface $list, array $currentFilter): array
    {
        $field = 'categoryIds';
        $includeSubCategories = $condition->getConfiguration()['includeSubCategories'];
        if ($includeSubCategories === true) {
            $field = 'parentCategoryIds';
        }

        $parsedValues = [];
        $rawValues = $list->getGroupByValues($field, true);

        foreach ($rawValues as $v) {
            $explode = explode(',', $v['value']);
            foreach ($explode as $e) {
                if (empty($e)) {
                    continue;
                }

                if (isset($parsedValues[$e])) {
                    $count = $parsedValues[$e]['count'] + (int)$v['count'];
                } else {
                    $count = (int)$v['count'];
                }

                $parsedValues[$e] = ['value' => $e, 'count' => $count];
            }
        }

        $values = array_values($parsedValues);

        $objects = [];
        foreach ($values as $value) {
            $object = Concrete::getById((int)$value['value']);
            if ($object instanceof Concrete) {
                $objects[] = $object;
            }
        }

        return [
            'type' => 'category_multiselect',
            'label' => $condition->getLabel(),
            'currentValues' => $currentFilter[$field],
            'includeSubCategories' => $includeSubCategories,
            'values' => $values,
            'objects' => $objects,
            'fieldName' => $field,
        ];
    }

    public function addCondition(FilterConditionInterface $condition, FilterInterface $filter, ListingInterface $list, array $currentFilter, ParameterBag $parameterBag, bool $isPrecondition = false): array
    {
        $field = 'categoryIds';
        $includeSubCategories = $condition->getConfiguration()['includeSubCategories'];
        if ($includeSubCategories === true) {
            $field = 'parentCategoryIds';
        }

        $values = $parameterBag->get($field);

        if (empty($values)) {
            $values = $condition->getConfiguration()['preSelects'];
        }

        $currentFilter[$field] = $values;

        if ($values === static::EMPTY_STRING) {
            $values = null;
        }

        if (!empty($values)) {
            $fieldName = $isPrecondition ? 'PRECONDITION_' . $field : $field;

            $likeConditions = [];

            foreach ($values as $v) {
                $v = ',' . $v . ',';

                $likeConditions[] = new LikeCondition($field, 'both', $v);
            }

            unset($v);

            $list->addCondition(new ConcatCondition($field, 'OR', $likeConditions), $fieldName);
        }

        return $currentFilter;
    }
}
