<?php

declare(strict_types=1);

namespace CodeRhapsodie\Bundle\IbexaMailingBundle;

use LogicException;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class IbexaMailingBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $extension = $this->createContainerExtension();
            if (null !== $extension) {
                if (!$extension instanceof ExtensionInterface) {
                    throw new LogicException(
                        sprintf('Extension %s must implement '.ExtensionInterface::class.'.', \get_class($extension))
                    );
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
