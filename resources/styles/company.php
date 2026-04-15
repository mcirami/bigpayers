<?php
header('Content-Type: text/css');

//include("../../bootstrap/legacy_loader.php");


$colors = \LeadMax\TrackYourStats\System\Company::loadFromSession()->getColors();

for ($i = 0; $i < 11; $i++) {
  $colors[$i] = $colors[$i] ?? '000000'; // Default to black if not set
}

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

echo "
.value_span1 {
background-color: #$valueSpan1;
}

.value_span1-2:hover {
background: #$valueSpan1 !important;
}

.value_span2 {
color: #$valueSpan2!important;
}

.value_span2-2:hover {
color: #$valueSpan2 ;
}

.value_span2-3:hover {
border: 2px solid #$valueSpan2 !important;
}
.value_span3 {
background: #$valueSpan3 ;
}
.value_span3-1 {
background: #$valueSpan3 ;
}
.value_span3-1:hover {
background: #$valueSpan1 ;
}

<!--
.value_span3-2 {
border-left: 3px solid #<?php /*echo $valueSpan3; */?>;
}
-->
.value_span4:hover, .value_span4.active {
background:  #$valueSpan4 ;
color: #fff;
}

.value_span4.active:hover {
background: #$valueSpan4;
color: #fff;
}

.value_span4-1, .value_span4-2 {
background: #$valueSpan4;
}

.value_span4-1:hover {
background: #$valueSpan3;
}

.value_span5 {
color: #$valueSpan5 ;
}

.value_span5-1 {
  background: #$valueSpan5 ;
}

.value_span6:hover {
  color: #$valueSpan6 ;
}

.value_span6-1 {
  background: #$valueSpan6 ;
}

.value_span6-2 {
background: #$valueSpan6 !important;
}

.value_span6-3:hover a {
  color: #$valueSpan6 ;
}

.value_span6-4:before {
  border-bottom: 12px solid #$valueSpan6 !important;
}

.value_span6-5:hover {
  background: #$valueSpan6 ;
}
.value_span7 {
  background: #$valueSpan7 ;
}

.tr_row_space {
border-bottom: 3em solid #$valueSpan7;


}

.value_span8 {
background: #$valueSpan8 ;
}

.value_span9 {
color: #$valueSpan9 ;
}

.value_span10 {
color: #999999;
}

.value_span11 {
  background: #$valueSpan11;
}
  ";