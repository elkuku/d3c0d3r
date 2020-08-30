<?php

namespace App\Twig;

use App\Entity\Waypoint;
use App\Service\UploaderHelper;
use App\Service\WayPointHelper;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    private $container;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    public function getFunctions(): array
    {
        return [
            new TwigFunction('uploaded_asset', [$this, 'getUploadedAssetPath'])
        ];
    }

    public function getFilters(): array
    {
        return [
            // new TwigFilter('cast_to_array', [$this, 'objectFilter']),
            new TwigFilter('intelLink', [$this, 'intelLink']),
        ];
    }

    public function getUploadedAssetPath(string $path): string
    {
        return $this->container
            ->get(UploaderHelper::class)
            ->getPublicPath($path);
    }
    public static function getSubscribedServices()
    {
        return [
            UploaderHelper::class,
            WayPointHelper::class,
        ];
    }

    public function intelLink(Waypoint $wayPoint): string
    {
        return sprintf(
            '%s/intel?ll=%s,%s&z=17&pll=%s,%s',
            $this->container
                ->get(WayPointHelper::class)
                ->getIntelUrl()
        //    $this->wayPointHelper->getIntelUrl()
        ,
            $wayPoint->getLat(),
            $wayPoint->getLon(),
            $wayPoint->getLat(),
            $wayPoint->getLon(),
        );
    }
}
