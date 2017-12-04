<?php

namespace AppBundle\Calendar;

use AppBundle\Entity\Event;

class Calendar
{
    private $userArray;
    private $durationTime;
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
     * @return mixed
     */
    public function getUserArray()
    {
        return $this->userArray;
    }

    /**
     * @param mixed $userArray
     */
    public function setUserArray(array $userArray)
    {
        $this->userArray = $userArray;
    }

    /**
     * @return mixed
     */
    public function getDurationTime()
    {
        return $this->durationTime;
    }

    /**
     * @param mixed $durationTime
     */
    public function setDurationTime(int $durationTime)
    {
        $this->durationTime = $durationTime;
    }

    /**
     * @return mixed
     */
    public function getTimeFrame()
    {
        return $this->timeFrame;
    }

    /**
     * @param mixed $timeFrame
     */
    public function setTimeFrame(int $timeFrame)
    {
        $this->timeFrame = $timeFrame;
    }

    /**
     * @param $userArray
     * @param $durationTime
     * @param $timeFrame
     * @return array
     */
    function findEmptySlot($userArray, $durationTime, $timeFrame)
    {

        $timeSlotArray = []; //collection array
        for ($i = $timeFrame->start; $i < $timeFrame->end; $i++) {
            $timeSlot = $i . "-" . ($i + $durationTime);
            $frameStart = $i;
            $frameEnd = $i + $durationTime;
            foreach ($userArray as $user => $events) {
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

    function getEventsByDate()
    {
        $result = [];
        /** @var Event $event */
        foreach ($this->events as $event) {
            $result[$event->getStart()->format('Y-m-d')][] = $event;
        }
        return $result;
    }

    function currentMonth()
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



}