# BuddyPress Mutual Friends Finder

A BuddyPress plugin that adds mutual friends tooltips to member directory pages, allowing users to see their mutual friends with other members on hover.

## Features

- **Mutual Friends Tooltips**: Hover over member names in the directory to see mutual friends
- **AJAX-Powered**: Fast, responsive tooltips loaded via AJAX
- **Caching System**: Built-in caching for improved performance
- **Customizable Display**: Configure tooltip position, animation, and display count
- **Admin Settings**: Easy-to-use settings panel for configuration
- **Rate Limiting**: Built-in protection against excessive AJAX requests

## Requirements

- WordPress 5.0 or higher
- BuddyPress 10.0 or higher
- PHP 7.4 or higher

## Installation

1. Download the plugin zip file
2. Go to WordPress Admin > Plugins > Add New > Upload Plugin
3. Upload the zip file and activate the plugin
4. Configure settings in **BuddyPress > Mutual Friends**

## Configuration

### Basic Settings

- **Display Count**: Number of mutual friends to show in tooltip (default: 3)
- **Tooltip Position**: Auto, top, bottom, left, or right positioning
- **Animation Effect**: Choose between fade or slide animations

### Advanced Settings

- **Cache Timeout**: How long to cache mutual friends data (default: 5 minutes)
- **Rate Limiting**: Enable/disable AJAX request rate limiting
- **Debug Mode**: Enable debug logging for troubleshooting

## Usage

1. Navigate to the BuddyPress Members Directory
2. Hover over any member name for 500ms
3. A tooltip will appear showing mutual friends
4. Click "View All" to see all mutual friends in a modal

## Screenshots

*Coming soon - screenshots of the tooltip in action*

## Frequently Asked Questions

### Why don't I see the tooltips?

Make sure:
- You're logged in as a user
- The plugin is activated
- You're on a BuddyPress members directory page
- JavaScript is enabled in your browser

### Can I customize the tooltip appearance?

Yes! The plugin includes CSS classes you can override:
- `.bpmff-tooltip` - Main tooltip container
- `.bpmff-tooltip-visible` - Visible tooltip state
- `.bpmff-tooltip-top/bottom/left/right` - Position classes

### Performance Considerations

The plugin includes several performance optimizations:
- AJAX caching (5-minute default)
- Rate limiting to prevent abuse
- Efficient database queries
- Minimal JavaScript footprint

## Developer Documentation

### Hooks and Filters

#### Actions
- `bpmff_before_tooltip_display` - Fires before tooltip HTML is rendered
- `bpmff_after_tooltip_display` - Fires after tooltip is displayed
- `bpmff_cache_cleared` - Fires when cache is manually cleared

#### Filters
- `bpmff_tooltip_html` - Modify tooltip HTML output
- `bpmff_mutual_friends_query` - Modify the mutual friends database query
- `bpmff_display_count` - Filter the number of friends displayed

### AJAX Endpoints

- `bpmff_get_mutual_friends` - Get mutual friends for tooltip
- `bpmff_get_all_mutual_friends` - Get all mutual friends for modal
- `bpmff_clear_cache` - Clear plugin cache

### Template Files

The plugin uses template files located in `/templates/`:
- `tooltip.php` - Tooltip HTML template
- `modal.php` - Modal HTML template
- `friend-item.php` - Individual friend item template

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## Changelog

### Version 1.0.0
- Initial release
- Mutual friends tooltips
- AJAX loading
- Caching system
- Admin settings panel

## Support

For support, please:
1. Check the FAQ section above
2. Review the troubleshooting guide
3. Create an issue on GitHub
4. Contact the plugin author

## License

This plugin is licensed under the GPL v2 or later.

## Credits

Developed by [Your Name/Company]
Built for the BuddyPress community
