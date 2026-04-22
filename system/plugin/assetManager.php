<?php

/**
 * Bismillahir Rahmanir Raheem
 * 
 * PHP Mikrotik Billing (https://github.com/hotspotbilling/phpnuxbill/)
 *
 * Asset Manager System For PHPNuxBill 
 *
 * @author: Focuslinks Digital Solutions <focuslinkstech@gmail.com>
 * Website: https://focuslinkstech.com.ng/
 * GitHub: https://github.com/Focuslinkstech/
 * Telegram: https://t.me/focuslinkstech/
 *
 **/

register_menu(" Asset Manager", true, "assetManager", 'AFTER_MESSAGE', 'fa fa-cubes', '', "");


function assetManager()
{
    global $ui, $config, $routes;
    _admin();
    $ui->assign('_title', $GLOBALS['config']['CompanyName'] . ' - ' . 'Asset Manager');
    $ui->assign('_system_menu', '');
    $admin = Admin::_info();
    $ui->assign('_admin', $admin);
    $ui->assign('version', 'v1.0.0');
    $ui->assign('xheader', '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
     <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
     <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>');

    // Check user type for access
    if (!in_array($admin['user_type'], ['SuperAdmin', 'Admin', 'Sales'])) {
        _alert(Lang::T('You do not have permission to access this page'), 'danger', "dashboard");
        exit;
    }

    // Initialize database tables
    createAssetsTable();

    // Ensure database schema is up to date
    ensureSchemaUpdates();

    // Handle AJAX requests first, before any UI output
    if (isset($_GET['ajax']) && $_GET['ajax'] === 'get-models') {
        getModelsByBrand();
    }

    // Get the action from routes
    $action = isset($routes[2]) ? $routes[2] : 'dashboard';

    switch ($action) {
        case 'dashboard':
            assetDashboard();
            break;
        case 'categories':
            assetCategories();
            break;
        case 'categories-add':
            assetCategoriesAdd();
            break;
        case 'categories-edit':
            assetCategoriesEdit();
            break;
        case 'categories-delete':
            assetCategoriesDelete();
            break;
        case 'brands':
            assetBrands();
            break;
        case 'brands-add':
            assetBrandsAdd();
            break;
        case 'brands-edit':
            assetBrandsEdit();
            break;
        case 'brands-delete':
            assetBrandsDelete();
            break;
        case 'models':
            assetModels();
            break;
        case 'models-add':
            assetModelsAdd();
            break;
        case 'models-edit':
            assetModelsEdit();
            break;
        case 'models-delete':
            assetModelsDelete();
            break;
        case 'assets':
            assetsList();
            break;
        case 'assets-add':
            assetsAdd();
            break;
        case 'assets-edit':
            assetsEdit();
            break;
        case 'assets-view':
            assetsView();
            break;
        case 'assets-delete':
            assetsDelete();
            break;
        case 'reports':
            assetReports();
            break;
        case 'reports-generate':
            generateAssetReport();
            break;
        case 'reports-export':
            exportAssetReport();
            break;
        case 'welcome':
            asset_welcome_seen();
            break;
        default:
            assetDashboard();
            break;
    }
}

function createAssetsTable()
{
    try {
        // Create asset categories table
        $sql = "CREATE TABLE IF NOT EXISTS tbl_asset_categories (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            status enum('Active','Inactive') DEFAULT 'Active',
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_category_name (name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        ORM::raw_execute($sql);

        // Create asset brands table
        $sql = "CREATE TABLE IF NOT EXISTS tbl_asset_brands (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            country varchar(100),
            website varchar(255),
            status enum('Active','Inactive') DEFAULT 'Active',
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_brand_name (name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        ORM::raw_execute($sql);

        // Create asset models table
        $sql = "CREATE TABLE IF NOT EXISTS tbl_asset_models (
            id int(11) NOT NULL AUTO_INCREMENT,
            brand_id int(11) NOT NULL,
            name varchar(255) NOT NULL,
            model_number varchar(100),
            description text,
            specifications text,
            status enum('Active','Inactive') DEFAULT 'Active',
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_brand_id (brand_id),
            CONSTRAINT fk_models_brand FOREIGN KEY (brand_id) REFERENCES tbl_asset_brands(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        ORM::raw_execute($sql);

        // Create assets table
        $sql = "CREATE TABLE IF NOT EXISTS tbl_assets (
            id int(11) NOT NULL AUTO_INCREMENT,
            category_id int(11) NOT NULL,
            brand_id int(11) NOT NULL,
            model_id int(11) NOT NULL,
            asset_tag varchar(100) NOT NULL,
            serial_number varchar(255),
            name varchar(255) NOT NULL,
            description text,
            purchase_date date,
            purchase_cost decimal(15,2),
            warranty_expiry date,
            location varchar(255),
            latitude decimal(10,8),
            longitude decimal(11,8),
            assigned_to int(11),
            status enum('Active','Inactive','Under Maintenance','Disposed') DEFAULT 'Active',
            condition_status enum('Excellent','Good','Fair','Poor') DEFAULT 'Good',
            notes text,
            created_by int(11),
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_asset_tag (asset_tag),
            KEY idx_category_id (category_id),
            KEY idx_brand_id (brand_id),
            KEY idx_model_id (model_id),
            KEY idx_assigned_to (assigned_to),
            KEY idx_status (status),
            KEY idx_coordinates (latitude, longitude),
            CONSTRAINT fk_assets_category FOREIGN KEY (category_id) REFERENCES tbl_asset_categories(id) ON DELETE CASCADE,
            CONSTRAINT fk_assets_brand FOREIGN KEY (brand_id) REFERENCES tbl_asset_brands(id) ON DELETE CASCADE,
            CONSTRAINT fk_assets_model FOREIGN KEY (model_id) REFERENCES tbl_asset_models(id) ON DELETE CASCADE,
            CONSTRAINT fk_assets_customer FOREIGN KEY (assigned_to) REFERENCES tbl_customers(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        ORM::raw_execute($sql);

        // Insert default categories if table is empty
        try {
            $categoryCount = ORM::for_table('tbl_asset_categories')->count();
            if ($categoryCount == 0) {
                $categories = [
                    ['name' => 'Network Equipment', 'description' => 'Routers, switches, access points, and other networking hardware'],
                    ['name' => 'Power Equipment', 'description' => 'UPS, power supplies, batteries, and power management devices'],
                    ['name' => 'Transmission Equipment', 'description' => 'Fiber optic equipment, wireless transmission, antennas'],
                    ['name' => 'Computing Equipment', 'description' => 'Servers, computers, storage devices'],
                    ['name' => 'Infrastructure', 'description' => 'Towers, cabinets, cables, and physical infrastructure']
                ];

                foreach ($categories as $cat) {
                    try {
                        $category = ORM::for_table('tbl_asset_categories')->create();
                        $category->name = $cat['name'];
                        $category->description = $cat['description'];
                        $category->status = 'Active';
                        $category->save();
                    } catch (Exception $e) {
                        // Ignore duplicates
                    }
                }
            }
        } catch (Exception $e) {
            // Table might not exist yet, ignore
        }

        // Insert default brands if table is empty
        try {
            $brandCount = ORM::for_table('tbl_asset_brands')->count();
            if ($brandCount == 0) {
                $brands = [
                    ['name' => 'Cisco Systems', 'description' => 'Leading provider of networking equipment', 'country' => 'United States', 'website' => 'https://www.cisco.com'],
                    ['name' => 'Ubiquiti Networks', 'description' => 'Manufacturer of networking and wireless communication products', 'country' => 'United States', 'website' => 'https://www.ui.com'],
                    ['name' => 'MikroTik', 'description' => 'Latvian manufacturer of computer networking equipment', 'country' => 'Latvia', 'website' => 'https://mikrotik.com'],
                    ['name' => 'TP-Link', 'description' => 'Chinese manufacturer of computer networking products', 'country' => 'China', 'website' => 'https://www.tp-link.com'],
                    ['name' => 'Huawei', 'description' => 'Chinese multinational technology corporation', 'country' => 'China', 'website' => 'https://www.huawei.com']
                ];

                foreach ($brands as $mak) {
                    try {
                        $brand = ORM::for_table('tbl_asset_brands')->create();
                        $brand->name = $mak['name'];
                        $brand->description = $mak['description'];
                        $brand->country = $mak['country'];
                        $brand->website = $mak['website'];
                        $brand->status = 'Active';
                        $brand->save();
                    } catch (Exception $e) {
                        // Ignore duplicates
                    }
                }
            }
        } catch (Exception $e) {
            // Table might not exist yet, ignore
        }
        // Continue
    } catch (Exception $e) {
        _log(Lang::T('Asset Manager DB Error: ') . $e->getMessage());
        echo Lang::T("Error creating Asset Manager tables.  Error: ") . $e->getMessage();
    }
}

function ensureSchemaUpdates()
{
    try {
        // Try to add purchase_cost column if it doesn't exist
        try {
            ORM::raw_execute("ALTER TABLE tbl_assets ADD COLUMN purchase_cost decimal(15,2) DEFAULT NULL");
            // Continue
        } catch (Exception $e) {
            // Column probably already exists, which is fine
            if (strpos($e->getMessage(), 'Duplicate column name') === false) {
                _log("Asset Manager: purchase_cost column check - " . $e->getMessage());
            }
        }

        // Try to add purchase_date column if it doesn't exist
        try {
            ORM::raw_execute("ALTER TABLE tbl_assets ADD COLUMN purchase_date date DEFAULT NULL");
            // Continue
        } catch (Exception $e) {
            // Column probably already exists, which is fine
            if (strpos($e->getMessage(), 'Duplicate column name') === false) {
                _log("Asset Manager: purchase_date column check - " . $e->getMessage());
            }
        }

        // Try to add warranty_expiry column if it doesn't exist
        try {
            ORM::raw_execute("ALTER TABLE tbl_assets ADD COLUMN warranty_expiry date DEFAULT NULL");
            // Continue
        } catch (Exception $e) {
            // Column probably already exists, which is fine
            if (strpos($e->getMessage(), 'Duplicate column name') === false) {
                _log("Asset Manager: warranty_expiry column check - " . $e->getMessage());
            }
        }

        // Try to add condition_status column if it doesn't exist
        try {
            ORM::raw_execute("ALTER TABLE tbl_assets ADD COLUMN condition_status enum('Excellent','Good','Fair','Poor') DEFAULT 'Good'");
            // Continue
        } catch (Exception $e) {
            // Column probably already exists, which is fine
            if (strpos($e->getMessage(), 'Duplicate column name') === false) {
                _log("Asset Manager: condition_status column check - " . $e->getMessage());
            }
        }

        // Update assigned_to column to int type and add foreign key if needed
        try {
            // Set all non-numeric values to NULL first
            ORM::raw_execute("UPDATE tbl_assets SET assigned_to = NULL WHERE assigned_to IS NOT NULL AND assigned_to NOT REGEXP '^[0-9]+$'");

            // Change column type to int
            ORM::raw_execute("ALTER TABLE tbl_assets MODIFY assigned_to int(11) DEFAULT NULL");

            // Add index if it doesn't exist
            try {
                ORM::raw_execute("ALTER TABLE tbl_assets ADD KEY idx_assigned_to (assigned_to)");
            } catch (Exception $e) {
                // Index might already exist, which is fine
            }

            // Add foreign key constraint if it doesn't exist
            try {
                ORM::raw_execute("ALTER TABLE tbl_assets ADD CONSTRAINT fk_assets_customer FOREIGN KEY (assigned_to) REFERENCES tbl_customers(id) ON DELETE SET NULL");
            } catch (Exception $e) {
                // Constraint might already exist or there might be invalid references
                if (strpos($e->getMessage(), 'Duplicate') === false) {
                    _log(Lang::T("Asset Manager: Could not add foreign key constraint - ") . $e->getMessage());
                }
            }
        } catch (Exception $e) {
            _log(Lang::T("Asset Manager: assigned_to column conversion error - ") . $e->getMessage());
        }
    } catch (Exception $e) {
        _log(Lang::T("Asset Manager Schema Update Error: ") . $e->getMessage());
    }
}

// Dashboard Functions
function assetDashboard()
{
    global $ui, $config;

    try {
        // Get analytics data
        $totalAssets = ORM::for_table('tbl_assets')->count();
        $activeAssets = ORM::for_table('tbl_assets')->where('status', 'Active')->count();
        $inactiveAssets = ORM::for_table('tbl_assets')->where('status', 'Inactive')->count();
        $maintenanceAssets = ORM::for_table('tbl_assets')->where('status', 'Under Maintenance')->count();

        $totalCategories = ORM::for_table('tbl_asset_categories')->count();
        $totalBrands = ORM::for_table('tbl_asset_brands')->count();
        $totalModels = ORM::for_table('tbl_asset_models')->count();

        // Get recent assets
        $recentAssets = ORM::for_table('tbl_assets')
            ->select('tbl_assets.*')
            ->select('tbl_asset_categories.name', 'category_name')
            ->select('tbl_asset_brands.name', 'brand_name')
            ->select('tbl_asset_models.name', 'model_name')
            ->join('tbl_asset_categories', ['tbl_assets.category_id', '=', 'tbl_asset_categories.id'])
            ->join('tbl_asset_brands', ['tbl_assets.brand_id', '=', 'tbl_asset_brands.id'])
            ->join('tbl_asset_models', ['tbl_assets.model_id', '=', 'tbl_asset_models.id'])
            ->order_by_desc('tbl_assets.created_at')
            ->limit(10)
            ->find_array();

        // Get assets by category for chart
        $assetsByCategory = ORM::for_table('tbl_assets')
            ->select('tbl_asset_categories.name', 'category_name')
            ->select_expr('COUNT(*)', 'count')
            ->join('tbl_asset_categories', ['tbl_assets.category_id', '=', 'tbl_asset_categories.id'])
            ->group_by('tbl_asset_categories.name')
            ->find_array();

        // Get assets by status for chart
        $assetsByStatus = ORM::for_table('tbl_assets')
            ->select('status')
            ->select_expr('COUNT(*)', 'count')
            ->group_by('status')
            ->find_array();

        // Get cost-related statistics (with error handling for missing column)
        try {

            $totalAssetValue = ORM::for_table('tbl_assets')
                ->select_expr('COALESCE(SUM(purchase_cost), 0)', 'total')
                ->find_one();
            $totalAssetValue = $totalAssetValue ? $totalAssetValue['total'] : 0;

            // Get currency code from config, fallback to USD if not set
            $currencyCode = isset($config['currency_code']) ? $config['currency_code'] : 'USD';

            // Get assets by cost range for chart
            $assetsByCostRange = ORM::for_table('tbl_assets')
                ->select_expr(
                    "CASE 
                        WHEN purchase_cost IS NULL OR purchase_cost = 0 OR purchase_cost = '' THEN 'No Cost Data'
                        WHEN purchase_cost <= 1000 THEN 'Under {$currencyCode} 1,000'
                        WHEN purchase_cost <= 5000 THEN '{$currencyCode} 1,000 - {$currencyCode} 5,000'
                        WHEN purchase_cost <= 10000 THEN '{$currencyCode} 5,000 - {$currencyCode} 10,000'
                        WHEN purchase_cost <= 25000 THEN '{$currencyCode} 10,000 - {$currencyCode} 25,000'
                        ELSE 'Over {$currencyCode} 25,000'
                    END",
                    'cost_range'
                )
                ->select_expr('COUNT(*)', 'count')
                ->group_by('cost_range')
                ->find_array();

            // Get top 5 most expensive assets
            $expensiveAssets = ORM::for_table('tbl_assets')
                ->select('tbl_assets.name')
                ->select('tbl_assets.asset_tag')
                ->select('tbl_assets.purchase_cost')
                ->select('tbl_asset_categories.name', 'category_name')
                ->join('tbl_asset_categories', ['tbl_assets.category_id', '=', 'tbl_asset_categories.id'])
                ->where_not_null('purchase_cost')
                ->where_not_equal('purchase_cost', '')
                ->where_not_equal('purchase_cost', 0)
                ->order_by_desc('purchase_cost')
                ->limit(5)
                ->find_array();

            // Get cost by category for chart
            $costByCategory = ORM::for_table('tbl_assets')
                ->select('tbl_asset_categories.name', 'category_name')
                ->select_expr('COALESCE(SUM(purchase_cost), 0)', 'total_cost')
                ->join('tbl_asset_categories', ['tbl_assets.category_id', '=', 'tbl_asset_categories.id'])
                ->where_not_null('purchase_cost')
                ->where_not_equal('purchase_cost', '')
                ->where_not_equal('purchase_cost', 0)
                ->where_gt('purchase_cost', 0)
                ->group_by('tbl_asset_categories.name')
                ->find_array();
        } catch (Exception $costException) {
            // If purchase_cost column doesn't exist, set default values
            $totalAssetValue = 0;
            $assetsByCostRange = [];
            $expensiveAssets = [];
            $costByCategory = [];
            _log(Lang::T('Asset Manager Cost Query Error (column may not exist): ') . $costException->getMessage());
        }
    } catch (Exception $e) {
        // If tables don't exist yet, set default values
        $totalAssets = 0;
        $activeAssets = 0;
        $inactiveAssets = 0;
        $maintenanceAssets = 0;
        $totalCategories = 0;
        $totalBrands = 0;
        $totalModels = 0;
        $recentAssets = [];
        $assetsByCategory = [];
        $assetsByStatus = [];
        $totalAssetValue = 0;
        $assetsByCostRange = [];
        $expensiveAssets = [];
        $costByCategory = [];

        _log(Lang::T('Asset Manager Dashboard Error: ') . $e->getMessage());
    }

    // Get currency code for UI display
    $currencyCode = isset($config['currency_code']) ? $config['currency_code'] : 'USD';

    $ui->assign('totalAssets', $totalAssets);
    $ui->assign('activeAssets', $activeAssets);
    $ui->assign('inactiveAssets', $inactiveAssets);
    $ui->assign('maintenanceAssets', $maintenanceAssets);
    $ui->assign('totalCategories', $totalCategories);
    $ui->assign('totalBrands', $totalBrands);
    $ui->assign('totalModels', $totalModels);
    $ui->assign('recentAssets', $recentAssets);
    $ui->assign('assetsByCategory', $assetsByCategory);
    $ui->assign('assetsByStatus', $assetsByStatus);
    $ui->assign('totalAssetValue', $totalAssetValue);
    $ui->assign('assetsByCostRange', $assetsByCostRange);
    $ui->assign('expensiveAssets', $expensiveAssets);
    $ui->assign('costByCategory', $costByCategory);
    $ui->assign('currencyCode', $currencyCode);

    $ui->display('assetManager_dashboard.tpl');
}

// Category Functions
function assetCategories()
{
    global $ui;

    try {
        $categories = ORM::for_table('tbl_asset_categories')
            ->select('tbl_asset_categories.*')
            ->select_expr('COUNT(tbl_assets.id)', 'asset_count')
            ->left_outer_join('tbl_assets', ['tbl_asset_categories.id', '=', 'tbl_assets.category_id'])
            ->group_by('tbl_asset_categories.id')
            ->order_by_desc('tbl_asset_categories.created_at');
    } catch (Exception $e) {
        $categories = [];
        _log(Lang::T("Asset Categories Error: ") . $e->getMessage());
    }

    $totalCategories = Paginator::findMany($categories, ['search' => ''], 25, '');
    $ui->assign('categories', $totalCategories);
    $ui->display('assetManager_categories.tpl');
}

function assetCategoriesAdd()
{
    global $ui;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = _post('name');
        $description = _post('description');
        $status = _post('status');

        if (empty($name)) {
            r2(getUrl('plugin/assetManager/categories-add'), 'e', Lang::T('Category name is required'));
        }

        // Check if category already exists
        $exists = ORM::for_table('tbl_asset_categories')->where('name', $name)->count();
        if ($exists > 0) {
            r2(getUrl('plugin/assetManager/categories-add'), 'e', Lang::T('Category with this name already exists'));
        }

        $category = ORM::for_table('tbl_asset_categories')->create();
        $category->name = $name;
        $category->description = $description;
        $category->status = $status;
        $category->save();

        r2(getUrl('plugin/assetManager/categories'), 's', Lang::T('Category added successfully'));
    }

    $ui->display('assetManager_categories_add.tpl');
}

function assetCategoriesEdit()
{
    global $ui, $routes;

    $id = $routes[3];
    if (empty($id)) {
        r2(getUrl('plugin/assetManager/categories'), 'e', Lang::T('Category ID is required'));
    }

    $category = ORM::for_table('tbl_asset_categories')->find_one($id);
    if (!$category) {
        r2(getUrl('plugin/assetManager/categories'), 'e', Lang::T('Category not found'));
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = _post('name');
        $description = _post('description');
        $status = _post('status');

        if (empty($name)) {
            r2(getUrl('plugin/assetManager/categories-edit/' . $id), 'e', Lang::T('Category name is required'));
        }

        // Check if category name already exists (excluding current)
        $exists = ORM::for_table('tbl_asset_categories')
            ->where('name', $name)
            ->where_not_equal('id', $id)
            ->count();
        if ($exists > 0) {
            r2(getUrl('plugin/assetManager/categories-edit/' . $id), 'e', Lang::T('Category with this name already exists'));
        }

        $category->name = $name;
        $category->description = $description;
        $category->status = $status;
        $category->save();

        r2(getUrl('plugin/assetManager/categories'), 's', Lang::T('Category updated successfully'));
    }

    $ui->assign('category', $category);
    $ui->display('assetManager_categories_edit.tpl');
}

function assetCategoriesDelete()
{
    global $routes;

    $id = $routes[3];
    if (empty($id)) {
        r2(getUrl('plugin/assetManager/categories'), 'e', Lang::T('Category ID is required'));
    }

    $category = ORM::for_table('tbl_asset_categories')->find_one($id);
    if (!$category) {
        r2(getUrl('plugin/assetManager/categories'), 'e', Lang::T('Category not found'));
    }

    // Check if category has assets
    $assetCount = ORM::for_table('tbl_assets')->where('category_id', $id)->count();
    if ($assetCount > 0) {
        r2(getUrl('plugin/assetManager/categories'), 'e', Lang::T('Cannot delete category that has assets assigned to it'));
    }

    $category->delete();
    r2(getUrl('plugin/assetManager/categories'), 's', Lang::T('Category deleted successfully'));
}

// Brand Functions
function assetBrands()
{
    global $ui;

    try {
        $brands = ORM::for_table('tbl_asset_brands')
            ->select('tbl_asset_brands.*')
            ->select_expr('COUNT(DISTINCT tbl_asset_models.id)', 'model_count')
            ->select_expr('COUNT(DISTINCT tbl_assets.id)', 'asset_count')
            ->left_outer_join('tbl_asset_models', ['tbl_asset_brands.id', '=', 'tbl_asset_models.brand_id'])
            ->left_outer_join('tbl_assets', ['tbl_asset_brands.id', '=', 'tbl_assets.brand_id'])
            ->group_by('tbl_asset_brands.id')
            ->order_by_desc('tbl_asset_brands.created_at');
    } catch (Exception $e) {
        $brands = [];
        _log(Lang::T("Asset Brands Error: ") . $e->getMessage());
    }

    $totalBrands = Paginator::findMany($brands, ['search' => ''], 25, '');
    $ui->assign('brands', $totalBrands);
    $ui->display('assetManager_brands.tpl');
}

function assetBrandsAdd()
{
    global $ui;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = _post('name');
        $description = _post('description');
        $country = _post('country');
        $website = _post('website');
        $status = _post('status');

        if (empty($name)) {
            r2(getUrl('plugin/assetManager/brands-add'), 'e', Lang::T('Brand name is required'));
        }

        // Check if brand already exists
        $exists = ORM::for_table('tbl_asset_brands')->where('name', $name)->count();
        if ($exists > 0) {
            r2(getUrl('plugin/assetManager/brands-add'), 'e', Lang::T('Brand with this name already exists'));
        }

        $brand = ORM::for_table('tbl_asset_brands')->create();
        $brand->name = $name;
        $brand->description = $description;
        $brand->country = $country;
        $brand->website = $website;
        $brand->status = $status;
        $brand->save();

        r2(getUrl('plugin/assetManager/brands'), 's', Lang::T('Brand added successfully'));
    }

    $ui->display('assetManager_brands_add.tpl');
}

function assetBrandsEdit()
{
    global $ui, $routes;

    $id = $routes[3];
    if (empty($id)) {
        r2(getUrl('plugin/assetManager/brands'), 'e', Lang::T('Brand ID is required'));
    }

    $brand = ORM::for_table('tbl_asset_brands')->find_one($id);
    if (!$brand) {
        r2(getUrl('plugin/assetManager/brands'), 'e', Lang::T('Brand not found'));
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = _post('name');
        $description = _post('description');
        $country = _post('country');
        $website = _post('website');
        $status = _post('status');

        if (empty($name)) {
            r2(getUrl('plugin/assetManager/brands-edit/' . $id), 'e', Lang::T('Brand name is required'));
        }

        // Check if brand name already exists (excluding current)
        $exists = ORM::for_table('tbl_asset_brands')
            ->where('name', $name)
            ->where_not_equal('id', $id)
            ->count();
        if ($exists > 0) {
            r2(getUrl('plugin/assetManager/brands-edit/' . $id), 'e', Lang::T('Brand with this name already exists'));
        }

        $brand->name = $name;
        $brand->description = $description;
        $brand->country = $country;
        $brand->website = $website;
        $brand->status = $status;
        $brand->save();

        r2(getUrl('plugin/assetManager/brands'), 's', Lang::T('Brand updated successfully'));
    }

    $ui->assign('brand', $brand);
    $ui->display('assetManager_brands_edit.tpl');
}

function assetBrandsDelete()
{
    global $routes;

    $id = $routes[3];
    if (empty($id)) {
        r2(getUrl('plugin/assetManager/brands'), 'e', Lang::T('Brand ID is required'));
    }

    $brand = ORM::for_table('tbl_asset_brands')->find_one($id);
    if (!$brand) {
        r2(getUrl('plugin/assetManager/brands'), 'e', Lang::T('Brand not found'));
    }

    // Check if brand has models or assets
    $modelCount = ORM::for_table('tbl_asset_models')->where('brand_id', $id)->count();
    $assetCount = ORM::for_table('tbl_assets')->where('brand_id', $id)->count();

    if ($modelCount > 0 || $assetCount > 0) {
        r2(getUrl('plugin/assetManager/brands'), 'e', Lang::T('Cannot delete brand that has models or assets assigned to it'));
    }

    $brand->delete();
    r2(getUrl('plugin/assetManager/brands'), 's', Lang::T('Brand deleted successfully'));
}

// Model Functions
function assetModels()
{
    global $ui;

    try {
        $models = ORM::for_table('tbl_asset_models')
            ->select('tbl_asset_models.*')
            ->select('tbl_asset_brands.name', 'brand_name')
            ->select_expr('COUNT(tbl_assets.id)', 'asset_count')
            ->join('tbl_asset_brands', ['tbl_asset_models.brand_id', '=', 'tbl_asset_brands.id'])
            ->left_outer_join('tbl_assets', ['tbl_asset_models.id', '=', 'tbl_assets.model_id'])
            ->group_by('tbl_asset_models.id')
            ->order_by_desc('tbl_asset_models.created_at');
    } catch (Exception $e) {
        $models = [];
        _log("Asset Models Error: " . $e->getMessage());
    }

    $totalModels = Paginator::findMany($models, ['search' => ''], 25, '');
    $ui->assign('models', $totalModels);
    $ui->display('assetManager_models.tpl');
}

function assetModelsAdd()
{
    global $ui;

    try {
        $brands = ORM::for_table('tbl_asset_brands')->where('status', 'Active')->order_by_asc('name')->find_array();
    } catch (Exception $e) {
        $brands = [];
        _log(Lang::T("Asset Models Add - Brands Error: ") . $e->getMessage());
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $brand_id = _post('brand_id');
        $name = _post('name');
        $model_number = _post('model_number');
        $description = _post('description');
        $specifications = _post('specifications');
        $status = _post('status');

        if (empty($name) || empty($brand_id)) {
            r2(getUrl('plugin/assetManager/models-add'), 'e', Lang::T('Model name and brand are required'));
        }

        try {
            $model = ORM::for_table('tbl_asset_models')->create();
            $model->brand_id = $brand_id;
            $model->name = $name;
            $model->model_number = $model_number;
            $model->description = $description;
            $model->specifications = $specifications;
            $model->status = $status;
            $model->save();

            r2(getUrl('plugin/assetManager/models'), 's', Lang::T('Model added successfully'));
        } catch (Exception $e) {
            _log(Lang::T("Asset Models Add Error: ") . $e->getMessage());
            r2(getUrl('plugin/assetManager/models-add'), 'e', Lang::T('Error adding model: ') . $e->getMessage());
        }
    }

    $ui->assign('brands', $brands);
    $ui->display('assetManager_models_add.tpl');
}

function assetModelsEdit()
{
    global $ui, $routes;

    $id = $routes[3];
    if (empty($id)) {
        r2(getUrl('plugin/assetManager/models'), 'e', Lang::T('Model ID is required'));
    }

    $model = ORM::for_table('tbl_asset_models')->find_one($id);
    if (!$model) {
        r2(getUrl('plugin/assetManager/models'), 'e', Lang::T('Model not found'));
    }

    $brands = ORM::for_table('tbl_asset_brands')->where('status', 'Active')->order_by_asc('name')->find_array();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $brand_id = _post('brand_id');
        $name = _post('name');
        $model_number = _post('model_number');
        $description = _post('description');
        $specifications = _post('specifications');
        $status = _post('status');

        if (empty($name) || empty($brand_id)) {
            r2(getUrl('plugin/assetManager/models-edit/' . $id), 'e', Lang::T('Model name and brand are required'));
        }

        $model->brand_id = $brand_id;
        $model->name = $name;
        $model->model_number = $model_number;
        $model->description = $description;
        $model->specifications = $specifications;
        $model->status = $status;
        $model->save();

        r2(getUrl('plugin/assetManager/models'), 's', Lang::T('Model updated successfully'));
    }

    $ui->assign('model', $model);
    $ui->assign('brands', $brands);
    $ui->display('assetManager_models_edit.tpl');
}

function assetModelsDelete()
{
    global $routes;

    $id = $routes[3];
    if (empty($id)) {
        r2(getUrl('plugin/assetManager/models'), 'e', Lang::T('Model ID is required'));
    }

    $model = ORM::for_table('tbl_asset_models')->find_one($id);
    if (!$model) {
        r2(getUrl('plugin/assetManager/models'), 'e', Lang::T('Model not found'));
    }

    // Check if model has assets
    $assetCount = ORM::for_table('tbl_assets')->where('model_id', $id)->count();
    if ($assetCount > 0) {
        r2(getUrl('plugin/assetManager/models'), 'e', Lang::T('Cannot delete model that has assets assigned to it'));
    }

    $model->delete();
    r2(getUrl('plugin/assetManager/models'), 's', Lang::T('Model deleted successfully'));
}

// Asset Functions
function assetsList()
{
    global $ui;

    try {
        $assets = ORM::for_table('tbl_assets')
            ->select('tbl_assets.*')
            ->select('tbl_asset_categories.name', 'category_name')
            ->select('tbl_asset_brands.name', 'brand_name')
            ->select('tbl_asset_models.name', 'model_name')
            ->select('tbl_customers.fullname', 'assigned_to_name')
            ->join('tbl_asset_categories', ['tbl_assets.category_id', '=', 'tbl_asset_categories.id'])
            ->join('tbl_asset_brands', ['tbl_assets.brand_id', '=', 'tbl_asset_brands.id'])
            ->join('tbl_asset_models', ['tbl_assets.model_id', '=', 'tbl_asset_models.id'])
            ->left_outer_join('tbl_customers', ['tbl_assets.assigned_to', '=', 'tbl_customers.id'])
            ->order_by_desc('tbl_assets.created_at');
    } catch (Exception $e) {
        $assets = [];
        _log(Lang::T('Assets List Error: ') . $e->getMessage());
    }

    $totalAssets = Paginator::findMany($assets, ['search' => ''], 25, '');

    // Get currency code from config
    global $config;
    $currencyCode = isset($config['currency_code']) ? $config['currency_code'] : 'USD';

    $ui->assign('assets', $totalAssets);
    $ui->assign('currencyCode', $currencyCode);
    $ui->display('assetManager_assets.tpl');
}

function assetsAdd()
{
    global $ui;

    try {
        $categories = ORM::for_table('tbl_asset_categories')->where('status', 'Active')->order_by_asc('name')->find_array();
        $brands = ORM::for_table('tbl_asset_brands')->where('status', 'Active')->order_by_asc('name')->find_array();
        $customers = ORM::for_table('tbl_customers')->order_by_asc('id')->find_array();
    } catch (Exception $e) {
        $categories = [];
        $brands = [];
        $customers = [];
        _log(Lang::T('Assets Add - Data Error: ') . $e->getMessage());
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $category_id = _post('category_id');
        $brand_id = _post('brand_id');
        $model_id = _post('model_id');
        $asset_tag = _post('asset_tag');
        $serial_number = _post('serial_number');
        $name = _post('name');
        $description = _post('description');
        $purchase_date = _post('purchase_date');
        $purchase_cost = _post('purchase_cost');
        $warranty_expiry = _post('warranty_expiry');
        $location = _post('location');
        $lat = _post('lat');
        $lng = _post('lng');
        $assigned_to = _post('assigned_to');
        $status = _post('status');
        $condition_status = _post('condition_status');
        $notes = _post('notes');

        // Handle empty dates - convert empty strings to NULL for database
        if (empty($purchase_date)) {
            $purchase_date = null;
        }
        if (empty($warranty_expiry)) {
            $warranty_expiry = null;
        }

        // Handle empty assigned_to - convert empty strings to NULL for database
        if (empty($assigned_to)) {
            $assigned_to = null;
        }

        // Handle coordinates - validate and convert to proper decimal values
        $latitude = null;
        $longitude = null;
        if (!empty($lat) && is_numeric($lat)) {
            $latitude = (float)$lat;
        }
        if (!empty($lng) && is_numeric($lng)) {
            $longitude = (float)$lng;
        }

        if (empty($name) || empty($category_id) || empty($brand_id) || empty($model_id) || empty($asset_tag)) {
            r2(getUrl('plugin/assetManager/assets-add'), 'e', Lang::T('Required fields are missing'));
        }

        try {
            // Check if asset tag already exists
            $exists = ORM::for_table('tbl_assets')->where('asset_tag', $asset_tag)->count();
            if ($exists > 0) {
                r2(getUrl('plugin/assetManager/assets-add'), 'e', Lang::T('Asset tag already exists'));
            }

            $admin = Admin::_info();

            $asset = ORM::for_table('tbl_assets')->create();
            $asset->category_id = $category_id;
            $asset->brand_id = $brand_id;
            $asset->model_id = $model_id;
            $asset->asset_tag = $asset_tag;
            $asset->serial_number = $serial_number;
            $asset->name = $name;
            $asset->description = $description;
            $asset->purchase_date = $purchase_date;
            $asset->purchase_cost = $purchase_cost;
            $asset->warranty_expiry = $warranty_expiry;
            $asset->location = $location;
            $asset->latitude = $latitude;
            $asset->longitude = $longitude;
            $asset->assigned_to = $assigned_to;
            $asset->status = $status;
            $asset->condition_status = $condition_status;
            $asset->notes = $notes;
            $asset->created_by = $admin['id'];
            $asset->save();

            r2(getUrl('plugin/assetManager/assets'), 's', Lang::T('Asset added successfully'));
        } catch (Exception $e) {
            _log("Assets Add Error: " . $e->getMessage());
            r2(getUrl('plugin/assetManager/assets-add'), 'e', Lang::T('Error adding asset: ') . $e->getMessage());
        }
    }

    $ui->assign('categories', $categories);
    $ui->assign('brands', $brands);
    $ui->assign('customers', $customers);
    $ui->display('assetManager_assets_add.tpl');
}

function assetsEdit()
{
    global $ui, $routes;

    $id = $routes[3];
    if (empty($id)) {
        r2(getUrl('plugin/assetManager/assets'), 'e', Lang::T('Asset ID is required'));
    }

    $asset = ORM::for_table('tbl_assets')->find_one($id);
    if (!$asset) {
        r2(getUrl('plugin/assetManager/assets'), 'e', Lang::T('Asset not found'));
    }

    $categories = ORM::for_table('tbl_asset_categories')->where('status', 'Active')->order_by_asc('name')->find_array();
    $brands = ORM::for_table('tbl_asset_brands')->where('status', 'Active')->order_by_asc('name')->find_array();
    $models = ORM::for_table('tbl_asset_models')->where('brand_id', $asset['brand_id'])->where('status', 'Active')->order_by_asc('name')->find_array();
    $customers = ORM::for_table('tbl_customers')->order_by_asc('id')->find_array();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $category_id = _post('category_id');
        $brand_id = _post('brand_id');
        $model_id = _post('model_id');
        $asset_tag = _post('asset_tag');
        $serial_number = _post('serial_number');
        $name = _post('name');
        $description = _post('description');
        $purchase_date = _post('purchase_date');
        $purchase_cost = _post('purchase_cost');
        $warranty_expiry = _post('warranty_expiry');
        $location = _post('location');
        $lat = _post('lat');
        $lng = _post('lng');
        $assigned_to = _post('assigned_to');
        $status = _post('status');
        $condition_status = _post('condition_status');
        $notes = _post('notes');

        // Handle empty dates - convert empty strings to NULL for database
        if (empty($purchase_date)) {
            $purchase_date = null;
        }
        if (empty($warranty_expiry)) {
            $warranty_expiry = null;
        }

        // Handle empty assigned_to - convert empty strings to NULL for database
        if (empty($assigned_to)) {
            $assigned_to = null;
        }

        // Handle coordinates - validate and convert to proper decimal values
        $latitude = null;
        $longitude = null;
        if (!empty($lat) && is_numeric($lat)) {
            $latitude = (float)$lat;
        }
        if (!empty($lng) && is_numeric($lng)) {
            $longitude = (float)$lng;
        }

        if (empty($name) || empty($category_id) || empty($brand_id) || empty($model_id) || empty($asset_tag)) {
            r2(getUrl('plugin/assetManager/assets-edit/' . $id), 'e', Lang::T('Required fields are missing'));
        }

        // Check if asset tag already exists (excluding current)
        $exists = ORM::for_table('tbl_assets')
            ->where('asset_tag', $asset_tag)
            ->where_not_equal('id', $id)
            ->count();
        if ($exists > 0) {
            r2(getUrl('plugin/assetManager/assets-edit/' . $id), 'e', Lang::T('Asset tag already exists'));
        }

        $asset->category_id = $category_id;
        $asset->brand_id = $brand_id;
        $asset->model_id = $model_id;
        $asset->asset_tag = $asset_tag;
        $asset->serial_number = $serial_number;
        $asset->name = $name;
        $asset->description = $description;
        $asset->purchase_date = $purchase_date;
        $asset->purchase_cost = $purchase_cost;
        $asset->warranty_expiry = $warranty_expiry;
        $asset->location = $location;
        $asset->latitude = $latitude;
        $asset->longitude = $longitude;
        $asset->assigned_to = $assigned_to;
        $asset->status = $status;
        $asset->condition_status = $condition_status;
        $asset->notes = $notes;
        $asset->save();

        r2(getUrl('plugin/assetManager/assets'), 's', Lang::T('Asset updated successfully'));
    }

    $ui->assign('asset', $asset);
    $ui->assign('categories', $categories);
    $ui->assign('brands', $brands);
    $ui->assign('models', $models);
    $ui->assign('customers', $customers);
    $ui->display('assetManager_assets_edit.tpl');
}

function assetsView()
{
    global $ui, $routes;

    $id = $routes[3];
    if (empty($id)) {
        r2(getUrl('plugin/assetManager/assets'), 'e', Lang::T('Asset ID is required'));
    }

    // Get asset with related data
    $asset = ORM::for_table('tbl_assets')
        ->select('tbl_assets.*')
        ->select('tbl_asset_categories.name', 'category_name')
        ->select('tbl_asset_brands.name', 'brand_name')
        ->select('tbl_asset_models.name', 'model_name')
        ->select('tbl_customers.fullname', 'assigned_name')
        ->left_outer_join('tbl_asset_categories', ['tbl_assets.category_id', '=', 'tbl_asset_categories.id'])
        ->left_outer_join('tbl_asset_brands', ['tbl_assets.brand_id', '=', 'tbl_asset_brands.id'])
        ->left_outer_join('tbl_asset_models', ['tbl_assets.model_id', '=', 'tbl_asset_models.id'])
        ->left_outer_join('tbl_customers', ['tbl_assets.assigned_to', '=', 'tbl_customers.id'])
        ->find_one($id);

    if (!$asset) {
        r2(getUrl('plugin/assetManager/assets'), 'e', Lang::T('Asset not found'));
    }

    // Convert ORM object to array for template
    $assetData = $asset->as_array();

    // Add assigned customer name
    $assetData['assigned_name'] = $asset->assigned_to ? $asset->assigned_name : Lang::T('Not Assigned');
    // Add related names
    $assetData['category_name'] = $asset->category_name ?: Lang::T('Not Assigned');
    $assetData['brand_name'] = $asset->brand_name ?: Lang::T('Not Assigned');
    $assetData['model_name'] = $asset->model_name ?: Lang::T('Not Assigned');

    // Get currency code from config
    global $config;
    $currencyCode = isset($config['currency_code']) ? $config['currency_code'] : 'USD';

    $ui->assign('asset', $assetData);
    $ui->assign('currencyCode', $currencyCode);
    $ui->assign('_title', Lang::T('View Asset - ') . $assetData['name']);
    $ui->display('assetManager_assets_view.tpl');
}

function assetsDelete()
{
    global $routes;

    $id = $routes[3];
    if (empty($id)) {
        r2(getUrl('plugin/assetManager/assets'), 'e', Lang::T('Asset ID is required'));
    }

    $asset = ORM::for_table('tbl_assets')->find_one($id);
    if (!$asset) {
        r2(getUrl('plugin/assetManager/assets'), 'e', Lang::T('Asset not found'));
    }

    $asset->delete();
    r2(getUrl('plugin/assetManager/assets'), 's', Lang::T('Asset deleted successfully'));
}

// AJAX Function to get models by brand
function getModelsByBrand()
{
    // Force JSON response header early
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');

    try {
        // Check authentication
        $admin = Admin::_info();
        if (!$admin) {
            _log("getModelsByBrand: No admin authentication");
            echo json_encode(['success' => false, 'message' => Lang::T('Authentication required')]);
            exit;
        }

        // Check user permissions
        if (!in_array($admin['user_type'], ['SuperAdmin', 'Admin', 'Sales'])) {
            _log("getModelsByBrand: Access denied for user type: " . $admin['user_type']);
            echo json_encode(['success' => false, 'message' => Lang::T('Access denied')]);
            exit;
        }

        $brand_id = _get('brand_id');
        if (empty($brand_id)) {
            _log("getModelsByBrand: No brand_id provided");
            echo json_encode(['success' => false, 'message' => Lang::T('Brand ID is required')]);
            exit;
        }

        // Check if the brand exists
        $brandExists = ORM::for_table('tbl_asset_brands')->find_one($brand_id);
        if (!$brandExists) {
            _log(Lang::T("getModelsByBrand: Brand not found for ID: ") . $brand_id);
            echo json_encode(['success' => false, 'message' => Lang::T('Brand not found')]);
            exit;
        }

        $models = ORM::for_table('tbl_asset_models')
            ->where('brand_id', $brand_id)
            ->where('status', 'Active')
            ->order_by_asc('name')
            ->find_array();

        _log(Lang::T("getModelsByBrand: Found ") . count($models) . Lang::T(" models for brand ") . $brand_id);
        echo json_encode(['success' => true, 'models' => $models, 'count' => count($models)]);
    } catch (Exception $e) {
        _log(Lang::T("getModelsByBrand Error: ") . $e->getMessage());
        echo json_encode(['success' => false, 'message' => Lang::T('Database error: ') . $e->getMessage()]);
    }
    exit;
}

/**
 * Handler for marking welcome message as seen
 */
function asset_welcome_seen()
{
    _admin();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => Lang::T('Invalid request method')]);
        exit;
    }

    try {
        $exists = ORM::for_table('tbl_appconfig')
            ->where('setting', 'asset_welcome_message_viewed')
            ->count();

        if ($exists) {
            ORM::for_table('tbl_appconfig')
                ->where('setting', 'asset_welcome_message_viewed')
                ->find_one()
                ->set('value', 'yes')
                ->save();
        } else {
            ORM::for_table('tbl_appconfig')->create()
                ->set('setting', 'asset_welcome_message_viewed')
                ->set('value', 'yes')
                ->save();
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ============================================
// ASSET REPORTS FUNCTIONS
// ============================================

function assetReports()
{
    global $ui, $config;

    // Get currency code from config
    $currencyCode = isset($config['currency_code']) ? $config['currency_code'] : 'USD';

    // Get categories for filter
    $categories = ORM::for_table('tbl_asset_categories')
        ->where('status', 'Active')
        ->order_by_asc('name')
        ->find_array();

    // Get brands for filter
    $brands = ORM::for_table('tbl_asset_brands')
        ->where('status', 'Active')
        ->order_by_asc('name')
        ->find_array();

    // Get models for filter
    $models = ORM::for_table('tbl_asset_models')
        ->where('status', 'Active')
        ->order_by_asc('name')
        ->find_array();

    // Get assigned_to values from assets for assignment filter
    $assignedToValues = ORM::for_table('tbl_assets')
        ->select('tbl_assets.assigned_to')
        ->select('tbl_customers.fullname', 'assigned_name')
        ->select('tbl_customers.username', 'assigned_username')
        ->select('tbl_customers.email', 'assigned_email')
        ->join('tbl_customers', ['tbl_assets.assigned_to', '=', 'tbl_customers.id'])
        ->where_not_null('tbl_assets.assigned_to')
        ->where_not_equal('tbl_assets.assigned_to', '')
        ->group_by('tbl_assets.assigned_to')
        ->order_by_asc('tbl_customers.fullname')
        ->find_array();

    $ui->assign('categories', $categories);
    $ui->assign('brands', $brands);
    $ui->assign('models', $models);
    $ui->assign('assignedToValues', $assignedToValues);
    $ui->assign('currencyCode', $currencyCode);
    $ui->assign('_title', Lang::T('Asset Reports'));
    $ui->display('assetManager_reports.tpl');
}

function generateAssetReport()
{
    global $ui, $config;

    // Get POST parameters
    $reportType = $_POST['report_type'] ?? 'summary';
    $categoryId = $_POST['category_id'] ?? '';
    $brandId = $_POST['brand_id'] ?? '';
    $modelId = $_POST['model_id'] ?? '';
    $status = $_POST['status'] ?? '';
    $dateFrom = $_POST['date_from'] ?? '';
    $dateTo = $_POST['date_to'] ?? '';
    $costFrom = $_POST['cost_from'] ?? '';
    $costTo = $_POST['cost_to'] ?? '';

    // Get currency code from config
    $currencyCode = isset($config['currency_code']) ? $config['currency_code'] : 'USD';

    $data = [];

    try {
        switch ($reportType) {
            case 'detailed':
                $data = generateDetailedReport($categoryId, $brandId, $modelId, $status, $dateFrom, $dateTo, $costFrom, $costTo);
                break;
            case 'category':
                $data = generateCategoryReport($categoryId, $dateFrom, $dateTo);
                break;
            case 'brand':
                $data = generateBrandReport($brandId, $dateFrom, $dateTo);
                break;
            case 'status':
                $data = generateStatusReport($status, $dateFrom, $dateTo);
                break;
            case 'assigned':
                $assignedTo = $_POST['assigned_to'] ?? '';
                $data = generateAssignmentReport($assignedTo, $dateFrom, $dateTo);
                break;
            case 'cost':
                $data = generateCostAnalysisReport($costFrom, $costTo, $dateFrom, $dateTo);
                break;
            case 'warranty':
                $data = generateWarrantyReport($dateFrom, $dateTo);
                break;
            default:
                $data = generateSummaryReport();
                break;
        }

        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $data,
            'currencyCode' => $currencyCode,
            'reportType' => $reportType
        ]);
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Error generating report: ' . $e->getMessage()
        ]);
    }
    exit;
}

function exportAssetReport()
{
    global $config;

    // Get POST parameters
    $reportType = $_POST['report_type'] ?? 'summary';
    $exportFormat = $_POST['export_format'] ?? 'csv';
    $categoryId = $_POST['category_id'] ?? '';
    $brandId = $_POST['brand_id'] ?? '';
    $modelId = $_POST['model_id'] ?? '';
    $status = $_POST['status'] ?? '';
    $dateFrom = $_POST['date_from'] ?? '';
    $dateTo = $_POST['date_to'] ?? '';
    $costFrom = $_POST['cost_from'] ?? '';
    $costTo = $_POST['cost_to'] ?? '';

    try {
        // Generate the report data
        switch ($reportType) {
            case 'detailed':
                $data = generateDetailedReport($categoryId, $brandId, $modelId, $status, $dateFrom, $dateTo, $costFrom, $costTo);
                break;
            case 'category':
                $data = generateCategoryReport($categoryId, $dateFrom, $dateTo);
                break;
            case 'brand':
                $data = generateBrandReport($brandId, $dateFrom, $dateTo);
                break;
            case 'status':
                $data = generateStatusReport($status, $dateFrom, $dateTo);
                break;
            case 'assigned':
                $assignedTo = $_POST['assigned_to'] ?? '';
                $data = generateAssignmentReport($assignedTo, $dateFrom, $dateTo);
                break;
            case 'cost':
                $data = generateCostAnalysisReport($costFrom, $costTo, $dateFrom, $dateTo);
                break;
            case 'warranty':
                $data = generateWarrantyReport($dateFrom, $dateTo);
                break;
            default:
                $data = generateSummaryReport();
                break;
        }

        // Export based on format
        if ($exportFormat === 'csv') {
            exportToCSV($data, $reportType);
        } else if ($exportFormat === 'pdf') {
            exportToPDF($data, $reportType);
        } else {
            exportToExcel($data, $reportType);
        }
    } catch (Exception $e) {
        r2(getUrl('plugin/assetManager/reports'), 'e', 'Error exporting report: ' . $e->getMessage());
    }
}

// Helper functions for different report types
function generateSummaryReport()
{
    $summary = [];

    // Total assets
    $totalAssets = ORM::for_table('tbl_assets')->count();

    // Assets by status
    $statusCounts = ORM::for_table('tbl_assets')
        ->select('status')
        ->select_expr('COUNT(*)', 'count')
        ->group_by('status')
        ->find_array();

    // Assets by category
    $categoryCounts = ORM::for_table('tbl_assets')
        ->select('tbl_asset_categories.name', 'category_name')
        ->select_expr('COUNT(*)', 'count')
        ->join('tbl_asset_categories', ['tbl_assets.category_id', '=', 'tbl_asset_categories.id'])
        ->group_by('tbl_assets.category_id')
        ->find_array();

    // Total asset value
    $totalValue = ORM::for_table('tbl_assets')
        ->select_expr('SUM(CAST(purchase_cost AS DECIMAL(10,2)))', 'total_cost')
        ->find_one();

    $summary = [
        'total_assets' => $totalAssets,
        'status_breakdown' => $statusCounts,
        'category_breakdown' => $categoryCounts,
        'total_value' => $totalValue ? $totalValue->total_cost : 0
    ];

    return $summary;
}

function generateDetailedReport($categoryId, $brandId, $modelId, $status, $dateFrom, $dateTo, $costFrom, $costTo)
{
    $query = ORM::for_table('tbl_assets')
        ->select('tbl_assets.*')
        ->select('tbl_asset_categories.name', 'category_name')
        ->select('tbl_asset_brands.name', 'brand_name')
        ->select('tbl_asset_models.name', 'model_name')
        ->left_outer_join('tbl_asset_categories', ['tbl_assets.category_id', '=', 'tbl_asset_categories.id'])
        ->left_outer_join('tbl_asset_brands', ['tbl_assets.brand_id', '=', 'tbl_asset_brands.id'])
        ->left_outer_join('tbl_asset_models', ['tbl_assets.model_id', '=', 'tbl_asset_models.id']);

    // Apply filters
    if (!empty($categoryId)) {
        $query->where('tbl_assets.category_id', $categoryId);
    }
    if (!empty($brandId)) {
        $query->where('tbl_assets.brand_id', $brandId);
    }
    if (!empty($modelId)) {
        $query->where('tbl_assets.model_id', $modelId);
    }
    if (!empty($status)) {
        $query->where('status', $status);
    }
    if (!empty($dateFrom)) {
        $query->where_gte('purchase_date', $dateFrom);
    }
    if (!empty($dateTo)) {
        $query->where_lte('purchase_date', $dateTo);
    }
    if (!empty($costFrom)) {
        $query->where_gte('purchase_cost', $costFrom);
    }
    if (!empty($costTo)) {
        $query->where_lte('purchase_cost', $costTo);
    }

    return $query->order_by_desc('created_at')->find_array();
}

function generateCategoryReport($categoryId, $dateFrom, $dateTo)
{
    $query = ORM::for_table('tbl_asset_categories')
        ->select('tbl_asset_categories.*')
        ->select_expr('COUNT(tbl_assets.id)', 'asset_count')
        ->select_expr('SUM(CAST(tbl_assets.purchase_cost AS DECIMAL(10,2)))', 'total_value')
        ->left_outer_join('tbl_assets', ['tbl_asset_categories.id', '=', 'tbl_assets.category_id'])
        ->group_by('tbl_asset_categories.id');

    if (!empty($categoryId)) {
        $query->where('tbl_asset_categories.id', $categoryId);
    }
    if (!empty($dateFrom)) {
        $query->where_gte('tbl_assets.purchase_date', $dateFrom);
    }
    if (!empty($dateTo)) {
        $query->where_lte('tbl_assets.purchase_date', $dateTo);
    }

    return $query->find_array();
}

function generateBrandReport($brandId, $dateFrom, $dateTo)
{
    $query = ORM::for_table('tbl_asset_brands')
        ->select('tbl_asset_brands.*')
        ->select_expr('COUNT(tbl_assets.id)', 'asset_count')
        ->select_expr('SUM(CAST(tbl_assets.purchase_cost AS DECIMAL(10,2)))', 'total_value')
        ->left_outer_join('tbl_assets', ['tbl_asset_brands.id', '=', 'tbl_assets.brand_id'])
        ->group_by('tbl_asset_brands.id');

    if (!empty($brandId)) {
        $query->where('tbl_asset_brands.id', $brandId);
    }
    if (!empty($dateFrom)) {
        $query->where_gte('tbl_assets.purchase_date', $dateFrom);
    }
    if (!empty($dateTo)) {
        $query->where_lte('tbl_assets.purchase_date', $dateTo);
    }

    return $query->find_array();
}

function generateStatusReport($status, $dateFrom, $dateTo)
{
    $query = ORM::for_table('tbl_assets')
        ->select('tbl_assets.*')
        ->select('tbl_asset_categories.name', 'category_name')
        ->select('tbl_asset_brands.name', 'brand_name')
        ->select('tbl_asset_models.name', 'model_name')
        ->left_outer_join('tbl_asset_categories', ['tbl_assets.category_id', '=', 'tbl_asset_categories.id'])
        ->left_outer_join('tbl_asset_brands', ['tbl_assets.brand_id', '=', 'tbl_asset_brands.id'])
        ->left_outer_join('tbl_asset_models', ['tbl_assets.model_id', '=', 'tbl_asset_models.id']);

    if (!empty($status)) {
        $query->where('status', $status);
    }
    if (!empty($dateFrom)) {
        $query->where_gte('purchase_date', $dateFrom);
    }
    if (!empty($dateTo)) {
        $query->where_lte('purchase_date', $dateTo);
    }

    return $query->order_by_desc('created_at')->find_array();
}

function generateCostAnalysisReport($costFrom, $costTo, $dateFrom, $dateTo)
{
    $query = ORM::for_table('tbl_assets')
        ->select('tbl_assets.*')
        ->select('tbl_asset_categories.name', 'category_name')
        ->select('tbl_asset_brands.name', 'brand_name')
        ->select('tbl_asset_models.name', 'model_name')
        ->left_outer_join('tbl_asset_categories', ['tbl_assets.category_id', '=', 'tbl_asset_categories.id'])
        ->left_outer_join('tbl_asset_brands', ['tbl_assets.brand_id', '=', 'tbl_asset_brands.id'])
        ->left_outer_join('tbl_asset_models', ['tbl_assets.model_id', '=', 'tbl_asset_models.id']);

    if (!empty($costFrom)) {
        $query->where_gte('purchase_cost', $costFrom);
    }
    if (!empty($costTo)) {
        $query->where_lte('purchase_cost', $costTo);
    }
    if (!empty($dateFrom)) {
        $query->where_gte('purchase_date', $dateFrom);
    }
    if (!empty($dateTo)) {
        $query->where_lte('purchase_date', $dateTo);
    }

    return $query->order_by_desc('purchase_cost')->find_array();
}

function generateWarrantyReport($dateFrom, $dateTo)
{
    $query = ORM::for_table('tbl_assets')
        ->select('tbl_assets.*')
        ->select('tbl_asset_categories.name', 'category_name')
        ->select('tbl_asset_brands.name', 'brand_name')
        ->select('tbl_asset_models.name', 'model_name')
        ->left_outer_join('tbl_asset_categories', ['tbl_assets.category_id', '=', 'tbl_asset_categories.id'])
        ->left_outer_join('tbl_asset_brands', ['tbl_assets.brand_id', '=', 'tbl_asset_brands.id'])
        ->left_outer_join('tbl_asset_models', ['tbl_assets.model_id', '=', 'tbl_asset_models.id'])
        ->where_not_null('warranty_expiry')
        ->where_not_equal('warranty_expiry', '0000-00-00');

    if (!empty($dateFrom)) {
        $query->where_gte('warranty_expiry', $dateFrom);
    }
    if (!empty($dateTo)) {
        $query->where_lte('warranty_expiry', $dateTo);
    }

    return $query->order_by_asc('warranty_expiry')->find_array();
}

function generateAssignmentReport($assignedTo, $dateFrom, $dateTo)
{
    $query = ORM::for_table('tbl_assets')
        ->select('tbl_assets.name')
        ->select('tbl_assets.asset_tag')
        ->select('tbl_assets.created_at')
        ->select('tbl_assets.updated_at')
        ->select('tbl_assets.status')
        ->select('tbl_asset_categories.name', 'category_name')
        ->select('tbl_asset_brands.name', 'brand_name')
        ->select('tbl_asset_models.name', 'model_name')
        ->left_outer_join('tbl_asset_categories', ['tbl_assets.category_id', '=', 'tbl_asset_categories.id'])
        ->left_outer_join('tbl_asset_brands', ['tbl_assets.brand_id', '=', 'tbl_asset_brands.id'])
        ->left_outer_join('tbl_asset_models', ['tbl_assets.model_id', '=', 'tbl_asset_models.id']);

    // Filter by assignment
    if ($assignedTo === 'unassigned') {
        $query->where_raw('(tbl_assets.assigned_to IS NULL OR tbl_assets.assigned_to = "")');
    } elseif (!empty($assignedTo)) {
        $query->where('tbl_assets.assigned_to', $assignedTo);
        $query->left_outer_join('tbl_customers', ['tbl_assets.assigned_to', '=', 'tbl_customers.id']);
        $query->select('tbl_customers.fullname', 'assigned_to_customer');
    }

    // Date filters
    if (!empty($dateFrom)) {
        $query->where_gte('tbl_assets.created_at', $dateFrom);
    }
    if (!empty($dateTo)) {
        $query->where_lte('tbl_assets.created_at', $dateTo . ' 23:59:59');
    }

    return $query->order_by_asc('tbl_assets.assigned_to')->order_by_asc('tbl_assets.name')->find_array();
}

// Export functions
function exportToCSV($data, $reportType)
{
    $filename = "asset_report_" . $reportType . "_" . date('Y-m-d_H-i-s') . ".csv";

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    if (!empty($data)) {
        // Write headers
        if ($reportType === 'summary') {
            fputcsv($output, ['Report Type', 'Value']);
            fputcsv($output, ['Total Assets', $data['total_assets']]);
            fputcsv($output, ['Total Value', $data['total_value']]);
            fputcsv($output, ['']);

            if (!empty($data['status_breakdown'])) {
                fputcsv($output, ['Status Breakdown']);
                fputcsv($output, ['Status', 'Count']);
                foreach ($data['status_breakdown'] as $status) {
                    fputcsv($output, [$status['status'], $status['count']]);
                }
            }

            if (!empty($data['category_breakdown'])) {
                fputcsv($output, ['']);
                fputcsv($output, ['Category Breakdown']);
                fputcsv($output, ['Category', 'Count']);
                foreach ($data['category_breakdown'] as $category) {
                    fputcsv($output, [$category['category_name'], $category['count']]);
                }
            }
        } else {
            // For detailed reports
            $headers = array_keys($data[0]);
            fputcsv($output, $headers);

            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
    }

    fclose($output);
    exit;
}

function exportToPDF($data, $reportType)
{
    try {
        // Get currency code from config
        global $config;
        $currencyCode = isset($config['currency_code']) ? $config['currency_code'] : 'USD';

        // Get company name from config
        $companyName = isset($config['CompanyName']) ? $config['CompanyName'] : 'Asset Manager';

        // Create mPDF instance
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 16,
            'margin_bottom' => 16,
            'margin_header' => 9,
            'margin_footer' => 9
        ]);

        // Set PDF metadata
        $mpdf->SetTitle('Asset Report - ' . ucfirst($reportType));
        $mpdf->SetAuthor($companyName);
        $mpdf->SetCreator('Asset Manager Plugin');
        $mpdf->SetSubject('Asset Management Report');

        // Generate HTML content
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; }
                .header { text-align: center; margin-bottom: 20px; }
                .company-name { font-size: 18px; font-weight: bold; color: #2c3e50; margin-bottom: 10px; }
                .report-title { font-size: 16px; font-weight: bold; color: #34495e; margin-bottom: 5px; }
                .report-date { font-size: 10px; color: #7f8c8d; }
                .summary-box { background-color: #f8f9fa; padding: 15px; margin: 20px 0; border: 1px solid #dee2e6; }
                .summary-item { margin-bottom: 8px; }
                .summary-label { font-weight: bold; color: #495057; }
                .summary-value { color: #28a745; font-weight: bold; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th { background-color: #343a40; color: white; padding: 8px; text-align: left; font-size: 11px; }
                td { padding: 6px 8px; border-bottom: 1px solid #dee2e6; font-size: 10px; }
                tr:nth-child(even) { background-color: #f8f9fa; }
                .section-title { font-size: 14px; font-weight: bold; margin: 20px 0 10px 0; color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5px; }
                .no-data { text-align: center; color: #6c757d; font-style: italic; padding: 20px; }
                .footer { text-align: center; font-size: 9px; color: #6c757d; margin-top: 30px; }
            </style>
        </head>
        <body>';

        // Header
        $html .= '<div class="header">
            <div class="company-name">' . htmlspecialchars($companyName) . '</div>
            <div class="report-title">Asset Report - ' . ucfirst($reportType) . '</div>
            <div class="report-date">Generated on: ' . date('F j, Y \a\t g:i A') . '</div>
        </div>';

        // Generate content based on report type
        if ($reportType === 'summary' && !empty($data)) {
            $html .= '<div class="summary-box">
                <div class="summary-item">
                    <span class="summary-label">Total Assets:</span> 
                    <span class="summary-value">' . number_format($data['total_assets']) . '</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Total Value:</span> 
                    <span class="summary-value">' . $currencyCode . ' ' . number_format($data['total_value'], 2) . '</span>
                </div>
            </div>';

            // Status breakdown
            if (!empty($data['status_breakdown'])) {
                $html .= '<div class="section-title">Assets by Status</div>
                <table>
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th style="text-align: center;">Count</th>
                            <th style="text-align: center;">Percentage</th>
                        </tr>
                    </thead>
                    <tbody>';

                foreach ($data['status_breakdown'] as $status) {
                    $percentage = $data['total_assets'] > 0 ? number_format(($status['count'] / $data['total_assets']) * 100, 1) : 0;
                    $html .= '<tr>
                        <td>' . htmlspecialchars($status['status']) . '</td>
                        <td style="text-align: center;">' . number_format($status['count']) . '</td>
                        <td style="text-align: center;">' . $percentage . '%</td>
                    </tr>';
                }

                $html .= '</tbody></table>';
            }

            // Category breakdown
            if (!empty($data['category_breakdown'])) {
                $html .= '<div class="section-title">Assets by Category</div>
                <table>
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th style="text-align: center;">Count</th>
                            <th style="text-align: center;">Percentage</th>
                        </tr>
                    </thead>
                    <tbody>';

                foreach ($data['category_breakdown'] as $category) {
                    $percentage = $data['total_assets'] > 0 ? number_format(($category['count'] / $data['total_assets']) * 100, 1) : 0;
                    $html .= '<tr>
                        <td>' . htmlspecialchars($category['category_name']) . '</td>
                        <td style="text-align: center;">' . number_format($category['count']) . '</td>
                        <td style="text-align: center;">' . $percentage . '%</td>
                    </tr>';
                }

                $html .= '</tbody></table>';
            }
        } else if (!empty($data)) {
            // Detailed report table
            $html .= '<div class="section-title">Detailed Asset Information</div>';

            if (is_array($data) && count($data) > 0) {
                $headers = array_keys($data[0]);
                $html .= '<table><thead><tr>';

                foreach ($headers as $header) {
                    $displayHeader = ucfirst(str_replace('_', ' ', $header));
                    $html .= '<th>' . htmlspecialchars($displayHeader) . '</th>';
                }

                $html .= '</tr></thead><tbody>';

                foreach ($data as $row) {
                    $html .= '<tr>';
                    foreach ($headers as $header) {
                        $value = isset($row[$header]) ? $row[$header] : '';

                        // Format currency fields
                        if (strpos($header, 'cost') !== false || strpos($header, 'value') !== false) {
                            if (is_numeric($value) && $value > 0) {
                                $value = $currencyCode . ' ' . number_format($value, 2);
                            }
                        }

                        // Format dates
                        if (strpos($header, 'date') !== false && $value && $value !== '0000-00-00') {
                            $value = date('M j, Y', strtotime($value));
                        }

                        $html .= '<td>' . htmlspecialchars($value) . '</td>';
                    }
                    $html .= '</tr>';
                }

                $html .= '</tbody></table>';
            } else {
                $html .= '<div class="no-data">No data available for the selected criteria.</div>';
            }
        } else {
            $html .= '<div class="no-data">No data available for this report.</div>';
        }

        // Footer
        $html .= '<div class="footer">
            Report generated by ' . htmlspecialchars($companyName) . ' Asset Management System<br>
            For internal use only - Confidential
        </div>';

        $html .= '</body></html>';

        // Write HTML to PDF
        $mpdf->WriteHTML($html);

        // Generate filename
        $filename = "asset_report_" . $reportType . "_" . date('Y-m-d_H-i-s') . ".pdf";

        // Output PDF
        $mpdf->Output($filename, 'D'); // 'D' for download

    } catch (Exception $e) {
        // Fallback to simple PDF if mPDF fails
        _log("mPDF Error: " . $e->getMessage());

        $filename = "asset_report_" . $reportType . "_" . date('Y-m-d_H-i-s') . ".pdf";

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo "<h1>Asset Report - " . ucfirst($reportType) . "</h1>";
        echo "<p>Generated on: " . date('Y-m-d H:i:s') . "</p>";
        echo "<p><em>Note: Enhanced PDF generation temporarily unavailable.</em></p>";

        if ($reportType === 'summary' && !empty($data)) {
            echo "<h2>Summary</h2>";
            echo "<p>Total Assets: " . $data['total_assets'] . "</p>";
            echo "<p>Total Value: " . number_format($data['total_value'], 2) . "</p>";
        } else if (!empty($data)) {
            echo "<table border='1'>";
            $headers = array_keys($data[0]);
            echo "<tr>";
            foreach ($headers as $header) {
                echo "<th>" . ucfirst(str_replace('_', ' ', $header)) . "</th>";
            }
            echo "</tr>";

            foreach ($data as $row) {
                echo "<tr>";
                foreach ($row as $cell) {
                    echo "<td>" . htmlspecialchars($cell) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        }
    }

    exit;
}

function exportToExcel($data, $reportType)
{
    // Simple Excel export using CSV with Excel-specific headers
    $filename = "asset_report_" . $reportType . "_" . date('Y-m-d_H-i-s') . ".xls";

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    echo "<table border='1'>";

    if ($reportType === 'summary' && !empty($data)) {
        echo "<tr><th colspan='2'>Asset Report Summary</th></tr>";
        echo "<tr><td>Total Assets</td><td>" . $data['total_assets'] . "</td></tr>";
        echo "<tr><td>Total Value</td><td>" . number_format($data['total_value'], 2) . "</td></tr>";

        if (!empty($data['status_breakdown'])) {
            echo "<tr><th colspan='2'>Status Breakdown</th></tr>";
            foreach ($data['status_breakdown'] as $status) {
                echo "<tr><td>" . $status['status'] . "</td><td>" . $status['count'] . "</td></tr>";
            }
        }
    } else if (!empty($data)) {
        $headers = array_keys($data[0]);
        echo "<tr>";
        foreach ($headers as $header) {
            echo "<th>" . ucfirst(str_replace('_', ' ', $header)) . "</th>";
        }
        echo "</tr>";

        foreach ($data as $row) {
            echo "<tr>";
            foreach ($row as $cell) {
                echo "<td>" . htmlspecialchars($cell) . "</td>";
            }
            echo "</tr>";
        }
    }

    echo "</table>";
    exit;
}
