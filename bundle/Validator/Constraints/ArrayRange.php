<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ArrayRange extends Constraint
{
    public string $message = 'The value "{{ value }}" is invalid or out of range {{ min }} {{ max }}.';
    public int $min;

    public int $max;

    public function validatedBy(): string
    {
        return ArrayRangeValidator::class;
    }
}
