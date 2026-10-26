# Configuration

This section describes the configuration options for this module.

```ini
[map]

# The default map position (latitude) when opening the map, as WGS84 coordinate
default_lat = "52.515855"

# The default map position (longitude) when opening the map, as WGS84 coordinate
default_long = "13.377485"

# The default zoom level when opening the map (integer)
default_zoom = 4

# The maximum zoom level for the map (integer)
max_zoom = 19

# The minimum zoom level for the map (integer)
min_zoom = 2

# The maximum zoom level natively supported by the map (integer)
max_native_zoom = 19

# The escaped URL for the tile server
# This URL is used for the Leaflet TileLayer
tile_url = "//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"

# An optional OpenCage Geocoder API key to convert coordinates from places
opencage_apikey = ""

# The Icinga state type to use for status indication. Available options are: soft, hard
stateType = "soft"

# Disable clustering at a certain zoom level. When set the clusting is disabled at this zoom level.
# Use 1 to disable the clustering completely
# The default is: max_zoom - 1
disable_cluster_at_zoom = ""

# Show number of problems in a cluster instead of the number of markers (default: off)
cluster_problem_count = 0

# Show the detail pop-up when hovering over a marker with the mouse (default: off)
popup_mouseover = 0
```

**Zoom levels**

Leaflet works with latitude, longitude and a so called zoom level.

Lower zoom levels means that the map shows entire continents, while higher zoom levels means that the map can show details of a city.

**Disable clustering**

The `disable_cluster_at_zoom` configuration can be used to disabled clustering for markers.

* To disable clustering at a certain zoom level: `disable_cluster_at_zoom = 10`
* To disable clustering completely: `disable_cluster_at_zoom = 1`

**OpenCage integration**

The module can integrate with OpenCage when an API key is configured.

With a valid API key the Map module's search will include results from the OpenCache API.

**Important:** when the OpenCage API key is configured, every search query in the Map module will be sent to the OpenCage API.

## Overrides

Besides the central `config.ini` configuration for the module, it is possible to override these settings per user or per each map.

The following parameters can be overridden:

| Parameter |
| --- |
| min_zoom |
| max_zoom |
| max_native_zoom |
| disable_cluster_at_zoom |
| default_zoom |
| default_long |
| default_lat |
| stateType |
| cluster_problem_count |
| popup_mouseover |
| tile_url |

Precedence for the configuration overrides: `Module Config < User Config < Map Config < URL`

**Note:** With Content Security Policy (CSP) enabled, `tile_url` can still be overridden per user and per map, but not via the URL parameter.

### Map specific config overrides

All config parameters above can be overwritten inside a `maps.ini` file. Each section is named after a map.

Example:

```
/etc/icingaweb2/modules/map/maps.ini

[example]
popup_mouseover = "1"
```

To use this `example` map use the `load` URL parameter: `/icingaweb2/map?load=example`.

### Map specific URL overrides

All config parameters above can be changed for a specific map by adding it to the URL. Use the ampersand sign for concatenation of multiple parameters.

Example: `/icingaweb2/map?default_zoom=20&default_long=13.370324&default_lat=52.500859`

### User specific configuration overrides

The way in which a user-specific configuration can be stored depends on how Icinga Web 2 manages its configuration.

**File**

User specific configuration overrides can be added to the user's Icinga Web 2 configuration (``/etc/icingaweb2/preferences/<USERNAME>/config.ini``).

```ini
[map]
default_zoom = 5
```

This file contains the parameters you want to override for the specific user.

**Database**

Alternatively, user specific configuration overrides can be added to the user's database configuration. For each parameter you want to override:

MySQL:

```sql
INSERT INTO icingaweb_user_preference VALUES ("USER", "map", "default_lat", "29.87", UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000);
INSERT INTO icingaweb_user_preference VALUES ("USER", "map", "default_long", "-88.90", UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000);
```

PostgreSQL:

```sql
INSERT INTO icingaweb_user_preference VALUES ("USER", "map", "default_lat", "29.87", EXTRACT(EPOCH FROM NOW()) * 1000, EXTRACT(EPOCH FROM NOW()) * 1000);
INSERT INTO icingaweb_user_preference VALUES ("USER", "map", "default_long", "-88.90", EXTRACT(EPOCH FROM NOW()) * 1000, EXTRACT(EPOCH FROM NOW()) * 1000);
```
