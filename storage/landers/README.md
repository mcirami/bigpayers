Drop company landing pages in `storage/landers/<subdomain>/`.

Expected structure:
- `storage/landers/<subdomain>/index.php` or `index.html`
- any supporting assets in child folders like `css/`, `js/`, `images/`

Public asset URLs should continue using:
- `/resources/landers/<subdomain>/<asset path>`

The app now treats `storage/landers` as the single source of truth for root/company landers.
The older `public/resources/landers` tree is legacy-only and should not be used for new installs.
