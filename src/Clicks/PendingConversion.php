<?php


namespace LeadMax\TrackYourStats\Clicks;


use LeadMax\TrackYourStats\Database\DatabaseConnection;

class PendingConversion
{
    public $id;
    public $click_id;
    public $payout;
    public $converted;
    public $timestamp;

    public function __construct()
    {

    }

    /**
     * Activates a pending conversion
     * @param $pendingConversionId
     * @return bool
     */
    public static function activate($pendingConversionId)
    {
        $pendingConversion = \App\PendingConversion::find($pendingConversionId);

        if (is_null($pendingConversion)) {
            return false;
        }

        $conversion = new Conversion();
        $conversion->click_id = $pendingConversion->click_id;
        $conversion->paid = $pendingConversion->payout;

        if ($conversion->registerSale()) {
            $pendingConversion->converted = 1;

            return $pendingConversion->save();
        } else {
            return false;
        }
    }

    public static function getPendingConversionIdFromConversionID($conversionId)
    {
        $conversion = \App\Conversion::find($conversionId);

        if (is_null($conversion)) {
            return false;
        }

        return self::getPendingConversionIdFromClickId($conversion->click_id);
    }

    public static function getPendingConversionIdFromClickId($click_id)
    {
        $pendingConversion = self::selectOneByClickIdQuery($click_id)->fetch(\PDO::FETCH_OBJ);
        if ($pendingConversion) {
            return $pendingConversion->id;
        } else {
            return false;
        }
    }

    public static function selectOneQuery($pendingConversion)
    {
        $db = DatabaseConnection::getInstance();
        $sql = "SELECT * FROM pending_conversions WHERE id =:id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":id", $pendingConversion);
        $prep->execute();

        return $prep;
    }

    public static function selectOneByClickIdQuery($click_id)
    {
        $db = DatabaseConnection::getInstance();
        $sql = "SELECT * FROM pending_conversions WHERE click_id = :click_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":click_id", $click_id);
        $prep->execute();

        return $prep;
    }

    private function checkAndSetOptionalFields()
    {
        if (!isset($this->timestamp)) {
            $this->timestamp = date("Y-m-d H:i:s");
        }

        if (!isset($this->converted)) {
            $this->converted = 0;
        }

    }

    public function register()
    {
        $this->checkAndSetOptionalFields();

        if (self::isClickIdAlreadyRegistered($this->click_id)) {
            return false;
        }

        return $this->insertPendingConversion();
    }


    private function insertPendingConversion()
    {
        $db = DatabaseConnection::getInstance();
        $sql = "INSERT INTO pending_conversions (click_id, payout, converted, timestamp) VALUES(:click_id, :payout, :converted, :timestamp)";
        $prep = $db->prepare($sql);
        $prep->bindParam(":click_id", $this->click_id);
        $prep->bindParam(":payout", $this->payout);
        $prep->bindParam(":converted", $this->converted);
        $prep->bindParam(":timestamp", $this->timestamp);

        return $prep->execute();
    }

    public static function isClickIdAlreadyRegistered($click_id)
    {
        return self::selectOneByClickIdQuery($click_id)->rowCount() > 0;
    }

}