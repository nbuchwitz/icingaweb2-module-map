(function (Icinga) {

    function colorMarker(color) {
        img_base = icinga.config.baseUrl + '/img/map/';

        return L.icon({
                iconUrl: img_base + 'marker-icon-' + color + '.png',
                //iconSize: [20, 20],
                //shadowSize: [25, 18],
                shadowUrl: img_base + 'marker-shadow.png',
            }
        );
    }

    function zoomAll(id) {
        cache[id].map.fitBounds(cache[id].markers.getBounds(), {padding: [15, 15]});
    }

    function updateUrl(pkey, pvalue) {
        // don't update url if in dashlet mode
        if (dashlet) {
            return;
        }

        var params = {};
        var link = window.location.href.replace(/(map\?|map\/\?|&)+([^=&]+)=([^&#]*)/gi, function (m, prefix, key, value) {
            params[key] = value;
            if (key == pkey) {
                value = pvalue
            }
            return prefix + key + "=" + value
        });

        if (!(pkey in params)) {
            link = window.location.href.replace(/(map$|map\?|map\/\?|&)+([^#]*)/gi, function (m, prefix, list) {
                // url without parameters and ? => append ?
                if (prefix.charAt(prefix.length - 1) == "p") {
                    prefix += "?"
                }

                var param = ""
                if (list != "") {
                    param = list + "&"
                }

                param += pkey + "=" + pvalue

                return prefix + param
            });
        }

        window.history.replaceState(history.state, "Icinga2", link)
    }

    function getWorstState(states) {
        var worstState = 0
        var allPending = -1
        var allUnknown = -1
        var last = -1

        for (var i = 0, len = states.length; i < len; i++) {
            var state = states[i]
            if (state < 3) {
                if (allPending == 1) {
                    allPending = 0
                } else if (allUnknown == 1) {
                    allUnknown = 0
                }
            }

            if (state > 2) {
                // PENDING
                if (state == 99 && allPending < 0 && last < 0) {
                    allPending = 1
                }

                // UNKNOWN
                if (state == 3 && allUnknown < 0 && last < 0) {
                    allUnknown = 1
                }

                // treat PENDING and UNKNOWN at the moment as OK
                state = 0
            }

            if (state > worstState) {
                worstState = state
            }

            last = state
        }

        if (allPending == 1) {
            worstState = 99
        }

        if (allUnknown == 1) {
            worstState = 3
        }

        return worstState;
    }

    function mapCenter(hostname) {
        console.log(hostname)
        if (cache[id].hostMarkers[hostname]) {
            var el = cache[id].hostMarkers[hostname];
            cache[id].map.panTo(cache[id].hostMarkers[hostname].getLatLng())
        }
    }

    var cache = {};

    var Map = function (module) {
        this.module = module;
        this.initialize();
        this.timer;
        this.module.icinga.logger.debug('Map module loaded');
    };

    Map.prototype = {

        initialize: function () {
            this.timer = {};
            this.module.on('rendered', this.onRenderedContainer);
            this.registerTimer()
        },

        registerTimer: function (id) {
            this.timer = this.module.icinga.timer.register(
                this.updateAllMapData,
                this,
                10000
            );
            return this;
        },

        updateAllMapData: function () {
            var _this = this;

            if (cache.length == 0) {
                this.module.icinga.timer.unregister(this.timer);
                return this
            }

            $.each(cache, function (id) {
                if (!$('#map-' + id).length) {
                    delete cache[id]
                } else {
                    _this.updateMapData({id: id})
                }
            });
        },

        updateMapData: function (parameters) {
            var id = parameters.id;
            var show_host = parameters.show_host;

            function showHost(hostname) {
                if (cache[id].hostMarkers[hostname]) {
                    var el = cache[id].hostMarkers[hostname];
                    cache[id].markers.zoomToShowLayer(el, function () {
                        el.openPopup();
                    })
                }
            }


            function removeOldMarkers(id, data) {
                // remove old markers
                $.each(cache[id].hostMarkers, function (hostname, d) {
                    if (!data[hostname]) {
                        cache[id].markers.removeLayer(d);
                        delete cache[id].hostMarkers[hostname];
                    }
                });
            }

            function processData(json) {
                removeOldMarkers(id, json)

                $.each(json, function (hostname, data) {
                    if (data.length < 1 || data['coordinates'] == "") {
                        console.log('found empty coordinates: ' + data)
                        return true

                    }

                    var hostState = data['host_state'];
                    var icon;
                    var services;
                    var states = [];

                    states.push((hostState == 1 ? 2 : hostState ))

                    services = '<div class="map-popup-services">';
                    services += '<h1><span class="icon-services"></span> Services</h1>';
                    services += '<div class="scroll-view">';
                    services += '<table class="icinga-module module-monitoring">';
                    services += '<tbody>';

                    $.each(data['services'], function (service_display_name, service) {
                        states.push(service['service_state'])

                        services += '<tr>';

                        services += '<td class="';
                        services += "state-col";
                        services += " state-" + service_status[service['service_state']].toLowerCase();
                        services += "" + (service['service_acknowledged'] == 1 ? " handled" : "");
                        services += '">';
                        services += '<div class="state-label">';
                        services += service_status[service['service_state']];
                        services += '</div>';
                        services += '</td>';

                        services += '<td>';
                        services += '<div class="state-header">';
                        services += service_display_name;
                        services += '</td>';

                        services += '</tr>';
                    });

                    services += '</tbody>';
                    services += '</table>';
                    services += '</div>';
                    services += '</div>';

                    var worstState = getWorstState(states)
                    switch (parseInt(worstState)) {
                        case 0:
                            icon = colorMarker("green")
                            break
                        case 1:
                            icon = colorMarker("orange")
                            break
                        case 2:
                            icon = colorMarker("red")
                            break
                        case 3:
                            icon = colorMarker("violet")
                            break
                        default:
                            icon = colorMarker("blue")
                    }

                    var host_icon = "";
                    if (data['host_icon_image'] != "") {
                        host_icon = '<img src="' + icinga.config.baseUrl + '/img/icons/'
                            + data['host_icon_image']
                            + '"'
                            + (( data['host_icon_image_alt'] != "" ) ? ' alt="' + data['host_icon_image_alt'] + '"' : '')
                            + ' class="host-icon-image icon">';
                    }

                    var host_display_name = (data['host_display_name'] ? data['host_display_name'] : hostname )

                    var info = '<div class="map-popup">';
                    info += '<h1>';
                    info += '<a class="detail-link" data-hostname="' + hostname + '" data-base-target="_next" href="'
                        + icinga.config.baseUrl
                        + '/monitoring/host/show?host='
                        + hostname
                        + '">';
                    info += ' <span class="icon-eye"></span> ';
                    info += '</a>';
                    info += host_display_name + '</h1>';

                    info += services;
                    info += '</div>';

                    var marker;

                    if (cache[id].hostMarkers[hostname]) {
                        marker = cache[id].hostMarkers[hostname];
                        marker.options.state = worstState;
                        marker.setIcon(icon);
                    } else {
                        marker = L.marker(data['coordinates'],
                            {
                                icon: icon,
                                title: host_display_name,
                                id: hostname,
                                state: worstState,
                            }).addTo(cache[id].markers);

                        cache[id].hostMarkers[hostname] = marker;
                        cache[id].hostData[hostname] = data
                    }

                    marker.bindPopup(info);
                });

                cache[id].markers.refreshClusters()
                cache[id].map.spin(false)

                if (show_host != "") {
                    showHost(show_host);
                    show_host = ""
                    //} else if (!dashlet) {
                    //    zoomAll(id)
                }
            }

            // get host objects
            $.getJSON(icinga.config.baseUrl + '/map/data/points', processData);
        },

        onRenderedContainer: function (event) {
            cache[id] = {};
            cache[id].markers = new L.MarkerClusterGroup({
                iconCreateFunction: function (cluster) {
                    var childCount = cluster.getChildCount();

                    var states = []
                    $.each(cluster.getAllChildMarkers(), function (id, el) {
                        states.push(el.options.state)
                    })

                    var worstState = getWorstState(states)
                    var c = ' marker-cluster-' + worstState;

                    return new L.DivIcon({
                        html: '<div><span>' + childCount + '</span></div>',
                        className: 'marker-cluster' + c,
                        iconSize: new L.Point(40, 40)
                    });
                }
            });
            cache[id].hostMarkers = {};
            cache[id].hostData = {};

            cache[id].map = L.map('map-' + id)

            if (map_default_lat !== null && map_default_long !== null) {
                if (map_default_zoom !== null) {
                    cache[id].map.setView([map_default_lat, map_default_long], map_default_zoom);
                } else {
                    cache[id].map.setView([map_default_lat, map_default_long]);
                }

            } else {
                cache[id].map.fitWorld()
            }

            if (!dashlet) {
                L.easyButton({
                    states: [{
                        icon: 'icon-dashboard', title: 'Add To dashboard', onClick: function (btn, map) {
                            var dashletUri = window.location.href.replace(new RegExp(".*" + icinga.config.baseUrl + "/", "g"), "")
                            var uri = icinga.config.baseUrl + "/" + "dashboard/new-dashlet?url=" + encodeURIComponent(dashletUri)

                            window.open(uri, "_self")
                        }
                    },]
                }).addTo(cache[id].map);

                //L.easyButton({
                //    states: [{
                //        icon: 'icon-resize-full', title: 'Show all', onClick: function (btn, map) {
                //            zoomAll(id)
                //       }
                //    },]
                //}).addTo(cache[id].map);

                L.control.locate({
                    icon: 'icon-pin'
                }).addTo(cache[id].map);

                cache[id].map.on('moveend', function (e) {
                    var center = cache[id].map.getCenter()

                    var lat = center.lat
                    var lng = center.lng

                    updateUrl('default_lat', lat)
                    updateUrl('default_long', lng)
                })
                cache[id].map.on('zoomend', function (e) {
                    var zoomLevel = cache[id].map.getZoom()
                    updateUrl('default_zoom', zoomLevel)
                })
                cache[id].map.on('click', function (e) {
                    // TODO: any other way?
                    var id = e.target._container.id.replace('map-', '');

                    if (e.originalEvent.ctrlKey) {
                        var coord = 'vars.geolocation = "'
                            + e.latlng.lat.toFixed(6)
                            + ','
                            + e.latlng.lng.toFixed(6)
                            + '"';
                        var marker;
                        marker = L.marker(e.latlng, {icon: colorMarker("blue")});
                        marker.bindPopup("<h1>selected coordinates:</h1><pre>" + coord + "</pre>");
                        marker.addTo(cache[id].markers);
                        marker.on('popupclose', function (evt) {
                            cache[id].markers.removeLayer(marker);
                        });
                        cache[id].markers.zoomToShowLayer(marker, function () {
                            marker.openPopup();
                        })
                    }
                });
            }


            var osm = L.tileLayer('//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                subdomains: ['a', 'b', 'c'],
                maxZoom: map_max_zoom,
                minZoom: map_min_zoom,
            });
            osm.addTo(cache[id].map);

            cache[id].markers.addTo(cache[id].map);

            cache[id].map.spin(true)
            this.updateMapData({id: id, show_host: map_show_host})

        },
    };

    Icinga.availableModules.map = Map;

}(Icinga));
