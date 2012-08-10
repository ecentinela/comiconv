<?php

namespace Ecentinela\ComiconvBundle\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase,
    Symfony\Component\Filesystem\Filesystem;

use Ecentinela\ComiconvBundle\Entity\Conversion;

/**
 * Test conversion command.
 */
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
        $this->executeConversionCommand($conversion);

        // check files
        $src = $this->extractImagesFromCbz(
            __DIR__.'/../Assets/cbz.cbz'
        );

        $dst = $this->extractImagesFromCbz(
            __DIR__.'/../../../../../files/output/'.$conversion->getHash().'.cbz'
        );

        $this->compareFilesOn($src, $dst);
    }

    /**
     * Test pdf conversion.
     */
    public function testPdf()
    {
        // create the conversion
        $conversion = $this->createConversion('pdf');

        // execute command
        $this->executeConversionCommand($conversion);

        // check files
        $src = $this->extractImagesFromPdf(
            __DIR__.'/../Assets/pdf.pdf'
        );

        $dst = $this->extractImagesFromPdf(
            __DIR__.'/../../../../../files/output/'.$conversion->getHash().'.pdf'
        );

        $this->compareFilesOn($src, $dst);
    }

    /**
     * Create a conversion object for the given format.
     *
     * @param  string     $format The format (cbz/pdf).
     * @return Conversion         The created conversion object.
     */
    private function createConversion($format)
    {
        // extension
        $extension = $format == 'pdf' ? 'pdf' : 'zip';

        // hash
        $hash = base_convert(sha1(uniqid(mt_rand(), TRUE)), 16, 36);

        // get root path
        $root = static::$kernel->getRootDir();

        // create the file
        $fs = new Filesystem();
        $fs->copy(
            __DIR__.'/../Assets/'.$format.'.'.$format,
            $root.'/../files/input/'.$hash.'/1.'.$extension
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

    /**
     * Execute the conversion command for the given conversion object.
     *
     * @param Conversion $conversion The conversion object.
     */
    private function executeConversionCommand(Conversion $conversion)
    {
        $root = static::$kernel->getRootDir();
        $id = $conversion->getId();

        `php $root/console ecentinela:comiconv:process $id`;
    }

    /**
     * Extract the images on the given cbz file.
     *
     * @param  string $path The path where the cbz file is.
     * @return string       The path where images are extracted.
     */
    private function extractImagesFromCbz($path)
    {
        // get a temp directory
        $tmp = $this->tempdir();

        // extract the images
        $zip = new \ZipArchive();
        $zip->open($path);
        $zip->extractTo($tmp);
        $zip->close();

        // return the extracted path
        return $tmp;
    }

    /**
     * Extract the images on the given pdf file.
     *
     * @param  string $path The path where the pdf file is.
     * @return string       The path where images are extracted.
     */
    private function extractImagesFromPdf($path)
    {
        // get a temp directory
        $tmp = $this->tempdir();

        // extract the images
        $pdf = new \Imagick();

        $pdf->setResolution(300, 450);

        $pdf->readImage($path);

        $pdf->setImageFormat('jpg');

        $pdf->setImageCompression(\imagick::COMPRESSION_JPEG);

        $pdf->setImageUnits(\imagick::RESOLUTION_PIXELSPERINCH);

        foreach ($pdf as $i => $img) {
            $index = $i + 1;
            $img->writeImage("$tmp/$index.jpg");
        }

        // return the extracted path
        return $tmp;
    }

    /**
     * Compare files on the given directories.
     *
     * @param string
     * @param string
     */
    private function compareFilesOn($srcTmpPath, $dstTmpPath)
    {
        $srcFiles = glob($srcTmpPath.'/*');
        $dstFiles = glob($dstTmpPath.'/*');

        $this->assertCount(3, $srcFiles);

        $this->assertEquals(
            count($srcFiles),
            count($dstFiles)
        );

        for ($i = 0; $i < count($srcFiles); $i ++) {
            $this->assertEquals(
                file_get_contents($srcFiles[$i]),
                file_get_contents($dstFiles[$i])
            );
        }
    }

    /**
     * Creates a temporary directory.
     *
     * @return string The temp directory path.
     */
    private function tempdir()
    {
        // create temp file
        $tempfile = tempnam(
            sys_get_temp_dir(),
            'conversion'
        );

        // remove the file
        if (file_exists($tempfile)) {
            unlink($tempfile);
        }

        // create a directory with previous temp file name
        mkdir($tempfile);

        // check the folder has been created
        if (is_dir($tempfile)) {
            return $tempfile;
        }
    }
}
