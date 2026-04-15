<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
class CountryClicksExport implements FromView {
	protected $data;

	public function __construct($data)
	{
		$this->data = $data;
	}

	public function view(): View
	{
		return view('exports.country-clicks', [
			'countryClicks' => $this->data,
		]);
	}
}