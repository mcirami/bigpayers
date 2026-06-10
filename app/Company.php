<?php

namespace App;

use App\Support\NativeSession;
use Throwable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

/**
 * App\Company
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $shortHand
 * @property string $subDomain
 * @property string $companyName
 * @property string $city
 * @property string $state
 * @property string $address
 * @property string $zip
 * @property string $telephone
 * @property string $email
 * @property string $skype
 * @property string|null $messenger_type
 * @property string|null $messenger_username
 * @property string $colors
 * @property string $uid
 * @property float $db_version
 * @property string $login_url
 * @property string $landing_page
 * @property string $login_theme
 * @property bool $allow_register
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Company whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Company whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Company whereColors($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Company whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Company whereDbVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Company whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Company whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Company whereLandingPage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Company whereLoginUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Company whereShortHand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Company whereSkype($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Company whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Company whereSubDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Company whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Company whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Company whereZip($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\OfferURL[] $offerUrls
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Company instance()
 */
class Company extends Model
{
    protected $table = 'company';

    protected $connection = 'master';

    public $timestamps = false;

    public static function getInstance(): Company
    {
        return static::where('subDomain', static::currentSubDomain())->first();
    }

    // This stuff should really be refactored but whatever
    public function scopeInstance(Builder $query)
    {
        return $query->where('subDomain', static::currentSubDomain());
    }

    public static function currentSubDomain(): string
    {
        return (string) NativeSession::get('COMPANY_SUBDOMAIN', env('DB_DATABASE'));
    }

    public function offerUrls()
    {
        return $this->hasMany(OfferURL::class);
    }

    public function isCompanyOfferUrl(string $url): bool
    {
        $normalizedUrl = $this->normalizeHost($url);

        return $this->offerUrls()
            ->where('status', 1)
            ->pluck('url')
            ->contains(fn ($offerUrl) => $this->normalizeHost((string) $offerUrl) === $normalizedUrl);
    }

    public function colors(): array
    {
        return explode(';', (string) $this->colors);
    }

    public function getColors(): array
    {
        return $this->colors();
    }

    public function getShortHand(): string
    {
        return (string) $this->shortHand;
    }

    public function getImgDir(): string
    {
        return 'images/' . $this->subDomain;
    }

    public function getSubDomain(): string
    {
        return (string) $this->subDomain;
    }

    public function getID(): int
    {
        return (int) $this->id;
    }

    public function getBrandAssetUrl(string $filename): string
    {
        $relativePath = trim($this->getImgDir(), '/\\') . '/' . ltrim($filename, '/\\');
        $fullPath = public_path($relativePath);

        if (file_exists($fullPath)) {
            return '/' . $relativePath . '?v=' . filemtime($fullPath);
        }

        return '/' . $relativePath;
    }

    public function loginTheme(): ?string
    {
        $savedTheme = trim((string) ($this->login_theme ?? ''));

        if ($savedTheme !== '' && File::exists(public_path("login_themes/{$savedTheme}/theme.css"))) {
            return $savedTheme;
        }

        if (File::exists(public_path('login_themes/command-center/theme.css'))) {
            return 'command-center';
        }

        return null;
    }

    public function themeCssUrl(): ?string
    {
        $theme = $this->loginTheme();

        if (!$theme) {
            return null;
        }

        $themeCssPath = public_path("login_themes/{$theme}/theme.css");

        return File::exists($themeCssPath)
            ? "/login_themes/{$theme}/theme.css?v=" . filemtime($themeCssPath)
            : null;
    }

    public function getMessengerType(): string
    {
        return (string) ($this->messenger_type ?: 'Telegram');
    }

    public function getMessengerUsername(): string
    {
        return (string) ($this->messenger_username ?: $this->skype);
    }

    public function getSkype(): string
    {
        return (string) $this->skype;
    }

    public function getEmail(): string
    {
        return (string) $this->email;
    }

    public function getUID(): string
    {
        return (string) $this->uid;
    }

    public function getLoginURL(): string
    {
        return (string) $this->login_url;
    }

    public function getLandingPage(): string
    {
        return (string) $this->landing_page;
    }

    public function allowsRegister(): bool
    {
        return (bool) $this->allow_register;
    }

    public function updateSettings(
        string $shortHand,
        string $colors,
        string $email,
        string $skype,
        string $loginURL,
        string $landingPage,
        string $loginTheme,
        string $messengerType,
        string $messengerUsername,
        bool $allowRegister
    ): bool {
        try {
            $this->shortHand = $shortHand;
            $this->colors = $colors;
            $this->email = $email;
            $this->skype = $skype;
            $this->login_url = $loginURL;
            $this->landing_page = $landingPage;
            $this->login_theme = $loginTheme;
            $this->messenger_type = $messengerType;
            $this->messenger_username = $messengerUsername;
            $this->allow_register = $allowRegister;

            $saved = $this->save();

            if ($saved) {
                NativeSession::forget('company');
            }

            return $saved;
        } catch (Throwable $e) {
            return false;
        }
    }

    private function normalizeHost(string $host): string
    {
        $normalized = strtolower(trim($host));
        $normalized = preg_replace('/:\d+$/', '', $normalized);
        $normalized = rtrim($normalized, '.');

        if (str_starts_with($normalized, 'www.')) {
            return substr($normalized, 4);
        }

        return $normalized;
    }

}
