<?php
/**
* version 0.2 by Benjamin Neu 24.01.2014
*/

<?php
class TimespanConverter {

    /**
     * @param timestamp $start
     * @param timestamp $end
     * @return array
     */
    public function timestampToBlocks($start,$end){

        $begin =   DateTime::createFromFormat('U', $start);
        $end =   DateTime::createFromFormat('U', $end);

       return $this->datetimeToBlocks($start,$end);
    }
	
	public function datetimeToBlocks($start,$end){
		$return = $this->toDay($begin,$end);
        echo "Final";
        print_r($return);
        return $return;
	}

    private function toDay($start,$end){
        $return = $this->toWeek($start,$end);

        // Initialise Day Array
        $return['days'] = array();

        if(count($return['timespans']) > 0){
    
            foreach($return['timespans'] as $time){
                // Daterange Days
                echo "to Day Timespan: \n";
                print_r($time);
                $interval = new DateInterval('P1D'); // 1Day Interval
                $start= clone $time['start'];
                $start->setTime(0,0,0);
                $end = clone $time['end'];
                $end->setTime(0,0,0);
                $end->add($interval);
                $daterange = new DatePeriod($start, $interval,$end);

                foreach($daterange as $date){
                    $return['days'][] = $date->format("Y-m-d");
                }
            }
        }
        else{
            $return['days'] = array();
        }
        return $return;
    }
    private function toWeek($start,$end){
        $return = $this->toMonth($start,$end);

        //print_r($return);

        $newtimes = array();

        $return['weeks'] = array();

        echo "To Week Timepans\n";
        print_r($return['timespans']);

        foreach((array)$return['timespans'] as $time){
            $startdate = getdate($time['start']->getTimestamp());
            $enddate = getdate($time['end']->getTimestamp());

            echo "Startweeksdate ".$startdate['wday']." End WD: ".$enddate['wday'];

            $diffdays = $this->dateTimesToDays($time['start'],$time['end']);
            echo "DIFF DAYS ".$diffdays."\n";

            if($diffdays < 7){
                // Not a complete week, put timespan back on array
                $newtimes[] = array('start' => $time['start'],'end'=>$time['end']);
                continue;
            }
            elseif($diffdays == 7){
                // one week or 7 daylies
                if($startdate['wday'] == 1 and $enddate['wday'] == 0){
                    // 1 Week Monday to Sunday, insert a week and continue
                    echo "\n One Week \n";
                    $return['weeks'][] = $this->getWeek($time['start']);

                    continue;
                }
                else{
                  // A 7days not within a week, put timespan back on array
                    echo "7 Days but not in a week return to Dailies\n";
                   $newtimes[] = array('start' => $time['start'],'end'=>$time['end']);
                   continue;
                }
            }
            else{
                // At least one week maybe more
                // Moving DateTime Objects
                $movingstart = clone $time['start'];
                $movingend = clone $time['end'];

                $startarray = getdate($movingstart->getTimestamp());
                $endarray = getdate($movingend->getTimestamp());

                // Start Monday ?
                if($startarray['wday'] != 1){
                    // jump to first monday
                    echo "Week Fix Start Monday";
                    $movingstart =  DateTime::createFromFormat('U',strtotime('next Monday '.$movingstart->format("Y-m-d H:i:s")));
                    $newtimes[] = array('start' => $time['start'],'end'=>$this->modifyLastDay($movingstart));
                    echo "\nMovingStart ".$movingstart->format("Y-m-d H:i:s")."\n";
                    print_r($newtimes);
                }

                // End Sunday ?
                if($endarray['wday'] != 0){
                    // backtrack last sunday
                    echo "\nWeek Fix End Sunday current MovingEnd ".$movingend->format("Y-m-d H:i:s")."\n";
                    $movingend =  $this->modifyLastSunday($movingend);
                    echo "Updated Moving End: \n";
                    print_r($movingend);
                    $newtimes[] = array('start' => $movingend->modify('+1 days'),'end'=>$time['end']);
                }

                // Wieviel Wochen ?
                $diff = $movingstart->diff($movingend);
                $diffweeks = round($diff->days/7);

                for($i=0;$i<$diffweeks;$i++){
                    $return['weeks'][] = $this->getWeek($movingstart);
                    $movingstart->modify('+7 days');
                }

            } // at least one week maybe more
        } // foreach

        $return['timespans'] = $newtimes;
        return $return;
    }

    /**
     * Gets Full Months in Timespan
     *
     * @param $start
     * @param $end
     * @return array
     */
    private function toMonth($start,$end){
        $return = $this->toYear($start,$end);
        $return['months'] = array();
        $return['quarters'] = array();
        
        if(count($return['timespans']) > 0){
            $newtimes = array();
            foreach($return['timespans'] as $time){
                $months = $this->dateTimesToMonth($time['start'],$time['end']);

                if(empty($months)){
                    //
                    echo "0 Months \n";
                    $newtimes[] = $time;
                    continue;
                }
                else{
                    // There is at least one month and there might be days / before or after
                    echo count($months['months']) ." Months \n";
                    print_r($months);

                    if(count($months['before']) > 0){
                        $newtimes[] = array('start' => $months['before']['start'], 'end' => $months['before']['end']);
                    }
                    if(count($months['after']) > 0){
                        $newtimes[] = array('start' => $months['after']['start'], 'end' => $months['after']['end']);
                    }

                    // Append to existing months array
                    $return['months'] = array_merge($return['months'],$months['months']);
                }

            } // foreach
            
            
            // ###########################
            // Combine Month to Quarters
            // ###########################
            echo count($return['months']) ." Months to Quarters \n";
            print_r($return['months']);
            $mcount = count($return['months']);
            if($mcount > 2){
                $i = 0;
                $takeovermonths = array();
                
                // There have always to be at least 3 month left to form a quarter, or end loop
                while($i < $mcount){
                    //if($return['months'][$i]['m'] > 10){
                    // Check if Jan, Apr, Jul, Okt for Quarter Start and that there at least 3 month left
                    if (($return['months'][$i]['m'] % 3) != 1 or $i+2 >= $mcount){
                        echo "Skip Month".$return['months'][$i]['m']."-".$return['months'][$i]['y']."\n";
                        $takeovermonths[] = $return['months'][$i];
                        $i++;
                        continue;
                    }
                    else{
                        // Check + 2 Month if same year
                        $movingmonth = $return['months'][$i]['m'] + 2;
                        if($return['months'][$i+2]['m'] == $movingmonth and $return['months'][$i]['y'] == $return['months'][$i+2]['y'] )
                        {
                            // We found a quarter
                            $return['quarters'][] = array('q' => $this->monthToQuarter($return['months'][$i]['m']), 'y' => $return['months'][$i]['y']);
                            $i = $i +3;
                            echo "Found Quarter next i $i\n";
                            continue;                            
                        }
                        else{
                            // Remember Month left
                            $takeovermonths[] = $return['months'][$i];
                            $i++;
                            continue;
                        }
                    }
                    
                } // while
                
                // If there are quarters update Months
                if(count($return['quarters']) > 0){
                    echo "Update Return Months because of Quarters \n";
                    print_r($takeovermonths);
                    $return['months'] = $takeovermonths;
                }
            }

            // Update Timespans
            $return['timespans'] = $newtimes;
        } // if there are timespans


        return $return;
    }


    private function toYear($start,$end){
        $return = array();


        $return['start'] = $start;
        $return['end'] = $end;

        $return['timespans'] = array();

        $return['years'] = array();
        if($this->dateTimesToDays($start,$end) < 365){
            $return['timespans'][] =  array('start' => $start,'end'=>$end);
        }
        else{
            if($this->isSameYear($start,$end)){
               $return['years'][] = $this->getIntYear($start);
            }
            else{
                echo "> 365 but not in same year ?!?".$this->getIntDayofYear($start)." vs. ".$this->getIntDayofYear($end)."\n";

                $years = $this->dateTimesToYears($start,$end);
                if(empty($years)){
                    //
                    echo "0 Years \n";
                    $return['timespans'][] =  array('start' => $start,'end'=>$end);
                }
                else{
                    // There is at least one month and there might be days / before or after
                    echo count($years) ." Years \n";
                    print_r($years);

                    if(count($years['before']) > 0){
                        $return['timespans'] [] = array('start' => $years['before']['start'], 'end' => $years['before']['end']);
                    }
                    if(count($years['after']) > 0){
                        $return['timespans'] [] = array('start' => $years['after']['start'], 'end' => $years['after']['end']);
                    }
                    $return['years'] = $years['years'];
                }
            }
        }





        return $return;
    }

    /**
     * Returns the total number of days from start to end DateTimes
     * Return will always be >= 1
     *
     * @param $start DateTime
     * @param $end DateTime
     * @return $days int
     */
    public function dateTimesToDays($start,$end){
        $s = clone $start;
        $e = clone $end;
        $s->setTime(0,0,0);
        $e->setTime(0,0,0);

        return $e->diff($s)->days +1;
    }

    /**
     * Returns the complete Months between two DateTimes
     * @param $start DateTime
     * @param $end DateTime
     * @return $month array
     */
    public function dateTimesToMonth($start,$end){

        $return = array();
        $return['before'] = array();
        $return['after'] = array();
        if($this->dateTimesToDays($start,$end) < 28){
            return array();
        }

        // Start Check if Day is first of month
        if($this->getIntDayOfMonth($start) != 1){
            if($this->isSameMonth($start,$end)){
                // Within same month but not the first
                return array();
            }
            $return['before']['start'] = clone $start;
            $start = $this->modifyNextFirstDay($start);
            $return['before']['end'] = $this->modifyLastDay($start);
        }

        // Check if Day last of month
        if(!$this->isLastDayofMonth($end)){
            if($this->isSameMonth($start,$end)){
                // Within same month but not the last
                return array();
            }


            $return['after']['end'] = clone $end;
            $end = $this->modifyLastLastDay($end);
            echo "Modified end \n";
            print_r($end);
            $return['after']['start'] = $this->modifyNextDay($end);
        }


        echo "Get Months For \n";
        print_r($start);
        print_r($end);
        $return['months'] = $this->getMonths($start,$end);


        return $return;

    }

    /**
     * Returns the complete Years between two DateTimes and days before and after
     * @param $start DateTime
     * @param $end DateTime
     * @return $month array
     */
    public function dateTimesToYears($start,$end){

        $return = array();
        $return['before'] = array();
        $return['after'] = array();
        if($this->dateTimesToDays($start,$end) < 365){
            return array();
        }

        // Start Check if Day is first of year
        if($this->getIntDayofYear($start) != 0){
            if($this->isSameYear($start,$end)){
                // Within same year but not the first
                echo "RETURN: Start/End in same year but start is not first day!\n";
                return array();
            }

            $return['before']['start'] = clone $start;
            $start = $this->modifyNextFirstDayofYear($start);
            $return['before']['end'] = $this->modifyLastDay($start);

            echo "Updated Start\n";
            print_r($start);
            echo "Updated Before\n";
            print_r($return['before']);
        }

        // Check if Day last of year
        if($this->getIntDayofYear($start) != 365){
            if($this->isSameYear($start,$end)){
                // Within same year but not the last
                echo "RETURN: Start/End in same year but end is not last day!\n";
                return array();
            }

            $return['after']['end'] = clone $end;
            $end = $this->modifyLastLastDayofYear($end);
            echo "Modified end \n";
            print_r($end);
            $return['after']['start'] = $this->modifyNextDay($end);
        }


        echo "Get Years For \n";
        print_r($start);
        print_r($end);
        $return['years'] = $this->getYears($start,$end);


        return $return;

    }

    public function isLastDayofMonth($datetime){
        if($datetime->format('j') == $datetime->format('t')){
            return true;
        }
        return false;
    }

    /**
     * Returns true if Dates are within the same month
     * @param $start
     * @param $end
     * @return bool
     */
    public function isSameMonth($start,$end){
        if   ($start->format('Y-m') == $end->format('Y-m')){
            return true;
        }
        return false;
    }

    /**
     * Returns true if Dates are within the same year
     * @param $start
     * @param $end
     * @return bool
     */
    public function isSameYear($start,$end){
        if   ($start->format('Y') == $end->format('Y')){
            return true;
        }
        return false;
    }

    /**
     * Return the day of the year for a given DateTime
     * @param $datetime
     * @return int
     */
    public function getIntDayofYear($datetime){
        return intval($datetime->format('z'));
    }
    /**
     * Returns as int the day of the month count
     *
     * @param $datetime DateTime
     * @return int
     */
    public function getIntDayOfMonth($datetime){
        return intval($datetime->format('j'));
    }

    /**
     * Returns Array with Week and Year for Datetime Object
     *
     * @param $datetime
     * @return Array
     */
    public function getWeek($datetime){
        $week = array();
        $week['w'] = $datetime->format('W');
        $week['y'] = $datetime->format('Y');
        return $week;
    }

    /**
     * Returns how many days the given month has
     *
     * @param $datetime
     * @return int
     */
    public function getIntDaysInMonth($datetime){
        return intval($datetime->format('t'));
    }

    public function getIntYear($datetime){
        return intval($datetime->format('Y'));
    }
    /**
     * Returns simple month array of DateTimes
     *
     * @param $start
     * @param $end
     * @return array Months
     */
    public function getMonths($start,$end){
        echo "getMonths \n";
        print_r($start);
        print_r($end);

        $year1 = intval($start->format('Y'));
        $year2 = intval($end->format('Y'));

        $month1 = intval($start->format('m'));
        $month2 = intval($end->format('m'));

        $count = (($year2 - $year1) * 12) + ($month2 - $month1) + 1;

        if($count != 0){
            $dates = array();

            for($i=0;$i<$count;$i++){
                 $dates[] = array('m' => $month1,'y' => $year1);

                $month1++;
                if($month1 == 13){
                    $month1 = 1;
                    $year1++;
                }
            }

            return $dates;
        }
        return array();
    }

    /**
     * Returns simple of years within two DateTimes, without checking for complete years
     *
     * @param $start
     * @param $end
     * @return array Months
     */
    public function getYears($start,$end){
        echo "getYears \n";
        print_r($start);
        print_r($end);

        $year1 = $this->getIntYear($start);
        $year2 = $this->getIntYear($end);

        $dates = array();
        while($year1 <= $year2){
            $dates[] = $year1;
            $year1++;
        }

        return $dates;

    }

    /**
     * Returns a cloned DateTime Object from the before the given day
     * @param type $datetime
     * @return DateTime
     */
    public function modifyLastSunday($datetime){
        $datetime = clone $datetime;
        return $datetime->modify('last Sunday');
    }

    /**
     * Returns a cloned DateTime Object from the before the given day
     * @param type $datetime
     * @return DateTime
     */
    public function modifyLastDay($datetime){
        $datetime = clone $datetime;
        return $datetime->modify('-1 days');
    }

    public function modifyNextDay($datetime){
        $datetime = clone $datetime;
        return $datetime->modify('+1 days');
    }
    /**
     * Returns the First day of the month after the given DateTime
     * @param $datetime
     * @return mixed
     */
    public function modifyNextFirstDay($datetime){
        //first day of next month
        $datetime = clone $datetime;
        return $datetime->modify('first day of next month');
    }

    /**
     * Returns the First day of the next Year after the given DateTime
     * @param $datetime
     * @return mixed
     */
    public function modifyNextFirstDayofYear($datetime){
        //first day of next month
        //$datetime = clone $datetime;
        //return $datetime->modify('first day of next year');

        // Because of reported php Feaure (bug of older versions) work around
        // https://bugs.php.net/bug.php?id=53650
        $year = $this->getIntYear($datetime) + 1;
        echo "NextFirst ".$this->getIntYear($datetime)." to ".$year;
        return new DateTime("$year-01-01");
    }

    public function modifyLastLastDayofYear($datetime){
        // Because of reported php Feaure (bug of older versions) work around
        // https://bugs.php.net/bug.php?id=53650
        $year = $this->getIntYear($datetime) - 1;

        return new DateTime("$year-12-31");
    }
    /**
     * Returns the last day of the month before the given DateTime
     * @param $datetime
     * @return mixed
     */
    public function modifyLastLastDay($datetime){
        // last day of last month
       // echo "\nLast Last of ";
        //print_r($datetime);
        $datetime = clone $datetime;
        $last =  $datetime->modify('last day of previous month');
        //print_r($datetime);
        return $last;
    }
    
    /**
     * Returns Quarter int for a given month int
     * @param int $month
     * @return int $quarter
     */
    public function monthToQuarter($month){
        if($month <= 3) return 1;
        if($month <= 6) return 2;
        if($month <= 9) return 3;
        if($month <= 12) return 4;
    }

 }
 ?>