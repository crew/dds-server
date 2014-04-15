<?php
/**
 * Shortcode statuses for Digital Display Systems
 * User: Danny Wolf - wolfd <wolf@ccs.neu.edu>
 * Date: 4/14/14
 * Time: 8:02 PM
 */

define('DDS_MBTA_STATUS_ORANGE_LINE_URL', 'http://developer.mbta.com/lib/rthr/orange.json');
define('DDS_MBTA_QUERY_CONNECT_TIMEOUT', 5); //seconds to wait for updated status
define('DDS_MBTA_NUMBER_OF_TRAINS_TO_DISPLAY', 4);
define('DDS_MBTA_TIME_ZONE', 'America/New_York');
define('DDS_MBTA_STYLE_NAME', 'mbtastatus');
define('DDS_MBTA_GOOGLE_FONTS_NAME', 'mbtastatusgooglefonts');


class MBTAShortcoder
{
    var $current_status_orange = false;

    function __construct()
    {
        add_shortcode('mbtastatus', array(&$this, 'mbta_status_short_code'));

        wp_register_style(DDS_MBTA_STYLE_NAME, plugins_url('mbta-status.css', __FILE__));
        wp_register_style(DDS_MBTA_GOOGLE_FONTS_NAME, '//fonts.googleapis.com/css?family=Nunito:300,400,700');
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

        // load stylesheets
        wp_enqueue_style(DDS_MBTA_STYLE_NAME);
        wp_enqueue_style(DDS_MBTA_GOOGLE_FONTS_NAME);

        $status = $this->get_status_orange($stop);

        ob_start();
        ?>
        <div class="mbta-container">
            <div class="mbta-stop-name-container"><h1 class="mbta-stop-name mbta-heavy"><?php echo $stop; ?></h1></div>
            <div class="mbta-status-banner-container"><h1 class="mbta-status-banner mbta-light">MBTA Status</h1></div>

            <?php
            foreach ($status as $destination => $predictions) {
                ?>
                <div class="mbta-destination-container"><h2 class="mbta-destination mbta-medium">
                        To <?php echo $destination; ?></h2>

                    <div class="mbta-trains-container">
                        <h3 class="mbta-trains-in mbta-light">Trains arrive in:</h3>
                        <?php

                        for ($i = 0; $i < min(count($predictions), DDS_MBTA_NUMBER_OF_TRAINS_TO_DISPLAY); $i++) {
                            $eta_minutes = number_format(($predictions[$i] / 60), 0, '.', ','); // In case of readability during/after armageddon.
                            ?><h3 class="mbta-minutes mbta-medium"><?php echo $eta_minutes; ?> minutes</h3><?php
                        }

                        ?>
                    </div>
                </div>
            <?php
            }
            ?>
        </div>
        <?php

        return ob_get_clean();
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
        foreach ($stop_predictions as $destination => $predictions) {
            // create temporary array for storing prediction times and then sorting.
            $updated_predictions_part = array();
            foreach ($predictions as $index => $prediction) {
                $updated_predictions_part[$index] = intval($prediction) - intval($time_difference); // just in case
            }
            // sort low to high prediction times
            sort($updated_predictions_part);
            // update destination
            $updated_predictions[$destination] = $updated_predictions_part;
        }

        // sort destination keys
        ksort($updated_predictions);

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