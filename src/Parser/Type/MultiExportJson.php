<?php

namespace App\Parser\Type;

use App\Parser\AbstractParser;
use JsonException;

class MultiExportJson extends AbstractParser
{
    protected function getType(): string
    {
        return 'multiexportjson';
    }

    public function parse(array $data): array
    {
        $waypoints = [];
        $errors = [];
        try {
            $items = json_decode(
                $data[$this->getType()],
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $e) {
            throw new \UnexpectedValueException(
                'Invalid multiexport JSON data'
            );
        }

        foreach ($items as $item) {
            if (!$guid = $item['guid'] ?? '') {
                $errors[] = $item;
                continue;
            }

            if (!$title = $item['title'] ?? '') {
                $errors[] = $item;
                continue;
            }

            $lat = $item['coordinates']['lat'];
            $lon = $item['coordinates']['lng'];
            $imageUrl = $item['image'] ?? null;

            $imageFileName = null;

            if ($imageUrl) {
                $imageFileName = $this->wayPointHelper->processImage($guid, $imageUrl);
            }

            $waypoints[] = $this->createWayPoint($guid, $lat, $lon, $title, $imageFileName);
        }

        return [
            'waypoints' => $waypoints,
            'errors' => $errors,
        ];
    }
}
