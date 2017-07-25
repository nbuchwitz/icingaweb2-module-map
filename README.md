Map - Icinga Web 2 module
========================================

## About
Display hosts on OpenStreetMap using http://leafletjs.com/. 

![Clustered map](https://github.com/nbuchwitz/icingaweb2-module-map/raw/master/screenshots/clustered-map.png)
![Clustered map 2](https://github.com/nbuchwitz/icingaweb2-module-map/raw/master/screenshots/clustered-map2.png)
![Detailed map](https://github.com/nbuchwitz/icingaweb2-module-map/raw/master/screenshots/detailed-map.png)
![Host detail view](https://github.com/nbuchwitz/icingaweb2-module-map/raw/master/screenshots/host-detail.png)

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

### Map

Go to Configuration -> Modules -> map  and click on the Configuration tab.

![Configuration Tab](https://github.com/nbuchwitz/icingaweb2-module-map/raw/master/screenshots/configuration-tab.png)

### Hosts

You have to add a custom geolocation variable to every host, you want to show on the map:

```
vars.geolocation = "<longitude>,<latitude>"
```
