<?php



declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Location extends Constraint
{
    public $message = 'The Content should be selected.';

    public function validatedBy(): string
    {
        return LocationValidator::class;
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
