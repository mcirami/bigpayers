<?php namespace LeadMax\TrackYourStats\Report\Formats;
use LeadMax\TrackYourStats\System\Session;
use App\Privilege;
/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 10/23/2017
 * Time: 11:33 AM
 */
class HTML implements Format
{

    public $lastRowStatic;

    public $printTheseArrayKeys;

    public $dates;

    public function __construct($lastRowStatic = false, $printTheseArrayKeys = [], $dates = [])
    {
        $this->lastRowStatic = $lastRowStatic;

        $this->printTheseArrayKeys = $printTheseArrayKeys;

        $this->dates = $dates;
    }

    public function resetArrayKeys($array)
    {
        $temp = [];
        foreach ($array as $item) {
            $temp[] = $item;
        }

        return $temp;
    }

    public function output($report)
    {
		$params = "";
		if(isset($_GET['d_from']) && isset($_GET['d_to']) && isset($_GET['dateSelect']) ) {
			$params = "d_from=" . $_GET['d_from'] . "&d_to=" . $_GET['d_to'] . "&dateSelect=" . $_GET["dateSelect"];
			if (isset($_GET['role'])) {
				$params .= "&role=" . $_GET['role'];
			}
		}  elseif (isset($this->dates['originalStart']) && isset($this->dates['originalEnd'])) {
            $params = "d_from=" . $this->dates['originalStart'] . "&d_to=" . $this->dates['originalEnd'] . "&dateSelect=";
        }

        $report = $this->resetArrayKeys($report);

        foreach ($report as $key => $row) {
            if ($this->lastRowStatic && $key == count($report) - 1) {
                echo "<tr class='static'>";
            } else {
                echo "<tr>";
            }

            if (empty($this->printTheseArrayKeys)) {
                foreach ($row as $item => $val) {
					if($item == "conversions" && $val > 0 && (key_exists('sub', $row) && $row["sub"] != "TOTAL") ) {
						echo "<td><a href='/report/sub/conversions?subid={$row["sub"]}". "&" . "{$params}'>{$val}</a></td>";
					} else {
						echo "<td>{$val}</td>";
					}
                }
            } else {

                foreach ($this->printTheseArrayKeys as $toPrint) {

                    if (isset($row[$toPrint])) {
						if($toPrint == "offer_name") {
							echo "<td><a href='/offer_update.php?idoffer=" . $row['idoffer'] . "'>$row[$toPrint]</a></td>";
						} elseif ($toPrint == "Conversions" && $row[$toPrint] > 0 && (key_exists('idoffer', $row) && $row["idoffer"] != "TOTAL") ) {
                            if(Session::userType() == Privilege::ROLE_AFFILIATE) {
                                $userId = Session::userID();
                                echo "<td><a href='/user/{$userId}/{$row['idoffer']}/conversions-by-country?{$params}'>$row[$toPrint]</a></td>";
                            } else {
                                echo "<td><a href='/report/offer/{$row['idoffer']}/user-conversions?{$params}'>$row[$toPrint]</a></td>";
                            }
						} elseif($toPrint == "Conversions" && $row[$toPrint] > 0 && (key_exists('idrep', $row) && $row[$toPrint] != "TOTAL")) {
							if ( isset( $_GET['role'] ) && $_GET['role'] == 2 ) {
								echo "<td><a href='/report/manager/{$row['idrep']}/conversions-by-offer?{$params}'>$row[$toPrint]</a></td>";
							} else {
								echo "<td><a href='/user/{$row['idrep']}/conversions-by-offer?{$params}'>$row[$toPrint]</a></td>";
							}
						} elseif ($toPrint == "Conversions" &&
						          $row[$toPrint] > 0 &&
						          (key_exists('type', $row) &&
						           $row['type'] == "advertiser" &&
						           $row[$toPrint] != "TOTAL")) {
							echo "<td><a href='/report/advertiser/{$row['id']}/conversions-by-offer?{$params}'>$row[$toPrint]</a></td>";
						} else {
							echo "<td>$row[$toPrint]</td>";
						}

                    }
                }
            }
            echo "</tr>";
        }
    }
}
