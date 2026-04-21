<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use LeadMax\TrackYourStats\System\Company;

class SettingsController extends Controller
{
    private const COLOR_FIELDS = [
        ['key' => 'valueSpan1', 'label' => 'Header & selected nav', 'note' => 'Top header and selected navigation backgrounds.'],
        ['key' => 'valueSpan2', 'label' => 'Button text', 'note' => 'Sub-menu text, button text, and sidebar labels.'],
        ['key' => 'valueSpan3', 'label' => 'Navigation base', 'note' => 'Left navigation and subtitle accent text.'],
        ['key' => 'valueSpan11', 'label' => 'Primary button', 'note' => 'Main button background color.'],
        ['key' => 'valueSpan4', 'label' => 'Hover state', 'note' => 'Sub-menu hover, selected states, and button hover.'],
        ['key' => 'valueSpan5', 'label' => 'Link text', 'note' => 'Navigation text and linked interface text.'],
        ['key' => 'valueSpan6', 'label' => 'Accent fill', 'note' => 'Menu hover and selected accent fill.'],
        ['key' => 'valueSpan7', 'label' => 'App backdrop', 'note' => 'Main shell and content-sub-box background tone.'],
        ['key' => 'valueSpan8', 'label' => 'Card background', 'note' => 'Primary content panel background color.'],
        ['key' => 'valueSpan9', 'label' => 'Title text', 'note' => 'Primary content heading and label text.'],
        ['key' => 'valueSpan10', 'label' => 'Body text', 'note' => 'Secondary content and helper copy.'],
    ];

    public function show()
    {
        return view('settings.index', $this->buildViewData());
    }

    public function update(Request $request)
    {
        $company = Company::loadFromSession();

        $validated = $request->validate([
            'shortHand' => 'required|string|max:255',
            'telegram' => 'nullable|string|max:255',
            'skype' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'loginURL' => 'nullable|string|max:255',
            'landingPage' => 'nullable|string|max:255',
            'logo' => 'nullable|file|mimes:png|max:16384',
            'favicon' => 'nullable|file|max:16384',
        ] + $this->colorValidationRules());

        if ($request->hasFile('favicon')) {
            $extension = strtolower((string) $request->file('favicon')->getClientOriginalExtension());
            if ($extension !== 'ico') {
                throw ValidationException::withMessages([
                    'favicon' => 'The favicon must be an .ico file.',
                ]);
            }
        }

        $colorString = implode(';', array_map(
            fn (array $field) => strtoupper(ltrim((string) $validated[$field['key']], '#')),
            self::COLOR_FIELDS
        ));

        $updated = $company->updateCompany(
            $validated['shortHand'],
            $colorString,
            $validated['email'] ?? '',
            $validated['telegram'] ?? $validated['skype'] ?? '',
            $validated['loginURL'] ?? '',
            $validated['landingPage'] ?? ''
        );

        if (!$updated) {
            throw ValidationException::withMessages([
                'shortHand' => 'Unable to save company settings right now. Please try again.',
            ]);
        }

        $this->storeBrandAsset($request, 'logo', 'logo.png');
        $this->storeBrandAsset($request, 'favicon', 'favicon.ico');

        return redirect('/settings')->with('message', 'Settings updated successfully.');
    }

    private function buildViewData(): array
    {
        $company = Company::loadFromSession();
        $colors = $company->getColors();
        $subDomain = Company::getCustomSub();
        $logoPath = public_path("images/{$subDomain}/logo.png");
        $faviconPath = public_path("images/{$subDomain}/favicon.ico");

        $colorFields = array_map(function (array $field, int $index) use ($colors) {
            $raw = $colors[$this->getColorIndexForField($field['key'])] ?? '000000';
            $clean = strtoupper($this->normalizeHex($raw));

            return $field + [
                'value' => $clean,
                'pickerValue' => '#' . $clean,
            ];
        }, self::COLOR_FIELDS, array_keys(self::COLOR_FIELDS));

        return [
            'pageTitle' => 'Settings',
            'colorFields' => $colorFields,
            'settingsValues' => [
                'shortHand' => old('shortHand', $company->getShortHand()),
                'telegram' => old('telegram', old('skype', $company->getSkype())),
                'email' => old('email', $company->getEmail()),
                'loginURL' => old('loginURL', $company->getLoginURL()),
                'landingPage' => old('landingPage', $company->getLandingPage()),
            ],
            'logoUrl' => file_exists($logoPath) ? "/images/{$subDomain}/logo.png?v=" . filemtime($logoPath) : null,
            'faviconUrl' => file_exists($faviconPath) ? "/images/{$subDomain}/favicon.ico?v=" . filemtime($faviconPath) : null,
            'subDomain' => $subDomain,
        ];
    }

    private function colorValidationRules(): array
    {
        $rules = [];

        foreach (self::COLOR_FIELDS as $field) {
            $rules[$field['key']] = ['required', 'regex:/^[A-Fa-f0-9]{6}$/'];
        }

        return $rules;
    }

    private function getColorIndexForField(string $field): int
    {
        return match ($field) {
            'valueSpan1' => 0,
            'valueSpan2' => 1,
            'valueSpan3' => 2,
            'valueSpan4' => 3,
            'valueSpan5' => 4,
            'valueSpan6' => 5,
            'valueSpan7' => 6,
            'valueSpan8' => 7,
            'valueSpan9' => 8,
            'valueSpan10' => 9,
            'valueSpan11' => 10,
        };
    }

    private function normalizeHex(string $value): string
    {
        $candidate = strtoupper(preg_replace('/[^A-F0-9]/i', '', ltrim($value, '#')));

        if (strlen($candidate) === 3) {
            return $candidate[0] . $candidate[0] . $candidate[1] . $candidate[1] . $candidate[2] . $candidate[2];
        }

        if (strlen($candidate) !== 6) {
            return '000000';
        }

        return $candidate;
    }

    private function storeBrandAsset(Request $request, string $field, string $filename): void
    {
        if (!$request->hasFile($field)) {
            return;
        }

        $directory = public_path('images/' . Company::getCustomSub());
        File::ensureDirectoryExists($directory);

        $request->file($field)->move($directory, $filename);
    }
}
