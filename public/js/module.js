;(function (Icinga, $) {

    'use strict';

    let cache = {};

    class MapModule {
        constructor(module) {
            this.timer = {};
            this.icinga = module.icinga;

            module.on('rendered', this.onRenderedContainer);

            this.registerTimer();
        }

        /**
         * Allow to destroy and clean up this module
         */
        destroy() {
            this.removeTimer();
            cache = {};
        }

        registerTimer(id) {
            this.timer = this.icinga.timer.register(this.updateAllMapData, this, 60000);
            return this;
        }

        removeTimer(id) {
            this.icinga.timer.unregister(this.timer);
            return this;
        }

        onPopupOpen(evt) {
            $('.detail-link').on("click", function (ievt) {
                mapCenter(evt.popup._source.options.id);
                cache[id].map.invalidateSize();
            });
        }

        updateAllMapData() {
            const _this = this;

            if (cache.length == 0) {
                this.removeTimer(id);
                return this;
            }

            $.each(cache, function (id) {
                if (!$('#map-' + id).length) {
                    delete cache[id];
                } else {
                    _this.updateMapData({id: id});
                }
            });
        }

        updateMapData(parameters) {
            const id = parameters.id;
            let show_host = parameters.show_host;
            const _this = this;

            function removeOldMarkers(id, data) {
                // remove old markers
                $.each(cache[id].hostMarkers, function (identifier, d) {
                    if ((data['hosts'] && !data['hosts'][identifier]) && (data['services'] && !data['services'][identifier])) {
                        cache[id].markers.removeLayer(d);
                        delete cache[id].hostMarkers[identifier];
                    }
                });
            }

            function errorMessage(msg) {
                cache[id].map.spin(false);
                cache[id].map.openModal({
                    content: "<p>Could not fetch data from API:</p><pre>" + msg + "</pre>",
                    onShow: function (evt) {
                        _this.removeTimer(id);
                    },
                    onHide: function (evt) {
                        _this.registerTimer(id);
                    }
                });
            }

            function processData(json) {
                if (json['message']) {
                    errorMessage(json['message']);
                    return;
                }
                removeOldMarkers(id, json);

                $.each(json, function (type, element) {
                    $.each(element, function (identifier, data) {
                        if (data.length < 1 || data['coordinates'] == "") {
                            console.log('found empty coordinates: ' + data);
                            return true;
                        }

                        let states = [];
                        let icon;
                        let services;
                        let worstState;
                        let display_name = (data['host_display_name'] ? data['host_display_name'] : data['host_name']);

                        if (type === 'hosts') {
                            states.push((data['host_state'] == 1 ? 2 : data['host_state']));
                        }

                        let table = '<table class="icinga-module module-monitoring">';

                        if (isUsingIcingadb) {
                            //TODO add icingadb design
                            table = '<table class="icinga-module module-icingadb">';
                        }

                        services = '<div class="map-popup-services">';
                        services += '<h1><i class="icon fa-cogs fa" aria-hidden="1"></i>Services</h1>';
                        services += '<div class="scroll-view">';
                        services += table;
                        services += '<tbody>';

                        $.each(data['services'], function (service_display_name, service) {
                            states.push(service['service_state']);

                            let service_handled = "";
                            if ((data['host_state'] == 1 && data['host_acknowledged']) || (service['service_acknowledged'] == 1 || service['service_in_downtime'] == 1)) {
                                service_handled = " handled";
                            }

                            let ServiceStateClass = " state-" + service_status[service['service_state']][1].toLowerCase();
                            let ServiceLink = '/monitoring/service/show?host=' + data['host_name'] + '&service=' + service['service_name'];
                            let tdClasses = "state-col" + ServiceStateClass + service_handled;
                            let statelabel = '<div class="state-label">' + service_status[service['service_state']][0] + '</div>';

                            if (isUsingIcingadb) {
                                ServiceLink = '/icingadb/service?host.name=' + data['host_name'] + '&name=' + service['service_name'];
                                tdClasses = "state-ball center" + ServiceStateClass + " ball-size-l" + service_handled;
                                statelabel = "";
                                if (service_handled) {
                                    statelabel = '<i class="icon fa fa-check"></i>';
                                }
                            }
                            services += '<tr>';

                            services += '<td class="' + tdClasses + '">';
                            services += statelabel;
                            services += '</td>';

                            services += '<td>';
                            services += '<div class="state-header">';
                            services += '<a data-hostname="' + data['host_name'] + '" data-base-target="_next" href="'
                                + icinga.config.baseUrl
                                + ServiceLink
                                + '">';
                            services += service_display_name;
                            services += '</a>';
                            services += '</div>';
                            services += '</td>';

                            services += '</tr>';

                            if (type === 'services') {
                                display_name = service_display_name + " (" + display_name + ")";
                            }
                        });

                        services += '</tbody>';
                        services += '</table>';
                        services += '</div>';
                        services += '</div>';

                        worstState = getWorstState(states);

                        let marker_icon = (type === 'hosts' ? 'host' : 'service');
                        if (data['icon']) {
                            marker_icon = data['icon'];
                        }

                        icon = colorMarker(worstState, marker_icon);

                        let host_icon = "";
                        if (data['host_icon_image'] != "") {
                            host_icon = '<img src="' + icinga.config.baseUrl + '/img/icons/'
                                + data['host_icon_image']
                                + '"'
                                + ((data['host_icon_image_alt'] != "") ? ' alt="' + data['host_icon_image_alt'] + '"' : '')
                                + ' class="host-icon-image icon">';
                        }

                        const host_status = type === 'hosts' && data['host_state'] == 1 ? "<div id=\"hoststatus\">" + translation['host-down'] + "</div>" : "";
                        let hostLink = '/monitoring/host/show?host=' + data['host_name'];

                        if (isUsingIcingadb) {
                            hostLink = '/icingadb/host?name=' + data['host_name'];
                        }

                        let info = '<div class="map-popup">';

                        info += '<h1>';
                        info += '<i class="icon fa-server fa" aria-hidden="1"></i> ';
                        info += '<a class="detail-link" data-hostname="' + data['host_name'] + '" data-base-target="_next" href="'
                            + icinga.config.baseUrl
                            + hostLink
                            + '">';
                        info += data['host_display_name'] + '</h1>';
                        info += host_status;
                        info += '</a>';

                        info += services;
                        info += '</div>';

                        let marker;

                        if (cache[id].hostMarkers[identifier]) {
                            marker = cache[id].hostMarkers[identifier];
                            marker.options.state = worstState;
                            marker.setIcon(icon);
                        } else {
                            marker = L.marker(data['coordinates'],
                                {
                                    icon: icon,
                                    title: display_name,
                                    riseOnHover: true,
                                    id: identifier,
                                    state: worstState,
                                }).addTo(cache[id].markers);

                            cache[id].hostMarkers[identifier] = marker;
                            cache[id].hostData[identifier] = data
                        }

                        marker.bindPopup(info);

                        if (popup_mouseover) {
                            marker.on('mouseover', function (e) {
                                this.openPopup();
                            });
                            marker.on('mouseout', function (e) {
                                // this.closePopup();
                            });
                        }
                    })
                });

                cache[id].markers.refreshClusters();
                cache[id].map.spin(false);

                // TODO: Should be updated instant and not only on data refresh
                cache[id].map.invalidateSize();

                if (show_host != "") {
                    showHost(show_host);
                    show_host = "";
                }
            }

            const url = icinga.config.baseUrl + '/map/data/points?' + filterParams(id, cache[id].parameters);

            $.getJSON(url, processData)
                .fail(function (jqxhr, textStatus, error) {
                    errorMessage(error);
                });
        }

        onRenderedContainer(event) {
            const mapElem = event.currentTarget.querySelector('.icinga-module.module-map > .content > #map-script');

            if (mapElem === null) {
                return;
            }

            let attrs = JSON.parse(mapElem.dataset.mapAttrs);

            // TODO: Move this to the Map class as config
            for (const [key, value] of Object.entries(attrs)) {
                if (value !== null && typeof value === 'object') {
                    for (const [key2, value2] of Object.entries(value)) {
                        if (typeof window[key] === 'undefined') {
                            window[key] = {};
                        }
                        window[key][key2] = value2;
                    }
                } else {
                    window[key] = value;
                }
            }

            cache[id] = {};
            cache[id].map = L.map('map-' + id, {
                    zoomControl: false,
                    worldCopyJump: true
                }
            );

            // in module configuration we don't have a map, so return peacefully
            if (typeof id === 'undefined') {
                return;
            }

            const osm = L.tileLayer(tile_url, {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                subdomains: ['a', 'b', 'c'],
                maxNativeZoom: map_max_native_zoom,
                maxZoom: map_max_zoom,
                minZoom: map_min_zoom
            });
            osm.addTo(cache[id].map);

            const options = {
                limit: 10,
                filter: function () {
                    return filterParams(id, cache[id].parameters);
                }
            };
            const control = L.Control.openCageSearch(options).addTo(cache[id].map);

            control.setMarker(function (el) {
                if (el['id'] && cache[id].hostMarkers[el.id]) {
                    showHost(el.id)
                } else {
                    const geocodeMarker = new L.Marker(el.center, {
                        icon: L.AwesomeMarkers.icon({
                            icon: 'globe',
                            markerColor: 'blue',
                            className: 'awesome-marker'
                        })
                    })
                        .bindPopup(el.name)
                        .addTo(cache[id].map)
                        .openPopup();

                    cache[id].map.setView(geocodeMarker.getLatLng(), map_max_zoom);

                    geocodeMarker.on('popupclose', function (evt) {
                        cache[id].map.removeLayer(evt.target);
                    });
                }
            });

            cache[id].markers = new L.MarkerClusterGroup({
                iconCreateFunction: function (cluster) {
                    let childCount = cluster.getChildCount();
                    let childProblem = 0;

                    let states = [];
                    $.each(cluster.getAllChildMarkers(), function (id, el) {
                        states.push(el.options.state);

                        if (el.options.state > 0) {
                            childProblem++;
                        }
                    });

                    const worstState = getWorstState(states);
                    const c = ' marker-cluster-' + worstState;
                    let clusterLabel = childProblem + '/' + childCount;

                    if (cluster_problem_count) {
                        clusterLabel = childProblem;
                    }

                    return new L.DivIcon({
                        html: '<div><span>' + clusterLabel + '</span></div>',
                        className: 'marker-cluster' + c,
                        iconSize: new L.Point(40, 40)
                    });
                },
                maxClusterRadius: function (zoom) {
                    return (zoom <= disable_cluster_at_zoom) ? 80 : 1; // radius in pixels
                },
            });

            cache[id].hostMarkers = {};
            cache[id].hostData = {};

            cache[id].fullscreen = false;
            cache[id].parameters = url_parameters;

            // TODO: fixme
            // var basePath = $currentUrl.replace(/\?.*$/, '');
            // var initialUrl = icinga.
            // $('#map-' + id).closest('.module-map').data('icingaUrl', url_parameters);

            showDefaultView();

            cache[id].map.on('popupopen', this.onPopupOpen);

            L.control.zoom({
                    zoomInTitle: translation['btn-zoom-in'],
                    zoomOutTitle: translation['btn-zoom-out']
                }
            ).addTo(cache[id].map);

            if (!dashlet) {
                L.easyButton({
                    states: [{
                        icon: 'icon-dashboard', title: translation['btn-dashboard'], onClick: function (btn, map) {
                            const dashletUri = "map" + window.location.search;
                            const uri = icinga.config.baseUrl + "/" + "dashboard/new-dashlet?url=" + encodeURIComponent(dashletUri);

                            window.open(uri, "_self")
                        }
                    }]
                }).addTo(cache[id].map);

                L.easyButton({
                    states: [{
                        icon: 'icon-resize-full-alt',
                        title: translation['btn-fullscreen'],
                        onClick: function (btn, map) {
                            toggleFullscreen();
                        }
                    }]
                }).addTo(cache[id].map);

                L.easyButton({
                    states: [{
                        icon: 'icon-globe', title: translation['btn-default'], onClick: function (btn, map) {
                            showDefaultView();
                        }
                    }]
                }).addTo(cache[id].map);


                L.control.locate({
                    icon: 'icon-pin',
                    strings: {title: translation['btn-locate']}
                }).addTo(cache[id].map);

                cache[id].map.on('map-container-resize', function () {
                    cache[id].map.invalidateSize();
                });

                cache[id].map.on('moveend', function (e) {
                    const center = cache[id].map.getCenter();

                    const lat = center.lat;
                    const lng = center.lng;

                    updateUrl('default_lat', lat);
                    updateUrl('default_long', lng)
                });

                cache[id].map.on('zoomend', function (e) {
                    const zoomLevel = cache[id].map.getZoom();
                    updateUrl('default_zoom', zoomLevel)
                });

                cache[id].map.on('click', function (e) {
                    // only for debugging needed
                    // var id = e.target._container.id.replace('map-', '');

                    if (e.originalEvent.ctrlKey) {
                        const coord = 'vars.geolocation = "' + e.latlng.lat.toFixed(6) + ',' + e.latlng.lng.toFixed(6) + '"';

                        const popup = "<h1>Location selected</h1>"
                            + "<p>To use this location with your host(s) or service(s), just add the following config to your object definition:</p>"
                            + "<pre>" + coord + "</pre>";

                        let marker;
                        marker = L.marker(e.latlng, {icon: colorMarker(99, 'globe')});
                        marker.bindPopup(popup);
                        marker.addTo(cache[id].markers);

                        marker.on('popupclose', function (evt) {
                            cache[id].markers.removeLayer(evt.target);
                        });

                        cache[id].markers.zoomToShowLayer(marker, function () {
                            marker.openPopup();
                        })
                    }
                });
            }

            cache[id].markers.addTo(cache[id].map);

            cache[id].map.spin(true);
            this.updateMapData({id: id, show_host: map_show_host});
        }
    };

    function colorMarker(worstState, icon) {
        const markerColor = 'awesome-marker';

        // TODO: Different marker icon for not-OK states
        // if(worstState > 0) {
        //     markerColor = markerColor + ' awesome-marker-square';
        // }

        const marker = L.AwesomeMarkers.icon({
            icon: icon,
            markerColor: state2color(worstState),
            className: markerColor
        });

        return marker
    }

    function state2color(state) {
        switch (parseInt(state)) {
            case 0:
                return "green";
            case 1:
                return "orange";
            case 2:
                return "red";
            case 3:
                return "purple";
            default:
                return "blue";
        }
    }

    function isFilterParameter(parameter) {
        return (parameter.charAt(0) === '(' || parameter.match('^[_]{0,1}(host|service)') || parameter.match('^(object|state)Type') || parameter.match('^problems'));
    }

    function getParameters(id) {
        const params = decodeURIComponent($('#map-' + id).closest('.module-map').data('icingaUrl')).split('&');

        // remove module path from url parameters
        if (params.length > 0) {
            params[0] = params[0].replace(/^.*\?/, '')
        }

        return params;
    }

    function unique(list) {
        const result = [];
        $.each(list, function (i, e) {
            if ($.inArray(e, result) == -1) result.push(e);
        });
        return result;
    }

    function filterParams(id, extra) {
        let sURLVariables = getParameters(id);
        const params = [];

        if (extra !== undefined) {
            sURLVariables = $.merge(extra.split('&'), sURLVariables);
            sURLVariables = unique(sURLVariables);
        }

        for (let i = 0; i < sURLVariables.length; i++) {
            // Protect Icinga filter syntax
            if (isFilterParameter(sURLVariables[i])) {
                params.push(sURLVariables[i]);

            }
        }

        return params.join('&');
    }

    function showHost(hostname) {
        if (cache[id].hostMarkers[hostname]) {
            const el = cache[id].hostMarkers[hostname];
            cache[id].markers.zoomToShowLayer(el, function () {
                el.openPopup();
            })
        }
    }

    function showDefaultView() {
        if (map_default_lat !== null && map_default_long !== null) {
            if (map_default_zoom !== null) {
                cache[id].map.setView([map_default_lat, map_default_long], map_default_zoom);
            } else {
                cache[id].map.setView([map_default_lat, map_default_long]);
            }
        } else {
            cache[id].map.fitWorld()
        }
    }

    function toggleFullscreen() {
        icinga.ui.toggleFullscreen();
        cache[id].map.invalidateSize();
        cache[id].fullscreen = !cache[id].fullscreen;
        if (cache[id].fullscreen) {
            $('.controls').hide();
        } else {
            $('.controls').show();
        }
    }

    // TODO: Allow update of multiple parameters
    function updateUrl(pkey, pvalue) {
        // Don't update URL if in dashlet mode
        if (dashlet) {
            return;
        }

        const $target = $('.module-map');
        const $currentUrl = $target.data('icingaUrl');
        const basePath = $currentUrl.replace(/\?.*$/, '');
        const searchPath = $currentUrl.replace(/^.*\?/, '');

        const sURLVariables = (searchPath === basePath ? [] : searchPath.split('&'));

        let updated = false;
        for (let i = 0; i < sURLVariables.length; i++) {
            // Don't replace Icinga filters
            if (isFilterParameter(sURLVariables[i])) {
                continue;
            }

            const tmp = sURLVariables[i].split('=');
            if (tmp[0] == pkey) {
                sURLVariables[i] = tmp[0] + '=' + pvalue;
                updated = true;
                break;
            }
        }

        // Parameter is to be added
        if (!updated) {
            sURLVariables.push(pkey + "=" + pvalue);
        }

        $target.data('icingaUrl', basePath + '?' + sURLVariables.join('&'));
        icinga.history.pushCurrentState();
    }

    function getWorstState(states) {
        let worstState = 0;
        let allPending = -1;
        let allUnknown = -1;
        let last = -1;

        if (states.length == 1) {
            return states[0];
        }

        for (let state of states) {
            if (state < 3) {
                if (allPending == 1) {
                    allPending = 0;
                } else if (allUnknown == 1) {
                    allUnknown = 0;
                }
            }

            if (state > 2) {
                // PENDING
                if (state == 99) {
                    if (allPending < 0 && last < 0) {
                        allPending = 1;
                    }

                    // OK -> PENDING -> UNKNOWN -> WARNING -> CRITICAL
                    state = 0.25;
                }

                // UNKNOWN
                if (state == 3) {
                    if (allUnknown < 0 && last < 0) {
                        allUnknown = 1;
                    }

                    // OK -> PENDING -> UNKNOWN -> WARNING -> CRITICAL
                    state = 0.5;
                }
            }

            if (state > worstState) {
                worstState = state;
            }

            last = state;
        }

        if (allPending == 1) {
            worstState = 99;
        }

        if (allUnknown == 1) {
            worstState = 3;
        }

        // Restore PENDING and UNKNOWN
        if (worstState == 0.25) {
            worstState = 99;
        } else if (worstState == 0.5) {
            worstState = 3;
        }

        return worstState;
    }

    function mapCenter(hostname) {
        if (cache[id].hostMarkers[hostname]) {
            const el = cache[id].hostMarkers[hostname];
            cache[id].map.panTo(cache[id].hostMarkers[hostname].getLatLng());
        }
    }

    Icinga.availableModules.map = MapModule;

}(Icinga, jQuery));
