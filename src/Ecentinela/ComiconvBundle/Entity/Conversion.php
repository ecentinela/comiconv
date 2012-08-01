<?php

namespace Ecentinela\ComiconvBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
     * @var string $input
     *
     * @ORM\Column(name="input", type="string", length=255)
     */
    private $input;

    /**
     * @var string $output
     *
     * @ORM\Column(name="output", type="string", length=255)
     */
    private $output;

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
     */
    private $created_at;

    /**
     * @var datetime $updated_at
     *
     * @ORM\Column(name="updated_at", type="datetime")
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
     * Set input
     *
     * @param string $input
     * @return Conversion
     */
    public function setInput($input)
    {
        $this->input = $input;

        return $this;
    }

    /**
     * Get input
     *
     * @return string
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Set output
     *
     * @param string $output
     * @return Conversion
     */
    public function setOutput($output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * Get output
     *
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
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