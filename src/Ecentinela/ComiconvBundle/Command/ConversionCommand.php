<?php

namespace Ecentinela\ComiconvBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class ConversionCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('ecentinela:comiconv:process')
             ->setDescription('Process prepared conversions');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $qb = $this->getContainer()
                   ->get('doctrine')
                   ->getRepository('EcentinelaComiconvBundle:Conversion')
                   ->createQueryBuilder('c');

        $qb->where('c.status = :status')
           ->setParameter('status', 'uploaded');

        $qb->orderBy('c.created_at');

        while (true) {
            $conversion = $qb->getQuery()
                             ->getOneOrNullResult();

            if ($conversion) {
                $this->convert($conversion);
            }
            else {
                $output->writeln('No jobs found, sleeping three seconds');

                sleep(3);
            }
        }
    }

    /**
     * Executes the given conversion.
     */
    private function convert(Conversion $conversion, InputInterface $input, OutputInterface $output)
    {

    }
}
