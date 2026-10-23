<?php

namespace Icinga\Module\Map\ProvidedHook;

use Icinga\Module\Map\Common\ModuleConfig;

use Icinga\Application\Hook\CspHook;
use Icinga\User;
use Icinga\Application\Icinga;

use ipl\Web\Common\Csp as CspInstance;

class Csp extends CspHook
{
    /**
     * replaceSubdomainPlaceholder makes sure the {s} placeholder is replaced by a CSP wildcard
     **/
    private function replaceSubdomainPlaceholder(string $host): string
    {
        return preg_replace('/[^.]*\{s\}[^.]*/', '*', $host);
    }

    /**
     * prepareTileURL makes sure the given URL is formatted for use in CSP
     **/
    private function prepareTileURL(string $url, string $defaultScheme = 'https'): string
    {
        $u = parse_url($url);
        $port = $u['port'] ?? 0;
        $host = $u['host'] ?? '';
        $scheme = $u['scheme'] ?? $defaultScheme;

        $src = $scheme . '://';
        $src .= $this->replaceSubdomainPlaceholder($host);

        if ($port !== 0) {
            $src = $src . ':' . $port;
        }

        return $src;
    }

    public function getCspForUser(User $user): CspInstance
    {
        $csp = new CspInstance();

        $request = Icinga::app()->getRequest();
        $currentScheme = $request->getScheme();

        $userPreferences = $user->getPreferences();
        if ($userPreferences->has('map')) {
            $c = $userPreferences->get('map');
            $src = $this->prepareTileURL($c['tile_url'] ?? '', $currentScheme);

            if ($src !== '') {
                $csp->add('img-src', $src);
            }
        }

        return $csp;
    }

    public function getCspForAllUsers(): CspInstance
    {
        $tileURLs = ModuleConfig::getTileURLs();

        $request = Icinga::app()->getRequest();
        $currentScheme = $request->getScheme();

        $csp = new CspInstance();

        foreach ($tileURLs as $url) {
            $src = $this->prepareTileURL($url, $currentScheme);

            if ($src === '') {
                continue;
            }

            $csp->add('img-src', $src);
        }

        return $csp;
    }
}
