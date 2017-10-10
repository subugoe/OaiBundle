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
     * @Serializer\SerializedName("setName")
     */
    private $label;

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
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     *
     * @return Collection
     */
    public function setLabel(string $label): Collection
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return Collection
     */
    public function setDescription(string $description): Collection
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return Collection
     */
    public function setId(string $id): Collection
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * @param string $image
     *
     * @return Collection
     */
    public function setImage(string $image): Collection
    {
        $this->image = $image;

        return $this;
    }
}
