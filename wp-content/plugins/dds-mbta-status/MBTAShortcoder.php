<?php
/**
 * Shortcode statuses for Digital Display Systems
 * User: Danny Wolf - wolfd <wolf@ccs.neu.edu>
 * Date: 4/14/14
 * Time: 8:02 PM
 */

define('DDS_MBTA_STATUS_ORANGE_LINE_URL', 'http://developer.mbta.com/lib/rthr/orange.json');
define('DDS_MBTA_QUERY_CONNECT_TIMEOUT', 5); //seconds to wait for updated status
define('DDS_MBTA_NUMBER_OF_TRAINS_TO_DISPLAY', 3);
define('DDS_MBTA_TIME_ZONE', 'America/New_York');


class MBTAShortcoder
{
    var $current_status_orange = false;

    function __construct()
    {
        add_shortcode('mbtastatus', array(&$this, 'mbta_status_short_code'));
    }

    function activate()
    {

    }

    function deactivate()
    {

    }

    function mbta_status_short_code($atts)
    {
        // get attributes out of short code
        extract(shortcode_atts(array(
            'line' => 'Orange Line',
            'stop' => 'Ruggles'
        ), $atts));

        $status = $this->get_status_orange($stop);

        ob_start();
        ?>
        <h1><?php echo $stop; ?></h1>
        <?php
        foreach ($status as $destination => $predictions) {

            ?>
            <div><h2><?php echo $destination; ?></h2>

                <div><h3>Trains in:</h3>
                    <?php

                    for ($i = 0; $i < min(count($predictions), DDS_MBTA_NUMBER_OF_TRAINS_TO_DISPLAY); $i++) {
                        $eta_minutes = number_format(($predictions[$i] / 60), 0, '.', ','); // please never have to format the ETA minutes with a comma. In case of readability during armageddon.
                        echo "<br>$eta_minutes";
                    }

                    ?>
                    <h3>minutes</h3></div>
            </div>
        <?php
        }

        ob_get_clean();
    }

    function get_status_orange($stop_name)
    {
        // Each different destination in the JSON file has an index.
        $stop_predictions = array();

        // update information if necessary
        $this->update_orange_line();

        // if for some reason the status update failed, exit
        if ($this->current_status_orange == false) return false;

        // the timestamp of the JSON
        $mbta_time = $this->current_status_orange->TripList->CurrentTime;

        // walk through the json and get every prediction that is for $stop_name
        foreach ($this->current_status_orange->TripList->Trips as $trip) {
            $destination = $trip->Destination;
            $predictions = $trip->Predictions;

            // if stop_predictions does not have an entry for this destination, initialize it.
            if (!isset($stop_predictions[$destination])) {
                $stop_predictions[$destination] = array();
            }


            foreach ($predictions as $prediction) {

                if ($prediction->Stop == $stop_name) {
                    // add prediction seconds to array under the right destination
                    array_push($stop_predictions[$destination], $prediction->Seconds);
                }
            }
        }

        // update each prediction based on the difference between $mbta_time and the server's time
        // get time difference
        $now = new DateTime;
        $now->setTimezone(new DateTimeZone(DDS_MBTA_TIME_ZONE));
        $server_time = $now->getTimestamp();
        $time_difference = $server_time - $mbta_time;

        $updated_predictions = array();

        // loop through predictions and subtract the time difference from every prediction.
        foreach ($stop_predictions as $destination => $prediction) {
            $updated_predictions[$destination] = intval($prediction) - intval($time_difference); // just in case
        }

        // sort destination keys
        ksort($updated_predictions);

        // sort low to high prediction times
        sort($updated_predictions);

        return $updated_predictions;
    }

    private function update_orange_line()
    {
        if ($this->current_status_orange == false) {
            // gets the transient or sets it
            if (false === ($this->current_status_orange = get_transient('dds_mbta_current_status_orange'))) {
                $raw_contents = $this->get_data(DDS_MBTA_STATUS_ORANGE_LINE_URL);
                if ($raw_contents != false) {
                    $this->current_status_orange = json_decode($raw_contents);
                } else {
                    $this->current_status_orange = false; // curl failed
                    error_log("DDS MBTA : Failed to get MBTA information from url" . DDS_MBTA_STATUS_ORANGE_LINE_URL);
                }

                // set transient
                set_transient(
                    'dds_mbta_current_status_orange',
                    $this->current_status_orange,
                    20 //seconds (MBTA doesn't want polls in frequencies greater than once every 10 seconds)
                );
            }
        }
    }


    /* gets the data from a URL
    generic code from: http://davidwalsh.name/curl-download
    */
    private function get_data($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, DDS_MBTA_QUERY_CONNECT_TIMEOUT);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}