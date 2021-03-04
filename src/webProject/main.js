function fillTable(table) {
    // let measurements = getAllMeasurements();
    console.log(table);
    let out = "<tr> <th>Humidité</th> <th>Température</th> <th>Date</th> <th>Device</th> <th>Salle</th></tr>";
    table.forEach(element => {
        out += '<tr>';
        for (const key in element) {
            if (Object.hasOwnProperty.call(element, key) && key != "res_seq") {
                const value = element[key];
                out += '<td>' + value + '</td>';
            }
        }
        out += '</tr>';
    });
    document.getElementById("ValueTable").innerHTML = out;
}

function getMeasures() {
    let httpGetAll = new XMLHttpRequest();
    httpGetAll.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            let content = JSON.parse(this.responseText);
            fillTable(content);
        }
    };
    httpGetAll.open("GET", "https://amsttho.divtec.me/iot/api/values", true);
    httpGetAll.send();
}

function getLatestTemp() {
    let httpGetLast = new XMLHttpRequest();
    httpGetLast.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            let content = JSON.parse(this.responseText);
            document.getElementById("latestTemp").innerHTML = content['res_temperature'] + "°C, " + content['res_humidity'] + "%";
        }
    };
    httpGetLast.open("GET", "https://amsttho.divtec.me/iot/api/values/latest", true);
    httpGetLast.send();
}

function init() {
    getLatestTemp();
    getMeasures();
}

init();
setInterval(getMeasures, 10000);
setInterval(getLatestTemp, 10000);