// round(2.6362, 2) => 2.63
var round = function (number, precission) {
    var precissionHelper = Math.pow(10, precission);
    return Math.round(number * precissionHelper) / precissionHelper;
};

// boundsToString(map.getBounds()) => {"_southWest":{"lat":58.96,"lng":10.37},"_northEast":{"lat":63.94,"lng":34.23}}
var boundsToString = function (bounds) {
    bounds._southWest.lat = round(bounds._southWest.lat, 2);
    bounds._southWest.lng = round(bounds._southWest.lng , 2);
    bounds._northEast.lat = round(bounds._northEast.lat , 2);
    bounds._northEast.lng = round(bounds._northEast.lng , 2);

    return JSON.stringify(bounds);
};

// like $_GET['name'] in PHP
var get = function (name) {
    var results = new RegExp('[\?&]' + name + '=([^&]*)').exec(window.location.href);
    if (results == null) {
        return null;
    }

    return results[1] || 0;
};
