<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Prevent direct access

class Kibsterlp_Echo {

    public function __construct() {
        // Run echo when class is instantiated
        add_action( 'init', [ $this, 'say_hello' ] );
    }

    public function say_hello() {
        echo '<p style="color: green; text-align:center;">Hello from Kibsterlp_Echo class!</p>';
    }
}

// Instantiate immediately
new Kibsterlp_Echo();
