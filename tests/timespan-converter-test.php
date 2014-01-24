<?php

class TimespanConverterTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        
        require_once("timepspan-converter.php");

    }

    public function testTimeToBlocks(){
        echo "############ Start testTimeToBlocks() ################## \n";


        // ######## 2 Days ####################
        $start = 1390239359; //time()-24*60*60; 20.01.2014 18:35:59 Local
        $end = 1390325759; //time(); 21.01.2014 18:35:59
        echo "2 Days Start $start End $end \n";
        $times = timestampToBlocks($start,$end);
        $this->assertEquals(2,count($times['days']));
        $this->assertEquals("2014-01-20",$times['days'][0]);
        $this->assertEquals("2014-01-21",$times['days'][1]);

        // ########## 7 Days ##############
        $start = 1390127424; // Sunday
        $end = 1390645821; // Saturday
        echo "7 Days Start $start End $end \n";
        $times = timestampToBlocks($start,$end);
        $this->assertEquals(7,count($times['days']));
        $this->assertEquals("2014-01-19",$times['days'][0]);
        $this->assertEquals("2014-01-20",$times['days'][1]);
        $this->assertEquals("2014-01-21",$times['days'][2]);
        $this->assertEquals("2014-01-22",$times['days'][3]);
        $this->assertEquals("2014-01-23",$times['days'][4]);
        $this->assertEquals("2014-01-24",$times['days'][5]);
        $this->assertEquals("2014-01-25",$times['days'][6]);

        // ######## 1 Week ####################
        $start = 1390239359; //time()-24*60*60; 20.01.2014 18:35:59
        $end =  1390742979; //time()+4*24*60*60; 26.01.2014 14:29:39
        echo "1 Week Start $start End $end \n";
        $times = timestampToBlocks($start,$end);
        $this->assertEquals(0,count($times['days']));
        $this->assertEquals(1,count($times['weeks']));
        $this->assertEquals("04",$times['weeks'][0]);
        /*
        $this->assertEquals("2014-01-20",$times['days'][0]);
        $this->assertEquals("2014-01-21",$times['days'][1]);
        */

        // ######## 1 Week 2 Days ###############
        $start = 1390239359; // 2014-01-20 17:35:59
        $end =  1390919694; // 28.01.2014 15:34:54
        echo "1 Week 2 Days Start $start End $end \n";
        $times = timestampToBlocks($start,$end);
        $this->assertEquals(2,count($times['days']));
        $this->assertEquals("2014-01-27",$times['days'][0]);
        $this->assertEquals("2014-01-28",$times['days'][1]);
        $this->assertEquals(1,count($times['weeks']));
        $this->assertEquals("04",$times['weeks'][0]);

        // ######## 2 Weeks ###############
        $start = 1390239359; //20.01.2014 18:35:59
        $end =  1391351694; //02.02.2014 15:34:54
        echo "2 Weeks  Start $start End $end \n";
        $times = timestampToBlocks($start,$end);
        $this->assertEquals(0,count($times['days']));
        $this->assertEquals(2,count($times['weeks']));
        $this->assertEquals("04",$times['weeks'][0]);
        $this->assertEquals("05",$times['weeks'][1]);

        // ######## 2 Weeks + 2 Days ###############
        $start = 1390239359; //2014-01-20 17:35:59
        $end =  1390671638+10*24*60*60; //time()+4*24*60*60;
        echo "2 Week 2 Days Start $start End $end \n";
        $times = timestampToBlocks($start,$end);
        $this->assertEquals(2,count($times['days']));
        $this->assertEquals("2014-02-03",$times['days'][0]);
        $this->assertEquals("2014-02-04",$times['days'][1]);
        $this->assertEquals(2,count($times['weeks']));
        $this->assertEquals("04",$times['weeks'][0]);
        $this->assertEquals("05",$times['weeks'][1]);

        echo "############ End testTimeToBlocks() ################## \n";

    }
    public function tearDown(){
    }
}

?>