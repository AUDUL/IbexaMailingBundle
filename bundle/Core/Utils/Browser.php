<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Core\Utils;

class Browser
{
    /**
     * @var string
     */
    private $userAgent;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $platform;

    public function __construct(string $userAgent)
    {
        $bname = 'Unknown';
        $userAgentBrand = 'Unknown';

        // Next get the name of the useragent
        if (stripos($userAgent, 'MSIE') !== false && stripos($userAgent, 'Opera') === false) {
            $bname = 'Internet Explorer';
            $userAgentBrand = 'MSIE';
        } elseif (stripos($userAgent, 'Firefox') !== false) {
            $bname = 'Mozilla Firefox';
            $userAgentBrand = 'Firefox';
        } elseif (stripos($userAgent, 'Chrome') !== false) {
            $bname = 'Google Chrome';
            $userAgentBrand = 'Chrome';
        } elseif (stripos($userAgent, 'Safari') !== false) {
            $bname = 'Apple Safari';
            $userAgentBrand = 'Safari';
        } elseif (stripos($userAgent, 'Opera') !== false) {
            $bname = 'Opera';
            $userAgentBrand = 'Opera';
        } elseif (stripos($userAgent, 'Netscape') !== false) {
            $bname = 'Netscape';
            $userAgentBrand = 'Netscape';
        }

        $this->userAgent = $userAgent;
        $this->name = $bname;
        $this->version = $this->setVersion($userAgentBrand, $userAgent);
        $this->platform = $this->setPlatform($userAgent);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFullName(): string
    {
        return "{$this->name} - ({$this->version})";
    }

    public function getPlatform(): string
    {
        return $this->platform;
    }

    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    private function setPlatform(string $userAgent): string
    {
        $platform = 'Unknown';
        // First get the platform
        if (stripos($userAgent, 'linux') !== false) {
            $platform = 'Linux';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $platform = 'Mac';
        } elseif (preg_match('/windows|win32/i', $userAgent)) {
            $platform = 'Windows';
        }

        return $platform;
    }

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    private function setVersion(string $userAgentBrand, string $userAgent): string
    {
        // finally get the correct version number
        $known = [
            'Version',
            $userAgentBrand,
            'other',
        ];
        $matches = null;
        $version = null;
        $pattern = '#(?<browser>'.implode('|', $known).')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        preg_match_all($pattern, $userAgent, $matches);

        // see how many we have
        if (\count($matches['browser']) > 1) {
            // we will have two since we are not using 'other' argument yet
            // see if version is before or after the name
            if (strripos($userAgent, 'Version') < strripos($userAgent, $userAgentBrand)) {
                $version = $matches['version'][0];
            } else {
                $version = $matches['version'][1];
            }
        } elseif (!empty($matches['version'])) {
            $version = $matches['version'][0];
        }

        // check if we have a number
        if ($version == null || $version == '') {
            $version = '?';
        }

        return $version;
    }
}
