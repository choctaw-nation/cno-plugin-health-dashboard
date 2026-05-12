# CNO Plugin: Health Dashboard

Plugin that displays a virality chart of common diseases with [recharts](https://recharts.github.io/).

## Overview

Plugin home file (`cno-plugin-health-dashboard.php`) loads the autoloader, wires up the plugin to the class's callbacks & declares a global helper function (`cno_health_dashboard_get_filesystem`) that's needed by the File_Reader class.

---

### Class Overview

#### Plugin Loader

-   Boots the plugin

#### File Reader

-   Handles reading the `POST`ed XML file and parsing it into an array.
-   Stores data with a transient with a one-hour expiry.

#### Notifier

-   Sends error emails when necessary

#### Rest Router

-   Sets up public endpoints for data consumption (uses the File Reader)
