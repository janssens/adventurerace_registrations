<?php
// src/Plopcom/InscriptionsBundle/Command/DeleteSavedDocumentCommand.php
namespace Plopcom\InscriptionsBundle\Command;

use Plopcom\InscriptionsBundle\Entity\Athlete;
use Plopcom\InscriptionsBundle\Entity\Document;
use Plopcom\InscriptionsBundle\Entity\Event;
use Plopcom\InscriptionsBundle\Entity\Inscription;
use Plopcom\InscriptionsBundle\Entity\Race;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class DeleteSavedDocumentCommand extends ContainerAwareCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:delete-saved-document';

    protected function configure()
    {
        $this
            ->setDescription('Will remove all saved document of a given race or event')
            ->setHelp('This command allows you to permanently delete saved document (from subscription) for a given race or event')
            ->addOption('dry','d',InputOption::VALUE_NONE,'dry mode : do not remove anything')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dry = $input->getOption('dry');


        $output->writeln([
            '====================================',
            '  permanently delete document from  ',
            '====================================',
        ]);

        if ($dry){
            $output->writeln('[DRY MODE]');
        }

        $em = $this->getContainer()->get('doctrine')->getManager();

        $events = $em->getRepository(Event::class)->findAll();
        $chooses = array();
        /** @var Event $event */
        foreach ($events as $event){
            $date = $event->getDate();
            if (!$date){
                $date = '?';
            }else{
                $date = $date->format('Y');
            }
            $chooses[$event->getId()] = $date . ' - ' . $event->getTitle();
        }

        $question = new ChoiceQuestion(
            'Please select your event',
            $chooses
        );
        $question->setErrorMessage('Event %s do not exist.');

        $helper = $this->getHelper('question');
        $event = $helper->ask($input, $output, $question);
        $output->writeln('You have just selected event: '.$event);

        $event_id = array_search($event,$chooses);

        if ($event_id) {
            $event = $em->getRepository(Event::class)->find($event_id);

            $races = $event->getRaces();
            $chooses = array();
            /** @var Race $race */
            foreach ($races as $race){
                $chooses[$race->getId()] = $race->getDate()->format('Y') . ' - ' . $race->getTitle();
            }
            $chooses[0] = 'All';

            $question = new ChoiceQuestion(
                'Please select the race',
                $chooses
            );
            $question->setErrorMessage('Race %s do not exist.');

            $helper = $this->getHelper('question');
            $race = $helper->ask($input, $output, $question);
            $output->writeln('You have just selected race: '.$race);

            $race_id = array_search($race,$chooses);

            if ($race_id) {
                $races = array();
                $races[] = $em->getRepository(Race::class)->find($race_id);
            }

            /** @var Race $race */
            foreach ($races as $race){
                $output->writeln('RACE : '.$race->getTitle());
                $progress = new ProgressBar($output,$race->getInscriptions()->count());
                $progress->start();
                /** @var Inscription $inscription */
                foreach ($race->getInscriptions() as $inscription){
                    /** @var Athlete $athlete */
                    foreach ($inscription->getAthletes() as $athlete){
                        $output->writeln('',OutputInterface::VERBOSITY_VERBOSE);
                        $output->writeln('ATHLETE : '.strtolower($athlete->getFirstname()).' '.strtoupper($athlete->getLastname()),OutputInterface::VERBOSITY_VERBOSE);
                        /** @var Document $doc */
                        $doc = $athlete->getDocument();
                        $path = $doc->getPath();
                        $fullpath = $doc->getAbsolutePath();
                        if ($path != 'removed.png'){
                            if (is_file($fullpath)){
                                $output->writeln("file found",OutputInterface::VERBOSITY_VERBOSE);
                                if (!$dry) {
                                    unlink($fullpath);
                                }
                            }else{
                                $output->writeln("");
                                $output->writeln("file not found !");
                            }
                            if (!$dry) {
                                $doc->setPath('removed.png');
                            }
                            $em->persist($doc);
                        }else{
                            $output->writeln("");
                            $output->writeln("file already removed !");
                        }
                    }
                    $progress->advance();
                }
                $progress->finish();
                $em->flush();
            }
        }else{
            $output->writeln('No event selected, nothing to do.');
        }
        $output->writeln('');
        $output->writeln('Goodbye 😀');
    }
}