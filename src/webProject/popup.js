// Get the modal
const modal = document.getElementById("modalBox");

// Get the button that opens the modal
const btn = document.getElementById("popup");

// Get the <span> element that closes the modal
const span = document.getElementsByClassName("close")[0];

const save = document.getElementById("saveApi");

const infos = document.getElementById("modalInfos");

// When the user clicks on the button, open the modal
btn.onclick = function() {
    modal.style.display = "block";
}

// When the user clicks on <span> (x), close the modal
span.onclick = function() {
    modal.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}

function checkKeyValidity(key) {
    let http = new XMLHttpRequest();
    http.open("GET", api_endpoint + "/api/keys/verify/" + key, true);
    http.setRequestHeader('X-API-KEY', '220e0466-cbaa-4e63-93c7-b78eab67a116');
    http.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            let responseText = this.responseText;
            if (responseText === false) {
                console.log(responseText);
                localStorage.setItem("apikey", key);
                infos.classList.remove('hidden');
                infos.classList.add('info');
                infos.innerHTML = "Clé enregistrée";
            } else {
                infos.classList.remove('hidden');
                infos.classList.add('errors');
                infos.innerHTML = "Clé invalide";
            }
        }
    };
    http.send();
}

save.onclick = function() {
    let key = $('#api').val();
    checkKeyValidity(key);
}

if (localStorage.apikey) {
    $('#api').val(localStorage.apikey);
}