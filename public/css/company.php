<?php
header('Content-Type: text/css');

include("../../bootstrap/legacy_loader.php");


$colors = \LeadMax\TrackYourStats\System\Company::loadFromSession()->getColors();

$valueSpan1 = $colors[0];
$valueSpan2 = $colors[1];
$valueSpan3 = $colors[2];
$valueSpan4 = $colors[3];
$valueSpan5 = $colors[4];
$valueSpan6 = $colors[5];
$valueSpan7 = $colors[6];
$valueSpan8 = $colors[7];
$valueSpan9 = $colors[8];
$valueSpan10 = $colors[9];
$valueSpan11 = $colors[10];
?>

.value_span1 {
background-color: #<?php echo $valueSpan1; ?>;
}

.value_span1-2:hover {
background: #<?php echo $valueSpan1; ?> !important;
}

.value_span2 {
color: #<?php echo $valueSpan2; ?>!important;
}

.value_span2-2:hover {
color: #<?php echo $valueSpan2; ?> ;
}

.value_span2-3:hover {
border: 2px solid #<?php echo $valueSpan2; ?> !important;
}
.value_span3 {
background: #<?php echo $valueSpan3; ?> ;
}
.value_span3-1 {
background: #<?php echo $valueSpan3; ?> ;
}
.value_span3-1:hover {
background: #<?php echo $valueSpan1; ?> ;
}

<!--
.value_span3-2 {
border-left: 3px solid #<?php /*echo $valueSpan3; */?>;
}
-->
.value_span4:hover, .value_span4.active {
background:  #<?php echo $valueSpan4; ?> ;
color: #fff;
}

.value_span4.active:hover {
background: #<?php echo $valueSpan4; ?>;
color: #fff;
}

.value_span4-1, .value_span4-2 {
background: #<?php echo $valueSpan4; ?>;
}

.value_span4-1:hover {
background: #<?php echo $valueSpan3; ?>;
}

.value_span5 {
color: #<?php echo $valueSpan5; ?> ;
}

.value_span5-1 {
  background: #<?php echo $valueSpan5; ?> ;
}

.value_span6:hover {
  color: #<?php echo $valueSpan6; ?> ;
}

.value_span6-1 {
  background: #<?php echo $valueSpan6; ?> ;
}

.value_span6-2 {
background: #<?php echo $valueSpan6; ?> !important;
}

.value_span6-3:hover a {
  color: #<?php echo $valueSpan6; ?> ;
}

.value_span6-4:before {
  border-bottom: 12px solid #<?php echo $valueSpan6; ?> !important;
}

.value_span6-5:hover {
  background: #<?php echo $valueSpan6; ?> ;
}
.value_span7 {
  background: #<?php echo $valueSpan7; ?> ;
}

.tr_row_space {
border-bottom: 3em solid #<?php echo $valueSpan7; ?>;


}

.value_span8 {
background: #<?php echo $valueSpan8; ?> ;
}

.value_span9 {
color: #<?php echo $valueSpan9; ?> ;
}

.value_span10 {
color: #999999;
}

.value_span11 {
  background: #<?php echo $valueSpan11; ?>;
}