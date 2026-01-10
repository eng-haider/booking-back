<?php

return [
    'guard_name' => 'api',
    
    'roles' => [
        'super_admin' => [
            // Booking permissions (from Admin/BookingController)
            'view_bookings',
            'create_bookings',
            'edit_bookings',
            'delete_bookings',
            'confirm_bookings',
            'cancel_bookings',
            'complete_bookings',
            'check_availability',
            
            // Category permissions (from Admin/CategoryController)
            'view_categories',
            'create_categories',
            'edit_categories',
            'delete_categories',
            'reorder_categories',
            
            // Customer permissions (from Admin/CustomerController)
            'view_customers',
            'create_customers',
            'edit_customers',
            'delete_customers',
            
            // Provider permissions (from Admin/ProviderController)
            'view_providers',
            'create_providers',
            'edit_providers',
            'delete_providers',
            
            // Review permissions (from Admin/ReviewController)
            'view_reviews',
            'delete_reviews',
            
            // Venue permissions (from Admin/VenueController)
            'view_venues',
            'create_venues',
            'edit_venues',
            'delete_venues',
            'feature_venues',
            
            // Payment permissions (from Admin/PaymentController)
            'view_payments',
            'process_payments',
            'refund_payments',
            
            // System permissions
            'manage_settings',
            'manage_roles',
            'manage_permissions',
            'view_statistics',
            'view_reports',
            'export_data',
        ],
        
        'provider' => [
            // Venue permissions (from Provider/VenueController)
            'manage_own_venues',
            'create_venues',
            
            // Booking permissions (from Provider/BookingController)
            'manage_own_bookings',
            'confirm_bookings',
            'cancel_bookings',
            'complete_bookings',
            
            // Provider profile permissions (from Provider/ProfileController)
            'manage_own_provider',
            
            // Review permissions (from Provider/ReviewController)
            'view_own_reviews',
            
            // Statistics
            'view_own_statistics',
        ],
    ],
];