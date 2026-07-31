<?php namespace App\Support\Report\Formats;
use App\Support\CurrentUserSession;
use App\Support\NativeRequest;
use App\Privilege;
/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 10/23/2017
 * Time: 11:33 AM
 */
class Html implements Format
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
        $dateFrom = NativeRequest::query('d_from');
        $dateTo = NativeRequest::query('d_to');
        $dateSelect = NativeRequest::query('dateSelect');
        $role = NativeRequest::query('role');

		if($dateFrom !== null && $dateTo !== null && $dateSelect !== null ) {
			$params = "d_from=" . $dateFrom . "&d_to=" . $dateTo . "&dateSelect=" . $dateSelect;
			if ($role !== null) {
				$params .= "&role=" . $role;
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
					if($item == "conversions" && $val > 0 && (key_exists('sub', $row) && $row["sub"] != "TOTAL") && $row["sub"] !== "(empty)" ) {
						echo "<td><a class='bp-report-link' href='/report/sub/conversions?subid={$row["sub"]}". "&" . "{$params}'>{$val}</a></td>";
					} else {
						echo "<td>{$val}</td>";
					}
                }
            } else {

                foreach ($this->printTheseArrayKeys as $toPrint) {

                    if (isset($row[$toPrint])) {
						if($toPrint == "offer_name") {
                            if (isset($row['idoffer']) && CurrentUserSession::permissions()->can('create_offers')) {
								echo "<td><a class='bp-report-link' href='/offer/edit/" . $row['idoffer'] . "'>$row[$toPrint]</a></td>";
                            } else {
								echo "<td>$row[$toPrint]</td>";
                            }
						} elseif ($toPrint == "Conversions" && $row[$toPrint] > 0 && (key_exists('idoffer', $row) && $row["idoffer"] != "TOTAL") ) {
                            if(CurrentUserSession::type() == Privilege::ROLE_AFFILIATE) {
                                $userId = CurrentUserSession::id();
                                echo "<td><a class='bp-report-link' href='/user/{$userId}/{$row['idoffer']}/conversions-by-country?{$params}'>$row[$toPrint]</a></td>";
                            } else {
                                echo "<td><a class='bp-report-link' href='/report/offer/{$row['idoffer']}/user-conversions?{$params}'>$row[$toPrint]</a></td>";
                            }
						} elseif($toPrint == "Conversions" && $row[$toPrint] > 0 && (key_exists('idrep', $row) && $row[$toPrint] != "TOTAL")) {
							if ( $role == 2 ) {
								echo "<td><a class='bp-report-link' href='/report/manager/{$row['idrep']}/conversions-by-offer?{$params}'>$row[$toPrint]</a></td>";
							} else {
								echo "<td><a class='bp-report-link' href='/user/{$row['idrep']}/conversions-by-offer?{$params}'>$row[$toPrint]</a></td>";
							}
						} elseif ($toPrint == "Conversions" &&
						          $row[$toPrint] > 0 &&
						          (key_exists('type', $row) &&
						           $row['type'] == "advertiser" &&
						           $row[$toPrint] != "TOTAL")) {
							echo "<td><a class='bp-report-link' href='/report/advertiser/{$row['id']}/conversions-by-offer?{$params}'>$row[$toPrint]</a></td>";
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
