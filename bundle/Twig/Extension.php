<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Twig;

use Symfony\Component\Intl\Countries;
use Twig\Extension\AbstractExtension as TwigExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;

class Extension extends TwigExtension implements GlobalsInterface
{
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'country_name',
                function ($value) {
                    if ($value !== null) {
                        return Countries::getName($value);
                    }

                    return '';
                }
            ),
        ];
    }

    public function getGlobals(): array
    {
        return [
            'ibexamailing' => [
                'dateformat' => [
                    'date' => 'short',
                    'time' => 'short',
                ],
            ],
        ];
    }
}
