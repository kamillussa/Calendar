<?php

namespace AppBundle\Calendar;

use AppBundle\Entity\Event;
use AppBundle\Calendar\Event as E;

class Calendar
{
    private $eventsArray;
    private $duration;
    private $timeFrame;
    private $events;

    /**
     * @param mixed $events
     */
    public function setEvents($events)
    {
        $this->events = $events;
    }


    /**
     * @param mixed $eventsArray
     */
    public function setEventsArray(array $eventsArray)
    {
        $this->eventsArray = $eventsArray;
    }

    /**
     * @return mixed
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param mixed $duration
     */
    public function setDuration(int $duration)
    {
        $this->duration = $duration;
    }

    /**
     * @return mixed
     */
    public function getTimeFrame()
    {
        return $this->timeFrame;
    }

    /**
     * @param $timeFrame
     */
    public function setTimeFrame($timeFrame)
    {
        $this->timeFrame = $timeFrame;
    }

    /**
     * @return array
     */
    public function findEmptySlot()
    {

        $timeSlotArray = []; //collection array
        for ($i = $this->timeFrame->getStart(); $i < $this->timeFrame->getEnd(); $i+=1800) {
            //More results
            //$timeSlot = date('H:i', $i) . "-" . date('H:i', ($i + $this->duration));
            $timeSlot = date('H:i d.m.Y', $i) . "-" . date('H:i d.m.Y', ($i + $this->duration));
            $frameStart = $i;
            $frameEnd = $i + $this->duration;
            foreach ($this->getEventsByUsername() as $user => $events) {
                for ($j = 0; $j < count($events); $j++) {
                    $eventStart = $events[$j]->getStart();
                    $eventEnd = $events[$j]->getEnd();
                    $isFree = $frameStart >= $eventEnd || $frameEnd <= $eventStart;
                    $timeSlotArray[$timeSlot][$user] = $isFree;
                    if (!$isFree) {
                        break;//if user have meeting in this iteration time-frame, stop checking another his meeting
                    }
                }
            }
        }
        return $timeSlotArray;
    }

    /**
     * @return array
     */
    public function getEventsByDate()
    {
        $result = [];
        /** @var Event $event */
        foreach ($this->events as $event) {
            $result[$event->getStart()->format('Y-m-d')][] = $event;
        }
        return $result;
    }

    /**
     * @return array
     */
    public function currentMonth()
    {
        $maxDays = date('t');
        $output = [[]];

        $events = $this->getEventsByDate();

        for ($j = 0, $i = 1; $i <= $maxDays; $i++) {
            $date = \DateTime::createFromFormat('Y-m-d', date("Y-m-{$i}"));
            $dayOfWeek = date('w', $date->getTimestamp());
            $dateString = $date->format('Y-m-d');
            if ($i === 1 && $dayOfWeek > 1) {
                for ($k = 0; $k < $dayOfWeek - 1; $k++) {
                    $output[$j][] = "EMPTY";
                }
            }
//            $output[$j][] = $i;
            if (isset($events[$dateString])) {
                $output[$j][$dateString] = $events[$dateString];
            } else {
                $output[$j][$dateString] = [];
            }

            if ((int)$dayOfWeek === 0) {
                $j++;
            }
        }
        return $output;
    }

    /**
     * @return array
     */
    public function getEventsByUsername()
    {
      $result = [];
      $events = $this->eventsArray;
      foreach ($events as $key => $value) {
          $timestampStart = date_timestamp_get($value['start']);
          $timestampEnd = date_timestamp_get($value['end']);
          $event = new E($timestampStart, $timestampEnd);
          $result[$value['username']][] = $event;
      }
      return $result;
    }
}