# Player ID Plugin
Contributors: your-username  
Tags: user-management, sso, oauth, player-id, authentication  
Requires at least: 6.0  
Tested up to: 6.8  
Stable tag: 1.0.0  
Requires PHP: 7.4  
License: GPL-2.0-or-later  
License URI: http://www.gnu.org/licenses/gpl-2.0.html  

Adds a mandatory, unique Player ID field to WordPress users with wp-oauth SSO integration.

## Description

The Player ID Plugin extends WordPress user profiles with a mandatory Player ID field that must be unique across all users. This field seamlessly integrates with wp-oauth to provide Player ID mapping to SSO clients, ensuring consistent player identification across connected applications.

### Key Features:
- **Mandatory Player ID**: Every user must have a unique Player ID
- **Duplicate Prevention**: Built-in validation prevents duplicate IDs
- **SSO Integration**: Maps Player ID through wp-oauth to connected clients
- **Admin Management**: Easy Player ID management through WordPress admin
- **User Registration**: Integrated into registration flow

---

## How It Works

### 1. Player ID Field
Adds a custom field to WordPress user profiles that stores a unique player identifier.

### 2. Validation
Real-time validation ensures no two users can have the same Player ID.

### 3. SSO Mapping
Automatically maps the Player ID field through wp-oauth's attribute mapping system.

### 4. Registration Flow
New users must provide a unique Player ID during registration.

### 5. Admin Interface
Administrators can view and manage Player IDs through the WordPress users panel.

---

## Installation

1. Upload the plugin to `/wp-content/plugins/player-id-plugin/`
2. Activate through the 'Plugins' menu in WordPress
3. Configure wp-oauth attribute mapping for Player ID field
4. Users will be prompted to set Player ID on next login if missing

## Requirements

- WordPress 6.0 or higher
- wp-oauth plugin installed and configured
- PHP 7.4 or higher