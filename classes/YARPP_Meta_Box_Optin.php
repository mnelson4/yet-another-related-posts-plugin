<?php

class YARPP_Meta_Box_Optin extends YARPP_Meta_Box {
    public function display() {
        $output =
            '<p>'.
            'Enable the free <a href="http://www.yarpp.com" target="_blank">YARPP Pro enhancements</a> to add even '.
            'more power to your blog or website!'.
            '<br/><br/>'.
            '<a href="'.plugins_url('/', dirname(__FILE__)).'includes/yarpp_switch.php" class="yarpp_switch_button button" data-go="pro">Turn them on now</a>&nbsp;&nbsp;'.
            '<a href="http://www.yarpp.com" target="_blank" style="float:right;text-decoration:underline">Learn more</a>'.
            '</p>'.
            '<p>'.
            'We can continue to improve the YARPP product for you if we know how it&#39;s used. Please help us by '.
            'allowing usage data to be sent back.'.
            '<br/>'.
            '</p>'.
            '<input
                type="checkbox"
                id="yarpp-optin"
                name="optin"
                value="true" '.
            checked(yarpp_get_option('optin') == 1 ,true, false).' '.
            '/>'.
            '<label for="yarpp-optin">Send usage data back.</label>'.
            '<a href="#" id="yarpp-optin-learnmore" style="float:right;text-decoration:underline">Learn More</a>'
        ;
        echo $output;
    }
}