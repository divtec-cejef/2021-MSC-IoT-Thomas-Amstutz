const api_endpoint = "https://amsttho.divtec.me/iot";
const errors = document.getElementById("errors");

/**
 * Function to produce UUID.
 * See: http://stackoverflow.com/a/8809472
 */
function generateUUID() {
  let d = new Date().getTime();
   
  if(window.performance && typeof window.performance.now === "function") {
    d += performance.now();
  }
   
  let uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
    let r = (d + Math.random() * 16) % 16 | 0;
    d = Math.floor(d / 16);
    return (c == 'x' ? r : (r&0x3|0x8)).toString(16);
  });
 
  return uuid;
}

/**
 * sends a request to the specified url from a form. this will change the window location.
 */
function post(key, get, add, upd, del) {
  let http = new XMLHttpRequest();
  let url = api_endpoint + '/api/keys';
  // http.open('POST', url);
  http.open('POST', url, true);
  
  //Send the proper header information along with the request
  http.setRequestHeader('Content-type', 'application/json');
  http.setRequestHeader('X-API-KEY', '220e0466-cbaa-4e63-93c7-b78eab67a116');
  
  http.onreadystatechange = function() { // Call a function when the state changes.
      if(http.readyState == 4 && http.status == 201) {
          console.log(JSON.parse(http.responseText));
      }
      if (http.readyState == 4 && http.status == 400) {
        errors.classList.remove("hidden");
        errors.classList.remove("info");
        errors.classList.add("errors");
        errors.innerHTML = "Cette clé est déjà utilisée";
      }
  }
  let params = { "key": key, "can_read": get, "can_add": add, "can_update": upd, "can_delete": del };
  http.send(JSON.stringify(params));
}

/**
 * Generate new key and insert into input value
 */
$('#keygen').on('click', function() {
  $('#apikey').val(generateUUID());
});

$('#addKey').on('click', function() {
  let key = $('#apikey').val();

  errors.classList.remove("hidden");
  
  if (key) {
    let get = document.getElementById("check_get").checked;
    let add = document.getElementById("check_add").checked;
    let upd = document.getElementById("check_upd").checked;
    let del = document.getElementById("check_del").checked;

    if (!get && !add && !upd && !del) {
      errors.classList.remove("info");
      errors.classList.add("errors");
      errors.innerHTML = "Aucunes options seléctionnées";
    } else {
      post(key, get, add, upd, del);
      errors.classList.remove("errors");
      errors.classList.add("info");
      errors.innerHTML = "La clé a été ajoutée.";
    }
  } else {
    errors.classList.remove("info");
    errors.classList.add("errors");
    errors.innerHTML = "Aucune clé n'a été générée";
  }
});

function copy() {
  let key = $('#apikey').val();
  if (key) {
    /* Get the text field */
    let copyText = document.getElementById("apikey");

    /* Select the text field */
    copyText.select();
    copyText.setSelectionRange(0, 99999); /* For mobile devices */

    /* Copy the text inside the text field */
    document.execCommand("copy");
  }
}