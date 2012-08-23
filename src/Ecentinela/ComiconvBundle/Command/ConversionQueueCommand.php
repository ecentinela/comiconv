<?php

namespace Ecentinela\ComiconvBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Input\ArrayInput,
    Symfony\Component\Console\Input\InputArgument;

use Ecentinela\ComiconvBundle\Entity\Conversion;

class ConversionQueueCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('ecentinela:comiconv:queue')
             ->setDescription('Start process to consume conversions')
             ->addArgument('number', InputArgument::OPTIONAL, 'Number of conversions to consume (0 = infinite');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // get number of conversions to consume
        $number = $input->getArgument('number');
        $number = $number ? intval($number) : 'infinite';

        // output
        $output->writeLn("Prepared to consume <info>$number</info> conversions");

        // create a query builder to get pending conversions
        $qb = $this->getContainer()
                   ->get('doctrine')
                   ->getRepository('EcentinelaComiconvBundle:Conversion')
                   ->createQueryBuilder('c');

        $qb->where('c.status = :status')
           ->setParameter('status', 'uploaded');

        $qb->andWhere('c.retries < 5');

        $qb->orderBy('c.created_at');

        $consumed = 0;

        while ($consumed < $number || $number == 'infinite') {
            $conversion = $qb->getQuery()
                             ->getOneOrNullResult();

            if ($conversion) {
                // info message
                $output->writeLn('Found conversion <info>'.$conversion->getId().'</info>...');

                // execute the conversion
                $this->process($output, $conversion);

                // info message
                $output->writeLn('done!');

                // increment consumed conversions
                $consumed++;
            }
            else {
                // info message
                $output->writeln('No jobs found, sleeping three seconds');

                // sleep for 3 seconds
                sleep(3);
            }
        }
    }

    /**
     * Process the given conversion object.
     *
     * @param  OutputInterface $output     The console output object.
     * @param  Conversion      $conversion The conversion to consume.
     * @return boolean                     True when process ends successfully.
     */
    private function process(OutputInterface $output, Conversion $conversion)
    {
        $command = $this->getApplication()
                        ->find('ecentinela:comiconv:process');

        $input = new ArrayInput(array(
            'command' => 'ecentinela:comiconv:process',
            'id' => $conversion->getId()
        ));

        return $command->run($input, $output) == 0;
    }
}
