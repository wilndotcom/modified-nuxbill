<?php
/**
 * Bootstrap 5 Migration Helper Script
 * 
 * This script helps automate the migration from Bootstrap 3 to Bootstrap 5
 * by finding and replacing deprecated classes in template files.
 * 
 * Usage: php migrate-bootstrap5.php [--dry-run] [--path=ui/ui/admin]
 */

class Bootstrap5Migrator {
    private $replacements = [
        // Float utilities
        'pull-right' => 'float-end',
        'pull-left' => 'float-start',
        
        // Grid system
        '/col-xs-(\d+)/' => 'col-$1',  // col-xs-6 -> col-6
        
        // Buttons
        'btn-default' => 'btn-secondary',
        'btn-xs' => 'btn-sm',
        
        // Labels to Badges
        '/\blabel\s+label-default\b/' => 'badge bg-secondary',
        '/\blabel\s+label-primary\b/' => 'badge bg-primary',
        '/\blabel\s+label-success\b/' => 'badge bg-success',
        '/\blabel\s+label-info\b/' => 'badge bg-info',
        '/\blabel\s+label-warning\b/' => 'badge bg-warning',
        '/\blabel\s+label-danger\b/' => 'badge bg-danger',
        '/\blabel\s+label-\w+\b/' => 'badge',  // Generic label -> badge
        
        // Panels to Cards
        '/\bpanel\s+panel-primary\b/' => 'card border-primary',
        '/\bpanel\s+panel-success\b/' => 'card border-success',
        '/\bpanel\s+panel-info\b/' => 'card border-info',
        '/\bpanel\s+panel-warning\b/' => 'card border-warning',
        '/\bpanel\s+panel-danger\b/' => 'card border-danger',
        '/\bpanel\b/' => 'card',
        'panel-body' => 'card-body',
        'panel-heading' => 'card-header',
        'panel-footer' => 'card-footer',
        'panel-title' => 'card-title',
        
        // Forms
        'form-group' => 'mb-3',
        'control-label' => 'form-label',
        'help-block' => 'form-text',
        'form-control-static' => 'form-control-plaintext',
        'input-group-addon' => 'input-group-text',
        'input-lg' => 'form-control-lg',
        'input-sm' => 'form-control-sm',
        'has-error' => 'is-invalid',
        'has-success' => 'is-valid',
        'has-warning' => 'is-warning',
        'checkbox-inline' => 'form-check-inline',
        'radio-inline' => 'form-check-inline',
        
        // Navbar
        'navbar-toggle' => 'navbar-toggler',
        'navbar-default' => 'navbar-light',
        'navbar-inverse' => 'navbar-dark',
        'navbar-fixed-top' => 'fixed-top',
        'navbar-fixed-bottom' => 'fixed-bottom',
        
        // Display utilities
        'hidden-xs' => 'd-none d-sm-block',
        'hidden-sm' => 'd-sm-none d-md-block',
        'hidden-md' => 'd-md-none d-lg-block',
        'hidden-lg' => 'd-lg-none d-xl-block',
        'visible-xs' => 'd-block d-sm-none',
        'visible-sm' => 'd-sm-block d-md-none',
        'visible-md' => 'd-md-block d-lg-none',
        'visible-lg' => 'd-lg-block d-xl-none',
        
        // Images
        'img-responsive' => 'img-fluid',
        'img-circle' => 'rounded-circle',
        'img-rounded' => 'rounded',
        
        // Tables
        'table-condensed' => 'table-sm',
        
        // Wells
        'well' => 'card',
        'well-sm' => 'card card-sm',
        'well-lg' => 'card card-lg',
        
        // Dropdowns
        'dropdown-menu-right' => 'dropdown-menu-end',
        'dropdown-menu-left' => 'dropdown-menu-start',
        
        // Modals
        'data-dismiss="modal"' => 'data-bs-dismiss="modal"',
        'data-toggle="tooltip"' => 'data-bs-toggle="tooltip"',
        'data-toggle="popover"' => 'data-bs-toggle="popover"',
        'data-placement=' => 'data-bs-placement=',
        
        // Close button
        '/<button[^>]*class="close"[^>]*>/' => '<button type="button" class="btn-close" aria-label="Close">',
    ];
    
    private $dryRun = false;
    private $path = 'ui/ui';
    private $extensions = ['tpl', 'php', 'html'];
    private $stats = [
        'files_processed' => 0,
        'files_modified' => 0,
        'replacements' => 0,
    ];
    
    public function __construct($options = []) {
        if (isset($options['dry-run'])) {
            $this->dryRun = true;
        }
        if (isset($options['path'])) {
            $this->path = $options['path'];
        }
    }
    
    public function migrate() {
        echo "Bootstrap 5 Migration Helper\n";
        echo "============================\n\n";
        
        if ($this->dryRun) {
            echo "⚠️  DRY RUN MODE - No files will be modified\n\n";
        }
        
        echo "Scanning: {$this->path}\n";
        echo "Extensions: " . implode(', ', $this->extensions) . "\n\n";
        
        $files = $this->findFiles($this->path);
        echo "Found " . count($files) . " files to process\n\n";
        
        foreach ($files as $file) {
            $this->processFile($file);
        }
        
        $this->printStats();
    }
    
    private function findFiles($dir) {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $ext = strtolower($file->getExtension());
                if (in_array($ext, $this->extensions)) {
                    $files[] = $file->getPathname();
                }
            }
        }
        
        return $files;
    }
    
    private function processFile($filepath) {
        $this->stats['files_processed']++;
        
        $content = file_get_contents($filepath);
        $originalContent = $content;
        $fileReplacements = 0;
        
        foreach ($this->replacements as $search => $replace) {
            if (is_string($search)) {
                // Simple string replacement
                $newContent = str_replace($search, $replace, $content);
                if ($newContent !== $content) {
                    $count = substr_count($content, $search);
                    $fileReplacements += $count;
                    $content = $newContent;
                }
            } else {
                // Regex replacement
                $newContent = preg_replace($search, $replace, $content);
                if ($newContent !== $content) {
                    $count = preg_match_all($search, $content);
                    $fileReplacements += $count;
                    $content = $newContent;
                }
            }
        }
        
        if ($content !== $originalContent) {
            $this->stats['files_modified']++;
            $this->stats['replacements'] += $fileReplacements;
            
            echo "✓ {$filepath} ({$fileReplacements} replacements)\n";
            
            if (!$this->dryRun) {
                file_put_contents($filepath, $content);
            }
        }
    }
    
    private function printStats() {
        echo "\n" . str_repeat('=', 50) . "\n";
        echo "Migration Statistics\n";
        echo str_repeat('=', 50) . "\n";
        echo "Files processed: {$this->stats['files_processed']}\n";
        echo "Files modified: {$this->stats['files_modified']}\n";
        echo "Total replacements: {$this->stats['replacements']}\n";
        echo "\n";
        
        if ($this->dryRun) {
            echo "⚠️  This was a dry run. Run without --dry-run to apply changes.\n";
        } else {
            echo "✅ Migration complete!\n";
            echo "\n⚠️  IMPORTANT: Review all changes and test thoroughly!\n";
        }
    }
}

// CLI execution
if (php_sapi_name() === 'cli') {
    $options = [];
    
    foreach ($argv as $arg) {
        if ($arg === '--dry-run') {
            $options['dry-run'] = true;
        } elseif (strpos($arg, '--path=') === 0) {
            $options['path'] = substr($arg, 7);
        } elseif ($arg === '--help' || $arg === '-h') {
            echo "Bootstrap 5 Migration Helper\n";
            echo "Usage: php migrate-bootstrap5.php [options]\n\n";
            echo "Options:\n";
            echo "  --dry-run          Show what would be changed without modifying files\n";
            echo "  --path=DIR         Directory to process (default: ui/ui)\n";
            echo "  --help, -h         Show this help message\n\n";
            echo "Examples:\n";
            echo "  php migrate-bootstrap5.php --dry-run\n";
            echo "  php migrate-bootstrap5.php --path=ui/ui/admin\n";
            echo "  php migrate-bootstrap5.php\n";
            exit(0);
        }
    }
    
    $migrator = new Bootstrap5Migrator($options);
    $migrator->migrate();
}
