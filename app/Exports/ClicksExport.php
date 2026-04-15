<?php

namespace App\Exports;

use App\Click;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ClicksExport implements FromView
{
    protected $data;
    
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('exports.clicks', [
            'clicks' => $this->data,
        ]);
    }
}
