<?php



declare(strict_types=1);

namespace CodeRhapsodie\Bundle\IbexaMailingBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ArrayRange extends Constraint
{
    /**
     * @var string
     */
    public $message = 'The value "{{ value }}" is invalid or out of range {{ min }} {{ max }}.';

    /**
     * @var int
     */
    public $min;

    /**
     * @var int
     */
    public $max;

    public function validatedBy(): string
    {
        return ArrayRangeValidator::class;
    }
}
