# PrestaShop Override Conflict Checker

This system provides a robust way to manage PrestaShop overrides while avoiding conflicts with other modules.

## Overview

The Override Conflict Checker system helps module developers safely install overrides by:

1. Checking if override files already exist
2. Detecting if custom code/markers are already installed
3. Identifying real conflicts (when someone else's override exists without your code)
4. Providing a reusable solution for any class override
5. Being easily extendable for custom needs

## Directory Structure

```
src/PrestaChamps/PrestaShop/Override/
├── ConflictChecker.php           # Main class for checking conflicts
├── OverrideInstaller.php         # Class to handle the installation of overrides
└── Exception/
    └── OverrideConflictException.php  # Custom exception for override conflicts

overrides_pending/
├── classes/
│   ├── Cart.php
│   └── ... (other override files)
└── ... (other override directories)
```

## How It Works

1. Override files are stored in the `overrides_pending` directory before installation
2. During module installation, the `OverrideInstaller` checks for conflicts
3. If no conflicts are found, the overrides are copied to the PrestaShop override directory
4. A marker is added to each override file to identify it as belonging to your module
5. PrestaShop's standard `installOverrides()` method is called to register the overrides

## Usage

### In Your Module's Install Method

```php
public function install()
{
    // Other installation steps...
    
    // Install overrides with conflict checking
    $overrideInstaller = new \PrestaChamps\PrestaShop\Override\OverrideInstaller($this);
    $overridesInstalled = $overrideInstaller->install();
    
    if (!$overridesInstalled) {
        $this->_errors[] = $this->trans('Could not install overrides. Please check for conflicts with other modules.');
        return false;
    }
    
    // Continue with other installation steps...
    return true;
}
```

### Checking for Conflicts Manually

```php
$conflictChecker = new \PrestaChamps\PrestaShop\Override\ConflictChecker($this);
$conflicts = $conflictChecker->checkAllOverrides();

if (!empty($conflicts)) {
    // Handle conflicts
    foreach ($conflicts as $conflict) {
        echo "Conflict found in: " . $conflict['path'];
    }
}
```

## Best Practices

1. **Always use the conflict checker** before installing overrides
2. **Add clear markers** to your override files to identify them
3. **Keep overrides minimal** - only override what you need
4. **Document your overrides** clearly
5. **Provide a way to disable overrides** in your module configuration

## Extending the System

The system is designed to be easily extendable. You can:

1. Create a subclass of `ConflictChecker` to add custom conflict detection logic
2. Extend `OverrideInstaller` to add custom installation steps
3. Add additional methods to handle specific override scenarios

## Troubleshooting

If conflicts are detected, the system will:

1. Log the conflict details using PrestaShop's logging system
2. Return false from the installation method
3. Provide detailed information about the conflict through the `OverrideConflictException`

You can access this information to help users resolve conflicts.
