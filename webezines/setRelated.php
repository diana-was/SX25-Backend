<?php
//call from Parked http://webezines.kwithost.com/setRelated.php?domain={DOMAIN}&keyword={KEYWORDS}
// Not replaced by parked -- &related_amount=6&related1={RELATED1}&related2={RELATED2}&related3={RELATED3}&related4={RELATED4}&related5={RELATED5}&related6={RELATED6}
//call from Parked setRelated.php?domain=diana.com&keyword=car&related_amount=6&related1=toyota&related2=car dealer&related3=subaru&related4=holden&related5=bmw&related6=lexus
include_once("../config.php");

$domain = isset($_REQUEST['domain'])?strip_tags($_REQUEST['domain']):'';
$keyword = $_REQUEST['keyword']?@mysql_real_escape_string(strip_tags($_REQUEST['keyword'])):'';
$related_amount = isset($_REQUEST['related_amount']) ? $_REQUEST['related_amount'] : 0;

if (empty($domain) || empty($keyword) || !is_numeric($related_amount))
{
	echo "<h2> Error: Missing parameters domain=$domain keyword=$keyword related_amount=$related_amount</h2>";
	exit;
}

// Only for ajax or manual call
$value = array();
$value['keyword'] = $keyword;
for($i=1; $i<=$related_amount; $i++){
	$var = 'related'.$i;
	$value['keyword_'.$var] = isset($_REQUEST[$var])?@mysql_real_escape_string(strip_tags($_REQUEST[$var])):'';
}

$Site = ParkedDomain::getInstance($db);
$Site->setRelatedKeywords($domain,$value);

echo "<h2> The following information have been extracted, they are ".implode(',',$value).". </h2><p>* Please adjust the related_amount and add more RELATED tag to extract more keywords. </p>";
?>