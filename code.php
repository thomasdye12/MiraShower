<?php


// 50097

class MiraShower
{

    public $device;

    public function __construct($device)
    {
        $this->device = $device;
    }


    public function update($eventUpdate)
    {

        // return $this->ChildSensor_update($eventUpdate);

    }



    public function getdeviceupdate()
    {

        $JWT = $this->getJWT();
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api-mirashowers-uk.kohler.io/devices/api/v1/device-management/plt-state/act-sio32263zh',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Host: api-mirashowers-uk.kohler.io',
                'Accept-Language: en-GB;q=1.0',
                'Connection: keep-alive',
                'Accept: application/json',
                'User-Agent: Mira App/4.5 (uk.co.mirashowers; build:13; iOS 14.3.0) Alamofire/5.4.3',
                'Authorization: Bearer ' . $JWT,
                'Ocp-Apim-Subscription-Key: ',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $response = json_decode($response, true);
        $response = $response['state'];
        $valve1 = $response['valve1'];

        $events = [];
        $events[] = array("atFlow", $valve1['atFlow']);
        $events[] = array("atTemp", $valve1['atTemp']);
        $events[] = array("flowSetpoint", $valve1['flowSetpoint']);
        $events[] = array("out1", $valve1['out1']);
        $events[] = array("out2", $valve1['out2']);
        // pauseFlag
        $events[] = array("pauseFlag", $valve1['pauseFlag']);
        // outletOne outletTemp
        $events[] = array("outletOneoutletTemp", $valve1['outletOne']['outletTemp']);
        // outletOne outletFlow
        $events[] = array("outletOneoutletFlow", $valve1['outletOne']['outletFlow']);
        // outletTwo
        $events[] = array("outletTwooutletTemp", $valve1['outletTwo']['outletTemp']);
        $events[] = array("outletTwooutletFlow", $valve1['outletTwo']['outletFlow']);
        // temperatureSetpoint
        $events[] = array("temperatureSetpoint", $valve1['temperatureSetpoint']);
        // errorCode
        $events[] = array("errorCode", $valve1['errorCode']);


        // playbackState


        // loop over the events and send them if the state has changed
        foreach ($events as $event) {
            $sysevent = array(
                "name" => $event[0],
                "value" => $event[1],
                "descriptionText" => "",
                "type" => "physical",
                "isStateChange" => true
            );

            if ($sysevent["value"] != $this->device["attributes"][$sysevent["name"]]) {
                sendEvent($this->device, $sysevent);
            }
        }
    }
    // Controling the shower value means 
    // 01542400

    //  01 - 
    // 54 - temperature, 00 off, C2 full 45C
    // 24 - flow rate, 00 off, 40 full 16L/min,
    // 00 - the shower you want, 0 off, 1 one on , 2 two on, 3 both on






    // function for the JWT 

    private function getJWT()
    {

        return $this->device["System"]["JWT"];
    }

    private function tenantId()
    {

        return $this->device["System"]["tenantId"];
    }

    // function to send update to shower with vlaue 

    private function sendShowerStatewithValue($value)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api-mirashowers-uk.kohler.io/platform/api/v1/commands/plt/solowritesystem',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{"pltValveControlModel":{"secondaryValve4":"00000000","secondaryValve7":"00000000","secondaryValve3":"00000000","secondaryValve6":"00000000","secondaryValve2":"00000000","secondaryValve5":"00000000","primaryValve1":"' . $value . '","secondaryValve1":"00000000"},"tenantId":"'.$this->tenantId() .'","sku":"PLT","deviceId":"act-sio32263zh"}',
            CURLOPT_HTTPHEADER => array(
                'Host: api-mirashowers-uk.kohler.io',
                'Accept-Language: en-GB;q=1.0',
                'Content-Length: 346',
                'Connection: keep-alive',
                'Accept: application/json',
                'User-Agent: Mira App/4.5 (uk.co.mirashowers; build:13; iOS 14.3.0) Alamofire/5.4.3',
                'Authorization: Bearer ' . $this->getJWT(),
                'Ocp-Apim-Subscription-Key: ',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;
    }


    // function to turn off shower 

    public function turnOff()
    {
        $this->sendShowerStatewithValue("00000000");
    }
    // send raw value to shower

    public function sendRawValue($value)
    {   
        // validate the length of the value
        if(strlen($value) != 8){
            return;
        }
        $this->sendShowerStatewithValue($value);
    }
}
