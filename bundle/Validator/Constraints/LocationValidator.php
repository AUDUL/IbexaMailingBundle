<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class LocationValidator extends ConstraintValidator
{
    /**
     * @param Location $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if ($value === null) {
            $this->context->buildViolation($constraint->message)
                          ->atPath('locationId')
                          ->addViolation();
        }
    }
}
