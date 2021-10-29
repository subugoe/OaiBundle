<?php

namespace Subugoe\OaiBundle\Model;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\ExclusionPolicy(Serializer\ExclusionPolicy::ALL)
 */
class Collection
{
    /**
     * @var string
     * @Serializer\Expose()
     */
    private $description;

    /**
     * @var string
     * @Serializer\Expose()
     * @Serializer\SerializedName("setSpec")
     * @Serializer\XmlElement(cdata=false)
     */
    private $id;

    /**
     * @var string
     * @Serializer\Expose()
     */
    private $image;
    /**
     * @var string
     * @Serializer\Expose()
     * @Serializer\SerializedName("setName")
     */
    private $label;

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return Collection
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection
     */
    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return Collection
     */
    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection
     */
    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }
}
