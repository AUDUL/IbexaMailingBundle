<?php


declare(strict_types=1);

namespace CodeRhapsodie\Bundle\IbexaMailingBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NamesValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        $empty = true;
        foreach ($value as $element) {
            if (null !== $element) {
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
