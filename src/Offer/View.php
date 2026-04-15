<?php
/**
 * Created by PhpStorm.
 * User: dean
 * Date: 7/24/2017
 * Time: 4:03 PM
 */

namespace LeadMax\TrackYourStats\Offer;

use App\Conversion;
use App\Privilege;
use Carbon\Carbon;
use LeadMax\TrackYourStats\System\Company;
use LeadMax\TrackYourStats\System\Session;
use LeadMax\TrackYourStats\Table\Date;
use LeadMax\TrackYourStats\Table\Paginate;
use \LeadMax\TrackYourStats\User\User;


class View
{
    public $userType = -1;

    public $result = array();

    public $url;

    public $urls;

    public $assignments;

    function __construct($userType, $assignments = false)
    {
        $this->userType = $userType;
        $this->assignments = $assignments;
    }

    function printOfferUrls()
    {
        $this->printSelectBoxScript();


        if (isset($_GET["url"])) {
            $url = $_GET["url"];

        }


        echo "<div style=\"margin:0 0 1px 0; padding:5px;\">";

        if ($this->userType == \App\Privilege::ROLE_AFFILIATE) {

            echo "<label class=\"value_span9\">Offer URLS: </label>
                      <select  onchange='handleSelect(this);'  class=\"form - control input - sm \" id=\"offer_url\" name=\"offer_url\">";

            $this->urls = Company::getOfferUrls();

            if (count($this->urls) == 0) {
                array_push($this->urls, array($_SERVER["HTTP_HOST"]));
            }

            if (!isset($this->url)) {
                $this->url = 0;
            }

            for ($i = 0; $i < count($this->urls); $i++) {

                if ($this->url == $i) {
                    echo "<option  selected value='{$i}'> {$this->urls[$i][0]} </option>";
                } else {
                    echo "<option  value='{$i}'> {$this->urls[$i][0]} </option>";
                }
            }

            echo "</select>";

        }
        echo "</div>";

    }

    function printHeaders()
    {
        if ($this->userType == \App\Privilege::ROLE_AFFILIATE) {
            echo " <th class=\"value_span9\">Postback Options</th>";
        }


        if ($this->userType != Privilege::ROLE_AFFILIATE) {
            echo "<th class=\"value_span9\">Offer Timestamp</th>
                 ";
        }

        echo "<th class=\"value_span9\" >Actions</th>";
    }

    public function printToSelectBox($customID = "offerSelectBox", $selectRedirectOffer = false, $customHTML = false)
    {
        $result = $this->getUsersQuery(false, false, 1)->fetchAll(\PDO::FETCH_OBJ);

        if ($customHTML) {
            echo "<select {$customHTML} class=\"selectBox \"  id=\"{$customID}\" name=\"{$customID}\">";
        } else {
            echo "<select class=\"selectBox \"  id=\"{$customID}\" name=\"{$customID}\">";
        }

        foreach ($result as $row) {
            if ($selectRedirectOffer == $row->idoffer) {
                echo "<option selected value={$row->idoffer} id={$row->idoffer}>{$row->offer_name}</option>";
            } else {
                echo "<option value={$row->idoffer} id={$row->idoffer}>{$row->offer_name}</option>";
            }

        }

        echo "</select>";

    }

    public function printReport()
    {


        if ($this->userType == \App\Privilege::ROLE_AFFILIATE) {
            $salesWeek = Date::getSalesWeek();
            $sales = Conversion::where('user_id', '=', Session::userID())->whereBetween('timestamp',
                [$salesWeek['start'], $salesWeek['end']])->count();
        }


        //Loop through

        foreach ($this->result as $rows) {
            //checks to see if offer is active, is so, set $print as active, else, de-active html
            if ($rows->status == 1) {
                $print = "Active";
            } else {
                $print = "Deactive";
            }

            if ($this->userType === \App\Privilege::ROLE_AFFILIATE) {
                if ($rows->required_sales > $sales) {
                    continue;
                }
            }

//            if ($this->userType == \App\Privilege::ROLE_GOD) {
//                if ($rows->status == 1)
//                    //offer_update.php?changeOfferStatus
//                    $print = "<a class=\"active\" href=\"javascript:void(0);\"><span>&nbsp;</span>Active</a>";
//                else
//                    $print = "<a class=\"deactive\" href=\"javascript:void(0);\"><span>&nbsp;</span>Deactive</a>";
//
//            }


            echo " <tr>
                
                    <td class=\"value_span10\">{$rows->idoffer}</td>
                    <td class=\"value_span10\">".ucwords($rows->offer_name)."</td>
                    <td class=\"value_span10\">".Offer::offerTypeAsString($rows->offer_type)."</td>
                   ";
            if ($this->userType == Privilege::ROLE_AFFILIATE) {
                echo "<p style='display:none;' id=\"url_{$rows->idoffer}\">http://{$this->urls[$this->url][0]}/?repid=".Session::userID()."&offerid={$rows->idoffer}&sub1=</p>";

                echo "<td class=\"value_span10\">
                        <button data-toggle=\"tooltip\" title=\"Copy Offer URL\" onclick=\"copyToClipboard(getElementById('url_{$rows->idoffer}'));\" class=\"btn btn-default\">
                            Copy Offer URL
                        </button>
                      </td> ";
            }

            if (Session::permissions()->can("create_offers")) {
                echo "<td class=\"value_span10\">
							<a target='_blank' class='btn btn-sm btn-default' href='offer_access.php?id={$rows->idoffer}'>Affiliate Access</a>
						</td>";
            }

            if (Session::userType() !== \App\Privilege::ROLE_MANAGER) {
                echo "<td class=\"value_span10\">$ {$rows->payout}</td>";
            }


            echo "

                    

                    <td class=\"value_span10\">
                       {$print}
                    </td>

                    ";

            if ($this->userType == \App\Privilege::ROLE_AFFILIATE) {
                echo "<td class=\"value_span10\"><a class='btn btn-default' data-toggle=\"tooltip\" title=\"Offer PostBack Options\" href=\"offer_edit_pb.php?offid={$rows->idoffer}\">Edit Post Back</a></td>";


            }


            if ($this->userType != \App\Privilege::ROLE_AFFILIATE) {
                $formatedTimestamp = Carbon::createFromFormat('Y-m-d H:i:s',
                    $rows->offer_timestamp)->toFormattedDateString();
                echo "<td class=\"value_span10\">{$formatedTimestamp} </td>";
            }
            $per = Session::permissions();

            if ($this->userType != Privilege::ROLE_AFFILIATE && $this->userType != \App\Privilege::ROLE_UNKNOWN) {
                if ($per->can("create_offers")) {
                    echo "  <td class=\"value_span10\" >
                                                         <a class=\"btn btn-default btn-sm\"  data-toggle=\"tooltip\" title=\"Edit Offer\" href=\"offer_update.php?idoffer=".$rows->idoffer."\">Edit</a>
                                                      
                                                 </td>";
                }

                if ($per->can("edit_offer_rules")) {
                    echo " <td class=\"value_span10\" >
                                                         <a class=\"btn btn-default btn-sm\" data-toggle=\"tooltip\" title=\"Edit Offer Rules\"  href=\"offer_edit_rules.php?offid=".$rows->idoffer."\"> Rules</a>
                                                      
                                           </td>    ";
                }

                echo " <td class=\"value_span10\" >
                                                         <a class=\"btn btn-default btn-sm\" data-toggle=\"tooltip\" title=\"View Offer\"  href=\"offer_details.php?idoffer=".$rows->idoffer."\"> View</a>
                                                    
                                              </td>     </td>";

                if (Session::userType() == \App\Privilege::ROLE_GOD) {
                    echo " <td class=\"value_span10\" >
                                                         <a class=\"btn btn-default btn-sm\" data-toggle=\"tooltip\" title=\"Duplicate Offer\"  href=\"offer_view.php?idoffer=".$rows->idoffer."&dupe=1\"> Duplicate </a>
                                                    
                                              </td>     </td>";

                    echo " <td class=\"value_span10\" >
                             <a class=\"btn btn-default btn-sm\" data-toggle=\"tooltip\" title=\"Delete Offer\" onclick=\"confirmSendTo('Are you sure you want to delete this offer?', 'offer_view.php?idoffer=".$rows->idoffer."&delete=1');\"  href=\"javascript:void(0);\"> Delete </a>
                                                    
                                              </td>     </td>";

                }


            } else {
                echo "<td></td>";
            }


            echo "</tr>";
        }


    }


    public function printRequestableOffers()
    {

        if ($this->userType == \App\Privilege::ROLE_AFFILIATE) {
            $result = $this->querySelectRequestableOffers(Session::userID())->fetchAll(\PDO::FETCH_OBJ);
        } else {
            return;
        }
//        else
//            $result = $this->querySelectRequestableOffers()->fetchAll(\PDO::FETCH_OBJ);


        foreach ($result as $offer) {
            echo "<tr>";
            echo "<td>{$offer->idoffer}</td>";
            echo "<td>{$offer->offer_name}</td>";
            echo "<td>Requires Offer</td>";
            echo "<td>$ {$offer->payout}</td>";
            echo "<td>{$offer->status}</td>";
            echo "<td>Requires Offer</td>";

            echo "<td><button id='btn_{$offer->idoffer}' class='btn btn-sm btn-default'  onclick='requestOffer({$offer->idoffer})'>Request Offer</button></td>";


            echo "</tr>";
        }

    }


    public function fetchResult($active)
    {
        $paginate = new Paginate($this->assignments->get("rpp"), $this->getUsersQuery()->rowCount());

        $this->result = $this->getUsersQuery($paginate->items_per_page, $paginate->offset(),
            $active)->fetchAll(\PDO::FETCH_OBJ);
    }


    // Returns proper query for the user type
    public function getUsersQuery($items_per_page = false, $offset = false, $active = 1)
    {
        switch ($this->userType) {
            case \App\Privilege::ROLE_GOD:
                return $this->queryGod($items_per_page, $offset, $active);
                break;

            case \App\Privilege::ROLE_ADMIN:
                return $this->queryGod($items_per_page, $offset, $active);
                break;

            case \App\Privilege::ROLE_MANAGER:
                return $this->queryManager($items_per_page, $offset, $active);
                break;

            case \App\Privilege::ROLE_AFFILIATE:
                return $this->queryAff($items_per_page, $offset, $active);
                break;

        }
    }


    function queryManager($items_per_page = false, $offset = false, $ACTIVE = 1)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();


        $sql = "SELECT
                        offer.idoffer,
                        offer.offer_name,
                        offer.offer_type,
                        offer.description,
                        offer.url,
                        offer.payout,
                        offer.status,
                        offer.offer_timestamp
                    FROM
                        offer
                    LEFT JOIN rep
                    ON
                    rep.lft > :left AND rep.rgt < :right
                    LEFT JOIN
                        rep_has_offer
                    ON
                       rep_has_offer.rep_idrep = rep.idrep
                       
                  
                  WHERE offer.created_by = :repID OR  rep_has_offer.offer_idoffer = offer.idoffer
                       
                        ";


        if ($ACTIVE == 1) {
            $sql .= " AND offer.status = 1 ";
        } else {
            $sql .= " AND offer.status = 0 ";
        }


        $sql .= " GROUP BY idoffer  ORDER BY idoffer DESC  ";

        if ($items_per_page !== false && $offset !== false) {
            $sql .= "LIMIT $items_per_page ";
            $sql .= "OFFSET {$offset}";
        }


        $stmt = $db->prepare($sql);

        $userID = Session::userID();
        $userData = User::SelectOne(Session::userID());


        $stmt->bindParam(':left', $userData->lft);
        $stmt->bindParam(':right', $userData->rgt);
        $stmt->bindParam(':repID', $userID);

        $stmt->execute();


        return $stmt;
    }


    function queryGod($items_per_page = false, $offset = false, $ACTIVE = 1)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();


        $sql = "SELECT
                            offer.idoffer,
                            offer.offer_name,
                            offer.offer_type,
                            offer.description,
                            offer.url,
                            offer.payout,
                            offer.status,
                            offer.offer_timestamp
                        FROM
                            offer ";

        if ($ACTIVE == 1) {
            $sql .= " WHERE offer.status = 1 ";
        } else {
            $sql .= "WHERE offer.status = 0 ";
        }


        $sql .= " ORDER BY idoffer DESC ";

        if ($items_per_page !== false && $offset !== false) {
            $sql .= "LIMIT $items_per_page ";
            $sql .= "OFFSET {$offset}";
        }



        $stmt = $db->prepare($sql);

        $user = new User();
        $userData = User::SelectOne(Session::userID());


        /*
         * TODO: DO I NEED THIS NONSENSE?
         * $stmt->bindParam(':left', $userData->lft);
        $stmt->bindParam(':right', $userData->rgt);*/

        $stmt->execute();

        return $stmt;
    }


    function queryAff($items_per_page = false, $offset = false, $ACTIVE = 1)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();


        $sql = "SELECT
                        offer.idoffer,
                        offer.offer_name,
                        offer.offer_type,
                        offer.description,
                        offer.url,
                        rep_has_offer.payout,
                        offer.status,
                        rep_has_offer.postback_url,
                        bonus_offers.required_sales
                    FROM
                        offer
                    INNER JOIN
                        rep_has_offer
                    ON
                       rep_has_offer.rep_idrep = :repid AND  rep_has_offer.offer_idoffer = offer.idoffer 
                      
                    LEFT JOIN 
                        bonus_offers 
                      ON 
                        bonus_offers.offer_id = offer.idoffer
                        ";


        if ($ACTIVE == 1) {
            $sql .= " WHERE status = 1 ";
        } else {
            $sql .= "WHERE status = 0 ";
        }


        $sql .= " ORDER BY idoffer DESC ";

        if ($items_per_page !== false && $offset !== false) {
            $sql .= "LIMIT $items_per_page ";
            $sql .= "OFFSET {$offset}";
        }


        $stmt = $db->prepare($sql);
        $userID = Session::userID();
        $stmt->bindParam(':repid', $userID);

        $stmt->execute();


        return $stmt;
    }


    private function querySelectRequestableOffers($user_id = false)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();


        $visibility = Offer::VISIBILITY_REQUESTABLE;

        $sql = "SELECT idoffer, offer_name, url, payout, status, offer_timestamp FROM offer WHERE offer.is_public = {$visibility}";

        if ($user_id) {
            $sql .= " AND offer.idoffer NOT IN (SELECT offer_idoffer FROM rep_has_offer WHERE rep_has_offer.rep_idrep = :user_id)";
        }

        $prep = $db->prepare($sql);

        if ($user_id) {
            $prep->bindParam(":user_id", $user_id);
        }

        $prep->execute();

        return $prep;
    }

    private function printSelectBoxScript()
    {
        echo "<script type=\"text/javascript\">
                    function handleSelect(elm)
                    {
                         window.location = '{$_SERVER['PHP_SELF']}?url='+ elm.value;
                    }
               </script>";
    }


}
