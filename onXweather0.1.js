/*
on{X} Detailed Weather Alarm
developed by Czero Slim
https://github.com/CzeroSlim/onX-Weather
*/

var time = "8:00 AM";

function getLocation(){
//location code borrowed from the onX team    
 var listener = device.location.createListener('CELL', 5000); 
 listener.on('changed', function (signal) {
 console.log('Latitude/longitude attained ' + signal.location.latitude + "," + signal.location.longitude);
 listener.stop();
 getWeather(signal.location.latitude, signal.location.longitude);
 });
 listener.start();
}

function getWeather(lat, lon){
    feeds.weather.get(
{ 
  location:  lat+','+lon,
  locationtype: 'latlon',
  unittype:  'i',
  days: 0
},
function onSucess (weather, textStatus, response) {
    console.log('Weather feed request successful');
    var mode='notification';
    var click=function(){
        /*
        resolves through google maps api to reverse geocode the  latitude/longitude
        coordinates to attain a usable address, then redirects to the corresponding Accuweather mobile site.
        eventually this will all be done client-side, however I couldn't get device.ajax to work properly
        */
        device.browser.launch('http://onxweather.t15.org?lat='+lat+'&lon='+lon);
        console.log("Detailed forecast launched");
    };
    var title='Forecast: '+weather.forecasts[0].skyTextDay;
    var text=weather.forecasts[0].rain+'% chance of rain, High '+weather.forecasts[0].temperature.high+'°F/Low '+weather.forecasts[0].temperature.low+'°F';
    notify({mode:mode, click:click, text:text, title:title});

},
function onError (response, textStatus) {
  console.error('Weather error from script');
}
  );
}

function notify(options){
    if (options.mode=='notification'){
    var notification = device.notifications.createNotification(options.title);
    notification.content = options.text;
    notification.on('click', options.click);
    notification.show();
    console.log("Notification sent")
    }
}
//Scheduler borrowed from the onX team
 function parseTime(timeString) {
        if (timeString === '') {
            return null;
        }

        var time = timeString.match(/^(\d+)(:(\d\d))?\s*((a|(p))m?)?$/i);

        if (time === null) {
            return null;
        }

        var hours = parseInt(time[1],10);
        if (hours === 12 && !time[6]) {
            hours = 0;
        } else {
            hours += (hours < 12 && time[6]) ? 12 : 0;
        }

        var d = new Date();
        d.setHours(hours);
        d.setMinutes(parseInt(time[3], 10) || 0);
        d.setSeconds(0, 0);
        return d;
    }

    var now = new Date(),
        timeString = time,
        firstTime = parseTime(timeString);

    if (firstTime === null) {
        console.error('Invalid time format: ' + timeString + '. Expected: hh:mm or hh:mm AM/PM');
    } else {
        if (firstTime.getTime() < now) {
            firstTime.setDate(firstTime.getDate() + 1);
        }
        device.scheduler.setTimer(
            {
                name: 'dailyWeatherTimer',
                time: firstTime.getTime(),
                interval: 'day',
                repeat: true,
                exact: true
            },
            getLocation);
    }
    
    //Test Functionality
    //getLocation();