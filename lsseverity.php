<?php
//$articleId = JRequest::getInt('id');
// IDL PS
use Joomla\CMS\Factory;
$app = Factory::getApplication();
$articleId = $app->input->getInt('id');

$db =& JFactory::getDBO();

$sql = "SELECT * FROM #__content WHERE id = ".intval($articleId);


$db->setQuery($sql);
$fullArticle = $db->loadObject();
$finalArr['severity'] = $fullArticle->fulltext;

//end of original
//JN modification, only if Severity is defined.

	$percentage = "53.5";
	$color = "ffff00";
if (strpos(strtolower($finalArr['severity']), 'minor') !== false && (strpos(strtolower($finalArr['severity']), 'severity') !== false || strpos(strtolower($finalArr['severity']), 'rating') !== false)) {
	$percentage = "13.5";
	$color = "00aeef";
}
if (strpos(strtolower($finalArr['severity']), 'informational') !== false && (strpos(strtolower($finalArr['severity']), 'severity') !== false || strpos(strtolower($finalArr['severity']), 'rating') !== false)) {
	$percentage = "13.5";
	$color = "00aeef";
}
if (strpos(strtolower($finalArr['severity']), 'low') !== false && (strpos(strtolower($finalArr['severity']), 'severity') !== false || strpos(strtolower($finalArr['severity']), 'rating') !== false)) {
	$percentage = "33.5";
	$color = "00a651";
}
if (strpos(strtolower($finalArr['severity']), 'moderate') !== false && (strpos(strtolower($finalArr['severity']), 'severity') !== false || strpos(strtolower($finalArr['severity']), 'rating') !== false)) {
	$percentage = "33.5";
	$color = "00a651";
}
if (strpos(strtolower($finalArr['severity']), 'trivial') !== false && (strpos(strtolower($finalArr['severity']), 'severity') !== false || strpos(strtolower($finalArr['severity']), 'rating') !== false)) {
	$percentage = "53.5";
	$color = "ffff00";
}
if (strpos(strtolower($finalArr['severity']), 'medium') !== false && (strpos(strtolower($finalArr['severity']), 'severity') !== false || strpos(strtolower($finalArr['severity']), 'rating') !== false)) {
	$percentage = "53.5";
	$color = "ffff00";
}
if (strpos(strtolower($finalArr['severity']), 'important') !== false && (strpos(strtolower($finalArr['severity']), 'severity') !== false || strpos(strtolower($finalArr['severity']), 'rating') !== false)) {
	$percentage = "73.5";
	$color = "f99609";
}
if (strpos(strtolower($finalArr['severity']), 'major') !== false && (strpos(strtolower($finalArr['severity']), 'severity') !== false || strpos(strtolower($finalArr['severity']), 'rating') !== false)) {
	$percentage = "73.5";
	$color = "f99609";
}
if (strpos(strtolower($finalArr['severity']), 'severe') !== false && (strpos(strtolower($finalArr['severity']), 'severity') !== false || strpos(strtolower($finalArr['severity']), 'rating') !== false)) {
	$percentage = "93.5";
	$color = "ed1c24";
}
if (strpos(strtolower($finalArr['severity']), 'critical') !== false && (strpos(strtolower($finalArr['severity']), 'severity') !== false || strpos(strtolower($finalArr['severity']), 'rating') !== false)) {
	$percentage = "93.5";
	$color = "ed1c24";
}

if (strpos(strtolower($finalArr['severity']), 'severity: high') !== false) {
	$percentage = "93.5";
	$color = "ed1c24";
}


//print_r(get_object_vars ($fullArticle));
//CentosFix (severity in title)
if($fullArticle->catid=='199'){
	if (strpos(strtolower($fullArticle->title), 'critical') !== false) {
		$percentage = "93.5";
		$color = "ed1c24";
	}
	if (strpos(strtolower($fullArticle->title), 'severe') !== false) {
		$percentage = "93.5";
		$color = "ed1c24";
	}
	if (strpos(strtolower($fullArticle->title), 'major') !== false) {
		$percentage = "93.5";
		$color = "ed1c24";
	}
	if (strpos(strtolower($fullArticle->title), 'important') !== false) {
		$percentage = "73.5";
		$color = "f99609";
	}
	if (strpos(strtolower($fullArticle->title), 'medium') !== false) {
		$percentage = "53.5";
		$color = "ffff00";
	}
	if (strpos(strtolower($fullArticle->title), 'trivial') !== false) {
		$percentage = "53.5";
		$color = "ffff00";
	}
	if (strpos(strtolower($fullArticle->title), 'moderate') !== false) {
		$percentage = "33.5";
		$color = "00a651";
	}
	if (strpos(strtolower($fullArticle->title), 'low') !== false) {
		$percentage = "33.5";
		$color = "00a651";
	}
	if (strpos(strtolower($fullArticle->title), 'informational') !== false) {
		$percentage = "13.5";
		$color = "00aeef";
	}
	if (strpos(strtolower($fullArticle->title), 'minor') !== false) {
		$percentage = "13.5";
		$color = "00aeef";
	}
}

// RockyLinux
if($fullArticle->catid=='219'){
	$finalArr['severity'] = json_decode($fullArticle->introtext)->severity;
	if (strpos(strtolower($finalArr['severity']), 'critical') !== false) {
		$percentage = "93.5";
		$color = "ed1c24";
	}
	if (strpos(strtolower($finalArr['severity']), 'severe') !== false) {
		$percentage = "93.5";
		$color = "ed1c24";
	}
	if (strpos(strtolower($finalArr['severity']), 'major') !== false) {
		$percentage = "93.5";
		$color = "ed1c24";
	}
	if (strpos(strtolower($finalArr['severity']), 'important') !== false) {
		$percentage = "73.5";
		$color = "f99609";
	}
	if (strpos(strtolower($finalArr['severity']), 'medium') !== false) {
		$percentage = "53.5";
		$color = "ffff00";
	}
	if (strpos(strtolower($finalArr['severity']), 'trivial') !== false) {
		$percentage = "53.5";
		$color = "ffff00";
	}
	if (strpos(strtolower($finalArr['severity']), 'moderate') !== false) {
		$percentage = "33.5";
		$color = "00a651";
	}
	if (strpos(strtolower($finalArr['severity']), 'low') !== false) {
		$percentage = "33.5";
		$color = "00a651";
	}
	if (strpos(strtolower($finalArr['severity']), 'informational') !== false) {
		$percentage = "13.5";
		$color = "00aeef";
	}
	if (strpos(strtolower($finalArr['severity']), 'minor') !== false) {
		$percentage = "13.5";
		$color = "00aeef";
	}
}


//oracle
if($fullArticle->catid=='217'){
	if (strpos(strtolower($fullArticle->title), 'critical') !== false) {
		$percentage = "93.5";
		$color = "ed1c24";
	}
	if (strpos(strtolower($fullArticle->title), 'severe') !== false) {
		$percentage = "93.5";
		$color = "ed1c24";
	}
	if (strpos(strtolower($fullArticle->title), 'major') !== false) {
		$percentage = "93.5";
		$color = "ed1c24";
	}
	if (strpos(strtolower($fullArticle->title), 'important') !== false) {
		$percentage = "73.5";
		$color = "f99609";
	}
	if (strpos(strtolower($fullArticle->title), 'medium') !== false) {
		$percentage = "53.5";
		$color = "ffff00";
	}
	if (strpos(strtolower($fullArticle->title), 'trivial') !== false) {
		$percentage = "53.5";
		$color = "ffff00";
	}
	if (strpos(strtolower($fullArticle->title), 'moderate') !== false) {
		$percentage = "33.5";
		$color = "00a651";
	}
	if (strpos(strtolower($fullArticle->title), 'low') !== false) {
		$percentage = "33.5";
		$color = "00a651";
	}
	if (strpos(strtolower($fullArticle->title), 'informational') !== false) {
		$percentage = "13.5";
		$color = "00aeef";
	}
	if (strpos(strtolower($fullArticle->title), 'minor') !== false) {
		$percentage = "13.5";
		$color = "00aeef";
	}
}




?>

<script type="text/javascript" src="https://code.jquery.com/jquery-1.12.0.min.js"></script>
<script>
$(document).ready(function(){
	//move image to summary container
	$('div#sppb-addon-1619715121594').prependTo(jQuery('.summary1 .sppb-addon-content'));
    $('.progress-title > span').each(function(){
        $(this).prop('Counter',0).animate({
            Counter: $(this).text()
        },{
            duration: 1500,
            easing: 'swing',
            step: function (now){
                $(this).text(Math.ceil(now));
            }
        });
    });
});
</script>
<style>
.severity {
	width: 100%;
}
.severity::after {
    display:none !important;
}
.progress-title{
    font-size: 18px;
    font-weight: 700;
    color: #333;
    margin: 0 0 20px;
}
.progress{
    height: 20px;
    /*background: #00aeef;*/
    background-image: linear-gradient(90deg,
        #00aeef 20%,
        #00a651 20%, #00a651 40%,
        #ffff00 40%, #ffff00 60%,
        #f99609 60%, #f99609 80%,
        #ed1c24 80%);
    
    border-radius: 30px;
    box-shadow: none;
	margin-top:20px;
    margin-bottom: 30px;
    overflow: visible;
}
.progress .progress-bar{
    border-radius: 30px;
    box-shadow: none;
    position: relative;
    animation: animate-positive 2s;
    overflow:visible;
    background-color:transparent !important;
}
.progress .progress-bar:before{
    content: "";
    width: 100%;
    height: 50%;
    /*background: rgba(0,0,0,0.3); */
    border-radius: 0 0 10px 10px;
    position: absolute;
    bottom: 0;
    left: 0;
    z-index: 2;
}
.progress .progress-bar:after{
    content: "";
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #002110;
    border: 10px solid #<?php echo $color; ?>;
    position: absolute;
    bottom: -6px;
    right: 0;
    z-index: 1;
    box-shadow: 0px 0px 8px 0px #000000;
}
@keyframes animate-positive{
    0%{ width: 0; }
}
</style>

            <div class="progress">
                <div class="progress-bar" style="width:<?php echo $percentage; ?>%; background:#deeb11;"></div>
            </div>