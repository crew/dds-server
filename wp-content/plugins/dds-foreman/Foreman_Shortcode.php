<?php
/*
Plugin Name: Foreman Statistics Plugin
Plugin URI: http://crew.ccs.neu.edu/
Description: Adds foreman statistics shortcode to wordpress
Version: 0.1
Author: Eddie Hurtig
Author URI: http://crew.ccs.neu.edu/people
*/

class Foreman_Shortcode {
    protected  $basic_authentication;
    protected $username;
    protected $password;
    protected $foreman_url;
    protected $transient_key;
    protected $ignore_ssl;
    protected $loaded;

    protected $data;
    /**
     * The Constructor for the Shortcode Plugin
     */
    function __construct() {

        add_action('init', array(&$this, 'init'));
    }

    /**
     * The init function for this Foreman API Shortcode Plugin
     * Runs on Wordpress init action
     */
    function init() {
        $this->loaded = false;

        // Abstractable
        $options = apply_filters('json_api_request_opts', get_option('dds_foreman_options', false));

        if ($options === false) {
            return;
        }


        $this->ignore_ssl = true;
        $this->basic_authentication = true;
        $this->username = $options['username'];
        $this->password = $options['password'];
        $this->foreman_url = $options['foreman_url'];
        $this->transient_key = $options['transient_key'];
        $this->transient_timeout = $options['transient_timeout'];


        add_shortcode('foreman-api', array(&$this, 'do_shortcode'));
    }

    /**
     * @param $atts
     * @param null $content
     * @return string
     */
    function do_shortcode($atts, $content = NULL) {


        $this->loadData();

        if (!$this->loaded)
            return;



        $json = (array) json_decode($this->data);

        $total_hosts = $json->total_hosts;


        $pie_fields = array(
            'good_hosts_enabled' => array('label' => __('Good Hosts'),
                'color' => 'blue'),
            'active_hosts_ok_enabled' => array('label' => __('Good host reports in the last 35 minutes'),
                'color' => 'blue'),
            'out_of_sync_hosts_enabled' => array('label' => __('Hosts that had performed modifications without error'),
                'color' => 'orange'),
            'bad_hosts_enabled' => array('label' => __('Hosts in error state'),
                'color' => 'red'),
            'pending_hosts_enabled' => array('label' => __('Hosts that had pending changes'),
                'color' => 'purple'),
            'reports_missing' => array('label' => __('Hosts with no reports'),
                'color' => 'yellow'),
            'disabled_hosts' => array('label' => __('Hosts with alerts disabled'),
                'color' => 'gray'),
        );

        $pie_data = array();

        foreach ($json as $key => $value) {
            if (array_key_exists($key, $pie_fields)) {
                $pie_data[] = array('label' => $pie_fields[$key]['label'], 'value' => $value);
            }
        }

        die(json_encode($pie_data));





        $html = '<div style="text-align:center;">';
        $html .= '<h1>Latest Foreman Statistics</h1>';
        $html .= "<uL>";
        foreach ($this->data as $key => $value) {
            $html .= '<li>' . $key . ': ' . $value . '</li>';
        }
        $html .= "</ul>";
        $html .= '</div>';

        return $html;
    }

    /**
     * @param string $resource
     * @return array|mixed
     */
    function loadData($resource = 'dashboard') {
        if (false === ($this->data = get_transient($this->transient_key, false))) {
            $curler = curl_init($this->foreman_url . $resource);
            if ($this->basic_authentication) {
                curl_setopt($curler, CURLOPT_USERPWD, "{$this->username}:{$this->password}");
            }
            curl_setopt($curler, CURLOPT_HTTPHEADER,  'Accept: application/json');
            curl_setopt($curler, CURLOPT_TIMEOUT, 30);
            curl_setopt($curler, CURLOPT_CONNECTTIMEOUT, 30);
            if ($this->ignore_ssl) {
                curl_setopt($curler, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($curler, CURLOPT_SSL_VERIFYPEER, 0);
            }
            curl_setopt($curler, CURLOPT_RETURNTRANSFER, true);
            $data = curl_exec($curler);
            curl_close($curler);
            $this->data = $data;

            $updated = set_transient($this->transient_key, $this->data, $this->transient_timeout);

            if (!$updated) {
                return false;
            }
            $this->loaded = true;
            return $this->data;
        } else {
            $this->loaded = true;
            return $this->data;
        }
    }
}
$foreman_api_shortcode = new Foreman_Shortcode();