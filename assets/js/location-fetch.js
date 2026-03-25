// Removed search field and search result functionality
const mapContainer = document.querySelector("#map");

let map, locationApi = `/wp-json/map/v1/locations`, searchRaidus = 20, position = { lat: 52.103359711221245, lng: 5.2462851862407405 }, markers = [], openInfoWindow;

// Removed search field and search result functionality

async function initMap(){
    const { Map, InfoWindow } = await google.maps.importLibrary("maps");
    const { MarkerClusterer } = markerClusterer;

    map = new google.maps.Map(mapContainer, {
        center: position,
        zoom: 8,
        styles: [{ elementType: "all", stylers: [{ saturation: "-100" }]}]
    });

    try{
        let response = await fetch(locationApi);
        let markerPoints = await response.json();
        markerPoints.forEach(markerAddFun);
        new MarkerClusterer({ markers, map,  renderer: {
            render: ({ count, position }) => {
                return new google.maps.Marker({
                        position,
                        icon: {
                            url: "https://www.podotherapiehermanns.nl/wp-content/uploads/2025/03/m5.png", // Replace with your custom cluster image URL
                            scaledSize: new google.maps.Size(50, 50), // Adjust size
                        },
                        label: {
                            text: count.toString(),
                            color: "white",
                            fontSize: "13px",
                            fontWeight: "bold",
                        },
                    });
            }
        }});
    }catch(err){
        console.log("error");
    }
}

window.addEventListener("DOMContentLoaded", initMap);
initMap();

function getCoordinates(address) {
    return new Promise((resolve, reject) => {
        const geocoder = new google.maps.Geocoder();
        geocoder.geocode({ address: address }, (results, status) => {
            if (status === "OK") {
                const location = results[0].geometry.location;
                resolve({ lat: location.lat(), lng: location.lng() });
            }else{
                reject(false);
            }
        });
    })
}

function markerAddFun(eachMarker, index){
    // Only add markers to the map, no search result DOM
    let marker = new google.maps.Marker({
        position: { lat: eachMarker?.lat, lng: eachMarker?.lng },
        title: eachMarker?.title || "Location",
        icon: {
            url: "https://www.podotherapiehermanns.nl/wp-content/uploads/2025/02/markerpurple-copy.png",
            scaledSize: new google.maps.Size(25, 25),
        },
    });

    marker.setMap(map);
    markers.push(marker);

    const infoWin = new google.maps.InfoWindow({
        content: `
        <div class="info_window">
            <div class="title">${eachMarker?.title}</div>
            <div class="address">${eachMarker?.address}</div>
            <a href="${eachMarker?.page_url}" target="_blank" class="sg_page_url">​Bekijk locatie ></a>
        </div>
        `,
    })

    marker.addListener("click", function(){
        if(openInfoWindow) openInfoWindow.close();
        openInfoWindow = infoWin;
        infoWin.open(map, marker);
    })
}