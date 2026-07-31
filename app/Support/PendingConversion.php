<?php

namespace App\Support;

class PendingConversion
{
    public $id;
    public $click_id;
    public $payout;
    public $converted;
    public $timestamp;

    public static function activate($pendingConversionId)
    {
        $pendingConversion = \App\PendingConversion::find($pendingConversionId);

        if ($pendingConversion === null) {
            return false;
        }

        $conversion = new LegacyConversion();
        $conversion->click_id = $pendingConversion->click_id;
        $conversion->paid = $pendingConversion->payout;

        if (! $conversion->registerSale()) {
            return false;
        }

        $pendingConversion->converted = 1;

        return $pendingConversion->save();
    }

    public static function getPendingConversionIdFromConversionID($conversionId)
    {
        $conversion = \App\Conversion::find($conversionId);

        if ($conversion === null) {
            return false;
        }

        return self::getPendingConversionIdFromClickId($conversion->click_id);
    }

    public static function getPendingConversionIdFromClickId($clickId)
    {
        $pendingConversion = self::selectOneByClickIdQuery($clickId)->fetch(\PDO::FETCH_OBJ);

        return $pendingConversion ? $pendingConversion->id : false;
    }

    public static function selectOneQuery($pendingConversion)
    {
        $database = DatabaseConnection::getInstance();
        $statement = $database->prepare('SELECT * FROM pending_conversions WHERE id = :id');
        $statement->bindParam(':id', $pendingConversion);
        $statement->execute();

        return $statement;
    }

    public static function selectOneByClickIdQuery($clickId)
    {
        $database = DatabaseConnection::getInstance();
        $statement = $database->prepare('SELECT * FROM pending_conversions WHERE click_id = :click_id');
        $statement->bindParam(':click_id', $clickId);
        $statement->execute();

        return $statement;
    }

    public function register()
    {
        $this->setOptionalFields();

        if (self::isClickIdAlreadyRegistered($this->click_id)) {
            return false;
        }

        $database = DatabaseConnection::getInstance();
        $statement = $database->prepare(
            'INSERT INTO pending_conversions (click_id, payout, converted, timestamp)
             VALUES (:click_id, :payout, :converted, :timestamp)'
        );
        $statement->bindParam(':click_id', $this->click_id);
        $statement->bindParam(':payout', $this->payout);
        $statement->bindParam(':converted', $this->converted);
        $statement->bindParam(':timestamp', $this->timestamp);

        return $statement->execute();
    }

    public static function isClickIdAlreadyRegistered($clickId): bool
    {
        return self::selectOneByClickIdQuery($clickId)->rowCount() > 0;
    }

    private function setOptionalFields(): void
    {
        if (! isset($this->timestamp)) {
            $this->timestamp = date('Y-m-d H:i:s');
        }

        if (! isset($this->converted)) {
            $this->converted = 0;
        }
    }
}
