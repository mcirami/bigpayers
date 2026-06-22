# Runtime Configuration

Runtime application code should read configuration through Laravel `config(...)`
values. Environment variables should be surfaced in config files only.

The legacy fallback audit enforces this for `app/`, `src/`, `routes/`, and
`resources/views`.

## Configured Values

| Environment variable | Config key | Used for |
| --- | --- | --- |
| `TYS_BASE_INSTALL` | `provisioning.base_install_sql` | Optional base install SQL dump path for provisioning/import helpers. |
| `SALE_LOG_DIRECTORY` | `filesystems.sale_log_directory` | Sale-log image storage root. |
| `GEO_IP_DATABASE` | `services.geo.ip_database` | MaxMind GeoIP database path. |
| `SMS_URL` | `services.sms.base_url` | Legacy SMS API base URL. |
| `LOGIN_PAGE_TEXT` | `branding.login.page_text` | Login page headline. |
| `LOGIN_PAGE_BUTTON_TEXT` | `branding.login.button_text` | Login submit button text. |
| `FORGOT_PASS_LINK_TEXT` | `branding.login.forgot_password_link_text` | Forgot-password link text on login. |
| `FORGOT_PASS_PAGE_TEXT` | `branding.login.forgot_password_page_text` | Forgot-password page headline. |
| `FORGOT_PASS_PAGE_BUTTON_TEXT` | `branding.login.forgot_password_button_text` | Forgot-password submit button text. |

Database settings continue to live under `database.connections.mysql` and
`database.connections.master`. Legacy database bootstrap code reads those through
`App\Services\LegacyDatabaseConfig`.
