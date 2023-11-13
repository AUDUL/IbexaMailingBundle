<?php



declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Names extends Constraint
{
    public $message = 'The Name should be NOT empty.';

    public function validatedBy(): string
    {
        return NamesValidator::class;
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
