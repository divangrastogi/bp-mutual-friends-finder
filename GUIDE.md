# BuddyPress Mutual Friends Finder - Technical Guide

This guide provides detailed technical information about the BuddyPress Mutual Friends Finder plugin, including architecture, implementation details, and customization options.

## Architecture Overview

### Plugin Structure

```
bp-mutual-friends-finder/
├── bp-mutual-friends-finder.php     # Main plugin file
├── admin/                           # Admin interface
│   ├── class-admin.php             # Admin menu and pages
│   └── class-settings.php          # Settings management
├── includes/                        # Core functionality
│   ├── class-bpmff-core.php        # Main plugin class
│   ├── class-bpmff-hooks.php       # BuddyPress integration
│   ├── class-bpmff-ajax-handler.php # AJAX processing
│   ├── class-bpmff-query.php       # Database queries
│   ├── class-bpmff-template.php    # Template rendering
│   ├── class-bpmff-cache.php       # Caching system
│   └── class-bpmff-rate-limiter.php # Rate limiting
├── assets/                         # Frontend assets
│   ├── css/
│   │   ├── frontend.css           # Tooltip styles
│   │   └── admin.css              # Admin styles
│   └── js/
│       ├── frontend.js            # Tooltip functionality
│       └── admin.js               # Admin scripts
├── templates/                      # HTML templates
│   ├── tooltip.php                # Tooltip template
│   ├── modal.php                  # Modal template
│   └── friend-item.php            # Friend item template
├── tests/                         # Unit tests
└── uninstall.php                  # Cleanup on uninstall
```

## Implementation Details

### Frontend JavaScript

The plugin uses a modular JavaScript architecture with the following components:

#### BPMFF Object
```javascript
window.BPMFF = {
    config: {
        ajaxUrl: '...',
        nonce: '...',
        displayCount: 3,
        tooltipDelay: 500,
        cacheTimeout: 300000
    },
    cache: new Map(),
    init: function() { /* ... */ },
    bindEvents: function() { /* ... */ },
    handleMemberHover: function(e) { /* ... */ },
    loadMutualFriends: function(userId, $target) { /* ... */ },
    showTooltip: function($target, data) { /* ... */ },
    showLoadingTooltip: function($target) { /* ... */ },
    showErrorTooltip: function($target, message) { /* ... */ },
    positionTooltip: function($tooltip, $target) { /* ... */ }
}
```

#### Event Binding
Events are bound to `[data-bpmff-user]` attributes added to member links:
```javascript
$(document).on('mouseenter', '[data-bpmff-user]', this.handleMemberHover.bind(this));
```

### PHP Classes

#### BPMFF_Core
Main plugin class handling initialization, asset enqueuing, and AJAX setup.

#### BPMFF_Hooks
Handles BuddyPress integration and DOM manipulation.

#### BPMFF_Ajax_Handler
Processes AJAX requests with security checks:
- Nonce verification
- User authentication
- Rate limiting
- Input validation

#### BPMFF_Query
Database query optimization for mutual friends lookup:
```php
public static function get_mutual_friends($user_id, $target_user_id, $limit = null) {
    global $wpdb;

    // Optimized query using EXISTS and JOINs
    $query = $wpdb->prepare("
        SELECT u.ID, u.display_name, u.user_login
        FROM {$wpdb->users} u
        INNER JOIN {$wpdb->bp_friends} f1 ON f1.friend_user_id = u.ID
        INNER JOIN {$wpdb->bp_friends} f2 ON f2.friend_user_id = u.ID
        WHERE f1.initiator_user_id = %d
        AND f2.initiator_user_id = %d
        AND f1.is_confirmed = 1
        AND f2.is_confirmed = 1
        AND u.ID != %d
        AND u.ID != %d
        ORDER BY u.display_name
        LIMIT %d
    ", $user_id, $target_user_id, $user_id, $target_user_id, $limit);

    return $wpdb->get_results($query);
}
```

## Database Schema

The plugin leverages BuddyPress's existing friends tables:

- `wp_bp_friends` - Friendship relationships
  - `initiator_user_id` - User who initiated friendship
  - `friend_user_id` - Friend user ID
  - `is_confirmed` - Whether friendship is confirmed
  - `date_created` - When friendship was created

## Caching System

### Implementation
```php
class BPMFF_Cache {
    private static $cache_group = 'bpmff_mutual_friends';

    public static function get($key) {
        return wp_cache_get($key, self::$cache_group);
    }

    public static function set($key, $data, $expire = 300) {
        wp_cache_set($key, $data, self::$cache_group, $expire);
    }

    public static function delete($key) {
        wp_cache_delete($key, self::$cache_group);
    }
}
```

### Cache Keys
- `mutual_{user_id}_{target_user_id}_{limit}` - Mutual friends data
- `count_{user_id}_{target_user_id}` - Mutual friends count

### Cache Invalidation
Cache is automatically cleared when:
- Friendships are accepted/deleted/withdrawn
- User profiles are updated
- Manual cache clear via admin

## Security Features

### AJAX Security
- WordPress nonces for request validation
- User capability checks
- Input sanitization and validation
- Rate limiting (configurable)

### Rate Limiting
```php
class BPMFF_Rate_Limiter {
    private static $limit = 30; // requests per minute
    private static $window = 60; // seconds

    public static function check_limit($user_id) {
        $key = "rate_limit_{$user_id}";
        $requests = get_transient($key) ?: [];

        // Remove old requests outside window
        $requests = array_filter($requests, function($time) {
            return $time > (time() - self::$window);
        });

        if (count($requests) >= self::$limit) {
            return false; // Rate limit exceeded
        }

        $requests[] = time();
        set_transient($key, $requests, self::$window);

        return true;
    }
}
```

## Customization

### CSS Customization

Override default styles by targeting these classes:

```css
/* Main tooltip */
.bpmff-tooltip {
    /* Custom styles */
}

/* Visible tooltip */
.bpmff-tooltip-visible {
    /* Animation styles */
}

/* Position-specific styles */
.bpmff-tooltip-top {}
.bpmff-tooltip-bottom {}
.bpmff-tooltip-left {}
.bpmff-tooltip-right {}

/* Loading and error states */
.bpmff-loading {}
.bpmff-error-message {}
```

### JavaScript Customization

Extend the BPMFF object:

```javascript
// Add custom functionality
BPMFF.customFeature = function() {
    // Your code here
};

// Override existing methods
var originalShowTooltip = BPMFF.showTooltip;
BPMFF.showTooltip = function($target, data) {
    // Custom logic
    originalShowTooltip.call(this, $target, data);
};
```

### PHP Customization

Use filters to modify behavior:

```php
// Modify tooltip HTML
add_filter('bpmff_tooltip_html', function($html, $friends, $count) {
    // Custom HTML generation
    return $html;
}, 10, 3);

// Modify query results
add_filter('bpmff_mutual_friends_query', function($query, $user_id, $target_id) {
    // Modify database query
    return $query;
}, 10, 3);
```

## Performance Optimization

### Database Optimization
- Uses indexed columns in queries
- Limits results to prevent large datasets
- Efficient EXISTS clauses instead of IN subqueries

### Frontend Optimization
- Debounced hover events (500ms delay)
- Client-side caching (5-minute default)
- Minimal DOM manipulation
- CSS-only animations where possible

### Server Optimization
- Object caching for query results
- Transient caching for expensive operations
- Background cache cleanup via WP Cron

## Troubleshooting

### Common Issues

#### Tooltips Not Appearing
1. Check browser console for JavaScript errors
2. Verify CSS is loading (`link[href*="frontend"]`)
3. Check if `[data-bpmff-user]` attributes exist
4. Verify AJAX endpoint responses

#### AJAX Errors
1. Check WordPress debug logs
2. Verify nonce validity
3. Check user permissions
4. Review rate limiting settings

#### Performance Issues
1. Check database query execution time
2. Review cache hit rates
3. Monitor AJAX request frequency
4. Verify server resources

### Debug Mode

Enable debug logging in wp-config.php:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Check debug.log for BPMFF entries.

## Testing

### Unit Tests
Run tests with:
```bash
phpunit tests/
```

### Manual Testing Checklist
- [ ] Tooltips appear on member directory pages
- [ ] AJAX requests return valid JSON
- [ ] Cache works correctly
- [ ] Rate limiting prevents abuse
- [ ] Admin settings save properly
- [ ] No JavaScript errors in console
- [ ] Responsive design works on mobile

## API Reference

### JavaScript API

#### Methods
- `BPMFF.init()` - Initialize plugin
- `BPMFF.loadMutualFriends(userId, $target)` - Load mutual friends
- `BPMFF.showTooltip($target, data)` - Display tooltip
- `BPMFF.closeModal()` - Close modal

#### Events
- `mouseenter` on `[data-bpmff-user]` - Trigger tooltip
- `mouseleave` on tooltips - Hide tooltip
- `click` on `.bpmff-view-all` - Open modal

### PHP API

#### Functions
- `bpmff_get_option($key, $default)` - Get plugin setting
- `bpmff_update_option($key, $value)` - Update plugin setting
- `bpmff_clear_cache()` - Clear all caches

#### Classes
All classes are documented with PHPDoc comments and can be extended for custom functionality.

## Contributing

### Code Standards
- Follow WordPress Coding Standards
- Use PHPDoc for all functions/classes
- Include unit tests for new features
- Maintain backward compatibility

### Development Setup
1. Clone repository
2. Install dependencies: `composer install`
3. Run tests: `phpunit`
4. Build assets: `npm run build`

### Pull Request Process
1. Create feature branch
2. Write tests
3. Update documentation
4. Submit PR with description

## Support and Maintenance

### Version Support
- Current version: 1.0.0
- Minimum WordPress: 5.0
- Minimum BuddyPress: 10.0
- Minimum PHP: 7.4

### Deprecation Policy
Features are deprecated with 2-version notice before removal.

### Security
Report security issues to [security@email.com] - do not create public issues.

---

This guide is maintained alongside the plugin code. Please report any inaccuracies or missing information.
