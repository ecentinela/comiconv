<?php

namespace Ecentinela\ComiconvBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Filesystem\Filesystem;

use Ecentinela\ComiconvBundle\Entity\Conversion;

class CleanFilesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('ecentinela:comiconv:clean')
             ->setDescription('Clean the files from old conversion');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // info message
        $output->write('Getting conversions to <info>clear</info>... ');

        // get container
        $container = $this->getContainer();

        // get entity manager
        $em = $container->get('doctrine')
                        ->getManager();

        // get the conversion to process
        $qb = $em->getRepository('EcentinelaComiconvBundle:Conversion')
                 ->createQueryBuilder('c');

        $qb->where('c.created_at < :removed_at')
           ->setParameter('removed_at', new \DateTime())
           ->andWhere('c.status = :status')
           ->setParameter('status', 'converted');

        $conversions = $qb->getQuery()
                          ->getResult();

        // info message
        $output->writeLn(count($conversions) . '!');

        if (!empty($conversions)) {
            // get folder for conversion files
            $root = $container->get('kernel')
                              ->getRootDir();

            $paths = array();

            foreach ($conversions as $conversion) {
                $paths[] = $root.'/../files/input/'.$conversion->getHash();
                $paths[] = $root.'/../files/output/'.$conversion->getHash().'.'.$conversion->getFormat();

                $conversion->setStatus('cleaned');
            }

            // info message
            $output->write('<info>Cleaning</info>... ');

            // clear directories and files
            $fs = new Filesystem();
            $fs->remove($paths);

            // info message
            $output->writeLn('done!');

            // update conversions
            $em->flush();
        }
    }}
