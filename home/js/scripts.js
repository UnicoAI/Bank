let markers = [];
let currentLocation = { lat: 0, lng: 0 };
let directionsService;
let directionsRenderer;
let distanceMatrixService;

function initMap() {
    directionsService = new google.maps.DirectionsService();
    directionsRenderer = new google.maps.DirectionsRenderer();
    distanceMatrixService = new google.maps.DistanceMatrixService();

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            position => {
                currentLocation = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                createMap(currentLocation);
                fetchFoodBanks(currentLocation.lat, currentLocation.lng, getRadius());
            },
            () => {
                handleLocationError(true);
            }
        );
    } else {
        handleLocationError(false);
    }
}

function createMap(location) {
    map = new google.maps.Map(document.getElementById('map'), {
        center: location,
        zoom: 12
    });

    directionsRenderer.setMap(map);

    const userLocationIcon = {
        url: "http://maps.google.com/mapfiles/ms/icons/blue-dot.png",
        scaledSize: new google.maps.Size(40, 40)
    };

    new google.maps.Marker({
        position: location,
        map: map,
        title: 'You are here!',
        icon: userLocationIcon
    });
}

function getRadius() {
    const radiusSelect = document.getElementById('radius');
    return parseInt(radiusSelect.value);
}

async function fetchFoodBanks(lat, lng, radius) {
    try {
        const response = await fetch(`api/getFoodBanks.php?lat=${lat}&lng=${lng}&radius=${radius}`);
        const data = await response.json();

        // Clear previous data
        clearMarkers();
        clearFoodBankContainer();

        if (data.status === 'success') {
            data.data.forEach(bank => {
                calculateTravelTimeAndDisplay(bank);
            });
        } else {
            displayMessageInContainer('No food banks found in your area.', 'alert-warning');
        }
    } catch (error) {
        console.error('Error fetching food banks:', error);
        displayMessageInContainer('An error occurred while fetching food banks.', 'alert-danger');
    }
}

function calculateTravelTimeAndDisplay(bank) {
    const destination = new google.maps.LatLng(bank.latitude, bank.longitude);
    const origin = new google.maps.LatLng(currentLocation.lat, currentLocation.lng);
    const travelMode = getTravelMode();

    distanceMatrixService.getDistanceMatrix({
        origins: [origin],
        destinations: [destination],
        travelMode: google.maps.TravelMode[travelMode],
    }, (response, status) => {
        if (status === 'OK' && response.rows[0].elements[0].status === 'OK') {
            const duration = response.rows[0].elements[0].duration.text;
            displayFoodBank(bank, duration);
        } else {
            console.error('Error fetching distance matrix:', status);
            displayFoodBank(bank, 'Travel time not available');
        }
    });
}

function displayFoodBank(bank, travelTime) {
    const container = document.getElementById('food-banks');
    const div = document.createElement('div');
    div.classList.add('list-group-item');

    div.innerHTML = `
           <h5>${bank.name}</h5>
        <p>${bank.address}</p>
        <p>Estimated travel time: ${travelTime}</p>
        <p>Phone: ${bank.phone}</p>
        <p>Email: <a href="mailto:${bank.email}">${bank.email}</a></p>
        <p><a href="details?id=${bank.id}" class="btn btn-primary btn-sm mt-2">👁 View Details</a></p>
        <p>${bank.referral_required ? '<span class="badge badge-warning"> 🕮 Referral Required</span>' : '<span class="badge badge-success">No Referral Needed</span>'}</p>
        <button class="btn btn-info btn-sm" onclick="focusOnBank(${bank.latitude}, ${bank.longitude})">🏱Show on Map</button>
        <button class="btn btn-success btn-sm" onclick="getDirections(${bank.latitude}, ${bank.longitude}, '${bank.name}')">🛲 Get Directions</button>
        <br/>
    `;

    container.appendChild(div);

    addMarker({
        lat: parseFloat(bank.latitude),
        lng: parseFloat(bank.longitude),
        name: bank.name,
        address: bank.address,
        phone: bank.phone,
        iconUrl: "http://maps.google.com/mapfiles/ms/icons/red-dot.png"
    });
}

function addMarker(location) {
    const marker = new google.maps.Marker({
        position: { lat: location.lat, lng: location.lng },
        map: map,
        title: location.name,
        icon: {
            url: location.iconUrl,
            scaledSize: new google.maps.Size(30, 30)
        }
    });

    const infoWindow = new google.maps.InfoWindow({
        content: `
            <div>
                <h5>${location.name}</h5>
                <p>${location.address}</p>
                <p>Phone: ${location.phone}</p>
            </div>
        `
    });

    marker.addListener('click', () => {
        infoWindow.open(map, marker);
    });

    markers.push(marker);
}

function focusOnBank(lat, lng) {
    const position = { lat: lat, lng: lng };
    map.panTo(position);
    map.setZoom(15);
}

function getDirections(destinationLat, destinationLng, destinationName) {
    const travelMode = getTravelMode();
    const destination = new google.maps.LatLng(destinationLat, destinationLng);
    const origin = new google.maps.LatLng(currentLocation.lat, currentLocation.lng);

    const request = {
        origin: origin,
        destination: destination,
        travelMode: google.maps.TravelMode[travelMode]
    };

    directionsService.route(request, (result, status) => {
        if (status === 'OK') {
            directionsRenderer.setDirections(result);
        } else {
            console.error('Directions request failed due to ' + status);
            alert('Could not get directions to ' + destinationName);
        }
    });
}

function clearMarkers() {
    markers.forEach(marker => marker.setMap(null));
    markers = [];
}

function clearFoodBankContainer() {
    const container = document.getElementById('food-banks');
    container.innerHTML = '';
}

function displayMessageInContainer(message, alertClass) {
    const container = document.getElementById('food-banks');
    container.innerHTML = `<p class="alert ${alertClass}">${message}</p>`;
}

function getTravelMode() {
    const travelModeSelect = document.getElementById('travel-mode');
    return travelModeSelect.value;
}

function updateRadius() {
    fetchFoodBanks(currentLocation.lat, currentLocation.lng, getRadius());
}

function handleLocationError(browserHasGeolocation) {
    const errorMessage = browserHasGeolocation
        ? 'Error: The Geolocation service failed.'
        : 'Error: Your browser doesn\'t support geolocation.';
    alert(errorMessage);
}

// Initialize the map when the page loads
window.onload = initMap;
