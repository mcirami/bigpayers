<?php
/**
 * Created by PhpStorm.
 * User: dean
 * Date: 7/25/2017
 * Time: 11:58 AM
 */

namespace LeadMax\TrackYourStats\Table;


//Clean up assignments on report pages, tables, etc. currently, there's way too much clutter and bullshit on the paginate assignments, bot and so on. Needs major clean up
//Start with having a page create an Assignments object, passing an assoc array with what assignments they want to $_GET. their value in the array will be the default value if no $_GET was assigned to that var
//Vars passed with an '!' at the beginning are considered a required variable and will redirect if not set


/*  $today = date("y-m-d");
 *  EX: $assignHandler = new \LeadMax\TrackYourStats\Table\Assignments(
 *                  [
 *                      'd_from' =>  $today,
 *                      'rrp' => 10,
 *                      '!affid' => -1
 *
 *
 *                  ]);
 *
 *
 *
 * since 'affid' has a !, if it is not set, we redirect to $redirectURL
 *
 */

class Assignments
{

    protected $assignments = array();

    protected $required = array();

    public $redirectURL = "home.php";

    public $cleanData = false;

    function __construct($assignedVars, $customRedirect = false, $cleanData = true)
    {
        $this->assignments = $assignedVars;
        if ($customRedirect) {
            $this->redirectURL = $customRedirect;
        }
        $this->findRequired();

        $this->cleanData = $cleanData;

    }

    public function clean()
    {
        foreach ($this->assignments as $key => $val) {
            if (isset($_GET[$key])) {
                $this->assignments[$key] = xss_clean($_GET[$key]);
            }
        }
    }

    //INPUT: (Optional) Numeric array with values set to keys you want to ignore when building
    //OUTPUT: builds JSON Encoded array for javascript. Ignores values passed Ex: buildJSONArray(["var1", "var2"]);
    public function buildJSONArray($ignore = false)
    {
        $temp = $this->assignments;

        if ($ignore) {
            $temp = $this->removeKeys($temp, $ignore);
        }

        return json_encode($temp);
    }

    public function setGlobals()
    {
        foreach ($this->assignments as $key => $val) {
            Global ${$key};
            ${$key} = $val;
        }
    }


    public function __get($varName)
    {

        if (!isset($this->assignments[$varName])) {
            //this attribute is not defined!
            throw new \Exception('Undefined variable '.$varName);
        } else {
            return $this->assignments[$varName];
        }

    }

    public function __set($varName, $value)
    {
        $this->assignments[$varName] = $value;
    }

    public function set($varName, $value)
    {
        $this->assignments[$varName] = $value;
    }

    public function getAssignments()
    {

        $this->checkRequired();


        foreach ($this->assignments as $key => $val) {
            if (isset($_GET[$key])) {
                $this->assignments[$key] = $_GET[$key];
            }
        }

        if ($this->cleanData) {
            $this->clean();
        }


    }

    public function has($keyName)
    {
        $temp = $this->assignments;
        foreach ($temp as $key => $val) {
            if ($key == $keyName) {
                return true;
            }
        }

        return false;
    }

    public function get($keyName)
    {
        $temp = $this->assignments;

        return $temp[$keyName];
    }


    // INPUT: $array = the array of which you want to remove keys from, $keys = the keys you want to remove from the former
    // OUTPUT: $array without the keys from $keys
    private function removeKeys($array, $keys)
    {
        foreach ($keys as $key => $val) {
            unset($array[$val]);
        }

        return $array;
    }


    //INPUT: (Optional) Numeric array with values set to keys you want to ignore when building
    //OUTPUT: builds php query ?arg1=432, etc. Ignores values passed Ex: buildAssignments(["var1", "var2"]);
    public function buildAssignments($ignore = false)
    {
        $temp = $this->assignments;

        if ($ignore) {
            $temp = $this->removeKeys($temp, $ignore);
        }


        $url = "?";
        $initial = true;

        foreach ($temp as $key => $val) {
            if ($initial) {
                $url .= $key."=".$val;
                $initial = false;
            } else {
                $url .= "&".$key."=".$val;
            }
        }

        if (isset($_GET["adminLogin"])) {
            $url .= "&adminLogin";
        }

        return $url;


    }


    public function getRedirectURL()
    {
        return $this->redirectURL;
    }

    public function setRedirect($url)
    {
        $this->redirectURL = $url;
    }

    public function redirect()
    {
        send_to($this->redirectURL);
    }


    private function findRequired()
    {
        //finds them
        foreach ($this->assignments as $key => $val) {
            $firstChar = substr($key, 0, 1);

            if ($firstChar == "!") {
                //add to required array
                $this->required[substr($key, 1, strlen($key))] = "!";

                //rename so doesn't have "!"
                $this->assignments[substr($key, 1, strlen($key))] = "!";
                //remove old one (with '!')
                unset($this->assignments[$key]);

            }


        }
    }

    private function checkRequired()
    {
        foreach ($this->required as $key => $val) {
            if (!isset($_GET[$key])) {
                $this->redirect();
            } else {
                if ($_GET[$key] == null) {
                    $this->redirect();
                }
            }
        }
    }


}