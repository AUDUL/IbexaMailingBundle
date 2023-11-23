<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class IbexaMailingBundle extends Bundle
{
    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        if ($this->extension === null) {
            $extension = $this->createContainerExtension();
            if ($extension !== null) {
                if (!$extension instanceof ExtensionInterface) {
                    throw new \LogicException(sprintf('Extension %s must implement '.ExtensionInterface::class.'.', $extension::class));
                }
                $this->extension = $extension;
            } else {
                $this->extension = false;
            }
        }
        if ($this->extension) {
            return $this->extension;
        }

        return null;
    }
}
