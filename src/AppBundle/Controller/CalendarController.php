<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Calendar\Calendar;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Event;

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
        $user = $this->getUser();
        $data = $request->request->all();
        $startDateTime = new \DateTime($data['startDate']." ".$data['startTime']);
        $endDateTime = new \DateTime($data['endDate']." ".$data['endTime']);

        $event = new Event();
        $event->setUser($user);
        $event->setStart($startDateTime);
        $event->setEnd($endDateTime);
        $event->setDescription($data['description']);

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
}
