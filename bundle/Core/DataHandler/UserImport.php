<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Core\DataHandler;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

class UserImport
{
    /**
     * @var File
     *
     * @Assert\NotBlank()
     *
     * @Assert\File(
     *     mimeTypes={"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" , "text/csv" , "text/plain"},
     *     mimeTypesMessage="Please upload a valid file (xls, xlsx , csv)"
     * )
     */
    private $file;

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(File $file): self
    {
        $this->file = $file;

        return $this;
    }
}
