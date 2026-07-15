<?php

namespace App\Support;

class PaginationHelper
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

    public function printPageSelector($assign): void
    {
        $path = htmlspecialchars(parse_url(NativeRequest::requestUri())['path'], ENT_QUOTES, 'utf-8');
        $url = $path . $assign->buildAssignments(['page']);

        echo '<div class="pages">';
        echo '<label for="page">Page &nbsp;</label>';
        echo '<select onchange="window.location = \'' . $url . '&page=\' + getElementById(\'page\').value" id="page" name="page">';

        for ($page = 1; $page <= $this->page_total(); $page++) {
            $selected = $page == $this->current_page ? ' selected' : '';
            echo '<option' . $selected . ' value="' . $page . '">' . $page . '</option>';
        }

        echo '</select> of <span class="total">' . $this->page_total() . '</span>';
        echo '</div>';
    }

    public function get_page()
    {
        $page = NativeRequest::query('page');

        return $this->current_page = $page !== null && is_numeric($page) ? $page : 1;
    }

    public function has_previous(): bool
    {
        return $this->previous() >= 1;
    }

    public function next()
    {
        return $this->current_page + 1;
    }

    public function previous()
    {
        return $this->current_page - 1;
    }

    public function has_next(): bool
    {
        return $this->next() <= $this->page_total();
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
