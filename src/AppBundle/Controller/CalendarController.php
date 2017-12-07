<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Calendar\Calendar;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Event;
use AppBundle\Calendar\Event as E;

class CalendarController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        $calendar = new Calendar();
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:Event');
        $events = $repository->findBy(['user' => $this->getUser()->getId()]);
        $calendar->setEvents($events);
        $currentMonth = $calendar->currentMonth();
        dump($currentMonth);
        return $this->render('AppBundle::index.html.twig', array('currentMonth' => $currentMonth));
    }

    /**
     * @Route("/schedule")
     */
    public function scheduleAction()
    {
        return $this->render('AppBundle::schedule.html.twig', array());
    }

    /**
     * @Route("/createMeeting")
     */
    public function createMeetingAction(Request $request)
    {
        $data = $request->request->all();
        $startDateTime = new \DateTime($data['startDate']." ".$data['startTime']);
        $endDateTime = new \DateTime($data['endDate']." ".$data['endTime']);

        $event = new Event();
        $user = $this->getUser();
        $event->setUser($user);
        $event->setStart($startDateTime);
        $event->setEnd($endDateTime);
        $event->setDescription($data['description']);
        $event->setIsWorkHours(false);

        $em = $this->getDoctrine()->getManager();
        $em->persist($event);
        $em->flush();

        return	$this->redirect("/");
    }

    /**
     * @Route("/scheduleForAll")
     */
    public function scheduleForAllAction()
    {

        return $this->render('AppBundle::schedule_for_all.html.twig', array());
    }

    /**
     * @Route("/findSlot")
     */
    public function findSlotAction(Request $request)
    {
        $calendar = new Calendar();
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:Event');
        $eventsArray = $repository->findAllEventsByUsername();
        $calendar->setEventsArray($eventsArray);
        dump($calendar->getEventsByUsername());
        $data = $request->request->all();
        $duration = $data['duration'] * 3600;
        $calendar->setDuration($duration);

        $timeFrameStart = date_timestamp_get(new \DateTime($data['startDate']." ".$data['startTime']));
        $timeFrameEnd = date_timestamp_get(new \DateTime($data['endDate']." ".$data['endTime']));
        $timeFrame = new E($timeFrameStart, $timeFrameEnd);
        $calendar->setTimeFrame($timeFrame);
        $slotsArray = $calendar->findEmptySlot();
        dump($slotsArray);

        return $this->render('AppBundle::find_slot.html.twig', array('slotsArray' => $slotsArray));
    }

    /**
     * @Route("/createMeetingForAll")
     */
    public function createMeetingForAllAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();


        $data = $request->request->all();
        $startDate = new \DateTime($data['startDate']);
        $endDate = new \DateTime($data['endDate']);
        $userManager = $this->get('fos_user.user_manager');
        foreach($data as $key => $value) {
            if($userManager->findUserByUsername($data[$key]) != null) {
                $user = $userManager->findUserByUsername($data[$key]);
                $event = new Event();
                $event->setUser($user);
                $event->setStart($startDate);
                $event->setEnd($endDate);
                $event->setDescription($data['description']);
                $event->setIsWorkHours(false);

                $em->persist($event);
                $em->flush();
            }
        }
        return	$this->redirect("/");
    }

}
