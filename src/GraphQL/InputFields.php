<?php

declare(strict_types=1);

namespace Pgraph\GraphQL;

use GraphQL\Type\Definition\Type;

class InputFields extends Fields
{
    /**
     * Make input fields callable to resolve for input type.
     *
     * @return array
     */
    public function __invoke(): array
    {
        $mappedFields = [];
        foreach ($this->type->fields() as $fieldKey => $fieldData) {
            $field = [];
            if (is_string($fieldKey)) {
                $field['name'] = $fieldKey;
            } else {
                $field['name'] = $fieldData instanceof \Traversable ? $fieldData['name'] : null;
            }
            if ($fieldData instanceof Type) {
                $field['type'] = $fieldData;
            } else {
                $field += $fieldData;
            }
            $mappedFields[] = $field;
        }
        return $mappedFields;
    }
}