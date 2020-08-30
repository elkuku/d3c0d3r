<?php

namespace App\Entity;

use App\Service\UploaderHelper;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\WaypointRepository")
 */
class Waypoint
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name = '';

    /**
     * @ORM\Column(type="decimal", precision=10, scale=6)
     */
    private float $lat = 0;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=6)
     */
    private float $lon = 0;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private ?string $guid = '';

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $imageFilename = '';

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="waypoints")
     */
    private ?Category $category = null;

    public function __toString()
    {
        return (string)$this->getName();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLat(): ?float
    {
        return $this->lat;
    }

    public function setLat(float $lat): self
    {
        $this->lat = $lat;

        return $this;
    }

    public function getLon(): ?float
    {
        return $this->lon;
    }

    public function setLon(float $lon): self
    {
        $this->lon = $lon;

        return $this;
    }

    public function getGuid(): ?string
    {
        return $this->guid;
    }

    public function setGuid(string $guid): self
    {
        $this->guid = $guid;

        return $this;
    }

    public function getImageFilename(): ?string
    {
        return $this->imageFilename;
    }

    public function getImagePath(): string
    {
        // return 'uploads/wp_images/'.$this->getImageFilename();
        return UploaderHelper::WAYPOINT_IMAGE.'/'.$this->getImageFilename();
    }

    public function setImageFilename(?string $imageFilename): self
    {
        $this->imageFilename = $imageFilename;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }
}
