<?php

/**
 * NovaeZMailingBundle Bundle.
 *
 * @package   Novactive\Bundle\eZMailingBundle
 *
 * @author    Novactive <s.morel@novactive.com>
 * @copyright 2018 Novactive
 * @license   https://github.com/Novactive/NovaeZMailingBundle/blob/master/LICENSE MIT Licence
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZMailingBundle\Validator\Constraints;

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
