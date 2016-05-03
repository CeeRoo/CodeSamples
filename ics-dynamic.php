<?php
/*
Plugin Name: ICS Dynamic links
Plugin URI: http://www.relnei.com	
Description: This plugin will create a dynamic ICS file based on event information so that users can download into their calendars.
Author: Chris Rousseau
Version: 1.2
Author URI: http://www.relnei.com
*/

/*-----------------------------------------------
|
|   CHANGES:
|
|   - Revamped the format of the output file as the Zulu time format(date with trailing 'Z') no longer
|   was functional on PC Outlook 2010+ even though Mac Outlook displayed
|   the event in the correct time/date. Added TZID, TZOFFSETFROM, TZOFFSETTO, X-ENTOURAGE fields
|   as well as STANDARD and DAYLIGHT sections. (3/5/15)
|   
|   - The date for the event is no longer manipulated based the time zone for the event. In the
|   prior Zulu version, the date itself was altered to reflect the Time Zone. With the new method
|   the date itself is static regardless of time zone, but the offsets for time zone are
|   reflected in the TZOFFSETFROM and TZOFFSETTO parameters which are adjusted based on the 
|   time zone for the event. The user's calendar client uses these as well as their local clock
|   to determine the correct time. (1/12/16)
|   
-----------------------------------------------*/
/**

* @version : 1.2

* Function : ics_tz_replace
* Purpose : Replaces timezone value for display purposes only, e.g., 'EDT' to 'ET'
*
* @access public
* @param string $datetimestring, array $tz_array
* @return string
**/
function ics_tz_replace($datetimestring,$tz_array = array("EDT", "EST")) {
    
    for($i=0; $i < count($tz_array); $i++) {
        
        $pos = strpos($datetimestring, $tz_array[$i]);
        
        if ($pos !== false) {
             $eventstarttime_tz = str_replace($tz_array[$i],"ET",$datetimestring); 
             return $eventstarttime_tz;
        }
    }
    
    return $datetimestring;
}

function create_ics($eventid) {
    
    // Get the event/ics id and get the associated event
    if (( isset ($_GET['icsid']) && is_numeric($_GET['icsid']))) {
        
        $eventid = $_GET['icsid'];
        
        // Get event information from passed id.
        $q="SELECT 
                * 
            FROM 
                wp_posts P
            LEFT JOIN 
                wp_postmeta M ON P.ID=M.post_id
            WHERE 
                P.post_status IN ('draft','publish')
            AND 
                M.meta_key='eventdate' 
            AND 
                ID = ".$eventid
	;
        
        $r = mysql_query ("$q");       
        
        while ($row = mysql_fetch_array ($r)) {
            
            $event = array(
                'event_name' => $row['post_title']. ' [REL Northeast and Islands]',
                'event_description' => strip_tags(get_field('eventbrief',$row['ID'])),
                'event_start' => str_replace('-', '',sqldate(get_field('eventdate',$row['ID']))),
                'event_end' => str_replace('-', '',sqldate(get_field('eventdate',$row['ID']))),
            );

            $title = $event['event_name'];

            $location = get_field('eventlocation', $row['ID']);

            // Get the event start and end time (one field)
            $timestring = get_field('eventstarttime',$row['ID']);
            
            // Divide start and end time
            $commapos = strpos($timestring, ",");
            $dashpos = strpos($timestring, "–");
            $endtime = substr($timestring, $dashpos+3, $commapos-$dashpos-3);
            $starttime = substr($timestring, 0, $dashpos-1);

            // Get hours, minutes, meridian - START TIME
            $startcolonpos = strpos($starttime, ":");
            $starttimehours = trim(substr($starttime, 0, $startcolonpos));
            $starttimehours = str_pad($starttimehours, 2, "0", STR_PAD_LEFT);

            $starttimemins = substr($starttime, $startcolonpos+1, 2);

            if (strpos($starttime, 'a')===FALSE && $starttimehours < 12) {
                $starttimehours = $starttimehours + 12;
            }
            
            // Get hours, minutes, meridian - END TIME
            $endcolonpos = strpos($endtime, ":");
            $endtimehours = trim(substr($endtime, 0, $endcolonpos));
            $endtimehours = str_pad($endtimehours, 2, "0", STR_PAD_LEFT);

            $endtimemins = substr($endtime, $endcolonpos+1, 2);

            if (strpos($endtime, 'a')===FALSE && $endtimehours < 12) {
                $endtimehours = $endtimehours + 12;
            }
            
            // Get timezone (allow for EDT, EST)
            $thetimezone = trim(substr(trim($timestring), $commapos+1, 4));

            /*
             *  Defaults for ET, EST, EDT time zones
             */

            $TZID = "America/New_York";

            $TZOFFSETFROM_standard = "-0400";
            $TZOFFSETTO_standard = "-0500";

            $TZOFFSETFROM_daylight = "-0500";
            $TZOFFSETTO_daylight = "-0400";

            // Default X-ENTOURAGE parameters required by Outlook
            $ENTOURAGE_TZID = 4;
            $ENTOURAGE_CFTIMEZONE = "US/Eastern";

            switch ($thetimezone) {
                
                case 'CT': {
                    $TZID = "Central Time (US & Canada)";
                    $TZOFFSETFROM_standard = "-0500";
                    $TZOFFSETTO_standard = "-0600";

                    $TZOFFSETFROM_daylight = "-0600";
                    $TZOFFSETTO_daylight = "-0500";
 
                    $ENTOURAGE_TZID = 3;
                    $ENTOURAGE_CFTIMEZONE = "US/Central";
                    break;
                }
                case 'MT': {
                    $TZID = "Mountain Time (US & Canada)";
                    $TZOFFSETFROM_standard = "-0600";
                    $TZOFFSETTO_standard = "-0700";

                    $TZOFFSETFROM_daylight = "-0700";
                    $TZOFFSETTO_daylight = "-0600";

                    $ENTOURAGE_TZID = 2;
                    $ENTOURAGE_CFTIMEZONE = "US/Mountain";
                    break;
                }
                case 'PT': {
                    $TZID = "Pacific Time (US & Canada)";
                    $TZOFFSETFROM_standard = "-0700";
                    $TZOFFSETTO_standard = "-0800";

                    $TZOFFSETFROM_daylight = "-0800";
                    $TZOFFSETTO_daylight = "-0700";

                    $ENTOURAGE_TZID = 1;
                    $ENTOURAGE_CFTIMEZONE = "US/Pacific";
                    break;
                }
            }
         
            $icsstarttime = $starttimehours . $starttimemins . '00';
            $icsendtime = $endtimehours . $endtimemins . '00';

            $start = $event['event_start'] . 'T' . $icsstarttime;
            $end = $event['event_end'] . 'T' . $icsendtime;
            
            // Description for the event
            $briefdesc = strip_tags(get_field('eventbrief',$row['ID']));
            $logintext = strip_tags(get_field('eventslogin',$row['ID']));
            
            if ($logintext != '') {
                
                // So newline \n is interpreted as part of the content and not the ICS end of line, must 
                // escape the new line with another \. Also, the space in front and end must be there or .
                // logintext will not show up in iCalendar
                $description = $briefdesc . " \\n\\n ". $logintext;

            }
            else {
                $description = $briefdesc;
            } 
            
            $slug = strtolower(str_replace(array(' ', "'", '.', ','), array('_', '', '', '_'), $title));

        }

        header("Content-Type: text/Calendar; charset=utf-8");
        header("Content-Disposition: inline; filename={$slug}.ics");

        echo "BEGIN:VCALENDAR\n";
        echo "VERSION:2.0\n";
        echo "PRODID:-//relnei.org//NONSGML {$title} //EN\n";
        echo "METHOD:REQUEST\n"; // required by Outlook
        echo "BEGIN:VTIMEZONE\n";
        echo "TZID:{$TZID}\n";
        echo "X-ENTOURAGE-TZID:{$ENTOURAGE_TZID}\n"; //required by Outlook
        echo "X-ENTOURAGE-CFTIMEZONE:{$ENTOURAGE_CFTIMEZONE}\n"; //required by Outlook
        echo "BEGIN:STANDARD\n";
        echo "DTSTART:20161101T020000\n"; //This value is not for the meeting. It is the beginning date of the current year when standard time started. 
        echo "TZOFFSETFROM:{$TZOFFSETFROM_standard}\n";
        echo "TZOFFSETTO:{$TZOFFSETTO_standard}\n";
        echo "END:STANDARD\n";
        echo "BEGIN:DAYLIGHT\n";
        echo "DTSTART:20160308T020000\n";//This value isn't for the meeting. It's the start of DST for the current year. 
        echo "TZOFFSETFROM:{$TZOFFSETFROM_daylight}\n";
        echo "TZOFFSETTO:{$TZOFFSETTO_daylight}\n";
        echo "END:DAYLIGHT\n";
        echo "END:VTIMEZONE\n";
        echo "BEGIN:VEVENT\n";
        echo "ORGANIZER;CN=\"REL Northeast & Islands\":MAILTO:xxxxx@yyy.zzz\n";
        echo "DESCRIPTION:{$description}\n";
        echo "SUMMARY:{$title}\n";
        echo "DTSTART;TZID={$TZID}:{$start}\n";
        echo "DTEND;TZID={$TZID}:{$end}\n";
        echo "UID:".date('Ymd').'T'.date('His')."-".rand()."-relnei.org\n"; // required by Outlook
        echo "DTSTAMP:".date('Ymd').'T'.date('His').'Z'."\n"; // required by Outlook
        echo "BEGIN:VALARM\n";
        echo "ACTION:DISPLAY\n";
        echo "DESCRIPTION:REMINDER\n";
        echo "TRIGGER:-PT15M\n";
        echo "END:VALARM\n";
        echo "END:VEVENT\n";
        echo "END:VCALENDAR\n";

        exit;
    }

}

add_action ('init', 'create_ics');