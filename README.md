Map - Icinga Web 2 module
========================================

## About

This plugins displays icinga2 host-objects as markers on a openstreetmap using leaflet.js. If more than one host-object is defined with the same coordinates (like servers in a datacenter) a clustered view is used.

![Clustered map](https://github.com/nbuchwitz/icingaweb2-module-map/raw/master/screenshots/clustered-map.png)
![Clustered map 2](https://github.com/nbuchwitz/icingaweb2-module-map/raw/master/screenshots/clustered-map2.png)
![Detailed map](https://github.com/nbuchwitz/icingaweb2-module-map/raw/master/screenshots/detailed-map.png)

To find a specific host on the map, you can use the custom host action:

![Detailed map](https://github.com/nbuchwitz/icingaweb2-module-map/raw/master/screenshots/detailed-map.png)

The map plugin integrates into the icingaweb2-tab-view:

![Host detail view](https://github.com/nbuchwitz/icingaweb2-module-map/raw/master/screenshots/host-action.png)

If the host-marker is clicked, a popup shows a service list and their current hardstates

![Host detail view](https://github.com/nbuchwitz/icingaweb2-module-map/raw/master/screenshots/host-details.png)


## Requirements

* Icinga Web 2 (&gt;= 2.0.0)

## Licence

Icinga Web 2 and this Icinga Web 2 module are licensed under the terms of the GNU General Public License Version 2, you will find a copy of this license in the LICENSE file included in the source package.

## Installation

Clone the git repository and rename it to map:

```
cd /usr/share/icingaweb2/modules/
git clone https://github.com/nbuchwitz/icingaweb2-module-map.git
mv icingaweb2-module-map map
```

Activate the module:

```
icingacli module enable map
```

## Configuration

### Plugin

To configure the default coordinates and the zoom levels go to Configuration -> Modules -> map  and click on the tab Configuration.

![Configuration Tab](https://github.com/nbuchwitz/icingaweb2-module-map/raw/master/screenshots/configuration-tab.png)

### Add coordinates to a host-object

For every host you want to display on the map, you have to add a custom variable named geolocation with the WGS84-coordinates:

```
vars.geolocation = "<latitude>,<longitude>"
```

To show a popup with the coordinates click on the desired location on the map and hold the CTRL-key while clicking on the map.
