<?php
/**
 * Plugin Name: dds-mbta-status
 * Plugin URI: http://crew.ccs.neu.edu
 * Description: Provides a shortcode to display MBTA subway statuses for given stops.
 * Version: 0.1
 * Author: Danny Wolf <wolf@ccs.neu.edu>
 * Author URI: http://crew.ccs.neu.edu
 */

include_once("MBTAShortcoder.php");

if (class_exists('MBTAShortcoder')) {
    register_activation_hook("MBTAShortcoder.php", array("QBAuthenticator", "activate"));
    register_deactivation_hook("MBTAShortcoder.php", array("QBAuthenticator", "deactivate"));

    $dds_mbta_status = new MBTAShortcoder();
}