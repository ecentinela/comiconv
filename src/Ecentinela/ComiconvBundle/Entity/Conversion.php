<?php

namespace Ecentinela\ComiconvBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Ecentinela\ComiconvBundle\Entity\Conversion
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Ecentinela\ComiconvBundle\Entity\ConversionRepository")
 */
class Conversion
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $hash
     *
     * @ORM\Column(name="hash", type="string", length=255)
     */
    private $hash;

    /**
     * @var string $format
     *
     * @ORM\Column(name="format", type="string", length=255)
     */
    private $format;

    /**
     * @var string $email
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * @var integer $total_files
     *
     * @ORM\Column(name="total_files", type="integer")
     */
    private $total_files;

    /**
     * @var integer $uploaded_files
     *
     * @ORM\Column(name="uploaded_files", type="integer")
     */
    private $uploaded_files;

    /**
     * @var string $status
     *
     * @ORM\Column(name="status", type="string", length=255)
     */
    private $status;

    /**
     * @var smallint $retries
     *
     * @ORM\Column(name="retries", type="smallint")
     */
    private $retries;

    /**
     * @var datetime $created_at
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $created_at;

    /**
     * @var datetime $updated_at
     *
     * @ORM\Column(name="updated_at", type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $updated_at;

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
     * Set hash
     *
     * @param string $hash
     * @return Conversion
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Get hash
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set format
     *
     * @param string $format
     * @return Conversion
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Get format
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Conversion
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set total_files
     *
     * @param integer $totalFiles
     * @return Conversion
     */
    public function setTotalFiles($totalFiles)
    {
        $this->total_files = $totalFiles;

        return $this;
    }

    /**
     * Get total_files
     *
     * @return integer
     */
    public function getTotalFiles()
    {
        return $this->total_files;
    }

    /**
     * Set uploaded_files
     *
     * @param integer $uploadedFiles
     * @return Conversion
     */
    public function setUploadedFiles($uploadedFiles)
    {
        $this->uploaded_files = $uploadedFiles;

        return $this;
    }

    /**
     * Get uploaded_files
     *
     * @return integer
     */
    public function getUploadedFiles()
    {
        return $this->uploaded_files;
    }

    /**
     * Set status
     *
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set retries
     *
     * @param smallint $retries
     * @return Conversion
     */
    public function setRetries($retries)
    {
        $this->retries = $retries;

        return $this;
    }

    /**
     * Get retries
     *
     * @return smallint
     */
    public function getRetries()
    {
        return $this->retries;
    }

    /**
     * Set created_at
     *
     * @param datetime $createdAt
     * @return Conversion
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get created_at
     *
     * @return datetime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set updated_at
     *
     * @param datetime $updatedAt
     * @return Conversion
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;

        return $this;
    }

    /**
     * Get updated_at
     *
     * @return datetime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }
}
