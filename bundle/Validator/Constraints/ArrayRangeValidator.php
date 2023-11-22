<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ArrayRangeValidator extends ConstraintValidator
{
    /**
     * @param ArrayRange $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        /* @var ArrayRange $constraint */
        foreach ($value as $item) {
            if (!preg_match('/[0-9]{1,2}/', $item) || ($item < $constraint->min) || ($item > $constraint->max)) {
                $this->context->buildViolation($constraint->message)
                              ->setParameter('{{ value }}', (string) $item)
                              ->setParameter('{{ min }}', (string) $constraint->min)
                              ->setParameter('{{ max }}', (string) $constraint->max)
                              ->addViolation();
            }
        }
    }
}
