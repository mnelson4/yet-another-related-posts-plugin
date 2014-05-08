<?php
global $yarpp;

if (isset($_GET['aid']) && isset($_GET['v']) && isset($_GET['st']) && isset($_GET['dpid'])) {
    $yarpp->yarppPro['aid'] = (trim($_GET['aid']) !== '') ? $_GET['aid'] : null;
    $yarpp->yarppPro['dpid']= (trim($_GET['dpid'])!== '') ? $_GET['dpid']: null;
    $yarpp->yarppPro['st']  = (trim($_GET['st'])  !== '') ? rawurlencode($_GET['st']) : null;
    $yarpp->yarppPro['v']   = (trim($_GET['v'])   !== '') ? rawurlencode($_GET['v'])  : null;

    update_option('yarpp_pro', $yarpp->yarppPro);
}

$src = urlencode(admin_url().'options-general.php?page='.$_GET['page']);
$aid = (isset($yarpp->yarppPro['aid']) && $yarpp->yarppPro['aid']) ? $yarpp->yarppPro['aid'] : 0;
$st  = (isset($yarpp->yarppPro['st'])  && $yarpp->yarppPro['st'])  ? $yarpp->yarppPro['st']  : 0;
$v   = (isset($yarpp->yarppPro['v'])   && $yarpp->yarppPro['v'])   ? $yarpp->yarppPro['v']   : 0;
$d   = urlencode(get_home_url());
$url = 'https://yarpp.adkengage.com/AdcenterUI/PublisherUI/PublisherDashboard.aspx?src='.$src.'&d='.$d.'&aid='.$aid.'&st='.$st.'&plugin=1';

include(YARPP_DIR.'/includes/phtmls/yarpp_pro_options.phtml');