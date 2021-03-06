<?php
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use AppBundle\ServiceFactories\RepositoryFactory;


// To use as default template
$definition = new Definition();

$definition->setAutowired(true)->setAutoconfigured(true)->setPublic(false);

// $this is a reference to the current loader
$this->registerClasses($definition, 'AppBundle\\', '../../src/AppBundle/*', '../../src/AppBundle/{Entity,Repository,Tests,Interfaces,Services/Adapters/RepositoryServiceAdapter.php}');

$definition->addTag('controller.service_arguments');
$this->registerClasses($definition, 'AppBundle\\Controller\\', '../../src/AppBundle/Controller/*');

$container->register(AppBundle\Services\CapthaServiceAdapter::class)
  ->setPublic(true)
  ->setArguments([new Reference('session')]);

$container->register(AppBundle\Services\NormalEmailSend::class)
  ->setPublic(true)
  ->setArguments([new Reference('mailer')]);

/*##################### Repositories ########################*/

$container->register(AppBundle\Repository\ContactEmailRepository::class,AppBundle\Repository\ContactEmailRepository::class)
    ->setFactory([new Reference(RepositoryFactory::class),'repositoryAsAService'])
    ->setArguments(['$entityManager'=>new Reference('doctrine.orm.entity_manager'),'$entityName'=>AppBundle\Entity\ContactEmail::class]);


/*######################## Logging ################################*/
$container->register('ellakcy.log.formatter.access_log',Monolog\Formatter\LineFormatter::class)
  ->setArguments(["%%message%% \n"]);


/*############################ Event Listeners ###################*/
$container->register(AppBundle\EventListener\AccessLogEventListener::class)
  ->setArgument('$logger', new Reference('monolog.logger.access_log'))
  ->setArgument('$twig', new Reference('twig'))
  ->addtag('kernel.event_listener',['event'=>'kernel.response','method'=>'onKernelResponse']);
