<?php
/**
* version 0.1 by Benjamin Neu 22.01.2014
*/

/**
     * @param $start timestamp
     * @param $end timestamp
     * @return array
     */
public function timestampToBlocks($start,$end){

        $begin =   DateTime::createFromFormat('U', $start);
        $end =   DateTime::createFromFormat('U', $end);
        $end = $end->modify( '+1 day' ); // Workaround ?!?

        $return = $this->toDay($begin,$end);
        echo "Final";
        print_r($return);
        return $return;

    }

    private function toDay($start,$end){
        $return = $this->toWeek($start,$end);


        if(count($return['timespans']) > 0){
            foreach($return['timespans'] as $time){
                // Daterange Days
                print_r($time);
                $interval = new DateInterval('P1D'); // 1Day Interval
                $time['end']->add($interval);
                $daterange = new DatePeriod($time['start'], $interval ,$time['end']);

                $return['days'] = array();

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

        print_r($return);

        $newtimes = array();


        foreach($return['timespans'] as $time){
            $startdate = getdate($time['start']->getTimestamp());
            $enddate = getdate($time['end']->getTimestamp());

            echo "Startweeksdate ".$startdate['wday']." End WD: ".$enddate['wday'];

            $diff = $time['start']->diff($time['end']);
            $diffdays = $diff->days + 1;
            echo "DIFF DAYS ".$diffdays;
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
                    $return['weeks'][] = date('W',$startdate[0]);

                    continue;
                }
                else{
                  // A 7days not within a week, put timespan back on array
                   $newtimes[] = array('start' => $time['start'],'end'=>$time['end']);
                   continue;
                }
            }
            else{
                // At least one week maybe more
                $newtimes = array();

                // Moving DateTime Objects
                $movingstart = clone $time['start'];
                $movingend = clone $time['end'];

                $startarray = getdate($movingstart->getTimestamp());
                $endarray = getdate($movingend->getTimestamp());

                // Start Monday ?
                if($startarray['wday'] != 1){
                    // jump to first monday
                    $movingstart =  DateTime::createFromFormat('U',strtotime('next Monday '.$movingstart->format("Y-m-d H:i:s")));
                    $newtimes[] = array('start' => $time['start'],'end'=>$movingstart->modify('-1 days'));
                }

                // End Sunday ?
                if($endarray['wday'] != 0){
                    // backtrack last sunday
                    echo "\nMovingEnd ".$movingend->format("Y-m-d H:i:s")."\n";
                    $movingend =  DateTime::createFromFormat('U',strtotime('last Sunday '.$movingend->format("Y-m-d-h H:i:s")));
                    print_r($movingend);
                    $newtimes[] = array('start' => $movingend->modify('+1 days'),'end'=>$time['end']);
                }

                // Wieviel Wochen ?
                $diff = $movingstart->diff($movingend);
                $diffweeks = round($diff->days/7);

                for($i=0;$i<$diffweeks;$i++){
                    $return['weeks'][] = date('W',$movingstart->getTimestamp());
                    $movingstart->modify('+7 days');
                }

                $return['timespans'] = $newtimes;

                return $return;
            } // at least one week maybe more
        } // foreach

        $return['timespans'] = $newtimes;
        return $return;
    }

    private function toMonth($start,$end){
        $return = $this->toQuarter($start,$end);
        $return['months'] = array();
        return $return;
    }

    private function toQuarter($start,$end){
        $return = $this->toYear($start,$end);

        $return['quarters'] = array();

        return $return;
    }

    private function toYear($start,$end){
        $return = array();
        $return['years'] = array();

         $return['start'] = $start;
         $return['end'] = $end;

        $return['timespans'] = array();
        $return['timespans'][] =  array('start' => $start,'end'=>$end);



        return $return;
    }
?>