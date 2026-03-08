function AjaxController() {
    var ajaxRequest;
    try {
        ajaxRequest = new XMLHttpRequest();
    } catch (e) {
        try {
            ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try {
                ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e) {
                alert("Your browser broke!");
                return false;
            }
        }
    }
    return ajaxRequest;
}

function ShowDropDown(RequestedFromId, ShowToID, ShowFunction, NextCallingFunction, SelectedValue = null) {
    return new Promise((resolve, reject) => {
        var ajaxRequest = AjaxController();
        var RequestingURL = "../Lib/AjaxDropDownHandler.php?";
        //var RequestingURL = "http://localhost/datasql/Lib/AjaxDropDownHandler.php?";

        var RequestedValue = document.getElementById(RequestedFromId).value;
        var RequestingParams = "ShowFunction=" + ShowFunction + "&RequestingValue=" + RequestedValue + "&NextCallFunction=" + NextCallingFunction + "&SelectedValue=" + SelectedValue;
        var SendRequest = RequestingURL + RequestingParams;
        //alert(SendRequest);
        ajaxRequest.open("POST", SendRequest, true);
        ajaxRequest.send();
        //alert("ok");
        ajaxRequest.onreadystatechange = function () {
            if (ajaxRequest.readyState == 4) {
                if (ajaxRequest.status == 200) {
                    document.getElementById(ShowToID).innerHTML = ajaxRequest.responseText;
                    resolve();
                } else {
                    reject(new Error("Failed to load data"));
                }
            }
        }
    });
}

function ShowDropDown1(RequestedFromID1, RequestedFromID2, RequestedFromID3, ShowToID, ShowFunction, NextCallingFunction, SelectedValue = null) {
    return new Promise((resolve, reject) => {
        var ajaxRequest = AjaxController();
        var RequestingURL = "Lib/AjaxDropDownHandler.php?";

        var RequestedValue1 = document.getElementById(RequestedFromID1).value;
        var RequestedValue2 = document.getElementById(RequestedFromID2).value;
        var RequestedValue3 = document.getElementById(RequestedFromID3).value;
        var RequestedValue = RequestedValue1 + "|" + RequestedValue2 + "|" + RequestedValue3;

        var RequestingParams = "ShowFunction=" + ShowFunction + "&RequestingValue=" + RequestedValue + "&NextCallFunction=" + NextCallingFunction + "&SelectedValue=" + SelectedValue;
        var SendRequest = RequestingURL + RequestingParams;

        ajaxRequest.open("POST", SendRequest, true);
        ajaxRequest.send();

        ajaxRequest.onreadystatechange = function () {
            if (ajaxRequest.readyState == 4) {
                if (ajaxRequest.status == 200) {
                    document.getElementById(ShowToID).innerHTML = ajaxRequest.responseText;
                    resolve();
                } else {
                    reject(new Error("Failed to load data"));
                }
            }
        };
    });
}

function ShowDropDown2(RequestedFromID, ShowToID, ShowToID1, ShowFunction, NextCallingFunction)
{
    var ajaxRequest = AjaxController();
    var RequestingURL = "Lib/AjaxDropDownHandler.php?";
    // var RequestingURL = "http://localhost/datasql/Lib/AjaxDropDownHandler.php?";

    var RequestedValue = document.getElementById(RequestedFromID).value;
    var RequestedValue = RequestedValue;
//            alert(RequestedValue);
    var RequestingParams = "ShowFunction=" + ShowFunction + "&RequestingValue=" + RequestedValue + "&NextCallFunction=" + NextCallingFunction + "";
    var SendRequest = RequestingURL + RequestingParams;
    //alert(SendRequest);
    ajaxRequest.open("POST", SendRequest, true);
    ajaxRequest.send();
    //alert("ok");
    ajaxRequest.onreadystatechange = function () {
        if (ajaxRequest.readyState == 4) {
            //alert("Response :: "+ajaxRequest.responseText);
            document.getElementById(ShowToID).innerHTML = ajaxRequest.responseText;
            document.getElementById(ShowToID1).innerHTML = ajaxRequest.responseText;
        }
    }
}

function ShowDropDown3(RequestedFromID1, RequestedFromID2, RequestedFromID3, RequestedFromID4, ShowToID, ShowFunction, NextCallingFunction, SelectedValue = null)
{
    var ajaxRequest = AjaxController();
    var RequestingURL = "../Lib/AjaxDropDownHandler.php?";
    // var RequestingURL = "http://localhost/datasql/Lib/AjaxDropDownHandler.php?";

    var RequestedValue1 = document.getElementById(RequestedFromID1).value;
    var RequestedValue2 = document.getElementById(RequestedFromID2).value;
    var RequestedValue3 = document.getElementById(RequestedFromID3).value;
    var RequestedValue4 = document.getElementById(RequestedFromID4).value;
    var RequestedValue = RequestedValue1 + "|" + RequestedValue2 + "|" + RequestedValue3 + "|" + RequestedValue4;
    //alert(RequestedValue);
    var RequestingParams = "ShowFunction=" + ShowFunction + "&RequestingValue=" + RequestedValue + "&NextCallFunction=" + NextCallingFunction + "&SelectedValue=" + SelectedValue + "";
    var SendRequest = RequestingURL + RequestingParams;
    //alert(SendRequest);
    ajaxRequest.open("POST", SendRequest, true);
    ajaxRequest.send();
    //alert("ok");
    ajaxRequest.onreadystatechange = function () {
        if (ajaxRequest.readyState == 4) {
            //alert("Response :: "+ajaxRequest.responseText);
            document.getElementById(ShowToID).innerHTML = ajaxRequest.responseText;
        }
    }
}

function ShowDropDown4(RequestedFromID, ShowToID, ShowToID1, ShowFunction,dynamicParams, additionalParams)
{
    var ajaxRequest = AjaxController();
    var RequestingURL = "../Lib/AjaxDropDownHandler-new.php?";
    // var RequestingURL = "http://localhost/datasql/Lib/AjaxDropDownHandler.php?";

    var RequestedValue = document.getElementById(RequestedFromID).value;
   
    var RequestedValue = RequestedValue;

    var RequestingParams = "ShowFunction=" + ShowFunction + "&RequestingValue=" + RequestedValue + "&NextCallFunction=test" ;
    if (Array.isArray(dynamicParams)) {
        dynamicParams.forEach(param => {
            var paramValue = document.getElementById(param)?.value || "";
            if (paramValue) {
                RequestingParams += `&${param}=${paramValue}`;
            }
        });
    }
	if (typeof additionalParams === 'object' && !Array.isArray(additionalParams) && additionalParams !== null) {
		Object.keys(additionalParams).forEach(function(key, index) {
		  RequestingParams += `&${key}=${this[key]}`;
		}, additionalParams);
    }
	console.log("RequestingParams: " + RequestingParams);
    var SendRequest = RequestingURL + RequestingParams;
    //alert(SendRequest);
    ajaxRequest.open("POST", SendRequest, true);
    ajaxRequest.send();
    //alert("ok");
    ajaxRequest.onreadystatechange = function () {
        // console.log("Response :: "+ajaxRequest.responseText);
        if (ajaxRequest.readyState == 4) {
            //alert("Response :: "+ajaxRequest.responseText);
            document.getElementById(ShowToID).innerHTML = ajaxRequest.responseText;
        }
    }

    var RequestingParamsUser = "ShowFunction=ShowUser&RequestingValue=" + RequestedValue + "&NextCallFunction=test";
    if (Array.isArray(dynamicParams)) {
        dynamicParams.forEach(param => {
            var paramValue = document.getElementById(param)?.value || "";
            if (paramValue) {
                RequestingParamsUser += `&${param}=${paramValue}`;
            }
        });
    }
	if (typeof additionalParams === 'object' && !Array.isArray(additionalParams) && additionalParams !== null) {
		Object.keys(additionalParams).forEach(function(key, index) {
		  RequestingParamsUser += `&${key}=${this[key]}`;
		}, additionalParams);
    }
	console.log("RequestingParamsUser: " + RequestingParamsUser);
    var SendRequest1 = RequestingURL + RequestingParamsUser;
    //alert(SendRequest);
    
    var ajaxRequest1 = AjaxController();
    ajaxRequest1.open("POST", SendRequest1, true);
    ajaxRequest1.send();
    
    ajaxRequest1.onreadystatechange = function () {
        if (ajaxRequest1.readyState == 4) {
            document.getElementById(ShowToID1).innerHTML = ajaxRequest1.responseText;
            runSelect2();
        }
    }
}

// Re Initialize Select2 after ajax call
function runSelect2() {
    (function($) {
        'use strict';
        if ( $.isFunction($.fn[ 'select2' ]) ) {
            $(function() {
                $('[data-plugin-selectTwo]').each(function() {
                    var $this = $( this ),
                        opts = {};
                    var pluginOptions = $this.data('plugin-options');
                    if (pluginOptions)
                        opts = pluginOptions;
                    $this.themePluginSelect2(opts);
                });
            });
        }
    }).apply(this, [jQuery]);
}