(function (factory) {
    // Packaging/modules magic dance
    var L;
    if (typeof define === 'function' && define.amd) {
        // AMD
        define(['leaflet'], factory);
    } else if (typeof module !== 'undefined') {
        // Node/CommonJS
        L = require('leaflet');
        module.exports = factory(L);
    } else {
        // Browser globals
        if (typeof window.L === 'undefined') {
            throw 'Leaflet must be loaded first';
        }
        factory(window.L);
    }
}(function (L) {
    'use strict';
    L.Control.OpenCageSearch = L.Control.extend({
        options: {
            showResultIcons: false,
            collapsed: true,
            expand: 'click',
            position: 'topright',
            placeholder: 'Search...',
            errorMessage: 'Nothing found.',
            key: '',
            limit: 5
        },

        _callbackId: 0,

        initialize: function (options) {
            L.Util.setOptions(this, options);
            if (!this.options.geocoder) {
                this.options.geocoder = new L.Control.OpenCageSearch.Geocoder(this.options);
            }
        },

        setMarker: function (f) {
            this._setMarkerFunction = f;
        },

        onAdd: function (map) {
            var className = 'leaflet-control-ocd-search';
            var container = L.DomUtil.create('div', className);
            var icon_container = L.DomUtil.create('div', 'leaflet-control-ocd-search-icon', container);
            var icon = L.DomUtil.create('span', '', icon_container);
            var form = this._form = L.DomUtil.create('form', className + '-form', container);
            var input;

            this._map = map;
            this._container = container;
            input = this._input = L.DomUtil.create('input');
            input.type = 'text';
            input.placeholder = this.options.placeholder;

            L.DomEvent.addListener(input, 'keydown', this._keydown, this);

            this._errorElement = document.createElement('div');
            this._errorElement.className = className + '-form-no-error';
            this._errorElement.innerHTML = this.options.errorMessage;

            this._alts = L.DomUtil.create('ul', className + '-alternatives leaflet-control-ocd-search-alternatives-minimized');

            form.appendChild(input);
            form.appendChild(this._errorElement);
            container.appendChild(this._alts);

            L.DomEvent.addListener(form, 'submit', this._geocode, this);

            if (this.options.collapsed) {
                if (this.options.expand === 'click') {
                    L.DomEvent.addListener(icon, 'click', function (e) {
                        // TODO: touch
                        if (e.button === 0 && e.detail !== 2) {
                            this._toggle();
                        }
                    }, this);
                } else {
                    L.DomEvent.addListener(icon, 'mouseover', this._expand, this);
                    L.DomEvent.addListener(icon, 'mouseout', this._collapse, this);
                    this._map.on('movestart', this._collapse, this);
                }
            } else {
                this._expand();
            }

            L.DomEvent.disableClickPropagation(container);

            return container;
        },

        _geocodeResult: function (results) {
            L.DomUtil.removeClass(this._container, 'leaflet-control-ocd-search-spinner');
            if (results.length === 1) {
                this._geocodeResultSelected(results[0]);
            } else if (results.length > 0) {
                this._alts.innerHTML = '';
                this._results = results;
                L.DomUtil.removeClass(this._alts, 'leaflet-control-ocd-search-alternatives-minimized');
                for (var i = 0; i < results.length; i++) {
                    this._alts.appendChild(this._createAlt(results[i], i));
                }
            } else {
                L.DomUtil.addClass(this._errorElement, 'leaflet-control-ocd-search-error');
            }
        },

        markGeocode: function (result) {
            if (result.bounds) {
                this._map.fitBounds(result.bounds);
            } else {
                this._map.panTo(result.center);
            }

            if (this._setMarkerFunction) {
                this._setMarkerFunction(result);
            } else {
                if (this._geocodeMarker) {
                    this._map.removeLayer(this._geocodeMarker);
                }

                this._geocodeMarker = new L.Marker(result.center, {
                    icon: L.AwesomeMarkers.icon({
                        icon: 'globe',
                        markerColor: 'blue',
                        className: 'awesome-marker'
                    })
                })
                    .bindPopup(result.name)
                    .addTo(this._map)
                    .openPopup();

                this._geocodeMarker.on('popupclose', function (evt) {
                    this._map.removeLayer(evt.target);
                });
            }

            return this;
        },

        getMarker: function () {
            return this._geocodeMarker;
        },

        _geocode: function (event) {
            L.DomEvent.preventDefault(event);
            L.DomEvent.stopPropagation(event);

            // L.DomUtil.addClass(this._container, 'leaflet-control-ocd-search-spinner');
            this._clearResults();
            this.options.geocoder.geocode(this._input.value, this._geocodeResult, this);

            return false;
        },

        _geocodeResultSelected: function (result) {
            if (this.options.collapsed) {
                this._collapse();
            } else {
                this._clearResults();
            }

            this.markGeocode(result);
        },

        _toggle: function () {
            if (this._container.className.indexOf('leaflet-control-ocd-search-expanded') >= 0) {
                this._collapse();
            } else {
                this._expand();
            }
        },

        _expand: function () {
            L.DomUtil.addClass(this._container, 'leaflet-control-ocd-search-expanded');
            this._input.select();
        },

        _collapse: function () {
            this._container.className = this._container.className.replace(' leaflet-control-ocd-search-expanded', '');
            L.DomUtil.addClass(this._alts, 'leaflet-control-ocd-search-alternatives-minimized');
            L.DomUtil.removeClass(this._errorElement, 'leaflet-control-ocd-search-error');
        },

        _clearResults: function () {
            L.DomUtil.addClass(this._alts, 'leaflet-control-ocd-search-alternatives-minimized');
            this._selection = null;
            L.DomUtil.removeClass(this._errorElement, 'leaflet-control-ocd-search-error');
        },

        _createAlt: function (result, index) {
            var icon = result['icon'] || "globe";
            var li = document.createElement('li');
            li.classList.add("leaflet-search-result-" + icon);
            li.innerHTML = '<a href="#" data-result-index="' + index + '">' +
                (this.options.showResultIcons && result.icon ?
                    '<img src="' + result.icon + '"/>' :
                    '') +
                result.name + '</a>';
            L.DomEvent.addListener(li, 'click', function clickHandler() {
                this._geocodeResultSelected(result);
            }, this);

            return li;
        },

        _keydown: function (e) {
            var _this = this,
                select = function select(dir) {
                    if (_this._selection) {
                        L.DomUtil.removeClass(_this._selection.firstChild, 'leaflet-control-ocd-search-selected');
                        _this._selection = _this._selection[dir > 0 ? 'nextSibling' : 'previousSibling'];
                    }

                    if (!_this._selection) {
                        _this._selection = _this._alts[dir > 0 ? 'firstChild' : 'lastChild'];
                    }

                    if (_this._selection) {
                        L.DomUtil.addClass(_this._selection.firstChild, 'leaflet-control-ocd-search-selected');
                    }
                };

            switch (e.keyCode) {
                // Up
                case 38:
                    select(-1);
                    L.DomEvent.preventDefault(e);
                    break;
                // Up
                case 40:
                    select(1);
                    L.DomEvent.preventDefault(e);
                    break;
                // Enter
                case 13:
                    if (this._selection) {
                        var index = parseInt(this._selection.firstChild.getAttribute('data-result-index'), 10);
                        this._geocodeResultSelected(this._results[index]);
                        this._clearResults();
                        L.DomEvent.preventDefault(e);
                    }
            }
            return true;
        }
    });

    L.Control.openCageSearch = function (id, options) {
        return new L.Control.OpenCageSearch(id, options);
    };

    L.Control.OpenCageSearch.callbackId = 0;
    L.Control.OpenCageSearch.jsonp = function (url, params, callback, context, jsonpParam) {
        var callbackId = '_ocd_geocoder_' + (L.Control.OpenCageSearch.callbackId++);

        params[jsonpParam || 'callback'] = callbackId;
        window[callbackId] = L.Util.bind(callback, context);
        var script = document.createElement('script');
        script.type = 'text/javascript';
        script.src = url + L.Util.getParamString(params) + '&' + context.options.filter();
        script.id = callbackId;
        script.addEventListener('error', function () {
            callback({results: []});
        });
        script.addEventListener('abort', function () {
            callback({results: []});
        });
        document.getElementsByTagName('head')[0].appendChild(script);
    };

    L.Control.OpenCageSearch.Geocoder = L.Class.extend({
        options: {
            serviceUrl: 'https://api.opencagedata.com/geocode/v1/',
            geocodingQueryParams: {},
            reverseQueryParams: {},
            key: '',
            limit: 5
        },

        initialize: function (options) {
            L.Util.setOptions(this, options);
        },

        geocode: function (query, cb, context) {
            var proximity = {};
            if (context && context._map && context._map.getCenter()) {
                var center = context._map.getCenter();
                proximity.proximity = center.lat + "," + center.lng;
            }

            L.Control.OpenCageSearch.jsonp(icinga.config.baseUrl + '/map/search', L.extend({
                    q: query,
                    limit: this.options.limit,
                }, proximity, this.options.geocodingQueryParams),
                function (data) {
                    var results = [];

                    if (data.hosts.length) {
                        results = results.concat(data.hosts)
                    }

                    if (data.services.length) {
                        results = results.concat(data.services)
                    }

                    if (data.ocg.length) {
                        results = results.concat(data.ocg)
                    }

                    cb.call(context, results);
                }, this, 'jsonp');
        },

        reverse: function (location, scale, cb, context) {
            this.geocode(location, cb, context);
        }
    });

    L.Control.OpenCageSearch.geocoder = function (options) {
        return new L.Control.OpenCageSearch.Geocoder(options);
    };

    return L.Control.OpenCageSearch;
}));
