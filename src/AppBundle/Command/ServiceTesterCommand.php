<?php
namespace AppBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ServiceTesterCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:service:tester')
            // the short description shown while running "php bin/console list"
            ->setDescription('Test freebox services')
            ->addOption('run', 'r', InputOption::VALUE_NONE, 'Run a dry-run test (no notification)')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Run tests with notification')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'option to run all plateforms tests (by default)')
            ->addOption('backends', 'b', InputOption::VALUE_NONE, 'option to run tests only on the prod backends')
            ->addOption('varnish', 'var', InputOption::VALUE_NONE, 'option to run tests only on the active varnish');
        $this->setHelp(<<<EOT
Test freebox services. Option --run is required

<info>%command.name% --run</info> (run a dry-run test, no notification is send)

Choose the test to be execute (by default --all is selected):

<info>%command.name% --backends</info> (Prod)
OR
<info>%command.name% --varnish</info> (Varnish actif)
OR
<info>%command.name% --all</info> (Varnish actif & Backends Prod)

Last:

<info>%command.name% --force</info> Allow you to enable notification service during the test



EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        // PARAM TESTER --------------------------------------------
        $force = true === $input->getOption('force');
        $run = true === $input->getOption('run');
        $backends = true === $input->getOption('backends');
        $varnish = true === $input->getOption('varnish');
        $all = true === $input->getOption('all');
        //-----------------------------------------------------------
        if (!$backends && !$varnish)
            $all = true;

        if ($run) {
            if ($backends || $all) {
                $output->writeln('Test des Backends  EN COURS...');
                $this->getContainer()->get('app.service.tester')->testBackends($output, $force);
                $output->writeln('Test des Backends TERMINE...');
                
                $output->writeln('Test des URLs Viacom EN COURS...');
                $this->getContainer()->get('app.service.tester')->testExternalLinks($output, $force);
                $output->writeln('Test des URL Viacom TERMINE...');

            }

            if ($varnish || $all) {
                $output->writeln('Test du Varnish Actif EN COURS...');
                $this->getContainer()->get('app.service.tester')->testVarnish($output, $force);
                $output->writeln('Test du Varnish Actif TERMINE...');
            }

            return 0;
        }

        $output->writeln(sprintf('    <info>%s --run</info> force a test to be run (with no notification, see --force)', $this->getName()));
        $output->writeln(sprintf('    <info>%s --force</info> option to run tests with notifications', $this->getName()));
        $output->writeln(sprintf('    <info>%s --all</info> option to run all plateform tests (by default)', $this->getName()));
        $output->writeln(sprintf('    <info>%s --varnish</info> option to run tests only on the active varnish', $this->getName()));
        $output->writeln(sprintf('    <info>%s --backends</info> option to run tests only on the prod backends', $this->getName()));

        return 1;
    }
}