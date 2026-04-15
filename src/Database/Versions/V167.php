<?php

namespace LeadMax\TrackYourStats\Database\Versions;


use function Couchbase\defaultDecoder;
use LeadMax\TrackYourStats\Database\Version;

class V167 extends Version
{

    public function getVersion()
    {
        return 1.67;
    }

    public function update()
    {
        $affiliates = $this->getAffiliatePermissions()->fetchAll(\PDO::FETCH_ASSOC);
        $question_marks = rtrim(str_repeat('?,', count($affiliates)), ','); // Create n question marks
        $sql = "UPDATE permissions SET sale_logs = 1 WHERE aff_id IN ($question_marks)";
        $stmt = $this->getDB()->prepare($sql);

        return $stmt->execute(array_column($affiliates,
            'user_id')); // get values from array for 'user_id', execute and return
    }

    public function getAffiliatePermissions()
    {
        $db = $this->getDB();
        $sql = "
                  SELECT
                    p.rep_idrep as user_id,

                    permissions.sale_logs
                  FROM
                    permissions

                    INNER JOIN privileges AS p on p.rep_idrep = permissions.aff_id
                    INNER JOIN rep_has_offer ON rep_has_offer.rep_idrep = permissions.aff_id
                    LEFT JOIN offer ON offer.idoffer = rep_has_offer.offer_idoffer

                  WHERE p.is_rep = 1
                        AND permissions.sale_logs != 1
                        AND offer.offer_type = 3";

        $prep = $db->prepare($sql);
        $prep->execute();

        return $prep;
    }


    public function verifyUpdate(): bool
    {
        return $this->getAffiliatePermissions()->rowCount() == 0;
    }

}