<?php

namespace App\Http\Controllers;

use App\Company;

class CompanyCssController extends Controller
{
    public function __invoke()
    {
        $css = $this->buildCss($this->normalizedColors($this->currentCompanyColors()));

        return response($css, 200)
            ->header('Content-Type', 'text/css; charset=UTF-8');
    }

    private function currentCompanyColors(): array
    {
        $company = Company::instance()->first();

        return $company ? $company->colors() : [];
    }

    private function normalizedColors(array $colors): array
    {
        for ($i = 0; $i < 11; $i++) {
            $colors[$i] = $this->hexColor($colors[$i] ?? null);
        }

        return $colors;
    }

    private function hexColor($value): string
    {
        $candidate = preg_replace('/[^a-fA-F0-9]/', '', (string) $value);

        if (strlen($candidate) === 3) {
            $candidate = $candidate[0] . $candidate[0] . $candidate[1] . $candidate[1] . $candidate[2] . $candidate[2];
        }

        return strlen($candidate) === 6 ? strtoupper($candidate) : '000000';
    }

    private function buildCss(array $colors): string
    {
        [
            $valueSpan1,
            $valueSpan2,
            $valueSpan3,
            $valueSpan4,
            $valueSpan5,
            $valueSpan6,
            $valueSpan7,
            $valueSpan8,
            $valueSpan9,
            $valueSpan10,
            $valueSpan11,
        ] = $colors;

        return <<<CSS
.value_span1 {
background-color: #{$valueSpan1};
}

.value_span1-2:hover {
background: #{$valueSpan1} !important;
}

.value_span2 {
color: #{$valueSpan2}!important;
}

.value_span2-2:hover {
color: #{$valueSpan2} ;
}

.value_span2-3:hover {
border: 2px solid #{$valueSpan2} !important;
}
.value_span3 {
background: #{$valueSpan3} ;
}
.value_span3-1 {
background: #{$valueSpan3} ;
}
.value_span3-1:hover {
background: #{$valueSpan1} ;
}

.value_span4:hover, .value_span4.active {
background:  #{$valueSpan4} ;
color: #fff;
}

.value_span4.active:hover {
background: #{$valueSpan4};
color: #fff;
}

.value_span4-1, .value_span4-2 {
background: #{$valueSpan4};
}

.value_span4-1:hover {
background: #{$valueSpan3};
}

.value_span5 {
color: #{$valueSpan5} ;
}

.value_span5-1 {
  background: #{$valueSpan5} ;
}

.value_span6:hover {
  color: #{$valueSpan6} ;
}

.value_span6-1 {
  background: #{$valueSpan6} ;
}

.value_span6-2 {
background: #{$valueSpan6} !important;
}

.value_span6-3:hover a {
  color: #{$valueSpan6} ;
}

.value_span6-4:before {
  border-bottom: 12px solid #{$valueSpan6} !important;
}

.value_span6-5:hover {
  background: #{$valueSpan6} ;
}
.value_span7 {
  background: #{$valueSpan7} ;
}

.tr_row_space {
border-bottom: 3em solid #{$valueSpan7};
}

.value_span8 {
background: #{$valueSpan8} ;
}

.value_span9 {
color: #{$valueSpan9} ;
}

.value_span10 {
color: #999999;
}

.value_span11 {
  background: #{$valueSpan11};
}
CSS;
    }
}
