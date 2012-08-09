<?php

namespace Ecentinela\ComiconvBundle\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase,
    Symfony\Component\Filesystem\Filesystem;

use Ecentinela\ComiconvBundle\Entity\Conversion;

class ConversionCommandTest extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * Set up test.
     */
    public function setUp()
    {
        // get kernel
        static::$kernel = static::createKernel();
        static::$kernel->boot();

        // get entity manager
        $this->em = static::$kernel->getContainer()
                                   ->get('doctrine.orm.entity_manager');
    }

    /**
     * Test cbz conversion.
     */
    public function testCbz()
    {
        // create the conversion
        $conversion = $this->createConversion('cbz');

        // execute command
        $root = static::$kernel->getRootDir();
        $id = $conversion->getId();

        `php $root/console ecentinela:comiconv:process $id`;

        // test results
    }

    /**
     * Test pdf conversion.
     */
    public function testPdf()
    {
        // create the conversion
        $conversion = $this->createConversion('pdf');

        // execute command
        $root = static::$kernel->getRootDir();
        $id = $conversion->getId();

        `php $root/console ecentinela:comiconv:process $id`;

        // test results
    }

    /**
     * Create a conversion object for the given format.
     *
     * @param  string     $format The format (cbz/pdf).
     * @return Conversion         The created conversion object.
     */
    private function createConversion($format)
    {
        // hash
        $hash = base_convert(sha1(uniqid(mt_rand(), TRUE)), 16, 36);

        // get root path
        $root = static::$kernel->getRootDir();

        // create the file
        $fs = new Filesystem();
        $fs->copy(
            __DIR__.'/../Assets/'.$format.'.'.$format,
            $root.'/../files/input/'.$hash.'/1.'.$format
        );

        // create the conversion
        $conversion = new Conversion();

        $conversion->setHash($hash);

        $conversion->setFormat($format);

        $conversion->setTotalFiles(1);

        $conversion->setUploadedFiles(1);

        $conversion->setStatus('uploaded');

        $conversion->setRetries(0);

        $this->em->persist($conversion);
        $this->em->flush();

        return $conversion;
    }
}
