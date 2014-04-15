<?php
/**
 * Shortcode statuses for Digital Display Systems
 * User: Danny Wolf - wolfd <wolf@ccs.neu.edu>
 * Date: 4/14/14
 * Time: 8:02 PM
 */

/**
 * Defines base url to add line name onto. Only supported lines are blue, red, and orange.
 */
define('DDS_MBTA_STATUS_BASE_LINE_URL', 'http://developer.mbta.com/lib/rthr/');
/**
 * Seconds to wait for updated status
 */
define('DDS_MBTA_QUERY_CONNECT_TIMEOUT', 5);
/**
 * Maximum number of train's prediction times to display
 */
define('DDS_MBTA_NUMBER_OF_TRAINS_TO_DISPLAY', 4);
/**
 * Time zone of MBTA
 */
define('DDS_MBTA_TIME_ZONE', 'America/New_York');
/**
 * Name of the css style registered with WordPress
 */
define('DDS_MBTA_STYLE_NAME', 'mbtastatus');
/**
 * Name of the css style registered with WordPress for google fonts
 */
define('DDS_MBTA_GOOGLE_FONTS_NAME', 'mbtastatusgooglefonts');


/**
 * Class MBTAShortcoder
 */
class MBTAShortcoder
{
    /**
     * @var array
     */
    var $current_status = array();

    /**
     *
     */
    function __construct()
    {
        add_shortcode('mbtastatus', array(&$this, 'mbta_status_short_code'));

        wp_register_style(DDS_MBTA_STYLE_NAME, plugins_url('mbta-status.css', __FILE__));
        wp_register_style(DDS_MBTA_GOOGLE_FONTS_NAME, '//fonts.googleapis.com/css?family=Nunito:300,400,700');

        // load stylesheets
        wp_enqueue_style(DDS_MBTA_STYLE_NAME);
        wp_enqueue_style(DDS_MBTA_GOOGLE_FONTS_NAME);
    }

    /**
     * Activate plugin, and add associated options
     */
    function activate()
    {

    }

    /**
     * Deactivate plugin, and remove associated options
     */
    function deactivate()
    {

    }

    /** Shortcode for getting subway status for red, orange, and blue lines
     * @param $atts array line: short name (red, orange, blue), stop: full name according to MBTA (might require looking up, e.g. 'Ruggles' or 'Park Street')
     * @return string the shortcode's html
     */
    function mbta_status_short_code($atts)
    {
        // get attributes out of short code
        extract(shortcode_atts(array(
            'line' => 'orange', // short name
            'stop' => 'Ruggles'
        ), $atts));

        $status = $this->get_status($line, $stop);

        $long_name = $this->get_long_name($line);

        ob_start();
        ?>
        <div class="mbta-container">
            <div class="mbta-status-banner-container"
                 style="border-bottom: 1px solid <?php echo $this->get_color($line); ?>">
                <div class="mbta-banner-left">
                    <h1 class="mbta-large mbta-heavy mbta-inline"><?php echo $stop; ?></h1>

                    <h1 class="mbta-large mbta-medium mbta-inline"
                        style="color: <?php echo $this->get_color($line); ?>"><?php echo $long_name; ?></h1>
                </div>
                <div class="mbta-banner-right"><h1 class="mbta-large mbta-light mbta-inline">MBTA Status</h1></div>
            </div>

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

            if (count($status) == 0) {
                ?>
                <div class="mbta-no-trains-container"><h1 class="mbta-superlarge mbta-medium">No Trains Running</h1>
                </div>
            <?php
            }
            ?>
        </div>
        <?php

        return ob_get_clean();
    }

    /** Get the subway's official color according to Wikipedia by the line's short name
     * @param $line string the short name of the line (e.g. orange)
     * @return string the HTML color code prefaced by a #
     */
    function get_color($line)
    {
        switch ($line) {
            case 'orange':
            return '#FD8A03';
            case 'red':
            return '#FA2D27';
            case 'green': //wishful thinking
            return '#008150';
            case 'blue':
            return '#2F5DA6';
            default:
                return '#000000';
        }
    }

    /** Get the short name of a line based on it's long name
     * @param $line string the long name of a line (e.g. 'Orange Line')
     * @return string the short name of the line (e.g. 'orange')
     */
    function get_short_name($line)
    {
        switch ($line) {
            case 'Orange Line':
                return 'orange';
            case 'Red Line':
                return 'red';
            case 'Green Line': //wishful thinking
                return 'green';
            case 'Blue Line':
                return 'blue';
            default:
                return 'default';
        }
    }

    /** Get the long name of a line based on it's short name
     * @param $line string the short name of the line (e.g. 'orange')
     * @return string the long name of a line (e.g. 'Orange Line')
     */
    function get_long_name($line)
    {
        switch ($line) {
            case 'orange':
                return 'Orange Line';
            case 'red':
                return 'Red Line';
            case 'green': //wishful thinking
                return 'Green Line';
            case 'blue':
                return 'Blue Line';
            default:
                return 'N/A';
        }
    }

    /** Get the status of a line. CURLs MBTA's API if necessary, otherwise uses a timestamp to count down.
     * @param $line_name string the short name of a line
     * @param $stop_name string the exact MBTA stop name (e.g. 'Ruggles' or 'Park Street')
     * @return array|bool json parsed status as an object or false if it couldn't get info
     */
    function get_status($line_name, $stop_name)
    {
        // Each different destination in the JSON file has an index.
        $stop_predictions = array();

        // update information if necessary
        $this->update_line($line_name);

        // if for some reason the status update failed, exit
        if (!isset($this->current_status[$line_name])) return false;

        // the timestamp of the JSON
        $mbta_time = $this->current_status[$line_name]->TripList->CurrentTime;

        // walk through the json and get every prediction that is for $stop_name
        foreach ($this->current_status[$line_name]->TripList->Trips as $trip) {
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

    /** Update the line if necessary, and set the $current_status variable to either a transient with recent info or currently CURLed info.
     * @param $line string the short name of the line
     */
    private function update_line($line)
    {
        if (!isset($this->current_status[$line])) {
            // gets the transient or sets it
            if (false === ($this->current_status[$line] = get_transient('dds_mbta_current_status_' . $line))) {
                $raw_contents = $this->get_data(DDS_MBTA_STATUS_BASE_LINE_URL . $this->get_short_name($line) . '.json');
                if ($raw_contents != false) {
                    $this->current_status[$line] = json_decode($raw_contents);
                } else {
                    $this->current_status[$line] = false; // curl failed
                    error_log("DDS MBTA : Failed to get MBTA information from url" . DDS_MBTA_STATUS_BASE_LINE_URL . $line . '.json');
                }

                // set transient
                set_transient(
                    'dds_mbta_current_status_' . $line,
                    $this->current_status[$line],
                    20 //seconds (MBTA doesn't want polls in frequencies greater than once every 10 seconds)
                );
            }
        }
    }

    /** gets the data from a URL through CURL
     * from: http://davidwalsh.name/curl-download
     * @param $url string the url to CURL
     * @return mixed|false returns false if failed, or data if true
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