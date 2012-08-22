<?php

namespace Ecentinela\ComiconvBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Filesystem\Filesystem,
    Symfony\Component\Finder\Finder,
    Symfony\Component\Finder\SplFileInfo;

use Ecentinela\ComiconvBundle\Entity\Conversion;

class ConversionCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('ecentinela:comiconv:process')
             ->setDescription('Process given conversion')
             ->addArgument('id', InputArgument::REQUIRED, 'Conversion id');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // get the conversion id
        $id = $input->getArgument('id');

        // get the conversion to process
        $conversion = $this->getContainer()
                           ->get('doctrine')
                           ->getRepository('EcentinelaComiconvBundle:Conversion')
                           ->find($id);

        // execute the conversion
        if ($conversion) {
            $this->process($output, $conversion);
        } else {
            $output->writeLn("Conversion <error>$id</error> not found or not ready for conversion");
        }
    }

    /**
     * Executes the given conversion.
     *
     * @param OutputInterface $output     The console output.
     * @param Conversion      $conversion The conversion.
     */
    private function process(OutputInterface $output, Conversion $conversion)
    {
        // get entity manager
        $em = $this->getContainer()
                   ->get('doctrine')
                   ->getManager();

        // get container
        $container = $this->getContainer();

        // get source and destination paths
        $path = $container->get('kernel')->getRootDir();
        $srcPath = $path.'/../files/input/'.$conversion->getHash();
        $dstPath = $path.'/../files/output/'.$conversion->getHash();

        // create directory
        $fs = new Filesystem();
        $fs->remove($dstPath);
        $fs->mkdir($dstPath);

        // get files to convert
        $files = Finder::create()
                       ->files()
                       ->in($srcPath)
                       ->name('/\.(pdf|jpg|zip)/')
                       ->sortByName()
                       ->getIterator();

        $index = 1;

        try {
            // start time
            $time = microtime(true);

            // convert the source files to jpg images
            foreach ($files as $file) {
                switch ($file->getExtension()) {
                    case 'zip':
                    case 'cbz':
                        $this->cbzToJpg($output, $file, $dstPath, $index);
                        break;

                    case 'pdf':
                        $this->pdfToJpg($output, $file, $dstPath, $index);
                        break;

                    case 'jpg':
                    case 'jpeg':
                        rename($file->getPathname(), "$dstPath/$index.jpg");
                        $index++;
                        break;
                }
            }

            // convert the jpg images to the destination file
            switch ($conversion->getFormat()) {
                case 'pdf':
                    $this->jpgToPdf($output, $dstPath);
                    break;

                case 'cbz':
                    $this->jpgToCbz($output, $dstPath);
                    break;
            }

            // refresh conversion (user maybe has inserted it's email while conversion is being done)
            $em->refresh($conversion);

            // update conversion
            $conversion->setStatus('converted');

            $conversion->setConversionTime(
                microtime(true) - $time
            );

            $conversion->setRemovedAt(
                new \DateTime('+1day')
            );

            // send email if conversion has it
            if ($email = $conversion->getEmail()) {
                $message = \Swift_Message::newInstance()
                                         ->setSubject(
                                            $container->get('translator')->trans('email.subject')
                                         )
                                         ->setFrom('no-reply@comiconv.com')
                                         ->setTo($email)
                                         ->setBody(
                                            $container->get('templating')
                                                      ->render('EcentinelaComiconvBundle:Default:email.txt.twig', array(
                                                        'conversion' => $conversion,
                                                        'url' => $container->get('router')->generate('output', array(
                                                            'hash' => $conversion->getHash(),
                                                            '_locale' => 'en'
                                                        ), true)
                                                      ))
                                         );

                if ($container->get('mailer')->send($message)) {
                    $output->writeLn("  sent email to <info>$email</info>");
                }
            }
        } catch (\Exception $e) {
            // info message
            $output->writeLn('  Failed to convert: <error>'.$conversion->getId().'</error> - '.$e->getMessage());

            // update conversion
            $conversion->setRetries(
                $conversion->getRetries() + 1
            );
        }

        // remove the folder
        $fs->remove($dstPath);

        // save conversion changes
        $em->flush();
    }

    /**
     * Extracts the images of the given cbz file to the given directory.
     *
     * @param OutputInterface $output     The console output.
     * @param Conversion      $conversion The conversion.
     * @param SplFileInfo     $file       The cbz file.
     * @param string          $path       The path where to extract the files.
     * @param int             $index      The index to generate the file names.
     */
    private function cbzToJpg(OutputInterface $output, SplFileInfo $file, $path, &$index)
    {
        // create the temp directory
        if (!$tmp = $this->tempdir($path, $index)) {
            throw new \Exception('can not create tmp folder to extract cbz images');
        }

        // create the zip
        $zip = new \ZipArchive();

        $res = $zip->open(
            $file->getPathname()
        );

        if ($res === true) {
            // info message
            $output->write('  Extracting file <info>'.$file->getFilename().'</info>... ');

            // extract the zip
            $zip->extractTo($tmp);
            $zip->close();

            // info message
            $output->writeLn('done!');

            // get the extracted image files
            $files = Finder::create()
                           ->files()
                           ->in($tmp)
                           ->name('/\.jpg/')
                           ->sort(function(SplFileInfo $file1, SplFileInfo $file2) {
                                // extract page number
                                $index1 = intval($file1->getFilename());
                                $index2 = intval($file2->getFilename());

                                return $index1 > $index2 ? 1 : ($index1 < $index2 ? -1 : 0);
                           })
                           ->getIterator();

            // move image files to the destination path
            foreach ($files as $file) {
                rename(
                    $file->getPathname(),
                    "$path/$index.jpg"
                );

                $index++;
            }
        } else {
            throw new \Exception('can not open cbz file');
        }
    }

    /**
     * Extracts the images of the given pdf file to the given directory.
     *
     * @param OutputInterface $output     The console output.
     * @param Conversion      $conversion The conversion.
     * @param SplFileInfo     $file       The pdf file.
     * @param string          $path       The path where to extract the files.
     * @param int             $index      The index to generate the file names.
     */
    private function pdfToJpg(OutputInterface $output, $file, $path, &$index)
    {
        // create the temp directory
        if (!$tmp = $this->tempdir($path, $index)) {
            throw new \Exception('can not create tmp folder to extract pdf images');
        }

        // info message
        $output->write('  Extracting file <info>'.$file->getFilename().'</info>... ');

        // create the image magick file
        $pdf = new \Imagick();

        $pdf->setResolution(300, 450);

        $pdf->readImage(
            $file->getPathname()
        );

        $pdf->setImageFormat('jpg');

        $pdf->setImageCompression(\imagick::COMPRESSION_JPEG);

        $pdf->setImageUnits(\imagick::RESOLUTION_PIXELSPERINCH);

        // extract images from pdf
        foreach ($pdf as $img) {
            $img->writeImage("$path/$index.jpg");

            $index++;
        }

        // info message
        $output->writeLn('done!');
    }

    /**
     * Create a pdf file from the images on the given directory.
     *
     * @param OutputInterface $output The console output.
     * @param string          $path   The path where the files are.
     */
    private function jpgToPdf(OutputInterface $output, $path)
    {
        // info message
        $output->write('  Creating <info>pdf</info> file <info>'.basename($path).'</info>... ');

        // get image files
        $files = Finder::create()
                       ->files()
                       ->in($path)
                       ->name('/\.jpg/')
                       ->depth('< 1')
                       ->sort(function(SplFileInfo $file1, SplFileInfo $file2) {
                            // extract page number
                            $index1 = intval($file1->getFilename());
                            $index2 = intval($file2->getFilename());

                            return $index1 > $index2 ? 1 : ($index1 < $index2 ? -1 : 0);
                       })
                       ->getIterator();

        // get files
        $paths = array();

        foreach ($files as $file) {
            $paths[] = $file->getPathname();
        }

        // create the pdf
        $im = new \Imagick($paths);

        $im->setImageFormat('pdf');

        $im->writeImages("$path.pdf", true);

        // info message
        $output->writeLn('done!');
    }

    /**
     * Convert the images on the destination path to a cbz file.
     *
     * @param OutputInterface $output The console output.
     * @param string          $path   The path where the files are.
     */
    private function jpgToCbz(OutputInterface $output, $path)
    {
        // info message
        $output->write('  Creating <info>cbz</info> file <info>'.basename($path).'</info>... ');

        // get image files
        $files = Finder::create()
                       ->files()
                       ->in($path)
                       ->name('/\.jpg/')
                       ->depth('< 1')
                       ->sort(function(SplFileInfo $file1, SplFileInfo $file2) {
                            // extract page number
                            $index1 = intval($file1->getFilename());
                            $index2 = intval($file2->getFilename());

                            return $index1 > $index2 ? 1 : ($index1 < $index2 ? -1 : 0);
                       })
                       ->getIterator();

        // create the zip
        $zip = new \ZipArchive();

        $zip->open("$path.cbz", \ZIPARCHIVE::CREATE);

        // add images to zip
        foreach ($files as $file) {
            $zip->addFile(
                $file->getPathname(),
                $file->getFilename()
            );
        }

        $zip->close();

        // info message
        $output->writeLn('done!');
    }

    /**
     * Creates a temporary directory.
     *
     * @param  string $dir    The directory where the temporary directory will
     *                        be created.
     * @param  string $prefix The prefix of the generated temporary filename.
     * @return string         The temp directory path.
     */
    private function tempdir($dir = false, $prefix = 'php')
    {
        // create temp file
        $tempfile = tempnam($dir, $prefix);

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
