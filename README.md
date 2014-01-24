timespan-converter
==================

Converts a timespan to days, weeks, months, quarters and years.

Timepspan can be 2 timestamps or 2 DateTime Objects

 // ######### 1 Year 2 Quarter 2 Weeks, 1 Months, 7 Days #################
        $start = 1384007694; // 09.11.2013
        $end =  1440250494; // 22.08.2015
		
		$tc = new TimespanConverter();
		$blocks = $tc->timestampsToBlocks($start,$end);
		echo "<pre>";
		print($blocks);
		echo </pre>";
		
		(
    [start] => DateTime Object
        (
            [date] => 2013-11-09 14:34:54
            [timezone_type] => 1
            [timezone] => +00:00
        )

    [end] => DateTime Object
        (
            [date] => 2015-08-22 13:34:54
            [timezone_type] => 1
            [timezone] => +00:00
        )


    [years] => Array
        (
            [0] => 2014
        )

    [months] => Array
        (
            [0] => Array
                (
                    [m] => 12
                    [y] => 2013
                )

            [1] => Array
                (
                    [m] => 7
                    [y] => 2015
                )

        )

    [quarters] => Array
        (
            [0] => Array
                (
                    [q] => 1
                    [y] => 2015
                )

            [1] => Array
                (
                    [q] => 2
                    [y] => 2015
                )

        )

    [weeks] => Array
        (
            [0] => Array
                (
                    [w] => 46
                    [y] => 2013
                )

            [1] => Array
                (
                    [w] => 47
                    [y] => 2013
                )

            [2] => Array
                (
                    [w] => 32
                    [y] => 2015
                )

            [3] => Array
                (
                    [w] => 33
                    [y] => 2015
                )

        )

    [days] => Array
        (
            [0] => 2013-11-09
            [1] => 2013-11-10
            [2] => 2013-11-25
            [3] => 2013-11-26
            [4] => 2013-11-27
            [5] => 2013-11-28
            [6] => 2013-11-29
            [7] => 2013-11-30
            [8] => 2015-08-01
            [9] => 2015-08-02
            [10] => 2015-08-17
            [11] => 2015-08-18
            [12] => 2015-08-19
            [13] => 2015-08-20
            [14] => 2015-08-21
            [15] => 2015-08-22
        )

)
