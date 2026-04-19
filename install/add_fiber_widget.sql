-- Add Fiber Stats Widget to Dashboard
-- Run this SQL to add the fiber_stats widget to the dashboard

-- First check if the widget already exists
SET @widget_exists = (SELECT COUNT(*) FROM tbl_widgets WHERE widget = 'fiber_stats');

-- Insert the widget if it doesn't exist
INSERT INTO tbl_widgets (widget, description, enabled, user, orders, position)
SELECT 'fiber_stats', 'Fiber Network Statistics', 1, 'Admin', 5, 1
WHERE @widget_exists = 0;

-- Alternative: You can also enable it for SuperAdmin
INSERT INTO tbl_widgets (widget, description, enabled, user, orders, position)
SELECT 'fiber_stats', 'Fiber Network Statistics', 1, 'SuperAdmin', 5, 1
WHERE @widget_exists = 0;

-- If you want to update an existing widget to enable it:
-- UPDATE tbl_widgets SET enabled = 1, orders = 5 WHERE widget = 'fiber_stats';
