# Filter Map Object

The map module allows you to filter objects by using the Icinga Web 2 filter syntax.

For more details have look into the [https://icinga.com/docs/icingaweb2/latest/doc/06-Security/#restrictions](filter section) of the offical Icinga Web 2 documentation.

## Special Filters

### objectType

This filter could be used to show only hosts, services or both object types.

**Example URL:** ``/icingaweb2/map?objectType=host``

**Allows values:** 

| Values | Description |
| ------ | ----------- |
| ``all`` | Show both (hosts & services) on the map |
| ``host`` | Show only hosts on the map |
| ``service`` | Show only services on the map |

### problems

If you only want to see hosts or services with states distinct from ``OK``, use this filter.

**Example URL:** ``/icingaweb2/map?problems``


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
