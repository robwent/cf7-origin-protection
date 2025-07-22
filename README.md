# Contact Form 7 Origin Protection

A WordPress plugin that blocks Contact Form 7 submissions from external domains to prevent spam attacks.

## Description

This plugin protects Contact Form 7 forms from spam submissions by validating that form submissions originate from the
same domain as your website. It intercepts REST API calls to Contact Form 7 endpoints and blocks requests that don't
have a valid origin header matching your site's domain.

## The Problem

Spammers often bypass Contact Form 7's frontend validation (including reCAPTCHA) by sending direct POST requests to the
CF7 REST API endpoints. These submissions:

- Skip client-side validation entirely
- Bypass reCAPTCHA protection
- Still get processed and logged as spam in Flamingo
- Create unnecessary server load and database bloat

## The Solution

This plugin validates the `HTTP_ORIGIN` header of incoming CF7 REST API requests. Only submissions originating from your
actual domain are allowed through - external requests are blocked with a 403 response before CF7 even processes them.

## Features

- ✅ **Zero Configuration** - Works automatically after activation
- ✅ **Lightweight** - Minimal performance impact
- ✅ **Future-Proof** - Works regardless of reCAPTCHA/Turnstile configuration
- ✅ **Logging** - Blocked attempts are logged for monitoring
- ✅ **Compatible** - Works with all CF7 forms and configurations

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Contact Form 7 plugin

## Installation

### From GitHub

1. Download or clone this repository
2. Upload the `cf7-origin-protection` folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' screen in WordPress

### Manual Installation

1. Download the latest release
2. Upload the plugin files via WordPress admin or FTP
3. Activate the plugin

## How It Works

1. **Intercepts CF7 REST API calls** - Hooks into `rest_pre_dispatch` filter
2. **Validates origin header** - Checks if `HTTP_ORIGIN` matches your site domain
3. **Blocks invalid requests** - Returns 403 error for external origins
4. **Allows legitimate submissions** - Passes valid requests to Contact Form 7

## Technical Details

The plugin validates requests to any endpoint matching `/contact-form-7/v1/` by:

```php
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$site_host = parse_url(get_site_url(), PHP_URL_HOST);

if (!str_contains($origin, $site_host)) {
    // Block the request
}
```

## Limitations

- **Header spoofing**: Determined attackers can still fake the Origin header
- **No Origin header**: Some legitimate clients might not send Origin headers
- **Subdomain variations**: May need adjustment for complex subdomain setups

Despite these limitations, the plugin effectively blocks the majority of automated spam bots that don't bother setting
proper headers.

## Development

### Testing Spam Submissions

You can test the plugin's effectiveness using tools like Insomnia or cURL:

```bash
# This should be blocked (403 response)
curl -X POST https://yoursite.com/wp-json/contact-form-7/v1/contact-forms/123/feedback \
  -H "Origin: http://spam-site.com" \
  -F "your-name=Spam" \
  -F "your-email=spam@example.com"

# This should work (normal CF7 response)
curl -X POST https://yoursite.com/wp-json/contact-form-7/v1/contact-forms/123/feedback \
  -H "Origin: https://yoursite.com" \
  -F "your-name=Real User" \
  -F "your-email=user@example.com"
```

### Logging

Blocked attempts are logged to your PHP error log:

```
CF7 Origin Protection: Blocked submission from origin: http://spam-site.com (expected: yoursite.com)
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

This plugin is licensed under the GPL v2 or later.

## Support

For issues and feature requests, please use
the [GitHub issue tracker](https://github.com/yourusername/cf7-origin-protection/issues).

## Changelog

### 1.0.0

- Initial release
- Basic origin header validation
- REST API interception
- Error logging