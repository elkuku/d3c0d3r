<?php

namespace App\Parser;

use App\Entity\Waypoint;
use App\Service\WayPointHelper;
use UnexpectedValueException;

abstract class AbstractParser
{
    protected WayPointHelper $wayPointHelper;

    public function __construct(WayPointHelper $wayPointHelper)
    {
        $this->wayPointHelper = $wayPointHelper;
    }

    abstract protected function getType(): string;

    /**
     * @return Waypoint[]
     */
    abstract public function parse(array $data): array;

    public function supports(array $data): bool
    {
        $type = $this->gettype();

        if (!$type) {
            throw new UnexpectedValueException(
                'Type is not set in class '.__CLASS__
            );
        }

        return $this->check($type, $data);
    }

    protected function check(string $key, array $data): bool
    {
        return array_key_exists($key, $data) && $data[$key];
    }

    protected function createWayPoint(
        string $guid,
        float $lat,
        float $lon,
        string $name,
        string $imageFileName = null
    ): Waypoint {
        return (new Waypoint())
            ->setGuid($guid)
            ->setName($name)
            ->setLat($lat)
            ->setLon($lon)
            ->setImageFilename($imageFileName);
    }
}
