<?php

namespace RobotsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use \Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Robot
 */
class Robot
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var int
     */
    private $year;

    static public $types = array(
        'Android'   => 'Android',
        'Mecha'     => 'Mecha',
        'Cyborg'    => 'Cyborg'
    );

    public function __construct() {
        $this->setCreatedAt(new \DateTime());
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Robot
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Robot
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set year
     *
     * @param integer $year
     * @return Robot
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     *
     * @return integer 
     */
    public function getYear()
    {
        return $this->year;
    }

    public static function getTypes()
    {
        $types = [];
        foreach(self::$types as $type)
        {
            $types[]['name'] = $type;
        }
        return $types;
    }
    /**
     * @var \DateTime
     */
    private $deletedAt;


    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     * @return Robot
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime 
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }
    /**
     * @var \DateTime
     */
    private $createdAt;


    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Robot
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Description: Mielőtt töröljük az entitást átnevezzük, hogy ne legyen probléma
     * a softDelete miatt.
     * @ORM\PreRemove
     */
    public function renameDeletedRobot(LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();

        $robot = $args->getEntity();

        // hogy a törlést követően se legyen ütközés
        $robot->setName($robot->getName().'_deleted_'.time());

        $em->persist($robot);
        $em->flush();

    }

}
