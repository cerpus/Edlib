<!DOCTYPE html>
<html>
<head>
    <title>Safari session test</title>
</head>
<body>
<script src="{{ mix('js/bootstrap.js') }}"></script>
<script>
    (function () {
    	var id = "K" + Math.round(Math.random() * 1000000000);
    	var value = "V" + Math.round(Math.random() * 1000000000);
    	jQuery.ajax({
            "url": "/v1/sessiontest/"+id,
            "method": "POST",
            "data": {
				"value": value,
                "_token": <?php echo json_encode(csrf_token()); ?>
			},
            "dataType": "json",
            "success": function (postdata) {
            	jQuery.ajax({
                    "url": "/v1/sessiontest/"+id,
                    "method": "GET",
					"dataType": "json",
                    "success": function (getdata) {
                    	if (value == getdata.value) {
                    		if (typeof(window.parent) != 'undefined' && window.parent != window) {
                    			window.parent.postMessage({'type': 'session-detect', 'result': 'ok'}, '*');
                            } else {
								alert("Session test needs a window.parent object to send the results - Test ok");
							}
                        } else {
							if (typeof(window.parent) != 'undefined' && window.parent != window) {
								window.parent.postMessage({'type': 'session-detect', 'result': 'fail'}, '*');
							} else {
								alert("Session test needs a window.parent object to send the results - Test failed");
							}
                        }
                    }
                });
            }
        })
    })();
</script>
</body>
