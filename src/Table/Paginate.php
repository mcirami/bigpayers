<?php

/**
 * Created by PhpStorm.
 * User: david
 * Date: 8/29/2016
 * Time: 4:11 AM
 */

namespace LeadMax\TrackYourStats\Table;

//paginate class by dave.

class Paginate
{


    public $current_page;
    public $items_per_page;
    public $items_total_count;
    public $col;
    public $order;

    public function __construct($items_per_page = 50, $items_total_count = 0)
    {

        $this->items_per_page = $items_per_page;
        $this->items_total_count = $items_total_count;
        $this->get_page();


    }

    public function printPageSelector($assign)
    {
        echo "
        
        <div class=\"pages\">
        <label for=\"page\">Page &nbsp;</label>
  
    
        <select onchange=\"window.location = '".htmlspecialchars(parse_url($_SERVER["REQUEST_URI"])["path"], ENT_QUOTES,
                "utf-8").$assign->buildAssignments(["page"])."&page='+getElementById('page').value + '';\" id=\"page\" name=\"page\" >
            ";

        for ($int = 1; $int <= $this->page_total(); $int++) {
            if ($int == $this->current_page) {
                echo "<option selected value=\"".$int."\">".$int."</option>";
            } else {
                echo "<option  value=\"".$int."\">".$int."</option>";
            }
        }

        echo "</select>  of <span class=\"total\">{$this->page_total()}</span>
  </div>

";
    }


    public function get_page()
    {

        if (isset($_GET['page']) && is_numeric($_GET['page'])) { // Already been determined.
            return $this->current_page = $_GET['page'];
        } else {
            return $this->current_page = 1;
        }


    }

    public function has_previous()
    {

        return $this->previous() >= 1 ? true : false;

    }

    public function next()
    {

        return $this->current_page + 1;
    }

    public function previous()
    {

        return $this->current_page - 1;

    }

    public function has_next()
    {

        return $this->next() <= $this->page_total() ? true : false;

    }

    public function page_total()
    {

        return ceil($this->items_total_count / $this->items_per_page);

    }

    public function offset()
    {

        return ($this->current_page - 1) * $this->items_per_page;

    }


}



