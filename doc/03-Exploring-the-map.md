# Exploring the map

Once you enable the *Host Map* module, it will pop up in your menu in the ``Maps`` section.
When you click on it, it will show you a map:

![Map overview](screenshot/02_getting-started/0201_map-overview.png)

## Basic usage

Every host is represented by a colored marker, which indicates the overall host state.

Markers are grouped into clusters, depending on their location and the zoom level. Every cluster marker has a label with the number of clustered markers.

![Clustered map](screenshot/02_getting-started/0202_sub-cluster.png)

By clicking on the icon, the cluster expands and the underlying host markers will be visible:

![Expanded cluster](screenshot/02_getting-started/0203-cluster-expanded.png)

To show more details about a host click on the host marker. If you want to show the host in the detail view of the `monitoring module` just click on the eye icon.

![Marker popup](screenshot/02_getting-started/0204_marker-popup.png)

### Control elements

In the upper left corner of the map there are six control elements:
 
![Control elements](screenshot/02_getting-started/0205_control-elements.png)

## Filtering host objects

The usual icingaweb2 filter syntax can be used to filter the set of hosts being displayed. Filters have to be appended to the url (eg. `?host=web*`)

**Filter examples:**

| Filter expression | Description |
| ----------------------------------------------------- | ------------ |
| hostgroup_name=customer1&_host_environment=production | Show all hosts of hostgroup `customer1` of where the custom variable environment is equal to `production` |
| _host_customer=(max-corp\|icinga)                     | Show all hosts where the custom variable `customer` is set to `max-corp` or `icinga` |

### Change default parameters

It's possible to change the parameters ``default_zoom``, ``default_long`` and ``default_lat`` for a map by adding the parameters to the url:

```map?default_zoom=20&default_long=13.370324&default_lat=52.500859```

## Dashboard integration

To add a map widget to a dashboard (or a new one) click on the `Add to dashboard` button as shown above. Any filters which are applied to the current view, are also stored in the dashlet.
