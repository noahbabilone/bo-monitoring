<?php

namespace AppBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InvoiceTesterCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:service:invoice')
            // the short description shown while running "php bin/console list"
            ->setDescription('Test invoice services of freebox ')
            ->addOption('customer', 'c', InputOption::VALUE_OPTIONAL, 'option to run tests only on the invoice of a customer')
            ->addOption('ftp', 'f', InputOption::VALUE_OPTIONAL, 'option to run tests on the ftp connection')
            ->addOption('prod', 'p', InputOption::VALUE_OPTIONAL, 'option to run test in prod or dev');

        $this->setHelp(<<<EOT
<info>%command.name% --customer</info> (Test invoice for a customer)
<info>%command.name% --all</info> (Test All invoices)

EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $customer = $input->getOption('customer');
        $ftp = $input->getOption('ftp');
        $prod = $input->getOption('prod') == 'dev' ? false : true;

        if ($ftp !== null) {
            if (in_array($ftp, ['hotvideo', 'pinktv', 'brazzers'])) {
                $output->writeln("Beginning of the customer test");
                $this->getContainer()->get('app.service.invoice')->testFTP($output, $ftp, $prod);
                $output->writeln("END: FTP TEST !!");
            } else if ($ftp == 'all') {
                $output->writeln("Beginning of the customer test");
                $this->getContainer()->get('app.service.invoice')->testAllFTP($output, $prod);
                $output->writeln("END: FTP TEST !!");
            } else {
                $output->writeln(sprintf('    <info>%s --customer</info> Which customer do you want to test ?(e.g. --ftp customerName) [hotvideo, pinktv, brazzers, all]', $this->getName()));
            }
        } else if ($customer !== null) {
            if (in_array($customer, ['hotvideo', 'pinktv', 'brazzers'])) {
                $output->writeln("Beginning of the customer test");
                $this->getContainer()->get('app.service.invoice')->testInvoice($output, $customer, $prod);
                $output->writeln("END Command!!");
            } else if ($customer == 'all') {
                $this->getContainer()->get('app.service.invoice')->testAllInvoices($output, $prod);
                $output->writeln("END Command!!");
            } else {
                $output->writeln(sprintf('    <info>%s --customer</info> Which customer do you want to test ?(e.g. --customer customerName) [hotvideo, pinktv, brazzers, all]', $this->getName()));
            }
        } else {

//            $output->writeln(sprintf('    <info>%s --all</info> option to run all customers tests (by default)', $this->getName()));
            $output->writeln(sprintf('    <info>%s --customer</info> option to run tests only on the invoice of a customer (e.g. --customer customerName, Ex: --customer hotvideo)', $this->getName()));
            $output->writeln(sprintf('    <info>%s --ftp</info> option to run tests on the ftp connection  (e.g. --ftp customerName, Ex: --ftp hotvideo)', $this->getName()));

        }
        return true;
    }
}