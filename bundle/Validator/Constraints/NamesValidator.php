<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NamesValidator extends ConstraintValidator
{
    /**
     * @param Names $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        $empty = true;
        foreach ($value as $element) {
            if ($element !== null) {
                $empty = false;
                break;
            }
        }
        if ($empty) {
            $this->context->buildViolation($constraint->message)
                ->atPath('names')
                ->addViolation();
        }
    }
}
