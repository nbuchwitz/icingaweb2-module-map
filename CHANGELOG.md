# Change Log

## [1.1.0](https://github.com/nbuchwitz/icingaweb2-module-map/tree/1.1.0) (2018-11-06)
[Full Changelog](https://github.com/nbuchwitz/icingaweb2-module-map/compare/v1.0.4...1.1.0)

**Implemented enhancements:**

- Show services as handled if host is down [\#72](https://github.com/nbuchwitz/icingaweb2-module-map/issues/72)
- Add host status indication to popup [\#70](https://github.com/nbuchwitz/icingaweb2-module-map/issues/70)
- Update leaflet to version 1.3.1 [\#64](https://github.com/nbuchwitz/icingaweb2-module-map/issues/64)
- Add custom icons to marker [\#62](https://github.com/nbuchwitz/icingaweb2-module-map/issues/62)
- additional Color Marker for handled state [\#54](https://github.com/nbuchwitz/icingaweb2-module-map/issues/54)
- Provides additional marker images for retina displays [\#42](https://github.com/nbuchwitz/icingaweb2-module-map/issues/42)
- Support also geolocation for services [\#40](https://github.com/nbuchwitz/icingaweb2-module-map/issues/40)
- Filter by status [\#32](https://github.com/nbuchwitz/icingaweb2-module-map/issues/32)
- configuring offline maps [\#18](https://github.com/nbuchwitz/icingaweb2-module-map/issues/18)
- Adapt colors to used theme [\#75](https://github.com/nbuchwitz/icingaweb2-module-map/pull/75) ([nbuchwitz](https://github.com/nbuchwitz))

**Fixed bugs:**

- Show action links only if geolocation is set [\#63](https://github.com/nbuchwitz/icingaweb2-module-map/issues/63)
- Status indication of unknown services / markers is broken [\#61](https://github.com/nbuchwitz/icingaweb2-module-map/issues/61)
- Marker tooltip is broken [\#60](https://github.com/nbuchwitz/icingaweb2-module-map/issues/60)
- Map filtering in dashboards don't work [\#51](https://github.com/nbuchwitz/icingaweb2-module-map/issues/51)

**Closed issues:**

- Name a hosts cluster? [\#79](https://github.com/nbuchwitz/icingaweb2-module-map/issues/79)
- configurable option for clustering count as number of problem hosts in the cluster rather than number of services [\#78](https://github.com/nbuchwitz/icingaweb2-module-map/issues/78)
- disable grouping? [\#77](https://github.com/nbuchwitz/icingaweb2-module-map/issues/77)
- User preference for default zoom, longitude and latitude [\#74](https://github.com/nbuchwitz/icingaweb2-module-map/issues/74)
- No host/services are shown in the map [\#68](https://github.com/nbuchwitz/icingaweb2-module-map/issues/68)
- Documentation for marker icons [\#58](https://github.com/nbuchwitz/icingaweb2-module-map/issues/58)
- Documentation for objectType and service markers [\#57](https://github.com/nbuchwitz/icingaweb2-module-map/issues/57)
- Raise php dependency to \>= 5.6 [\#53](https://github.com/nbuchwitz/icingaweb2-module-map/issues/53)
- problem viewing distant locations [\#52](https://github.com/nbuchwitz/icingaweb2-module-map/issues/52)
- Unable to filter hosts on map [\#50](https://github.com/nbuchwitz/icingaweb2-module-map/issues/50)
- Write documentation about using Dashlet function [\#49](https://github.com/nbuchwitz/icingaweb2-module-map/issues/49)
- module-map behind proxy [\#48](https://github.com/nbuchwitz/icingaweb2-module-map/issues/48)
- Marker points to incorrect location [\#44](https://github.com/nbuchwitz/icingaweb2-module-map/issues/44)

**Merged pull requests:**

- Add configuration option for value in cluster [\#83](https://github.com/nbuchwitz/icingaweb2-module-map/pull/83) ([nbuchwitz](https://github.com/nbuchwitz))
- Add support for disabling clustering [\#82](https://github.com/nbuchwitz/icingaweb2-module-map/pull/82) ([nbuchwitz](https://github.com/nbuchwitz))
- Feature/user settings [\#81](https://github.com/nbuchwitz/icingaweb2-module-map/pull/81) ([nbuchwitz](https://github.com/nbuchwitz))
- Add config options for leaflet map [\#80](https://github.com/nbuchwitz/icingaweb2-module-map/pull/80) ([rangoy](https://github.com/rangoy))
- Add documentation for markers [\#76](https://github.com/nbuchwitz/icingaweb2-module-map/pull/76) ([dgoetz](https://github.com/dgoetz))
- Feature/host down indication [\#73](https://github.com/nbuchwitz/icingaweb2-module-map/pull/73) ([nbuchwitz](https://github.com/nbuchwitz))
- Add host status indication in popup [\#71](https://github.com/nbuchwitz/icingaweb2-module-map/pull/71) ([nbuchwitz](https://github.com/nbuchwitz))
- Feature/fancy marker icons [\#55](https://github.com/nbuchwitz/icingaweb2-module-map/pull/55) ([nbuchwitz](https://github.com/nbuchwitz))

## [v1.0.4](https://github.com/nbuchwitz/icingaweb2-module-map/tree/v1.0.4) (2017-12-26)
[Full Changelog](https://github.com/nbuchwitz/icingaweb2-module-map/compare/v1.0.3...v1.0.4)

**Implemented enhancements:**

- No grouping on last zoom level [\#37](https://github.com/nbuchwitz/icingaweb2-module-map/issues/37)
- Proposal to show display\_name instead of hostname on popup [\#25](https://github.com/nbuchwitz/icingaweb2-module-map/issues/25)
- Move documentation to doc/ and enhance [\#20](https://github.com/nbuchwitz/icingaweb2-module-map/issues/20)
- Filter syntax [\#11](https://github.com/nbuchwitz/icingaweb2-module-map/issues/11)
- Provide support for localization [\#10](https://github.com/nbuchwitz/icingaweb2-module-map/issues/10)

**Fixed bugs:**

- Hostmarker not visible on mobile  [\#38](https://github.com/nbuchwitz/icingaweb2-module-map/issues/38)
- Show host on map parameter handling [\#34](https://github.com/nbuchwitz/icingaweb2-module-map/issues/34)
- 20 seconds to load the points. [\#28](https://github.com/nbuchwitz/icingaweb2-module-map/issues/28)

**Closed issues:**

- Other options for filtering in config file [\#45](https://github.com/nbuchwitz/icingaweb2-module-map/issues/45)
- Feature: Filter by host-group [\#39](https://github.com/nbuchwitz/icingaweb2-module-map/issues/39)
- Locations not shown on map [\#35](https://github.com/nbuchwitz/icingaweb2-module-map/issues/35)
- URL for Host Action [\#33](https://github.com/nbuchwitz/icingaweb2-module-map/issues/33)
- Filtering by hostgroup is not possible [\#29](https://github.com/nbuchwitz/icingaweb2-module-map/issues/29)

**Merged pull requests:**

- Uncluster hosts at max zoom level [\#47](https://github.com/nbuchwitz/icingaweb2-module-map/pull/47) ([nbuchwitz](https://github.com/nbuchwitz))
- Improved documentation [\#46](https://github.com/nbuchwitz/icingaweb2-module-map/pull/46) ([nbuchwitz](https://github.com/nbuchwitz))
- Added basic translation support [\#36](https://github.com/nbuchwitz/icingaweb2-module-map/pull/36) ([nbuchwitz](https://github.com/nbuchwitz))
- Fixed handling of broken coordinates [\#31](https://github.com/nbuchwitz/icingaweb2-module-map/pull/31) ([nbuchwitz](https://github.com/nbuchwitz))
- Filtering host objects [\#27](https://github.com/nbuchwitz/icingaweb2-module-map/pull/27) ([nbuchwitz](https://github.com/nbuchwitz))

## [v1.0.3](https://github.com/nbuchwitz/icingaweb2-module-map/tree/v1.0.3) (2017-08-15)
[Full Changelog](https://github.com/nbuchwitz/icingaweb2-module-map/compare/v1.0.2...v1.0.3)

**Fixed bugs:**

- Uncaught TypeError: Cannot read property 'lat' of null [\#19](https://github.com/nbuchwitz/icingaweb2-module-map/issues/19)

**Closed issues:**

- Default zoom/location redirect wrong [\#21](https://github.com/nbuchwitz/icingaweb2-module-map/issues/21)

**Merged pull requests:**

- Add GitHub issue template [\#23](https://github.com/nbuchwitz/icingaweb2-module-map/pull/23) ([dnsmichi](https://github.com/dnsmichi))

## [v1.0.2](https://github.com/nbuchwitz/icingaweb2-module-map/tree/v1.0.2) (2017-08-08)
[Full Changelog](https://github.com/nbuchwitz/icingaweb2-module-map/compare/v1.0.1...v1.0.2)

**Implemented enhancements:**

- worst state handling: how to treat pending/unknown? [\#16](https://github.com/nbuchwitz/icingaweb2-module-map/issues/16)

**Fixed bugs:**

- Use a green bubble if hosts are not down [\#13](https://github.com/nbuchwitz/icingaweb2-module-map/issues/13)

**Merged pull requests:**

- Clean vendor JS/CSS from Leaflet; add License details [\#17](https://github.com/nbuchwitz/icingaweb2-module-map/pull/17) ([dnsmichi](https://github.com/dnsmichi))
- Fix undefined variable error if there are no data points fetched [\#15](https://github.com/nbuchwitz/icingaweb2-module-map/pull/15) ([dnsmichi](https://github.com/dnsmichi))

## [v1.0.1](https://github.com/nbuchwitz/icingaweb2-module-map/tree/v1.0.1) (2017-08-06)
[Full Changelog](https://github.com/nbuchwitz/icingaweb2-module-map/compare/v1.0.0...v1.0.1)

**Implemented enhancements:**

- Switch for state modus [\#12](https://github.com/nbuchwitz/icingaweb2-module-map/issues/12)

**Fixed bugs:**

- Two dashlets, only the first one gets updated [\#9](https://github.com/nbuchwitz/icingaweb2-module-map/issues/9)

**Merged pull requests:**

- Remove sub menu icon - style guide change [\#8](https://github.com/nbuchwitz/icingaweb2-module-map/pull/8) ([dnsmichi](https://github.com/dnsmichi))
- Move "Host Map" into "Maps" menu item [\#7](https://github.com/nbuchwitz/icingaweb2-module-map/pull/7) ([dnsmichi](https://github.com/dnsmichi))
- Docs: Add release tarball installation [\#6](https://github.com/nbuchwitz/icingaweb2-module-map/pull/6) ([dnsmichi](https://github.com/dnsmichi))

## [v1.0.0](https://github.com/nbuchwitz/icingaweb2-module-map/tree/v1.0.0) (2017-08-04)
**Merged pull requests:**

- Rename menu entry, adopt module description [\#5](https://github.com/nbuchwitz/icingaweb2-module-map/pull/5) ([dnsmichi](https://github.com/dnsmichi))
- Add CONTRIBUTING details [\#4](https://github.com/nbuchwitz/icingaweb2-module-map/pull/4) ([dnsmichi](https://github.com/dnsmichi))
- Add missing LICENSE file [\#3](https://github.com/nbuchwitz/icingaweb2-module-map/pull/3) ([dnsmichi](https://github.com/dnsmichi))
- Update README [\#2](https://github.com/nbuchwitz/icingaweb2-module-map/pull/2) ([dnsmichi](https://github.com/dnsmichi))
- Load map tiles via protocol that is used to access icingaweb2 [\#1](https://github.com/nbuchwitz/icingaweb2-module-map/pull/1) ([Mikesch-mp](https://github.com/Mikesch-mp))



\* *This Change Log was automatically generated by [github_changelog_generator](https://github.com/skywinder/Github-Changelog-Generator)*