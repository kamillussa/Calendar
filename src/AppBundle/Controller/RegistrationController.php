<?php
namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use FOS\UserBundle\Controller\RegistrationController as BaseController;
use FOS\UserBundle\Event\GetResponseUserEvent;
use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use AppBundle\Entity\Event;
use FOS\UserBundle\Event\FilterUserResponseEvent;

class RegistrationController extends BaseController
{
    public function registerAction(Request $request)
    {
        /** @var $formFactory FactoryInterface */
        $formFactory = $this->get('fos_user.registration.form.factory');
        /** @var $userManager UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        /** @var $dispatcher EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $user = $userManager->createUser();
        $user->setEnabled(true);

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $event = new FormEvent($form, $request);
                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

                $userManager->updateUser($user);

                /*****************************************************
                 * Put user work hours in db
                 *****************************************************/
                $workHoursString = $form->getData()->getWorkHours();
                $workHoursArray = explode('-', $workHoursString);
                $workHoursStart = strtotime($workHoursArray[0]);
                $workHoursEnd = strtotime($workHoursArray[1]);
                $length = $workHoursEnd - $workHoursStart;
                $eventLength = (3600*24) - $length;
                $eventStart = $workHoursEnd;
                $eventEnd = $eventStart + $eventLength;
                for($i =1; $i < 31; $i++) {
                    $workHourEvent = new Event();
                    $user = $userManager->findUserByUsername($form->getData()->getUsername());
                    $workHourEvent->setUser($user);
                    $workHourEvent->setStart(new \DateTime(date('Y-m-d H:i:s', $eventStart)));
                    $workHourEvent->setEnd(new \DateTime(date('Y-m-d H:i:s', $eventEnd)));
                    $workHourEvent->setDescription('');
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($workHourEvent);
                    $em->flush();
                    $eventStart += 86400;
                    $eventEnd += 86400;
                }

                if (null === $response = $event->getResponse()) {
                    $url = $this->generateUrl('fos_user_registration_confirmed');
                    $response = new RedirectResponse($url);
                }

                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

                return $response;
            }

            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_FAILURE, $event);

            if (null !== $response = $event->getResponse()) {
                return $response;
            }
        }

        return $this->render('@FOSUser/Registration/register.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}