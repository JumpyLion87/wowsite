<?php

return [
    // Main headers
    'ban_management' => 'Ban Management',
    'ban_management_description' => 'User ban management and moderation',
    'ban_details' => 'Ban Details',
    'ban_details_description' => 'Detailed ban information',
    'create_ban' => 'Create Ban',
    'ban_information' => 'Ban Information',
    'account_information' => 'Account Information',
    'ban_history' => 'Ban History',
    'quick_actions' => 'Quick Actions',

    // Statistics
    'total_bans' => 'Total Bans',
    'active_bans' => 'Active Bans',
    'expired_bans' => 'Expired Bans',
    'permanent_bans' => 'Permanent Bans',
    'inactive_bans' => 'Inactive Bans',

    // Filters and search
    'filter_by_status' => 'Filter by Status',
    'all_statuses' => 'All Statuses',
    'search' => 'Search',
    'search_placeholder' => 'Search by name, email or reason...',
    'filter' => 'Filter',
    'clear' => 'Clear',

    // Table
    'user' => 'User',
    'reason' => 'Reason',
    'banned_by' => 'Banned By',
    'ban_date' => 'Ban Date',
    'unban_date' => 'Unban Date',
    'status' => 'Status',
    'actions' => 'Actions',
    'view_details' => 'View Details',
    'unban' => 'Unban',
    'delete' => 'Delete',

    // Statuses
    'active' => 'Active',
    'expired' => 'Expired',
    'inactive' => 'Inactive',
    'permanent' => 'Permanent',
    'unknown' => 'Unknown',
    'system' => 'System',

    // Ban creation form
    'select_account' => 'Select Account',
    'search_account_placeholder' => 'Enter username or email...',
    'ban_reason' => 'Ban Reason',
    'ban_reason_placeholder' => 'Enter ban reason...',
    'ban_duration' => 'Ban Duration',
    'ban_duration_placeholder' => 'Number of days (leave empty for permanent ban)',
    'ban_duration_help' => 'Leave empty for permanent ban',
    'ban_type' => 'Ban Type',
    'account_ban' => 'Account Ban',
    'ip_ban' => 'IP Ban',
    'character_ban' => 'Character Ban',
    'account_ban' => 'Account Ban',
    'ip_ban' => 'IP Ban',
    'type' => 'Type',
    'email' => 'Email',
    'not_provided' => 'Not provided',
    'ip_address' => 'IP Address',
    'ip_address_placeholder' => 'Enter IP address (e.g., 192.168.1.1)',
    'ip_address_help' => 'Enter IP address for ban. You can use masks (e.g., 192.168.1.*)',
    'select_character' => 'Select Character',
    'search_character_placeholder' => 'Enter character name...',
    'select_account_for_character' => 'Select Account to Find Characters',
    'select_character_first' => 'Please select an account first',
    'days' => 'days',

    // Bulk operations
    'bulk_unban' => 'Unban Selected',
    'bulk_delete' => 'Delete Selected',
    'apply_action' => 'Apply Action',
    'cancel' => 'Cancel',

    // Messages
    'no_bans_found' => 'No bans found',
    'no_bans_description' => 'There are no bans in the system yet',
    'no_reason' => 'No reason provided',
    'ban_not_found' => 'Ban not found',
    'account_already_banned' => 'Account is already banned',
    'ban_created_successfully' => 'Ban created successfully',
    'ban_creation_failed' => 'Failed to create ban',
    'unban_successful' => 'User unbanned',
    'unban_failed' => 'Failed to unban user',
    'ban_deleted_successfully' => 'Ban deleted',
    'ban_deletion_failed' => 'Failed to delete ban',
    'bulk_unban_successful' => 'Unbanned :count bans',
    'bulk_delete_successful' => 'Deleted :count bans',
    'bulk_action_failed' => 'Bulk action failed',

    // Confirmations
    'unban_confirm' => 'Are you sure you want to unban this user?',
    'delete_confirm' => 'Are you sure you want to delete this ban?',

    // Ban details
    'username' => 'Username',
    'email' => 'Email',
    'account_id' => 'Account ID',
    'last_login' => 'Last Login',
    'duration' => 'Duration',
    'banned' => 'Banned',
    'unbanned' => 'Unbanned',
    'by' => 'by',

    // Actions
    'view_account' => 'View Account',
    'unban_account' => 'Unban Account',
    'unban_character' => 'Unban Character',
    'unban_ip' => 'Unban IP',
    'ip_ban_info' => 'IP bans can only be deleted. To unban, use delete ban.',
    'delete_ban' => 'Delete Ban',
    'already_unbanned' => 'Already Unbanned',
    'back_to_bans' => 'Back to Bans',
    'back_to_list' => 'Back to List',
    'back_to_dashboard' => 'Back to Dashboard',
    
    // New keys for different ban types
    'ip_already_banned' => 'IP address is already banned.',
    'character_not_found' => 'Character not found.',
    'character_already_banned' => 'Character is already banned.',
];
